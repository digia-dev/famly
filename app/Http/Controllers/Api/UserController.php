<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends ApiController
{
    public function index()
    {
        $users = User::all();
        return $this->success($users);
    }


    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'role' => 'nullable|string',
            'family_id' => 'nullable|exists:families,id',
        ]);

        if ($validator->fails()) {
            return $this->error($validator->errors()->first(), 422);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role ?? 'viewer',
            'family_id' => $request->family_id,
        ]);

        \App\Models\ActivityLog::create([
            'user_id' => auth()->id() ?? 1,
            'action' => 'CREATE_USER',
            'entity_type' => 'User',
            'entity_id' => $user->id,
            'metadata' => ['name' => $user->name, 'email' => $user->email]
        ]);

        return $this->success($user, 'User created successfully', 201);

    }

    public function show($id)
    {
        $user = User::with('family')->find($id);
        if (!$user) {
            return $this->error('User not found', 444);
        }
        return $this->success($user);
    }

    public function update(Request $request, $id)
    {
        $user = User::find($id);
        if (!$user) {
            return $this->error('User not found', 444);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'string|max:255',
            'email' => 'string|email|max:255|unique:users,email,'.$id,
            'role' => 'string',
            'family_id' => 'nullable|exists:families,id',
        ]);

        if ($validator->fails()) {
            return $this->error($validator->errors()->first(), 422);
        }

        $user->update($request->except('password'));
        
        if ($request->filled('password')) {
            $user->update(['password' => Hash::make($request->password)]);
        }

        \App\Models\ActivityLog::create([
            'user_id' => auth()->id() ?? 1,
            'action' => 'UPDATE_USER',
            'entity_type' => 'User',
            'entity_id' => $user->id,
            'metadata' => ['name' => $user->name]
        ]);

        return $this->success($user, 'User updated successfully');

    }

    public function destroy($id)
    {
        $user = User::find($id);
        if (!$user) {
            return $this->error('User not found', 444);
        }
        
        $userName = $user->name;
        $user->delete();

        \App\Models\ActivityLog::create([
            'user_id' => auth()->id() ?? 1,
            'action' => 'DELETE_USER',
            'entity_type' => 'User',
            'entity_id' => $id,
            'metadata' => ['name' => $userName]
        ]);

        return $this->success(null, 'User deleted successfully');
    }

    public function impersonate($id)
    {
        $user = User::find($id);
        if (!$user) {
            return $this->error('User not found', 444);
        }

        // Log the impersonation event
        \App\Models\ActivityLog::create([
            'user_id' => auth()->id() ?? 1,
            'action' => 'IMPERSONATE',
            'entity_type' => 'User',
            'entity_id' => $id,
            'metadata' => ['target_user' => $user->name]
        ]);

        $token = $user->createToken('impersonation-token')->plainTextToken;

        return $this->success([
            'token' => $token,
            'user' => $user
        ], 'Impersonation token generated');
    }
}

