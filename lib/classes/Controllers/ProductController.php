<?php

namespace Demo\Api\Classes\Controllers;

use Demo\Api\Classes\Helpers\Response\Response;
use Demo\Api\Classes\Services\ProductService;


class ProductController extends \Bitrix\Main\Engine\Controller
{

    //Для демо глушим все фильтры
    protected function getDefaultPreFilters()
    {
        return [];
    }

    public function configureActions()
    {
        return [
            'viewAction' => [
                'prefilters' => [
                    new \Bitrix\Main\Engine\ActionFilter\Csrf(),
                ],
                '-postfilters' => [
                    new \Bitrix\Main\Engine\ActionFilter\Csrf(),
                ],
            ],
        ];
    }

    public function getProductsAction()
    {
        $request = \Bitrix\Main\Context::getCurrent()->getRequest();

        $categoryId = (int)$request->get('category_id');
        $limit = (int)$request->get('limit') ?: 20;
        $page = (int)$request->get('page') ?: 1;
        $sortField = (string)$request->get('sort') ?: 'id';
        $sortOrder = (string)$request->get('order') ?: 'asc';

        $sortMap = [
            'id' => 'ID',
            'price' => 'PROPS.VALUE', // Упрощенно, так как цена в свойствах
            'name' => 'NAME',
            'created' => 'DATE_CREATE'
        ];

        $sort = [$sortMap[strtolower($sortField)] ?? 'ID' => strtoupper($sortOrder) === 'DESC' ? 'DESC' : 'ASC'];

        $filter = [];
        if ($categoryId > 0) {
            $filter['IBLOCK_SECTION_ID'] = $categoryId;
        }

        $productRepository = new \Demo\Api\Classes\Repositories\ProductRepository([], $limit, 0, $sort);
        $productService = new ProductService($productRepository, new \Demo\Api\Classes\Services\StoreService(new \Demo\Api\Classes\Repositories\StoreRepository()));
        try {
            $filter['IBLOCK_ID'] = 4;
            $result = $productService->getProducts($filter, $limit, $page);
            if (!empty($result)) {
                $response = Response::success(json_encode($result), 200);
                $response = $response->toArray();
            } else {
                $response = Response::error('empty', 200);
            }
        } catch (\Exception $e) {
            $response = Response::error($e->getMessage(), 200);
        }


        return new \Bitrix\Main\Engine\Response\Json($response);
    }

    public function getSingleProductByIDAction(int $id)
    {
        $productService = new ProductService(new \Demo\Api\Classes\Repositories\ProductRepository(), new \Demo\Api\Classes\Services\StoreService(new \Demo\Api\Classes\Repositories\StoreRepository()));
        try {
            $result = $productService->getSingleProductByID($id);
            if (!empty($result)) {
                $response = Response::success(json_encode($result), 200);
                $response = $response->toArray();
            } else {
                $response = Response::error('empty', 200);
            }
        } catch (\Exception $e) {
            $response = Response::error($e->getMessage(), 200);
        }
        return new \Bitrix\Main\Engine\Response\Json($response);
    }

    public function getSingleProductByXMLIDAction(string $xmlID)
    {
        $productService = new ProductService(new \Demo\Api\Classes\Repositories\ProductRepository(), new \Demo\Api\Classes\Services\StoreService(new \Demo\Api\Classes\Repositories\StoreRepository()));
        $result = $productService->getSingleProductByXMLID($xmlID);
        return new \Bitrix\Main\Engine\Response\Json($result);
    }
}
