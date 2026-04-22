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
            // 1. Create Group (formerly Family)
            $group = \App\Models\Group::create([
                'name' => "Keluarga " . explode(' ', $request->name)[0],
                'type' => 'Family',
                'invite_code' => strtoupper(\Illuminate\Support\Str::random(10)),
            ]);

            // 2. Create User
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => 'dins',
                'group_id' => $group->id,
                'current_group_id' => $group->id,
            ]);

            // Set admin_id for the group
            $group->update(['admin_id' => $user->id]);

            // Add user to group_members table
            \DB::table('group_members')->insert([
                'group_id' => $group->id,
                'user_id' => $user->id,
                'role' => 'admin',
                'status' => 'Active',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // 3. Seed basic Wallet
            \App\Models\KategoriNamaTabungan::create([
                'nama' => 'Dompet Utama',
                'icon' => 'account_balance_wallet',
                'group_id' => $group->id,
                'wallet_type' => 'wallet',
                'user_id' => $user->id,
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
