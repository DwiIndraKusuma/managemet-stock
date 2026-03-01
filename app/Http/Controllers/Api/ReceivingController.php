<?php

namespace App\Http\Controllers\Api;

use App\Services\ReceivingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReceivingController extends BaseController
{
    public function __construct(
        private ReceivingService $receivingService
    ) {}

    public function index(Request $request): JsonResponse
    {
        try {
            $filters = $request->only(['status', 'purchase_order_id', 'search']);
            $perPage = $request->get('per_page', 15);

            $receivings = $this->receivingService->paginate($filters, $perPage);

            return $this->successResponse($receivings, 'Receivings retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $validator = \Validator::make($request->all(), [
                'purchase_order_id' => 'required|exists:purchase_orders,id',
                'received_date' => 'required|date',
                'notes' => 'nullable|string',
                'items' => 'required|array|min:1',
                'items.*.purchase_order_item_id' => 'required|exists:purchase_order_items,id',
                'items.*.quantity_received' => 'required|integer|min:1',
                'items.*.quantity_accepted' => 'nullable|integer|min:0',
                'items.*.quantity_rejected' => 'nullable|integer|min:0',
                'items.*.notes' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return $this->validationErrorResponse($validator);
            }

            $data = $validator->validated();
            $receiving = $this->receivingService->create($data, $request->user()->id);
            $receiving->refresh();

            return $this->successResponse($receiving, 'Receiving created successfully', [], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::warning("Validation error creating receiving: " . $e->getMessage(), ['errors' => $e->errors()]);
            return $this->validationErrorResponse($e->validator);
        } catch (\Exception $e) {
            \Log::error("Error creating receiving: " . $e->getMessage(), ['exception' => $e]);
            return $this->errorResponse($e->getMessage());
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $receiving = $this->receivingService->find($id);

            if (!$receiving) {
                return $this->errorResponse('Receiving not found', [], 404);
            }

            return $this->successResponse($receiving, 'Receiving retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $data = $request->validate([
                'received_date' => 'sometimes|required|date',
                'notes' => 'nullable|string',
                'items' => 'sometimes|array|min:1',
                'items.*.purchase_order_item_id' => 'required|exists:purchase_order_items,id',
                'items.*.quantity_received' => 'required|integer|min:1',
                'items.*.quantity_accepted' => 'nullable|integer|min:0',
                'items.*.quantity_rejected' => 'nullable|integer|min:0',
                'items.*.notes' => 'nullable|string',
            ]);

            $receiving = $this->receivingService->update($id, $data, $request->user()->id);

            return $this->successResponse($receiving, 'Receiving updated successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function returnItem(Request $request, int $id): JsonResponse
    {
        try {
            $validator = \Validator::make($request->all(), [
                'receiving_item_id' => 'required|exists:receiving_items,id',
                'quantity' => 'required|integer|min:1',
                'reason' => 'required|string|max:500',
            ]);

            if ($validator->fails()) {
                return $this->validationErrorResponse($validator);
            }

            $data = $validator->validated();
            $receiving = $this->receivingService->returnItem(
                $id,
                $data['receiving_item_id'],
                $data['quantity'],
                $data['reason'],
                $request->user()->id
            );

            return $this->successResponse($receiving, 'Item returned successfully');
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::warning("Validation error returning item: " . $e->getMessage(), ['errors' => $e->errors()]);
            return $this->validationErrorResponse($e->validator);
        } catch (\Exception $e) {
            \Log::error("Error returning item: " . $e->getMessage(), ['exception' => $e]);
            return $this->errorResponse($e->getMessage(), [], 422);
        }
    }
}
