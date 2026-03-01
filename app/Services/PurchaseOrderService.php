<?php

namespace App\Services;

use App\Contracts\PurchaseOrderRepositoryInterface;
use App\Models\PurchaseOrderItem;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class PurchaseOrderService
{
    public function __construct(
        private PurchaseOrderRepositoryInterface $purchaseOrderRepository
    ) {}

    public function getAll(array $filters = [])
    {
        return $this->purchaseOrderRepository->all($filters);
    }

    public function paginate(array $filters = [], int $perPage = 15)
    {
        return $this->purchaseOrderRepository->paginate($filters, $perPage);
    }

    public function find(int $id)
    {
        return $this->purchaseOrderRepository->find($id);
    }

    public function create(array $data)
    {
        DB::beginTransaction();
        try {
            $data['po_number'] = $this->generatePONumber();
            $data['status'] = 'draft';
            $data['total_amount'] = 0;

            $po = $this->purchaseOrderRepository->create($data);

            // Create PO items and calculate total
            $totalAmount = 0;
            if (isset($data['items']) && is_array($data['items'])) {
                foreach ($data['items'] as $item) {
                    $subtotal = $item['quantity'] * $item['unit_price'];
                    $totalAmount += $subtotal;

                    PurchaseOrderItem::create([
                        'purchase_order_id' => $po->id,
                        'item_id' => $item['item_id'],
                        'quantity' => $item['quantity'],
                        'unit_price' => $item['unit_price'],
                        'subtotal' => $subtotal,
                    ]);
                }
            }

            // Update total amount
            $po->update(['total_amount' => $totalAmount]);

            DB::commit();
            return $this->purchaseOrderRepository->find($po->id);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function update(int $id, array $data)
    {
        $po = $this->purchaseOrderRepository->find($id);

        if ($po->status !== 'draft') {
            throw new \Exception('Only draft purchase orders can be updated');
        }

        DB::beginTransaction();
        try {
            $this->purchaseOrderRepository->update($id, $data);

            // Update PO items if provided
            if (isset($data['items']) && is_array($data['items'])) {
                // Delete existing items
                PurchaseOrderItem::where('purchase_order_id', $id)->delete();

                // Create new items and calculate total
                $totalAmount = 0;
                foreach ($data['items'] as $item) {
                    $subtotal = $item['quantity'] * $item['unit_price'];
                    $totalAmount += $subtotal;

                    PurchaseOrderItem::create([
                        'purchase_order_id' => $id,
                        'item_id' => $item['item_id'],
                        'quantity' => $item['quantity'],
                        'unit_price' => $item['unit_price'],
                        'subtotal' => $subtotal,
                    ]);
                }

                // Update total amount
                $po->update(['total_amount' => $totalAmount]);
            }

            DB::commit();
            return $this->purchaseOrderRepository->find($id);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function submit(int $id)
    {
        $po = $this->purchaseOrderRepository->find($id);

        if ($po->status !== 'draft') {
            throw new \Exception('Only draft purchase orders can be submitted');
        }

        return $this->purchaseOrderRepository->updateStatus($id, 'pending_approval', []);
    }

    public function approve(int $id, int $approverId)
    {
        $po = $this->purchaseOrderRepository->find($id);

        if ($po->status !== 'draft' && $po->status !== 'pending_approval') {
            throw new \Exception('Only draft or pending approval purchase orders can be approved');
        }

        return $this->purchaseOrderRepository->updateStatus($id, 'approved', [
            'approved_at' => now(),
            'approved_by' => $approverId,
        ]);
    }

    public function sendToVendor(int $id)
    {
        $po = $this->purchaseOrderRepository->find($id);

        if ($po->status !== 'approved') {
            throw new \Exception('Only approved purchase orders can be sent to vendor');
        }

        return $this->purchaseOrderRepository->updateStatus($id, 'sent_to_vendor', [
            'sent_at' => now(),
        ]);
    }

    public function confirm(int $id)
    {
        $po = $this->purchaseOrderRepository->find($id);

        if ($po->status !== 'sent_to_vendor') {
            throw new \Exception('Only sent to vendor purchase orders can be confirmed');
        }

        return $this->purchaseOrderRepository->updateStatus($id, 'confirmed', [
            'confirmed_at' => now(),
        ]);
    }

    public function cancel(int $id, string $reason = null)
    {
        $po = $this->purchaseOrderRepository->find($id);

        if (in_array($po->status, ['confirmed'])) {
            throw new \Exception('Confirmed purchase orders cannot be cancelled');
        }

        return $this->purchaseOrderRepository->updateStatus($id, 'rejected', [
            'rejection_reason' => $reason ?? 'Cancelled',
        ]);
    }

    public function delete(int $id)
    {
        $po = $this->purchaseOrderRepository->find($id);

        if ($po->status !== 'draft') {
            throw new \Exception('Only draft purchase orders can be deleted');
        }

        return $this->purchaseOrderRepository->delete($id);
    }

    private function generatePONumber(): string
    {
        $date = now()->format('Ymd');
        $random = strtoupper(Str::random(4));
        $number = "PO-{$date}-{$random}";

        // Ensure uniqueness
        while ($this->purchaseOrderRepository->findByNumber($number)) {
            $random = strtoupper(Str::random(4));
            $number = "PO-{$date}-{$random}";
        }

        return $number;
    }
}
