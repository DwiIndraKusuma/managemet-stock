<?php

namespace App\Http\Controllers\Api;

use App\Models\Unit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UnitController extends BaseController
{
    public function index(Request $request): JsonResponse
    {
        try {
            $units = Unit::all();
            return $this->successResponse($units, 'Units retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $validator = \Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'code' => 'nullable|string|max:255|unique:units,code',
                'description' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return $this->validationErrorResponse($validator);
            }

            $data = $validator->validated();
            $unit = Unit::create($data);

            return $this->successResponse($unit, 'Unit created successfully', [], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->validationErrorResponse($e->validator);
        } catch (\Exception $e) {
            \Log::error('Unit store error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return $this->errorResponse($e->getMessage());
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $unit = Unit::find($id);

            if (!$unit) {
                return $this->errorResponse('Unit not found', [], 404);
            }

            return $this->successResponse($unit, 'Unit retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $validator = \Validator::make($request->all(), [
                'name' => 'sometimes|required|string|max:255',
                'code' => 'sometimes|nullable|string|max:255|unique:units,code,' . $id,
                'description' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return $this->validationErrorResponse($validator);
            }

            $unit = Unit::findOrFail($id);
            $data = $validator->validated();
            $unit->update($data);
            $unit->refresh();

            return $this->successResponse($unit, 'Unit updated successfully');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            \Log::warning("Unit {$id} not found for update.");
            return $this->errorResponse('Unit not found', [], 404);
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::warning("Validation error updating unit {$id}: " . $e->getMessage(), ['errors' => $e->errors()]);
            return $this->validationErrorResponse($e->validator);
        } catch (\Exception $e) {
            \Log::error('Unit update error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return $this->errorResponse($e->getMessage());
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $unit = Unit::findOrFail($id);
            
            // Check if unit is used in items
            $itemsCount = $unit->items()->count();
            if ($itemsCount > 0) {
                return $this->errorResponse("Unit tidak dapat dihapus karena sudah digunakan di {$itemsCount} Item. Silakan hapus atau ubah Item yang menggunakan unit ini terlebih dahulu.", [], 422);
            }
            
            $unit->delete();

            return $this->successResponse(null, 'Unit deleted successfully');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            \Log::warning("Unit {$id} not found for deletion.");
            return $this->errorResponse('Unit not found', [], 404);
        } catch (\Illuminate\Database\QueryException $e) {
            // Catch foreign key constraint violation
            if (str_contains($e->getMessage(), 'foreign key constraint')) {
                return $this->errorResponse('Unit tidak dapat dihapus karena masih digunakan di Item. Silakan hapus atau ubah Item yang menggunakan unit ini terlebih dahulu.', [], 422);
            }
            \Log::error('Unit delete error: ' . $e->getMessage());
            return $this->errorResponse('Gagal menghapus unit. ' . $e->getMessage());
        } catch (\Exception $e) {
            \Log::error('Unit delete error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return $this->errorResponse($e->getMessage());
        }
    }
}
