<?php

namespace App\Repositories;

use App\Contracts\RequestRepositoryInterface;
use App\Models\Request;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class RequestRepository implements RequestRepositoryInterface
{
    public function all(array $filters = []): Collection
    {
        $query = Request::with(['user', 'approver', 'requestItems.item']);

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        if (isset($filters['search'])) {
            $query->where('request_number', 'like', '%' . $filters['search'] . '%');
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Request::with(['user', 'approver', 'requestItems.item']);

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        if (isset($filters['search'])) {
            $query->where('request_number', 'like', '%' . $filters['search'] . '%');
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    public function find(int $id): ?Request
    {
        return Request::with(['user', 'approver', 'requestItems.item'])->find($id);
    }

    public function create(array $data): Request
    {
        return Request::create($data);
    }

    public function update(int $id, array $data): Request
    {
        $request = Request::findOrFail($id);
        $request->update($data);
        return $request->fresh(['user', 'approver', 'requestItems.item']);
    }

    public function delete(int $id): bool
    {
        return Request::findOrFail($id)->delete();
    }

    public function findByNumber(string $requestNumber): ?Request
    {
        return Request::where('request_number', $requestNumber)->first();
    }

    public function updateStatus(int $id, string $status, array $additionalData = []): Request
    {
        $request = Request::findOrFail($id);
        $data = array_merge(['status' => $status], $additionalData);
        $request->update($data);
        return $request->fresh(['user', 'approver', 'requestItems.item']);
    }
}
