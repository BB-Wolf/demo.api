<?php

namespace Demo\Api\Classes\Services;

use Demo\Api\Classes\Helpers\Response\Response;
use Demo\Api\Classes\Services\StoreService;
use Demo\Api\Classes\Repositories\ProductRepository;

class ProductService
{
    private ProductRepository $productRepository;
    private StoreService $storeService;

    public function __construct(ProductRepository $productRepository, StoreService $storeService)
    {
        $this->productRepository = $productRepository;
        $this->storeService = $storeService;
    }

    public function getSingleProductByID(int $id): array
    {
        $getData = $this->productRepository->setFilter(['IBLOCK_ID' => 4])->getSingleProductByID($id);
        if (empty($getData)) {
            return Response::error(json_encode(['message' => 'Товар не найден']))->toArray();
        }

        $quantity = $this->storeService->getItemStock($id);

        $properties = [];
        foreach ($getData['PROPERTIES'] as $code => $values) {
            $properties[strtolower($code)] = $values[0]['VALUE'];
        }

        $item['item'] = [
            'id' => (int)$getData['ID'],
            'name' => $getData['NAME'],
            'description' => $getData['PREVIEW_TEXT'],
            'price' => (float)($getData['PROPERTIES']['PRICE'][0]['VALUE'] ?? 0),
            'old_price' => (float)($getData['PROPERTIES']['OLD_PRICE'][0]['VALUE'] ?? 0),
            'currency' => 'RUB',
            'category' => [
                'id' => (int)$getData['IBLOCK_SECTION_ID'],
                'name' => $getData['SECTION_NAME'],
                'path' => '/catalog/category/'
            ],
            'images' => [
                "https://site.ru/upload/image1.jpg",
                "https://site.ru/upload/image2.jpg"
            ],
            'properties' => $properties,
            'available' => $quantity > 0,
            'quantity' => $quantity,
            'created_at' => date('c', strtotime($getData['DATE_CREATE'])),
            'updated_at' => date('c', strtotime($getData['TIMESTAMP_X']))
        ];


        return $item;
    }

    public function getSingleProductByXMLID(string $xmlID)
    {
        $getData = $this->productRepository->getSingleProductByXMLID($xmlID);
        if (empty($getData)) {
            return Response::error(json_encode(['message' => 'Товар не найден']));
        }

        $quantity = $this->storeService->getItemStock((int)$getData['ID']);

        $properties = [];
        foreach ($getData['PROPERTIES'] as $code => $values) {
            $properties[strtolower($code)] = $values[0]['VALUE'];
        }

        $item = [
            'id' => (int)$getData['ID'],
            'name' => $getData['NAME'],
            'description' => $getData['PREVIEW_TEXT'],
            'price' => (float)($getData['PROPERTIES']['PRICE'][0]['VALUE'] ?? 0),
            'old_price' => (float)($getData['PROPERTIES']['OLD_PRICE'][0]['VALUE'] ?? 0),
            'currency' => 'RUB',
            'category' => [
                'id' => (int)$getData['IBLOCK_SECTION_ID'],
                'name' => $getData['SECTION_NAME'],
                'path' => '/catalog/category/'
            ],
            'images' => [
                "https://site.ru/upload/image1.jpg",
                "https://site.ru/upload/image2.jpg"
            ],
            'properties' => $properties,
            'available' => $quantity > 0,
            'quantity' => $quantity,
            'created_at' => date('c', strtotime($getData['DATE_CREATE'])),
            'updated_at' => date('c', strtotime($getData['TIMESTAMP_X']))
        ];

        return Response::success(json_encode($item));
    }

    public function getProducts(array $filter = [], int $limit = 20, int $page = 1)
    {
        $total = $this->productRepository->getCount($filter);
        $pages = ceil($total / $limit);

        $this->productRepository->setLimit($limit)->setOffset(($page - 1) * $limit);
        $getData = $this->productRepository->getAll($filter);

        $items = [];

        foreach ($getData as $data) {
            $items[] = [
                'id' => (int)$data['ID'],
                'name' => $data['NAME'],
                'price' => (float)($data['PROPERTIES']['PRICE'][0]['VALUE'] ?? 0),
                'currency' => 'RUB',
                'category' => [
                    'id' => (int)$data['IBLOCK_SECTION_ID'],
                    'name' => 'Категория' // В getAll нет названия секции, для демо норм или надо доработать репозиторий
                ],
                'image' => "https://site.ru/upload/image.jpg",
                'available' => true,
                'quantity' => 10
            ];
        }

        return [
            'items' => $items,
            'pagination' => [
                'total' => (int)$total,
                'page' => (int)$page,
                'limit' => (int)$limit,
                'pages' => (int)$pages
            ]
        ];
    }
}
