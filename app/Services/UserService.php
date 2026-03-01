<?php

namespace App\Services;

use App\Contracts\UserRepositoryInterface;
use Illuminate\Support\Facades\Hash;

class UserService
{
    public function __construct(
        private UserRepositoryInterface $userRepository
    ) {}

    public function getAll(array $filters = [])
    {
        return $this->userRepository->all($filters);
    }

    public function paginate(array $filters = [], int $perPage = 15)
    {
        return $this->userRepository->paginate($filters, $perPage);
    }

    public function find(int $id)
    {
        return $this->userRepository->find($id);
    }

    public function create(array $data)
    {
        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        return $this->userRepository->create($data);
    }

    public function update(int $id, array $data)
    {
        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        return $this->userRepository->update($id, $data);
    }

    public function delete(int $id)
    {
        return $this->userRepository->delete($id);
    }

    public function activate(int $id)
    {
        return $this->userRepository->updateStatus($id, 'active');
    }

    public function deactivate(int $id)
    {
        return $this->userRepository->updateStatus($id, 'inactive');
    }
}
