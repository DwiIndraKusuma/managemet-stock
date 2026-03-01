<?php

namespace App\Services;

use App\Contracts\UserRepositoryInterface;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\DB;

class AuthService
{
    public function __construct(
        private UserRepositoryInterface $userRepository
    ) {}

    public function login(string $email, string $password)
    {
        $user = $this->userRepository->findByEmail($email);

        if (!$user || !Hash::check($password, $user->password)) {
            throw new \Exception('Invalid credentials');
        }

        if ($user->status !== 'active') {
            throw new \Exception('Akun Anda telah dinonaktifkan. Silakan hubungi administrator untuk mengaktifkan kembali akun Anda.');
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return [
            'user' => $user->load('role'),
            'token' => $token,
        ];
    }

    public function logout($user)
    {
        $user->currentAccessToken()->delete();
        return true;
    }

    public function forgotPassword(string $email)
    {
        $status = Password::sendResetLink(['email' => $email]);

        if ($status !== Password::RESET_LINK_SENT) {
            throw new \Exception('Unable to send reset link');
        }

        return true;
    }

    public function resetPassword(array $credentials)
    {
        $status = Password::reset(
            $credentials,
            function ($user, $password) {
                $user->password = Hash::make($password);
                $user->save();
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            throw new \Exception('Unable to reset password');
        }

        return true;
    }
}
