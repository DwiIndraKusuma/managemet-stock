<?php

namespace App\Contracts;

use App\Models\Receiving;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface ReceivingRepositoryInterface
{
    public function all(array $filters = []): Collection;
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator;
    public function find(int $id): ?Receiving;
    public function create(array $data): Receiving;
    public function update(int $id, array $data): Receiving;
    public function delete(int $id): bool;
    public function findByNumber(string $receivingNumber): ?Receiving;
    public function updateStatus(int $id, string $status): Receiving;
}
