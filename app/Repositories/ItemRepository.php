<?php

namespace App\Repositories;

use App\Contracts\ItemRepositoryInterface;
use App\Models\Item;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class ItemRepository implements ItemRepositoryInterface
{
    public function all(array $filters = []): Collection
    {
        $query = Item::with(['category', 'unit', 'inventory']);

        if (isset($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('name', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('code', 'like', '%' . $filters['search'] . '%');
            });
        }

        if (isset($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        return $query->get();
    }

    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Item::with(['category', 'unit', 'inventory']);

        if (isset($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('name', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('code', 'like', '%' . $filters['search'] . '%');
            });
        }

        if (isset($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        return $query->paginate($perPage);
    }

    public function find(int $id): ?Item
    {
        return Item::with(['category', 'unit', 'inventory'])->find($id);
    }

    public function create(array $data): Item
    {
        return Item::create($data);
    }

    public function update(int $id, array $data): Item
    {
        $item = Item::findOrFail($id);
        $item->update($data);
        return $item->fresh(['category', 'unit', 'inventory']);
    }

    public function delete(int $id): bool
    {
        return Item::findOrFail($id)->delete();
    }

    public function findByCode(string $code): ?Item
    {
        return Item::where('code', $code)->first();
    }
}
