<?php

namespace App\Http\Controllers\Api;

use App\Models\Role;
use Illuminate\Http\JsonResponse;

class RoleController extends BaseController
{
    public function index(): JsonResponse
    {
        try {
            $roles = Role::select('id', 'name', 'display_name', 'description')->get();
            return $this->successResponse($roles, 'Roles retrieved successfully');
        } catch (\Exception $e) {
            \Log::error("Error retrieving roles: " . $e->getMessage(), ['exception' => $e]);
            return $this->errorResponse($e->getMessage());
        }
    }
}
