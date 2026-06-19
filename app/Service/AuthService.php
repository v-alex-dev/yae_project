<?php
namespace App\Service;

use App\Models\User;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    public function register(array $data): User
    {
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => ($data['password']),
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;
        return compact('user', 'token');
    }

    public function login(array $data): array
    {
        if (!Auth::attempt($data)) {
            throw new AuthenticationException('Invalid credentials.');
        }

        $user = Auth::user();

        $user->tokens()->delete();

        $token = $user->createToken('auth_token')->plainTextToken;
        return compact('user', 'token');
    }

    public function logout(User $user): void
    {
        $user->currentAccessToken()->delete();
    }


}
