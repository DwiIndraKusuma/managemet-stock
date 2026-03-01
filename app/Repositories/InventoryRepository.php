<?php

namespace App\Repositories;

use App\Contracts\InventoryRepositoryInterface;
use App\Models\Inventory;
use App\Models\StockMovement;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class InventoryRepository implements InventoryRepositoryInterface
{
    public function all(array $filters = []): Collection
    {
        $query = Inventory::with(['item.category', 'item.unit']);

        if (isset($filters['search'])) {
            $query->whereHas('item', function ($q) use ($filters) {
                $q->where('name', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('code', 'like', '%' . $filters['search'] . '%');
            });
        }

        if (isset($filters['status'])) {
            if ($filters['status'] === 'available') {
                $query->where('quantity_available', '>', 0);
            } elseif ($filters['status'] === 'reserved') {
                $query->where('quantity_reserved', '>', 0);
            } elseif ($filters['status'] === 'in_transit') {
                $query->where('quantity_in_transit', '>', 0);
            }
        }

        return $query->get();
    }

    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Inventory::with(['item.category', 'item.unit']);

        if (isset($filters['search'])) {
            $query->whereHas('item', function ($q) use ($filters) {
                $q->where('name', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('code', 'like', '%' . $filters['search'] . '%');
            });
        }

        if (isset($filters['status'])) {
            if ($filters['status'] === 'available') {
                $query->where('quantity_available', '>', 0);
            } elseif ($filters['status'] === 'reserved') {
                $query->where('quantity_reserved', '>', 0);
            } elseif ($filters['status'] === 'in_transit') {
                $query->where('quantity_in_transit', '>', 0);
            }
        }

        return $query->paginate($perPage);
    }

    public function find(int $id): ?Inventory
    {
        return Inventory::with(['item.category', 'item.unit'])->find($id);
    }

    public function findByItemId(int $itemId): ?Inventory
    {
        return Inventory::with(['item.category', 'item.unit'])->where('item_id', $itemId)->first();
    }

    public function createOrUpdate(int $itemId, array $data): Inventory
    {
        return Inventory::updateOrCreate(
            ['item_id' => $itemId],
            $data
        );
    }

    public function getMovements(int $itemId, array $filters = []): Collection
    {
        $query = StockMovement::with(['creator', 'item'])
            ->where('item_id', $itemId);

        if (isset($filters['movement_type'])) {
            $query->where('movement_type', $filters['movement_type']);
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    public function paginateMovements(int $itemId, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = StockMovement::with(['creator', 'item'])
            ->where('item_id', $itemId);

        if (isset($filters['movement_type'])) {
            $query->where('movement_type', $filters['movement_type']);
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    public function createMovement(array $data): StockMovement
    {
        return StockMovement::create($data);
    }
}
