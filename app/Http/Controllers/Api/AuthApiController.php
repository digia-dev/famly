<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthApiController extends ApiController
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'device_name' => 'nullable'
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return $this->error('Email atau password salah.', 401);
        }

        // UNTUK CMS SINKRON: Hanya izinkan role Admin (dins)
        if ($user->role !== 'dins') {
            return $this->error('Akses ditolak. Anda bukan Administrator.', 403);
        }

        $token = $user->createToken($request->device_name ?? 'cms-token')->plainTextToken;

        return $this->success([
            'user' => $user,
            'token' => $token
        ], 'Login berhasil.');
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        return \DB::transaction(function() use ($request) {
            // 1. Create Family
            $family = \App\Models\Family::create([
                'family_name' => "Keluarga " . explode(' ', $request->name)[0]
            ]);

            // 2. Create User
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => 'dins',
                'family_id' => $family->id
            ]);

            // 3. Seed 기본 Wallet
            \App\Models\KategoriNamaTabungan::create([
                'nama' => 'Dompet Utama',
                'icon' => 'account_balance_wallet',
                'family_id' => $family->id,
                'wallet_type' => 'wallet'
            ]);

            $token = $user->createToken('auth_token')->plainTextToken;

            return $this->success([
                'user' => $user,
                'token' => $token
            ], 'Registrasi berhasil.');
        });
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return $this->success(null, 'Logout berhasil.');
    }
}
