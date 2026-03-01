<?php

namespace App\Http\Controllers\Api;

use App\Services\ItemService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ItemController extends BaseController
{
    public function __construct(
        private ItemService $itemService
    ) {}

    public function index(Request $request): JsonResponse
    {
        try {
            $filters = $request->only(['search', 'category_id']);
            $perPage = $request->get('per_page', 15);

            $items = $this->itemService->paginate($filters, $perPage);

            return $this->successResponse($items, 'Items retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $validator = \Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'code' => 'nullable|string|max:255|unique:items,code',
                'category_id' => 'required|exists:categories,id',
                'unit_id' => 'required|exists:units,id',
                'description' => 'nullable|string',
                'min_stock' => 'nullable|integer|min:0',
            ]);

            if ($validator->fails()) {
                return $this->validationErrorResponse($validator);
            }

            $data = $validator->validated();
            $item = $this->itemService->create($data);

            return $this->successResponse($item, 'Item created successfully', [], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->validationErrorResponse($e->validator);
        } catch (\Exception $e) {
            \Log::error('Item store error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return $this->errorResponse($e->getMessage());
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $item = $this->itemService->find($id);

            if (!$item) {
                return $this->errorResponse('Item not found', [], 404);
            }

            \Log::info('Item found: ::>> ');

            return $this->successResponse($item, 'Item retrieved successfully');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->errorResponse('Item not found', [], 404);
        } catch (\Exception $e) {
            \Log::error('Item show error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return $this->errorResponse($e->getMessage());
        }
    }

    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $validator = \Validator::make($request->all(), [
                'name' => 'sometimes|required|string|max:255',
                'code' => 'sometimes|nullable|string|max:255|unique:items,code,' . $id,
                'category_id' => 'sometimes|required|exists:categories,id',
                'unit_id' => 'sometimes|required|exists:units,id',
                'description' => 'nullable|string',
                'min_stock' => 'nullable|integer|min:0',
            ]);

            if ($validator->fails()) {
                return $this->validationErrorResponse($validator);
            }

            $data = $validator->validated();
            $item = $this->itemService->update($id, $data);
            $item->refresh();

            return $this->successResponse($item, 'Item updated successfully');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            \Log::warning("Item {$id} not found for update.");
            return $this->errorResponse('Item not found', [], 404);
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::warning("Validation error updating item {$id}: " . $e->getMessage(), ['errors' => $e->errors()]);
            return $this->validationErrorResponse($e->validator);
        } catch (\Exception $e) {
            \Log::error('Item update error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return $this->errorResponse($e->getMessage(), [], 422);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $this->itemService->delete($id);
            return $this->successResponse(null, 'Item deleted successfully');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            \Log::warning("Item {$id} not found for deletion.");
            return $this->errorResponse('Item not found', [], 404);
        } catch (\Illuminate\Database\QueryException $e) {
            // Catch foreign key constraint violation
            if (str_contains($e->getMessage(), 'foreign key constraint')) {
                return $this->errorResponse('Item tidak dapat dihapus karena masih digunakan di transaksi (Request, Purchase Order, Stock Movement, atau Stock Opname).', [], 422);
            }
            \Log::error('Item delete error: ' . $e->getMessage());
            return $this->errorResponse('Gagal menghapus item. ' . $e->getMessage(), [], 422);
        } catch (\Exception $e) {
            \Log::error('Item destroy error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return $this->errorResponse($e->getMessage(), [], 422);
        }
    }
}
