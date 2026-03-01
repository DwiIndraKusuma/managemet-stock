<?php

namespace App\Http\Controllers\Api;

use App\Services\StockOpnameService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StockOpnameController extends BaseController
{
    public function __construct(
        private StockOpnameService $stockOpnameService
    ) {}

    public function index(Request $request): JsonResponse
    {
        try {
            $filters = $request->only(['status', 'search']);
            $perPage = $request->get('per_page', 15);

            $opnames = $this->stockOpnameService->paginate($filters, $perPage);

            return $this->successResponse($opnames, 'Stock opnames retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $data = $request->validate([
                'opname_date' => 'required|date',
                'notes' => 'nullable|string',
                'items' => 'required|array|min:1',
                'items.*.item_id' => 'required|exists:items,id',
                'items.*.physical_quantity' => 'required|integer|min:0',
                'items.*.notes' => 'nullable|string',
            ]);

            $opname = $this->stockOpnameService->create($data);

            return $this->successResponse($opname, 'Stock opname created successfully', [], 201);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $opname = $this->stockOpnameService->find($id);

            if (!$opname) {
                return $this->errorResponse('Stock opname not found', [], 404);
            }

            return $this->successResponse($opname, 'Stock opname retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $data = $request->validate([
                'opname_date' => 'sometimes|required|date',
                'notes' => 'nullable|string',
                'items' => 'sometimes|array|min:1',
                'items.*.item_id' => 'required|exists:items,id',
                'items.*.physical_quantity' => 'required|integer|min:0',
                'items.*.notes' => 'nullable|string',
            ]);

            $opname = $this->stockOpnameService->update($id, $data);

            return $this->successResponse($opname, 'Stock opname updated successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function submit(Request $request, int $id): JsonResponse
    {
        try {
            $opname = $this->stockOpnameService->submit($id);
            return $this->successResponse($opname, 'Stock opname submitted successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function approve(Request $request, int $id): JsonResponse
    {
        try {
            $opname = $this->stockOpnameService->approve($id, $request->user()->id);
            return $this->successResponse($opname, 'Stock opname approved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function applyAdjustment(Request $request, int $id): JsonResponse
    {
        try {
            $opname = $this->stockOpnameService->applyAdjustment($id, $request->user()->id);
            return $this->successResponse($opname, 'Stock opname adjustment applied successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }
}
