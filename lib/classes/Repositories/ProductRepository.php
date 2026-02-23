<?php

namespace Demo\Api\Classes\Repositories;

class ProductRepository
{
    public array $filter = [];
    public int $limit;
    public int $offset;
    public array $sort;
    private string $repository;

    public function __construct(array $filter = [], int $limit = 20, int $offset = 0, array $sort = [], $repository = '')
    {
        $this->filter = $filter;
        $this->limit = $limit;
        $this->offset = $offset;
        $this->sort = !empty($sort) ? $sort : ['ID' => 'ASC'];

        if (empty($repository)) {
            $this->repository = \Bitrix\Iblock\ElementTable::class;
        } else {
            $this->repository = $repository;
        }
    }


    public function getCount(array $filter = []): int
    {
        return $this->repository::getCount(array_merge($this->filter, $filter));
    }

    public function getAll(array $filter = []): array
    {
        $products = $this->repository::getList([
            'select' => ['ID', 'NAME', 'PREVIEW_TEXT', 'IBLOCK_SECTION_ID'],
            'filter' => array_merge($this->filter, $filter),
            'limit'  => $this->limit,
            'offset' => $this->offset,
            'order'  => $this->sort,
        ]);

        $result = [];
        $ids = [];

        while ($row = $products->fetch()) {
            $result[$row['ID']] = $row + ['PROPERTIES' => []];
            $ids[] = $row['ID'];
        }
        try {
            $props = \Bitrix\Iblock\ElementPropertyTable::getList([
                'select' => [
                    'IBLOCK_ELEMENT_ID',
                    'VALUE',
                    'PROPERTY_CODE' => 'PROP.CODE',
                    'PROPERTY_NAME' => 'PROP.NAME',
                ],
                'filter' => [
                    '=IBLOCK_ELEMENT_ID' => $ids
                ],
                'runtime' => [
                    'PROP' => [
                        'data_type' => \Bitrix\Iblock\PropertyTable::class,
                        'reference' => ['=this.IBLOCK_PROPERTY_ID' => 'ref.ID']
                    ]
                ]
            ]);

            while ($row = $props->fetch()) {

                $elementId = $row['IBLOCK_ELEMENT_ID'];

                if (!isset($result[$elementId])) {
                    continue; // на всякий случай
                }

                $code = $row['PROPERTY_CODE'];

                if (!$code) {
                    continue;
                }

                $result[$elementId]['PROPERTIES'][$code][] = [
                    'NAME'  => $row['PROPERTY_NAME'],
                    'VALUE' => $row['VALUE'],
                ];
            }
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }

        return array_values($result);
    }

    public function getSingleProductByID(int $id): array
    {

        $runtime =
            [
                'ELEMENTPROPS' =>
                [
                    'data_type' => \Bitrix\Iblock\ElementPropertyTable::class,
                    'reference' => ['=this.ID' => 'ref.IBLOCK_ELEMENT_ID']
                ],
                'PROPS' =>
                [
                    'data_type' => \Bitrix\Iblock\PropertyTable::class,
                    'reference' => ['=this.ELEMENTPROPS.IBLOCK_PROPERTY_ID' => 'ref.ID']
                ],
            ];

        $result = $this->repository::getList([
            'select' => [
                'ID',
                'NAME',
                'PREVIEW_TEXT',
                'IBLOCK_SECTION_ID',
                'SECTION_NAME' => 'IBLOCK_SECTION.NAME',
                'DATE_CREATE',
                'TIMESTAMP_X',
                'PROP_ID' => 'ELEMENTPROPS.IBLOCK_PROPERTY_ID',
                'PROP_VALUE' => 'ELEMENTPROPS.VALUE',
                'PROP_CODE' => 'PROPS.CODE',
                'PROP_NAME' => 'PROPS.NAME',
            ],
            'filter' => array_merge($this->filter, ['=ID' => $id]),
            'runtime' => $runtime,
        ]);

        $rows = $result->fetchAll();

        if (!$rows) {
            return [];
        }

        $product = $rows[0];

        $product['PROPERTIES'] = [];
        foreach ($rows as $row) {
            if ($row['PROP_CODE']) {
                $product['PROPERTIES'][$row['PROP_CODE']][] = [
                    'ID' => $row['PROP_ID'],
                    'NAME' => $row['PROP_NAME'],
                    'VALUE' => $row['PROP_VALUE'],
                ];
            }
        }
        return $product;
    }


    public function getSingleProductByXMLID(string $xmlID): array
    {
        $runtime =
            [
                'ELEMENTPROPS' =>
                [
                    'data_type' => \Bitrix\Iblock\ElementPropertyTable::class,
                    'reference' => ['=this.ID' => '=ref.IBLOCK_ELEMENT_ID']
                ],
                'PROPS' =>
                [
                    'data_type' => \Bitrix\Iblock\PropertyTable::class,
                    'reference' => ['=this.ELEMENTPROPS.IBLOCK_PROPERTY_ID' => '=ref.ID']
                ],
            ];

        $result = $this->repository::getList([
            'select' => [
                'ID',
                'NAME',
                'PREVIEW_TEXT',
                'IBLOCK_SECTION_ID',
                'SECTION_NAME' => 'IBLOCK_SECTION.NAME',
                'DATE_CREATE',
                'TIMESTAMP_X',

                'PROP_ID' => 'ELEMENTPROPS.IBLOCK_PROPERTY_ID',
                'PROP_VALUE' => 'ELEMENTPROPS.VALUE',
                'PROP_CODE' => 'PROPS.CODE',
                'PROP_NAME' => 'PROPS.NAME',
            ],
            'filter' => array_merge($this->filter, ['=XML_ID' => $xmlID]),
            'runtime' => $runtime,
        ]);

        $rows = $result->fetchAll();

        if (!$rows) {
            return [];
        }

        $product = $rows[0];
        $product['PROPERTIES'] = [];

        foreach ($rows as $row) {
            if ($row['PROP_CODE']) {
                $product['PROPERTIES'][$row['PROP_CODE']][] = [
                    'ID' => $row['PROP_ID'],
                    'NAME' => $row['PROP_NAME'],
                    'VALUE' => $row['PROP_VALUE'],
                ];
            }
        }
        return $product;
    }

    public function getXMLIDbyID(int $id, $filter = []): string
    {
        $getitem = \Bitrix\Iblock\ElementTable::getList(
            [
                'select' => ['XML_ID'],
                'filter' => array_merge($filter, ['ID' => $id]),
                'limit' => 1
            ]
        )->fetch();

        if ($getitem) {
            return $getitem['XML_ID'];
        } else {
            return '';
        }
    }


    public function setFilter(array $filter): self
    {
        $this->filter = array_merge($this->filter, $filter);
        return $this;
    }

    public function setLimit(int $limit): self
    {
        $this->limit = $limit;
        return $this;
    }

    public function setOffset(int $offset): self
    {
        $this->offset = $offset;
        return $this;
    }

    public function setSort(array $sort): self
    {
        $this->sort = $sort;
        return $this;
    }
}
