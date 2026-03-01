<?php

namespace App\Contracts;

use App\Models\Inventory;
use App\Models\StockMovement;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface InventoryRepositoryInterface
{
    public function all(array $filters = []): Collection;
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator;
    public function find(int $id): ?Inventory;
    public function findByItemId(int $itemId): ?Inventory;
    public function createOrUpdate(int $itemId, array $data): Inventory;
    public function getMovements(int $itemId, array $filters = []): Collection;
    public function paginateMovements(int $itemId, array $filters = [], int $perPage = 15): LengthAwarePaginator;
    public function createMovement(array $data): StockMovement;
}
