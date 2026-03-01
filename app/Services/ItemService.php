<?php

namespace App\Services;

use App\Contracts\ItemRepositoryInterface;
use App\Models\Inventory;
use Illuminate\Support\Str;

class ItemService
{
    public function __construct(
        private ItemRepositoryInterface $itemRepository
    ) {}

    public function getAll(array $filters = [])
    {
        return $this->itemRepository->all($filters);
    }

    public function paginate(array $filters = [], int $perPage = 15)
    {
        return $this->itemRepository->paginate($filters, $perPage);
    }

    public function find(int $id)
    {
        return $this->itemRepository->find($id);
    }

    public function create(array $data)
    {
        // Generate code if not provided
        if (empty($data['code'])) {
            $data['code'] = $this->generateItemCode($data['name']);
        }

        $item = $this->itemRepository->create($data);

        // Create initial inventory record
        Inventory::create([
            'item_id' => $item->id,
            'quantity_available' => 0,
            'quantity_reserved' => 0,
            'quantity_in_transit' => 0,
        ]);

        return $item->fresh(['category', 'unit', 'inventory']);
    }

    public function update(int $id, array $data)
    {
        $item = $this->itemRepository->find($id);
        
        if (!$item) {
            throw new \Illuminate\Database\Eloquent\ModelNotFoundException('Item not found');
        }
        
        // Check if item is used in transactions and trying to change category_id or unit_id
        if (isset($data['category_id']) && $data['category_id'] != $item->category_id) {
            if ($this->isItemUsedInTransactions($id)) {
                throw new \Exception('Item tidak dapat diubah kategori karena sudah digunakan di transaksi (Request, Purchase Order, Stock Movement, atau Stock Opname).');
            }
        }
        
        if (isset($data['unit_id']) && $data['unit_id'] != $item->unit_id) {
            if ($this->isItemUsedInTransactions($id)) {
                throw new \Exception('Item tidak dapat diubah unit karena sudah digunakan di transaksi (Request, Purchase Order, Stock Movement, atau Stock Opname).');
            }
        }
        
        return $this->itemRepository->update($id, $data);
    }

    public function delete(int $id)
    {
        $item = $this->itemRepository->find($id);
        
        if (!$item) {
            throw new \Illuminate\Database\Eloquent\ModelNotFoundException('Item not found');
        }
        
        // Check if item is used in transactions
        if ($this->isItemUsedInTransactions($id)) {
            throw new \Exception('Item tidak dapat dihapus karena sudah digunakan di transaksi (Request, Purchase Order, Stock Movement, atau Stock Opname).');
        }
        
        return $this->itemRepository->delete($id);
    }
    
    /**
     * Check if item is used in any transactions
     */
    private function isItemUsedInTransactions(int $itemId): bool
    {
        // Check in RequestItems
        if (\App\Models\RequestItem::where('item_id', $itemId)->exists()) {
            return true;
        }
        
        // Check in PurchaseOrderItems
        if (\App\Models\PurchaseOrderItem::where('item_id', $itemId)->exists()) {
            return true;
        }
        
        // Check in StockMovements
        if (\App\Models\StockMovement::where('item_id', $itemId)->exists()) {
            return true;
        }
        
        // Check in StockOpnameItems
        if (\App\Models\StockOpnameItem::where('item_id', $itemId)->exists()) {
            return true;
        }
        
        return false;
    }

    private function generateItemCode(string $name): string
    {
        $prefix = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $name), 0, 3));
        $random = strtoupper(Str::random(4));
        $code = $prefix . '-' . $random;

        // Ensure uniqueness
        while ($this->itemRepository->findByCode($code)) {
            $random = strtoupper(Str::random(4));
            $code = $prefix . '-' . $random;
        }

        return $code;
    }
}
