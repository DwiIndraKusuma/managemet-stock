<?php

namespace App\Contracts;

use App\Models\Request;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface RequestRepositoryInterface
{
    public function all(array $filters = []): Collection;
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator;
    public function find(int $id): ?Request;
    public function create(array $data): Request;
    public function update(int $id, array $data): Request;
    public function delete(int $id): bool;
    public function findByNumber(string $requestNumber): ?Request;
    public function updateStatus(int $id, string $status, array $additionalData = []): Request;
}
