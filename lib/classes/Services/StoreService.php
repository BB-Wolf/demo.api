<?php

namespace Demo\Api\Classes\Services;

use Demo\Api\Classes\Helpers\Response\Response;
use Demo\Api\Classes\Repositories\StoreRepository;

class StoreService
{
    private StoreRepository $storeRepository;

    public function __construct(StoreRepository $storeRepository)
    {
        $this->storeRepository = $storeRepository;
    }

    public function getStoresWithStock(int $iblockID): array
    {
        return $this->storeRepository->getStoresWithStock($iblockID);
    }

    public function getItemStock(int $productId): int
    {
        return $this->storeRepository->getItemStock($productId);
    }
}
