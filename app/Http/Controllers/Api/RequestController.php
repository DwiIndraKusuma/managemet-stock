<?php

namespace App\Http\Controllers\Api;

use App\Services\RequestService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RequestController extends BaseController
{
    public function __construct(
        private RequestService $requestService
    ) {}

    public function index(Request $request): JsonResponse
    {
        try {
            $filters = $request->only(['status', 'user_id', 'search']);
            $perPage = $request->get('per_page', 15);
            
            $user = $request->user();
            $userRole = $user->role->name ?? null;
            
            // Filter technician requests to only show their own requests
            $requests = $this->requestService->paginate($filters, $perPage, $user->id, $userRole);

            return $this->successResponse($requests, 'Requests retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $data = $request->validate([
                'notes' => 'nullable|string',
                'items' => 'required|array|min:1',
                'items.*.item_id' => 'required|exists:items,id',
                'items.*.quantity' => 'required|integer|min:1',
                'items.*.notes' => 'nullable|string',
            ]);

            $requestModel = $this->requestService->create($data, $request->user()->id);

            return $this->successResponse($requestModel, 'Request created successfully', [], 201);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function show(Request $request, int $id): JsonResponse
    {
        try {
            $user = $request->user();
            $userRole = $user->role->name ?? null;
            
            $requestModel = $this->requestService->find($id);

            if (!$requestModel) {
                return $this->errorResponse('Request not found', [], 404);
            }
            
            // Check if technician can only view their own requests
            if ($userRole === 'technician' && $requestModel->user_id !== $user->id) {
                return $this->errorResponse('You can only view your own requests', [], 403);
            }

            return $this->successResponse($requestModel, 'Request retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $data = $request->validate([
                'notes' => 'nullable|string',
                'items' => 'sometimes|array|min:1',
                'items.*.item_id' => 'required|exists:items,id',
                'items.*.quantity' => 'required|integer|min:1',
                'items.*.notes' => 'nullable|string',
            ]);

            $requestModel = $this->requestService->update($id, $data);

            return $this->successResponse($requestModel, 'Request updated successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function submit(Request $request, int $id): JsonResponse
    {
        try {
            $user = $request->user();
            $userRole = $user->role->name ?? null;
            
            // Check if technician can only submit their own requests
            if ($userRole === 'technician') {
                $requestModel = $this->requestService->find($id);
                if ($requestModel && $requestModel->user_id !== $user->id) {
                    return $this->errorResponse('You can only submit your own requests', [], 403);
                }
            }
            
            $requestModel = $this->requestService->submit($id);
            return $this->successResponse($requestModel, 'Request submitted successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function approve(Request $request, int $id): JsonResponse
    {
        try {
            $requestModel = $this->requestService->approve($id, $request->user()->id);
            return $this->successResponse($requestModel, 'Request approved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function reject(Request $request, int $id): JsonResponse
    {
        try {
            $data = $request->validate([
                'reason' => 'required|string',
            ]);

            $requestModel = $this->requestService->reject($id, $request->user()->id, $data['reason']);
            return $this->successResponse($requestModel, 'Request rejected successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }
}
