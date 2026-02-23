<?php

namespace Demo\Api\Classes\Repositories;


use Bitrix\Main\Application;
use Bitrix\Catalog\StoreTable;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;

class StoreRepository
{
    private $repository;
    private $stockType = 'number';
    private $userRole;
    /**
     * Получить все склады
     * @return array
     * @throws ObjectPropertyException|ArgumentException|SystemException
     */

    public function __construct($repository = \Bitrix\Catalog\StoreTable::class)
    {
        $this->repository = $repository;
        return $this;
    }

    public function getAll(): array
    {
        $result = [];

        $stores = StoreTable::getList([
            'select' => ['ID', 'TITLE', 'ADDRESS', 'ACTIVE', 'XML_ID'],
            'filter' => ['=ACTIVE' => 'Y'],
            'order' => ['ID' => 'ASC']
        ]);

        while ($store = $stores->fetch()) {
            $result[] = $store;
        }

        return $result;
    }

    /**
     * Получить склад по ID
     * @param int $id
     * @return array|null
     */
    public function getById(int $id): ?array
    {
        $store = StoreTable::getById($id)->fetch();
        return $store ?: null;
    }

    public function getByName(string|array $name): ?array
    {
        $stores =  StoreTable::getList(
            [
                'select' => ['ID'],
                'filter' => ['TITLE' => $name]
            ]
        )->fetchAll();

        return $stores ?? null;
    }
    /**
     * 
     * Получить склады с остатками
     * @param int $iblockID
     * @return array
     * @throws ObjectPropertyException|ArgumentException|SystemException
     */

    public function getStoresWithStock(int $iblockID): array
    {

        /* Можно переписать через ORM. Используя runtime 
        * вида runtime =>[
        'STOREAMOUNTS'=>
        [
            'data_type'=>\Bitrix\Catalog\StoreProductTable::class,
            'reference'=>[ 
            '=this.ID'=>'ref.STORE_ID',
            '>AMOUNT'=> new \Bitrix\Main\DB\SqlExpression('?i',0)
            ],
        ]
        ]
        И дальше после fetchAll циклом отфильтровать по полю STOREAMOUNTS, если что-то прилетит
        *
        */

        $stores = \Bitrix\Catalog\StoreTable::getList(
            [
                'select' => ['ID'],
                'filter' => ['ACTIVE' => 'Y']
            ]
        )->fetchAll();
        $stringSQL = " AND s.ID IN (";
        foreach ($stores as $storeID) {
            $stringSQL .= $storeID['ID'] . ',';
        }

        $connection = Application::getConnection();

        $sql = "
                SELECT s.ID, s.TITLE, s.ADDRESS, s.ACTIVE, s.XML_ID,sp.PRODUCT_ID ,SUM(sp.AMOUNT) as TOTAL_AMOUNT
                FROM b_catalog_store s
                INNER JOIN b_catalog_store_product sp ON sp.STORE_ID = s.ID
                INNER JOIN b_iblock_element ie ON sp.PRODUCT_ID = ie.ID 
                INNER JOIN b_catalog_price as p ON p.PRODUCT_ID = ie.ID
                WHERE s.ACTIVE = 'Y' AND ie.IBLOCK_ID = $iblockID AND p.PRICE>0
                " . $stringSQL . " 
                GROUP BY s.ID, s.TITLE, s.ADDRESS, s.ACTIVE, s.XML_ID
                HAVING TOTAL_AMOUNT >= 0
                ORDER BY s.TITLE ASC
        ";


        $result = $connection->query($sql);

        $stores = [];
        while ($row = $result->fetch()) {
            $stores[] =
                [
                    'id' => $row['ID'],
                    'value' => $row['TITLE']
                ];
        }

        return $stores;
    }

    public function getItemStorages(int $itemId)
    {
        if ($itemId) {
            $stores = StoreTable::getList(
                [
                    'select' => ['ID'],
                    'filter' => ['ACTIVE' => 'Y'],
                    'cache' => [
                        'ttl' => 3600,
                        'cache_joins' => true
                    ]
                ]
            )->fetchAll();
            $storesFilter = array_column($stores, 'ID');

            $getStoreProducts = \Bitrix\Catalog\StoreProductTable::getList([
                'select' => [
                    'PRODUCT_ID',
                    'STORE_ID',
                    'AMOUNT',
                    'STORENAME' => 'STORAGE.TITLE'
                ],
                'filter' => ['>AMOUNT' > 0, 'PRODUCT_ID' => $itemId, 'STORAGE.ID' => $storesFilter, 'STORAGE.ACTIVE' => 'Y'],
                'runtime' =>
                [
                    'STORAGE' =>
                    [
                        'data_type' => \Bitrix\Catalog\StoreTable::class,
                        'reference' =>
                        [
                            'this.STORE_ID' => 'ref.ID',
                        ]
                    ]
                ]
            ])->fetchAll();

            $result = [];
            foreach ($getStoreProducts as $product) {
                $result[] = [
                    'name' => $product['STORENAME'],
                    'goods' => str_replace('.', ',', $product['AMOUNT']),
                    'stock' => $product['AMOUNT'],
                ];
            }
            return $result;
        } else {
            return [];
        }
    }

    public function getItemStock(int $productId): int
    {
        $getStoreProducts = \Bitrix\Catalog\StoreProductTable::getList([
            'select' => [
                'SUM_AMOUNT' => 'TOTAL_AMOUNT'
            ],
            'filter' => ['PRODUCT_ID' => $productId],
            'runtime' => [
                new \Bitrix\Main\Entity\ExpressionField('TOTAL_AMOUNT', 'SUM(%s)', ['AMOUNT'])
            ]
        ])->fetch();

        return (int)($getStoreProducts['SUM_AMOUNT'] ?? 0);
    }
}
