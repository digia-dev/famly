<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\GroupRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;

class GroupJoinController extends Controller
{
    /**
     * Handle the join link: famly.co.id/join/{code}
     */
    public function join($code)
    {
        try {
            // Decrypt the group ID
            $groupId = Crypt::decryptString($code);
            $group = Group::findOrFail($groupId);
            $user = Auth::user();

            // Check if already a member
            if ($group->users()->where('user_id', $user->id)->exists()) {
                return redirect()->route('admin.dashboard')->with('status', 'Anda sudah menjadi anggota di grup ini.');
            }

            // Check if request already pending
            $existingRequest = GroupRequest::where('user_id', $user->id)
                ->where('group_id', $group->id)
                ->first();

            if ($existingRequest) {
                return view('groups.join-status', compact('group', 'existingRequest'));
            }

            return view('groups.join-confirm', compact('group', 'code'));
        } catch (\Exception $e) {
            abort(404, 'Link join tidak valid.');
        }
    }

    /**
     * Submit the join request
     */
    public function request(Request $request, $code)
    {
        $groupId = Crypt::decryptString($code);
        $user = Auth::user();

        GroupRequest::updateOrCreate(
            ['user_id' => $user->id, 'group_id' => $groupId],
            ['status' => 'pending', 'message' => $request->message]
        );

        return redirect()->route('admin.dashboard')->with('status', 'Permintaan join telah dikirim. Admin akan segera meninjau!');
    }

    /**
     * Admin view to see pending requests
     */
    public function manageRequests($groupId)
    {
        $group = Group::findOrFail($groupId);
        // Only Admin can manage
        if (!$group->isAdmin(Auth::id())) abort(403);

        $requests = GroupRequest::where('group_id', $groupId)->where('status', 'pending')->with('user')->get();
        return view('groups.requests', compact('group', 'requests'));
    }

    /**
     * Approve or Reject request
     */
    public function updateStatus(Request $request, $requestId)
    {
        $gRequest = GroupRequest::findOrFail($requestId);
        $group = Group::findOrFail($gRequest->group_id);
        
        if (!$group->isAdmin(Auth::id())) abort(403);

        if ($request->action === 'approve') {
            $gRequest->update(['status' => 'approved']);
            // Add user to group
            $group->users()->attach($gRequest->user_id, ['role' => 'member']);
        } else {
            $gRequest->update(['status' => 'rejected']);
        }

        return back()->with('status', 'Status permintaan diperbarui.');
    }
}
