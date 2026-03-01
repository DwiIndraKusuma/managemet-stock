<?php

namespace App\Http\Controllers\Api;

use App\Services\InventoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InventoryController extends BaseController
{
    public function __construct(
        private InventoryService $inventoryService
    ) {}

    public function index(Request $request): JsonResponse
    {
        try {
            $filters = $request->only(['search', 'status']);
            $perPage = $request->get('per_page', 15);

            $inventories = $this->inventoryService->paginate($filters, $perPage);

            return $this->successResponse($inventories, 'Inventories retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $inventory = $this->inventoryService->find($id);

            if (!$inventory) {
                return $this->errorResponse('Inventory not found', [], 404);
            }

            return $this->successResponse($inventory, 'Inventory retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function movements(Request $request, int $id): JsonResponse
    {
        try {
            $inventory = $this->inventoryService->find($id);

            if (!$inventory) {
                return $this->errorResponse('Inventory not found', [], 404);
            }

            $filters = $request->only(['movement_type']);
            $perPage = $request->get('per_page', 15);

            $movements = $this->inventoryService->paginateMovements($inventory->item_id, $filters, $perPage);

            return $this->successResponse($movements, 'Stock movements retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }
}
