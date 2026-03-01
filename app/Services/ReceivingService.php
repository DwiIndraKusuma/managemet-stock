<?php

namespace App\Services;

use App\Contracts\ReceivingRepositoryInterface;
use App\Contracts\InventoryRepositoryInterface;
use App\Models\ReceivingItem;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class ReceivingService
{
    public function __construct(
        private ReceivingRepositoryInterface $receivingRepository,
        private InventoryRepositoryInterface $inventoryRepository
    ) {}

    public function getAll(array $filters = [])
    {
        return $this->receivingRepository->all($filters);
    }

    public function paginate(array $filters = [], int $perPage = 15)
    {
        return $this->receivingRepository->paginate($filters, $perPage);
    }

    public function find(int $id)
    {
        return $this->receivingRepository->find($id);
    }

    public function create(array $data, int $userId)
    {
        DB::beginTransaction();
        try {
            $data['receiving_number'] = $this->generateReceivingNumber();
            $data['status'] = 'open';

            $receiving = $this->receivingRepository->create($data);

            // Create receiving items
            if (isset($data['items']) && is_array($data['items'])) {
                foreach ($data['items'] as $item) {
                    ReceivingItem::create([
                        'receiving_id' => $receiving->id,
                        'purchase_order_item_id' => $item['purchase_order_item_id'],
                        'quantity_received' => $item['quantity_received'],
                        'quantity_accepted' => $item['quantity_accepted'] ?? $item['quantity_received'],
                        'quantity_rejected' => $item['quantity_rejected'] ?? 0,
                        'notes' => $item['notes'] ?? null,
                    ]);

                    // Update inventory
                    $poItem = \App\Models\PurchaseOrderItem::with('item')->find($item['purchase_order_item_id']);
                    $inventory = $this->inventoryRepository->findByItemId($poItem->item_id);

                    if ($inventory) {
                        $inventory->quantity_available += $item['quantity_accepted'] ?? $item['quantity_received'];
                        $inventory->last_movement_at = now();
                        $inventory->save();
                    } else {
                        $this->inventoryRepository->createOrUpdate($poItem->item_id, [
                            'quantity_available' => $item['quantity_accepted'] ?? $item['quantity_received'],
                            'quantity_reserved' => 0,
                            'quantity_in_transit' => 0,
                            'last_movement_at' => now(),
                        ]);
                    }

                    // Create stock movement
                    $this->inventoryRepository->createMovement([
                        'item_id' => $poItem->item_id,
                        'movement_type' => 'in',
                        'quantity' => $item['quantity_accepted'] ?? $item['quantity_received'],
                        'reference_type' => \App\Models\Receiving::class,
                        'reference_id' => $receiving->id,
                        'notes' => "Received from PO {$receiving->purchaseOrder->po_number}",
                        'created_by' => $userId,
                    ]);
                }

                // Update receiving status based on PO completion
                $this->updateReceivingStatus($receiving);
            }

            DB::commit();
            return $this->receivingRepository->find($receiving->id);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function update(int $id, array $data, int $userId)
    {
        $receiving = $this->receivingRepository->find($id);

        if ($receiving->status === 'completed') {
            throw new \Exception('Completed receivings cannot be updated');
        }

        DB::beginTransaction();
        try {
            // Update receiving items if provided
            if (isset($data['items']) && is_array($data['items'])) {
                // Delete existing items and reverse inventory
                foreach ($receiving->receivingItems as $receivingItem) {
                    $poItem = $receivingItem->purchaseOrderItem;
                    $inventory = $this->inventoryRepository->findByItemId($poItem->item_id);

                    if ($inventory) {
                        $inventory->quantity_available -= $receivingItem->quantity_accepted;
                        $inventory->save();
                    }
                }

                ReceivingItem::where('receiving_id', $id)->delete();

                // Create new items
                foreach ($data['items'] as $item) {
                    ReceivingItem::create([
                        'receiving_id' => $id,
                        'purchase_order_item_id' => $item['purchase_order_item_id'],
                        'quantity_received' => $item['quantity_received'],
                        'quantity_accepted' => $item['quantity_accepted'] ?? $item['quantity_received'],
                        'quantity_rejected' => $item['quantity_rejected'] ?? 0,
                        'notes' => $item['notes'] ?? null,
                    ]);

                    // Update inventory
                    $poItem = \App\Models\PurchaseOrderItem::with('item')->find($item['purchase_order_item_id']);
                    $inventory = $this->inventoryRepository->findByItemId($poItem->item_id);

                    if ($inventory) {
                        $inventory->quantity_available += $item['quantity_accepted'] ?? $item['quantity_received'];
                        $inventory->last_movement_at = now();
                        $inventory->save();
                    }
                }

                // Update receiving status
                $this->updateReceivingStatus($receiving);
            }

            DB::commit();
            return $this->receivingRepository->find($id);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    private function updateReceivingStatus($receiving)
    {
        $po = $receiving->purchaseOrder;
        $totalReceived = 0;
        $totalOrdered = 0;

        foreach ($po->purchaseOrderItems as $poItem) {
            $totalOrdered += $poItem->quantity;
            $receivedQty = $receiving->receivingItems
                ->where('purchase_order_item_id', $poItem->id)
                ->sum('quantity_accepted');
            $totalReceived += $receivedQty;
        }

        if ($totalReceived >= $totalOrdered) {
            $this->receivingRepository->updateStatus($receiving->id, 'completed');
        } elseif ($totalReceived > 0) {
            $this->receivingRepository->updateStatus($receiving->id, 'partial');
        }
    }

    public function returnItem(int $receivingId, int $receivingItemId, int $quantity, string $reason, int $userId)
    {
        $receiving = $this->receivingRepository->find($receivingId);

        if (!$receiving) {
            throw new \Exception('Receiving not found');
        }

        // Only allow return from completed receivings
        if ($receiving->status !== 'completed' && $receiving->status !== 'partial') {
            throw new \Exception('Only completed or partial receivings can have items returned');
        }

        // Find the receiving item
        $receivingItem = \App\Models\ReceivingItem::with('purchaseOrderItem.item')
            ->where('receiving_id', $receivingId)
            ->where('id', $receivingItemId)
            ->first();

        if (!$receivingItem) {
            throw new \Exception('Receiving item not found');
        }

        // Validate quantity
        if ($quantity <= 0) {
            throw new \Exception('Return quantity must be greater than 0');
        }

        // Validate quantity doesn't exceed accepted quantity
        $maxReturnable = $receivingItem->quantity_accepted;
        if ($quantity > $maxReturnable) {
            throw new \Exception("Return quantity cannot exceed accepted quantity ({$maxReturnable})");
        }

        DB::beginTransaction();
        try {
            // Update inventory - decrease available quantity
            $inventory = $this->inventoryRepository->findByItemId($receivingItem->purchaseOrderItem->item_id);

            if (!$inventory || $inventory->quantity_available < $quantity) {
                throw new \Exception("Insufficient stock available for return. Available: " . ($inventory ? $inventory->quantity_available : 0));
            }

            $inventory->quantity_available -= $quantity;
            $inventory->last_movement_at = now();
            $inventory->save();

            // Create stock movement record
            $this->inventoryRepository->createMovement([
                'item_id' => $receivingItem->purchaseOrderItem->item_id,
                'movement_type' => 'return',
                'quantity' => $quantity,
                'reference_type' => \App\Models\Receiving::class,
                'reference_id' => $receivingId,
                'notes' => "Return item from receiving {$receiving->receiving_number}. Reason: {$reason}",
                'created_by' => $userId,
            ]);

            // Update receiving item - decrease accepted quantity
            $receivingItem->quantity_accepted -= $quantity;
            $receivingItem->save();

            // Recalculate receiving status
            $this->updateReceivingStatus($receiving);

            DB::commit();
            return $this->receivingRepository->find($receivingId);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    private function generateReceivingNumber(): string
    {
        $date = now()->format('Ymd');
        $random = strtoupper(Str::random(4));
        $number = "REC-{$date}-{$random}";

        // Ensure uniqueness
        while ($this->receivingRepository->findByNumber($number)) {
            $random = strtoupper(Str::random(4));
            $number = "REC-{$date}-{$random}";
        }

        return $number;
    }
}
