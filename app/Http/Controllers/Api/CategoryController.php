<?php

namespace App\Http\Controllers\Api;

use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CategoryController extends BaseController
{
    public function index(Request $request): JsonResponse
    {
        try {
            $categories = Category::all();
            return $this->successResponse($categories, 'Categories retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $validator = \Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'code' => 'nullable|string|max:255|unique:categories,code',
                'description' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return $this->validationErrorResponse($validator);
            }

            $data = $validator->validated();
            
            // Remove null values but keep empty strings for description
            $data = array_filter($data, function($value, $key) {
                if ($key === 'description') {
                    return true; // Allow empty description
                }
                return $value !== null && $value !== '';
            }, ARRAY_FILTER_USE_BOTH);
            
            $category = Category::create($data);

            return $this->successResponse($category, 'Category created successfully', [], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->validationErrorResponse($e->validator);
        } catch (\Exception $e) {
            \Log::error('Category store error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return $this->errorResponse($e->getMessage());
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $category = Category::find($id);

            if (!$category) {
                return $this->errorResponse('Category not found', [], 404);
            }

            return $this->successResponse($category, 'Category retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $validator = \Validator::make($request->all(), [
                'name' => 'sometimes|required|string|max:255',
                'code' => 'sometimes|nullable|string|max:255|unique:categories,code,' . $id,
                'description' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return $this->validationErrorResponse($validator);
            }

            $category = Category::findOrFail($id);
            $data = $validator->validated();
            
            // Remove null values but keep empty strings for description
            $data = array_filter($data, function($value, $key) {
                if ($key === 'description') {
                    return true; // Allow empty description
                }
                return $value !== null && $value !== '';
            }, ARRAY_FILTER_USE_BOTH);
            
            $category->update($data);
            $category->refresh();

            return $this->successResponse($category, 'Category updated successfully');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            \Log::warning("Category {$id} not found for update.");
            return $this->errorResponse('Category not found', [], 404);
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::warning("Validation error updating category {$id}: " . $e->getMessage(), ['errors' => $e->errors()]);
            return $this->validationErrorResponse($e->validator);
        } catch (\Exception $e) {
            \Log::error('Category update error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return $this->errorResponse($e->getMessage());
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $category = Category::findOrFail($id);
            
            // Check if category is used in items
            $itemsCount = $category->items()->count();
            if ($itemsCount > 0) {
                return $this->errorResponse("Kategori tidak dapat dihapus karena sudah digunakan di {$itemsCount} Item. Silakan hapus atau ubah Item yang menggunakan kategori ini terlebih dahulu.", [], 422);
            }
            
            $category->delete();

            return $this->successResponse(null, 'Category deleted successfully');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            \Log::warning("Category {$id} not found for deletion.");
            return $this->errorResponse('Category not found', [], 404);
        } catch (\Illuminate\Database\QueryException $e) {
            // Catch foreign key constraint violation
            if (str_contains($e->getMessage(), 'foreign key constraint')) {
                return $this->errorResponse('Kategori tidak dapat dihapus karena masih digunakan di Item. Silakan hapus atau ubah Item yang menggunakan kategori ini terlebih dahulu.', [], 422);
            }
            \Log::error('Category delete error: ' . $e->getMessage());
            return $this->errorResponse('Gagal menghapus kategori. ' . $e->getMessage());
        } catch (\Exception $e) {
            \Log::error('Category delete error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return $this->errorResponse($e->getMessage());
        }
    }
}
