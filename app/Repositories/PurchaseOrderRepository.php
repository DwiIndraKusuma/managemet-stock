<?php

namespace App\Repositories;

use App\Contracts\PurchaseOrderRepositoryInterface;
use App\Models\PurchaseOrder;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class PurchaseOrderRepository implements PurchaseOrderRepositoryInterface
{
    public function all(array $filters = []): Collection
    {
        $query = PurchaseOrder::with(['approver', 'purchaseOrderItems.item', 'receivings']);

        if (isset($filters['status'])) {
            // Support multiple status (comma-separated) or single status
            $statuses = is_array($filters['status']) 
                ? $filters['status'] 
                : explode(',', $filters['status']);
            
            // Trim whitespace from each status
            $statuses = array_map('trim', $statuses);
            
            if (count($statuses) === 1) {
                $query->where('status', $statuses[0]);
            } else {
                $query->whereIn('status', $statuses);
            }
        }

        if (isset($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('po_number', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('vendor_name', 'like', '%' . $filters['search'] . '%');
            });
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = PurchaseOrder::with(['approver', 'purchaseOrderItems.item', 'receivings']);

        if (isset($filters['status'])) {
            // Support multiple status (comma-separated) or single status
            $statuses = is_array($filters['status']) 
                ? $filters['status'] 
                : explode(',', $filters['status']);
            
            // Trim whitespace from each status
            $statuses = array_map('trim', $statuses);
            
            if (count($statuses) === 1) {
                $query->where('status', $statuses[0]);
            } else {
                $query->whereIn('status', $statuses);
            }
        }

        if (isset($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('po_number', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('vendor_name', 'like', '%' . $filters['search'] . '%');
            });
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    public function find(int $id): ?PurchaseOrder
    {
        return PurchaseOrder::with(['approver', 'purchaseOrderItems.item', 'receivings'])->find($id);
    }

    public function create(array $data): PurchaseOrder
    {
        return PurchaseOrder::create($data);
    }

    public function update(int $id, array $data): PurchaseOrder
    {
        $po = PurchaseOrder::findOrFail($id);
        $po->update($data);
        return $po->fresh(['approver', 'purchaseOrderItems.item', 'receivings']);
    }

    public function delete(int $id): bool
    {
        return PurchaseOrder::findOrFail($id)->delete();
    }

    public function findByNumber(string $poNumber): ?PurchaseOrder
    {
        return PurchaseOrder::where('po_number', $poNumber)->first();
    }

    public function updateStatus(int $id, string $status, array $additionalData = []): PurchaseOrder
    {
        $po = PurchaseOrder::findOrFail($id);
        $data = array_merge(['status' => $status], $additionalData);
        $po->update($data);
        return $po->fresh(['approver', 'purchaseOrderItems.item', 'receivings']);
    }
}
