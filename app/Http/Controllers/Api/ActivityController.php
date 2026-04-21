<?php

namespace App\Http\Controllers\Api;

use App\Models\ActivityLog;
use Illuminate\Http\Request;

class ActivityController extends ApiController
{
    public function index()
    {
        $logs = ActivityLog::with('user')->latest()->get();
        return $this->success($logs);
    }

    public function store(Request $request)
    {
        // Internal system log creation
        $log = ActivityLog::create([
            'user_id' => auth()->id() ?? 1, // Fallback to first user if not auth (for dev)
            'action' => $request->action,
            'entity_type' => $request->entity_type,
            'entity_id' => $request->entity_id,
            'metadata' => $request->metadata,
        ]);

        return $this->success($log, 'Log recorded', 201);
    }
}
