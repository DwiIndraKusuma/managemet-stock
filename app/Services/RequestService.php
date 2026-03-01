<?php

namespace App\Services;

use App\Contracts\RequestRepositoryInterface;
use App\Contracts\InventoryRepositoryInterface;
use App\Models\RequestItem;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class RequestService
{
    public function __construct(
        private RequestRepositoryInterface $requestRepository,
        private InventoryRepositoryInterface $inventoryRepository
    ) {}

    public function getAll(array $filters = [], ?int $userId = null, ?string $userRole = null)
    {
        // Filter technician requests to only show their own requests
        if ($userRole === 'technician' && $userId) {
            $filters['user_id'] = $userId;
        }
        return $this->requestRepository->all($filters);
    }

    public function paginate(array $filters = [], int $perPage = 15, ?int $userId = null, ?string $userRole = null)
    {
        // Filter technician requests to only show their own requests
        if ($userRole === 'technician' && $userId) {
            $filters['user_id'] = $userId;
        }
        return $this->requestRepository->paginate($filters, $perPage);
    }

    public function find(int $id)
    {
        return $this->requestRepository->find($id);
    }

    public function create(array $data, int $userId)
    {
        DB::beginTransaction();
        try {
            $data['request_number'] = $this->generateRequestNumber();
            $data['user_id'] = $userId;
            $data['status'] = 'draft';

            $request = $this->requestRepository->create($data);

            // Create request items
            if (isset($data['items']) && is_array($data['items'])) {
                foreach ($data['items'] as $item) {
                    RequestItem::create([
                        'request_id' => $request->id,
                        'item_id' => $item['item_id'],
                        'quantity' => $item['quantity'],
                        'notes' => $item['notes'] ?? null,
                    ]);
                }
            }

            DB::commit();
            return $this->requestRepository->find($request->id);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function update(int $id, array $data)
    {
        $request = $this->requestRepository->find($id);

        if ($request->status !== 'draft') {
            throw new \Exception('Only draft requests can be updated');
        }

        DB::beginTransaction();
        try {
            $this->requestRepository->update($id, $data);

            // Update request items if provided
            if (isset($data['items']) && is_array($data['items'])) {
                // Delete existing items
                RequestItem::where('request_id', $id)->delete();

                // Create new items
                foreach ($data['items'] as $item) {
                    RequestItem::create([
                        'request_id' => $id,
                        'item_id' => $item['item_id'],
                        'quantity' => $item['quantity'],
                        'notes' => $item['notes'] ?? null,
                    ]);
                }
            }

            DB::commit();
            return $this->requestRepository->find($id);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function submit(int $id)
    {
        $request = $this->requestRepository->find($id);

        if ($request->status !== 'draft') {
            throw new \Exception('Only draft requests can be submitted');
        }

        return $this->requestRepository->updateStatus($id, 'submitted', [
            'submitted_at' => now(),
        ]);
    }

    public function approve(int $id, int $approverId)
    {
        $request = $this->requestRepository->find($id);

        if ($request->status !== 'submitted') {
            throw new \Exception('Only submitted requests can be approved');
        }

        DB::beginTransaction();
        try {
            // Reserve stock for approved requests
            foreach ($request->requestItems as $requestItem) {
                $inventory = $this->inventoryRepository->findByItemId($requestItem->item_id);

                if (!$inventory || $inventory->quantity_available < $requestItem->quantity) {
                    throw new \Exception("Insufficient stock for item: {$requestItem->item->name}");
                }

                // Update inventory - reserve stock
                $inventory->quantity_available -= $requestItem->quantity;
                $inventory->quantity_reserved += $requestItem->quantity;
                $inventory->last_movement_at = now();
                $inventory->save();

                // Create stock movement record
                $this->inventoryRepository->createMovement([
                    'item_id' => $requestItem->item_id,
                    'movement_type' => 'reserved',
                    'quantity' => $requestItem->quantity,
                    'reference_type' => \App\Models\Request::class,
                    'reference_id' => $request->id,
                    'notes' => "Reserved for request {$request->request_number}",
                    'created_by' => $approverId,
                ]);
            }

            // Update request status
            $request = $this->requestRepository->updateStatus($id, 'approved', [
                'approved_at' => now(),
                'approved_by' => $approverId,
            ]);

            DB::commit();
            return $request;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function reject(int $id, int $approverId, string $reason)
    {
        $request = $this->requestRepository->find($id);

        if ($request->status !== 'submitted') {
            throw new \Exception('Only submitted requests can be rejected');
        }

        return $this->requestRepository->updateStatus($id, 'rejected', [
            'rejected_at' => now(),
            'approved_by' => $approverId,
            'rejection_reason' => $reason,
        ]);
    }

    public function delete(int $id)
    {
        $request = $this->requestRepository->find($id);

        if ($request->status !== 'draft') {
            throw new \Exception('Only draft requests can be deleted');
        }

        return $this->requestRepository->delete($id);
    }

    private function generateRequestNumber(): string
    {
        $date = now()->format('Ymd');
        $random = strtoupper(Str::random(4));
        $number = "REQ-{$date}-{$random}";

        // Ensure uniqueness
        while ($this->requestRepository->findByNumber($number)) {
            $random = strtoupper(Str::random(4));
            $number = "REQ-{$date}-{$random}";
        }

        return $number;
    }
}
