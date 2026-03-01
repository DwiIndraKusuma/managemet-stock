<?php

namespace App\Repositories;

use App\Contracts\ReceivingRepositoryInterface;
use App\Models\Receiving;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class ReceivingRepository implements ReceivingRepositoryInterface
{
    public function all(array $filters = []): Collection
    {
        $query = Receiving::with(['purchaseOrder', 'receivingItems.purchaseOrderItem.item']);

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['purchase_order_id'])) {
            $query->where('purchase_order_id', $filters['purchase_order_id']);
        }

        if (isset($filters['search'])) {
            $query->where('receiving_number', 'like', '%' . $filters['search'] . '%');
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Receiving::with(['purchaseOrder', 'receivingItems.purchaseOrderItem.item']);

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['purchase_order_id'])) {
            $query->where('purchase_order_id', $filters['purchase_order_id']);
        }

        if (isset($filters['search'])) {
            $query->where('receiving_number', 'like', '%' . $filters['search'] . '%');
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    public function find(int $id): ?Receiving
    {
        return Receiving::with(['purchaseOrder', 'receivingItems.purchaseOrderItem.item'])->find($id);
    }

    public function create(array $data): Receiving
    {
        return Receiving::create($data);
    }

    public function update(int $id, array $data): Receiving
    {
        $receiving = Receiving::findOrFail($id);
        $receiving->update($data);
        return $receiving->fresh(['purchaseOrder', 'receivingItems.purchaseOrderItem.item']);
    }

    public function delete(int $id): bool
    {
        return Receiving::findOrFail($id)->delete();
    }

    public function findByNumber(string $receivingNumber): ?Receiving
    {
        return Receiving::where('receiving_number', $receivingNumber)->first();
    }

    public function updateStatus(int $id, string $status): Receiving
    {
        $receiving = Receiving::findOrFail($id);
        $receiving->update(['status' => $status]);
        return $receiving->fresh(['purchaseOrder', 'receivingItems.purchaseOrderItem.item']);
    }
}
