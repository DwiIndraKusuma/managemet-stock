<?php

namespace App\Http\Controllers\Api;

use App\Services\PurchaseOrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PurchaseOrderController extends BaseController
{
    public function __construct(
        private PurchaseOrderService $purchaseOrderService
    ) {}

    public function index(Request $request): JsonResponse
    {
        try {
            $filters = $request->only(['status', 'search']);
            $perPage = $request->get('per_page', 15);

            $purchaseOrders = $this->purchaseOrderService->paginate($filters, $perPage);

            return $this->successResponse($purchaseOrders, 'Purchase orders retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $validator = \Validator::make($request->all(), [
                'vendor_name' => 'required|string|max:255',
                'notes' => 'nullable|string',
                'items' => 'required|array|min:1',
                'items.*.item_id' => 'required|exists:items,id',
                'items.*.quantity' => 'required|integer|min:1',
                'items.*.unit_price' => 'required|numeric|min:0',
            ]);

            if ($validator->fails()) {
                return $this->validationErrorResponse($validator);
            }

            $po = $this->purchaseOrderService->create($validator->validated());
            $po->refresh();

            return $this->successResponse($po, 'Purchase order created successfully', [], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::warning("Validation error creating PO: " . $e->getMessage(), ['errors' => $e->errors()]);
            return $this->validationErrorResponse($e->validator);
        } catch (\Exception $e) {
            \Log::error("Error creating PO: " . $e->getMessage(), ['exception' => $e]);
            return $this->errorResponse($e->getMessage());
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $po = $this->purchaseOrderService->find($id);

            if (!$po) {
                return $this->errorResponse('Purchase order not found', [], 404);
            }

            return $this->successResponse($po, 'Purchase order retrieved successfully');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            \Log::warning("PO {$id} not found for show.");
            return $this->errorResponse('Purchase order not found', [], 404);
        } catch (\Exception $e) {
            \Log::error("Error retrieving PO {$id}: " . $e->getMessage(), ['exception' => $e]);
            return $this->errorResponse($e->getMessage());
        }
    }

    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $validator = \Validator::make($request->all(), [
                'vendor_name' => 'sometimes|required|string|max:255',
                'notes' => 'nullable|string',
                'items' => 'sometimes|array|min:1',
                'items.*.item_id' => 'required|exists:items,id',
                'items.*.quantity' => 'required|integer|min:1',
                'items.*.unit_price' => 'required|numeric|min:0',
            ]);

            if ($validator->fails()) {
                return $this->validationErrorResponse($validator);
            }

            $po = $this->purchaseOrderService->update($id, $validator->validated());
            $po->refresh();

            return $this->successResponse($po, 'Purchase order updated successfully');
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::warning("Validation error updating PO {$id}: " . $e->getMessage(), ['errors' => $e->errors()]);
            return $this->validationErrorResponse($e->validator);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            \Log::warning("PO {$id} not found for update.");
            return $this->errorResponse('Purchase order not found', [], 404);
        } catch (\Exception $e) {
            \Log::error("Error updating PO {$id}: " . $e->getMessage(), ['exception' => $e]);
            return $this->errorResponse($e->getMessage());
        }
    }

    public function submit(Request $request, int $id): JsonResponse
    {
        try {
            $po = $this->purchaseOrderService->submit($id);
            return $this->successResponse($po, 'Purchase order submitted successfully');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            \Log::warning("PO {$id} not found for submit.");
            return $this->errorResponse('Purchase order not found', [], 404);
        } catch (\Exception $e) {
            \Log::error("Error submitting PO {$id}: " . $e->getMessage(), ['exception' => $e]);
            return $this->errorResponse($e->getMessage());
        }
    }

    public function approve(Request $request, int $id): JsonResponse
    {
        try {
            $po = $this->purchaseOrderService->approve($id, $request->user()->id);
            return $this->successResponse($po, 'Purchase order approved successfully');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            \Log::warning("PO {$id} not found for approve.");
            return $this->errorResponse('Purchase order not found', [], 404);
        } catch (\Exception $e) {
            \Log::error("Error approving PO {$id}: " . $e->getMessage(), ['exception' => $e]);
            return $this->errorResponse($e->getMessage());
        }
    }

    public function sendToVendor(Request $request, int $id): JsonResponse
    {
        try {
            $po = $this->purchaseOrderService->sendToVendor($id);
            return $this->successResponse($po, 'Purchase order sent to vendor successfully');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            \Log::warning("PO {$id} not found for send to vendor.");
            return $this->errorResponse('Purchase order not found', [], 404);
        } catch (\Exception $e) {
            \Log::error("Error sending PO {$id} to vendor: " . $e->getMessage(), ['exception' => $e]);
            return $this->errorResponse($e->getMessage());
        }
    }

    public function confirm(Request $request, int $id): JsonResponse
    {
        try {
            $po = $this->purchaseOrderService->confirm($id);
            return $this->successResponse($po, 'Purchase order confirmed successfully');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            \Log::warning("PO {$id} not found for confirm.");
            return $this->errorResponse('Purchase order not found', [], 404);
        } catch (\Exception $e) {
            \Log::error("Error confirming PO {$id}: " . $e->getMessage(), ['exception' => $e]);
            return $this->errorResponse($e->getMessage());
        }
    }

    public function cancel(Request $request, int $id): JsonResponse
    {
        try {
            $validator = \Validator::make($request->all(), [
                'reason' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return $this->validationErrorResponse($validator);
            }

            $po = $this->purchaseOrderService->cancel($id, $validator->validated()['reason'] ?? null);
            return $this->successResponse($po, 'Purchase order cancelled successfully');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            \Log::warning("PO {$id} not found for cancel.");
            return $this->errorResponse('Purchase order not found', [], 404);
        } catch (\Exception $e) {
            \Log::error("Error cancelling PO {$id}: " . $e->getMessage(), ['exception' => $e]);
            return $this->errorResponse($e->getMessage());
        }
    }
}
