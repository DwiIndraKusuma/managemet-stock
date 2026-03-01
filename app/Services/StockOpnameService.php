<?php

namespace App\Services;

use App\Contracts\StockOpnameRepositoryInterface;
use App\Contracts\InventoryRepositoryInterface;
use App\Models\StockOpnameItem;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class StockOpnameService
{
    public function __construct(
        private StockOpnameRepositoryInterface $stockOpnameRepository,
        private InventoryRepositoryInterface $inventoryRepository
    ) {}

    public function getAll(array $filters = [])
    {
        return $this->stockOpnameRepository->all($filters);
    }

    public function paginate(array $filters = [], int $perPage = 15)
    {
        return $this->stockOpnameRepository->paginate($filters, $perPage);
    }

    public function find(int $id)
    {
        return $this->stockOpnameRepository->find($id);
    }

    public function create(array $data)
    {
        DB::beginTransaction();
        try {
            $data['opname_number'] = $this->generateOpnameNumber();
            $data['status'] = 'draft';

            $opname = $this->stockOpnameRepository->create($data);

            // Create opname items with system quantities
            if (isset($data['items']) && is_array($data['items'])) {
                foreach ($data['items'] as $item) {
                    $inventory = $this->inventoryRepository->findByItemId($item['item_id']);
                    $systemQuantity = $inventory ? $inventory->quantity_available : 0;
                    $physicalQuantity = $item['physical_quantity'];
                    $difference = $physicalQuantity - $systemQuantity;

                    StockOpnameItem::create([
                        'stock_opname_id' => $opname->id,
                        'item_id' => $item['item_id'],
                        'system_quantity' => $systemQuantity,
                        'physical_quantity' => $physicalQuantity,
                        'difference' => $difference,
                        'adjustment_type' => $difference > 0 ? 'increase' : ($difference < 0 ? 'decrease' : null),
                        'notes' => $item['notes'] ?? null,
                    ]);
                }
            }

            DB::commit();
            return $this->stockOpnameRepository->find($opname->id);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function update(int $id, array $data)
    {
        $opname = $this->stockOpnameRepository->find($id);

        if ($opname->status !== 'draft') {
            throw new \Exception('Only draft stock opnames can be updated');
        }

        DB::beginTransaction();
        try {
            $this->stockOpnameRepository->update($id, $data);

            // Update opname items if provided
            if (isset($data['items']) && is_array($data['items'])) {
                // Delete existing items
                StockOpnameItem::where('stock_opname_id', $id)->delete();

                // Create new items
                foreach ($data['items'] as $item) {
                    $inventory = $this->inventoryRepository->findByItemId($item['item_id']);
                    $systemQuantity = $inventory ? $inventory->quantity_available : 0;
                    $physicalQuantity = $item['physical_quantity'];
                    $difference = $physicalQuantity - $systemQuantity;

                    StockOpnameItem::create([
                        'stock_opname_id' => $id,
                        'item_id' => $item['item_id'],
                        'system_quantity' => $systemQuantity,
                        'physical_quantity' => $physicalQuantity,
                        'difference' => $difference,
                        'adjustment_type' => $difference > 0 ? 'increase' : ($difference < 0 ? 'decrease' : null),
                        'notes' => $item['notes'] ?? null,
                    ]);
                }
            }

            DB::commit();
            return $this->stockOpnameRepository->find($id);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function submit(int $id)
    {
        $opname = $this->stockOpnameRepository->find($id);

        if ($opname->status !== 'draft') {
            throw new \Exception('Only draft stock opnames can be submitted');
        }

        return $this->stockOpnameRepository->updateStatus($id, 'submitted', [
            'submitted_at' => now(),
        ]);
    }

    public function approve(int $id, int $approverId)
    {
        $opname = $this->stockOpnameRepository->find($id);

        if ($opname->status !== 'submitted') {
            throw new \Exception('Only submitted stock opnames can be approved');
        }

        // Update opname status to approved
        return $this->stockOpnameRepository->updateStatus($id, 'approved', [
            'approved_at' => now(),
            'approved_by' => $approverId,
        ]);
    }

    public function applyAdjustment(int $id, int $userId)
    {
        $opname = $this->stockOpnameRepository->find($id);

        if ($opname->status !== 'approved') {
            throw new \Exception('Only approved stock opnames can have adjustments applied');
        }

        DB::beginTransaction();
        try {
            // Apply adjustments to inventory
            foreach ($opname->stockOpnameItems as $opnameItem) {
                if ($opnameItem->difference != 0) {
                    $inventory = $this->inventoryRepository->findByItemId($opnameItem->item_id);

                    if ($inventory) {
                        $inventory->quantity_available += $opnameItem->difference;
                        $inventory->last_movement_at = now();
                        $inventory->save();
                    } else {
                        $this->inventoryRepository->createOrUpdate($opnameItem->item_id, [
                            'quantity_available' => $opnameItem->physical_quantity,
                            'quantity_reserved' => 0,
                            'quantity_in_transit' => 0,
                            'last_movement_at' => now(),
                        ]);
                    }

                    // Create stock movement
                    $this->inventoryRepository->createMovement([
                        'item_id' => $opnameItem->item_id,
                        'movement_type' => 'adjustment',
                        'quantity' => abs($opnameItem->difference),
                        'reference_type' => \App\Models\StockOpname::class,
                        'reference_id' => $opname->id,
                        'notes' => "Stock opname adjustment: {$opnameItem->adjustment_type} by {$opnameItem->difference}",
                        'created_by' => $userId,
                    ]);
                }
            }

            // Mark as adjusted
            $opname = $this->stockOpnameRepository->updateStatus($id, 'adjusted', [
                'adjusted_at' => now(),
            ]);

            DB::commit();
            return $this->stockOpnameRepository->find($id);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function delete(int $id)
    {
        $opname = $this->stockOpnameRepository->find($id);

        if ($opname->status !== 'draft') {
            throw new \Exception('Only draft stock opnames can be deleted');
        }

        return $this->stockOpnameRepository->delete($id);
    }

    private function generateOpnameNumber(): string
    {
        $date = now()->format('Ymd');
        $random = strtoupper(Str::random(4));
        $number = "OPN-{$date}-{$random}";

        // Ensure uniqueness
        while ($this->stockOpnameRepository->findByNumber($number)) {
            $random = strtoupper(Str::random(4));
            $number = "OPN-{$date}-{$random}";
        }

        return $number;
    }
}
