<?php

namespace App\Contracts;

use App\Models\StockOpname;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface StockOpnameRepositoryInterface
{
    public function all(array $filters = []): Collection;
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator;
    public function find(int $id): ?StockOpname;
    public function create(array $data): StockOpname;
    public function update(int $id, array $data): StockOpname;
    public function delete(int $id): bool;
    public function findByNumber(string $opnameNumber): ?StockOpname;
    public function updateStatus(int $id, string $status, array $additionalData = []): StockOpname;
}
