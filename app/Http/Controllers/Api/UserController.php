<?php

namespace App\Http\Controllers\Api;

use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends BaseController
{
    public function __construct(
        private UserService $userService
    ) {}

    public function index(Request $request): JsonResponse
    {
        try {
            $filters = $request->only(['role_id', 'status', 'search']);
            $perPage = $request->get('per_page', 15);

            $users = $this->userService->paginate($filters, $perPage);

            return $this->successResponse($users, 'Users retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $validator = \Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|string|min:8',
                'role_id' => 'required|exists:roles,id',
                'status' => 'sometimes|in:active,inactive',
            ]);

            if ($validator->fails()) {
                return $this->validationErrorResponse($validator);
            }

            $data = $validator->validated();
            $user = $this->userService->create($data);
            $user->refresh();

            return $this->successResponse($user, 'User created successfully', [], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::warning("Validation error creating user: " . $e->getMessage(), ['errors' => $e->errors()]);
            return $this->validationErrorResponse($e->validator);
        } catch (\Exception $e) {
            \Log::error("Error creating user: " . $e->getMessage(), ['exception' => $e]);
            return $this->errorResponse($e->getMessage());
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $user = $this->userService->find($id);

            if (!$user) {
                return $this->errorResponse('User not found', [], 404);
            }

            return $this->successResponse($user, 'User retrieved successfully');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            \Log::warning("User {$id} not found for show.");
            return $this->errorResponse('User not found', [], 404);
        } catch (\Exception $e) {
            \Log::error("Error retrieving user {$id}: " . $e->getMessage(), ['exception' => $e]);
            return $this->errorResponse($e->getMessage());
        }
    }

    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $validator = \Validator::make($request->all(), [
                'name' => 'sometimes|required|string|max:255',
                'email' => 'sometimes|required|email|unique:users,email,' . $id,
                'password' => 'sometimes|string|min:8',
                'role_id' => 'sometimes|required|exists:roles,id',
                'status' => 'sometimes|in:active,inactive',
            ]);

            if ($validator->fails()) {
                return $this->validationErrorResponse($validator);
            }

            $user = $this->userService->update($id, $validator->validated());
            $user->refresh();

            return $this->successResponse($user, 'User updated successfully');
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::warning("Validation error updating user {$id}: " . $e->getMessage(), ['errors' => $e->errors()]);
            return $this->validationErrorResponse($e->validator);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            \Log::warning("User {$id} not found for update.");
            return $this->errorResponse('User not found', [], 404);
        } catch (\Exception $e) {
            \Log::error("Error updating user {$id}: " . $e->getMessage(), ['exception' => $e]);
            return $this->errorResponse($e->getMessage());
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $this->userService->delete($id);
            return $this->successResponse(null, 'User deleted successfully');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            \Log::warning("User {$id} not found for deletion.");
            return $this->errorResponse('User not found', [], 404);
        } catch (\Exception $e) {
            \Log::error("Error deleting user {$id}: " . $e->getMessage(), ['exception' => $e]);
            return $this->errorResponse($e->getMessage());
        }
    }

    public function activate(Request $request, int $id): JsonResponse
    {
        try {
            $user = $this->userService->activate($id);
            return $this->successResponse($user, 'User activated successfully');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            \Log::warning("User {$id} not found for activation.");
            return $this->errorResponse('User not found', [], 404);
        } catch (\Exception $e) {
            \Log::error("Error activating user {$id}: " . $e->getMessage(), ['exception' => $e]);
            return $this->errorResponse($e->getMessage());
        }
    }

    public function deactivate(Request $request, int $id): JsonResponse
    {
        try {
            $user = $this->userService->deactivate($id);
            return $this->successResponse($user, 'User deactivated successfully');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            \Log::warning("User {$id} not found for deactivation.");
            return $this->errorResponse('User not found', [], 404);
        } catch (\Exception $e) {
            \Log::error("Error deactivating user {$id}: " . $e->getMessage(), ['exception' => $e]);
            return $this->errorResponse($e->getMessage());
        }
    }
}
