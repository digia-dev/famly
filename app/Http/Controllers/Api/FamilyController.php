<?php

namespace App\Http\Controllers\Api;

use App\Models\Family;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class FamilyController extends ApiController
{
    public function index()
    {
        $families = Family::with('users')->get();
        return $this->success($families);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'family_name' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return $this->error($validator->errors()->first(), 422);
        }

        $family = Family::create($request->all());

        \App\Models\ActivityLog::create([
            'user_id' => auth()->id() ?? 1,
            'action' => 'CREATE',
            'entity_type' => 'Family',
            'entity_id' => $family->id,
            'metadata' => ['name' => $family->family_name]
        ]);

        return $this->success($family, 'Family created successfully', 201);

    }

    public function show($id)
    {
        $family = Family::with('users')->find($id);
        if (!$family) {
            return $this->error('Family not found', 444);
        }
        return $this->success($family);
    }

    public function update(Request $request, $id)
    {
        $family = Family::find($id);
        if (!$family) {
            return $this->error('Family not found', 444);
        }

        $validator = Validator::make($request->all(), [
            'family_name' => 'string|max:255',
        ]);

        if ($validator->fails()) {
            return $this->error($validator->errors()->first(), 422);
        }

        $family->update($request->all());

        \App\Models\ActivityLog::create([
            'user_id' => auth()->id() ?? 1,
            'action' => 'UPDATE',
            'entity_type' => 'Family',
            'entity_id' => $family->id,
            'metadata' => ['changes' => $request->all()]
        ]);

        return $this->success($family, 'Family updated successfully');

    }

    public function destroy($id)
    {
        $family = Family::find($id);
        if (!$family) {
            return $this->error('Family not found', 444);
        }
        $family->delete();

        \App\Models\ActivityLog::create([
            'user_id' => auth()->id() ?? 1,
            'action' => 'DELETE',
            'entity_type' => 'Family',
            'entity_id' => $id,
            'metadata' => ['name' => $family->family_name]
        ]);

        return $this->success(null, 'Family deleted successfully');

    }
}
