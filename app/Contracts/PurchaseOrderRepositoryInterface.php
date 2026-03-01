<?php

namespace App\Contracts;

use App\Models\PurchaseOrder;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface PurchaseOrderRepositoryInterface
{
    public function all(array $filters = []): Collection;
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator;
    public function find(int $id): ?PurchaseOrder;
    public function create(array $data): PurchaseOrder;
    public function update(int $id, array $data): PurchaseOrder;
    public function delete(int $id): bool;
    public function findByNumber(string $poNumber): ?PurchaseOrder;
    public function updateStatus(int $id, string $status, array $additionalData = []): PurchaseOrder;
}
