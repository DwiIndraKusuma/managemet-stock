<?php

namespace App\Repositories;

use App\Contracts\StockOpnameRepositoryInterface;
use App\Models\StockOpname;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class StockOpnameRepository implements StockOpnameRepositoryInterface
{
    public function all(array $filters = []): Collection
    {
        $query = StockOpname::with(['approver', 'stockOpnameItems.item']);

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['search'])) {
            $query->where('opname_number', 'like', '%' . $filters['search'] . '%');
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = StockOpname::with(['approver', 'stockOpnameItems.item']);

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['search'])) {
            $query->where('opname_number', 'like', '%' . $filters['search'] . '%');
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    public function find(int $id): ?StockOpname
    {
        return StockOpname::with(['approver', 'stockOpnameItems.item'])->find($id);
    }

    public function create(array $data): StockOpname
    {
        return StockOpname::create($data);
    }

    public function update(int $id, array $data): StockOpname
    {
        $opname = StockOpname::findOrFail($id);
        $opname->update($data);
        return $opname->fresh(['approver', 'stockOpnameItems.item']);
    }

    public function delete(int $id): bool
    {
        return StockOpname::findOrFail($id)->delete();
    }

    public function findByNumber(string $opnameNumber): ?StockOpname
    {
        return StockOpname::where('opname_number', $opnameNumber)->first();
    }

    public function updateStatus(int $id, string $status, array $additionalData = []): StockOpname
    {
        $opname = StockOpname::findOrFail($id);
        $data = array_merge(['status' => $status], $additionalData);
        $opname->update($data);
        return $opname->fresh(['approver', 'stockOpnameItems.item']);
    }
}
