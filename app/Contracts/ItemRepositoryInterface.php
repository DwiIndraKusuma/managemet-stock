<?php

namespace App\Contracts;

use App\Models\Item;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface ItemRepositoryInterface
{
    public function all(array $filters = []): Collection;
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator;
    public function find(int $id): ?Item;
    public function create(array $data): Item;
    public function update(int $id, array $data): Item;
    public function delete(int $id): bool;
    public function findByCode(string $code): ?Item;
}
