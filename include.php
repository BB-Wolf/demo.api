<?php

use Bitrix\Main\Loader;

require_once __DIR__ . '/lib/classes/Repositories/ProductRepository.php';
require_once __DIR__ . '/lib/classes/Repositories/StoreRepository.php';
require_once __DIR__ . '/lib/classes/Services/ProductService.php';
require_once __DIR__ . '/lib/classes/Services/StoreService.php';
require_once __DIR__ . '/lib/classes/Controllers/ProductController.php';
require_once __DIR__ . '/lib/classes/Helpers/Response/Response.php';


Loader::registerAutoLoadClasses(
    'demo.api',
    [
        \Demo\Api\Classes\Repositories\ProductRepository::class => 'lib/classes/Repositories/ProductRepository.php',
        \Demo\Api\Classes\Repositories\StoreRepository::class => 'lib/classes/Repositories/StoreRepository.php',
        \Demo\Api\Classes\Services\ProductService::class => 'lib/classes/Services/ProductService.php',
        \Demo\Api\Classes\Services\StoreService::class => 'lib/classes/Services/StoreService.php',
        \Demo\Api\Classes\Controllers\ProductController::class => 'lib/classes/Controllers/ProductController.php',
        \Demo\Api\Classes\Helpers\Response\Response::class => 'lib/classes/Helpers/Response/Response.php',

    ]
);
