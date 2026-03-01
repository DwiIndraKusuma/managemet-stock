<?php

namespace App\Services;

use App\Contracts\InventoryRepositoryInterface;

class InventoryService
{
    public function __construct(
        private InventoryRepositoryInterface $inventoryRepository
    ) {}

    public function getAll(array $filters = [])
    {
        return $this->inventoryRepository->all($filters);
    }

    public function paginate(array $filters = [], int $perPage = 15)
    {
        return $this->inventoryRepository->paginate($filters, $perPage);
    }

    public function find(int $id)
    {
        return $this->inventoryRepository->find($id);
    }

    public function getMovements(int $itemId, array $filters = [])
    {
        return $this->inventoryRepository->getMovements($itemId, $filters);
    }

    public function paginateMovements(int $itemId, array $filters = [], int $perPage = 15)
    {
        return $this->inventoryRepository->paginateMovements($itemId, $filters, $perPage);
    }
}
