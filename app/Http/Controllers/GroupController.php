<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class GroupController extends Controller
{
    /**
     * Display a listing of groups the user belongs to.
     */
    public function index()
    {
        $user = Auth::user();
        $groups = $user->groups()->with('members')->get();
        
        return view('groups.index', compact('groups'));
    }

    /**
     * Display group details / workspace profile.
     */
    public function show($id)
    {
        $group = Group::with(['members' => function($q) {
            $q->wherePivot('status', 'Active');
        }])->findOrFail($id);
        
        $user = Auth::user();

        // Security: Must be a member to see details
        if (!$group->members()->where('user_id', $user->id)->exists()) {
            return redirect()->route('admin.dashboard')->with('error', 'Anda bukan anggota grup ini.');
        }

        $pendingRequests = [];
        if ($group->isAdmin($user->id)) {
            $pendingRequests = $group->members()
                ->wherePivot('status', 'Pending')
                ->get();
        }

        return view('groups.details', compact('group', 'pendingRequests'));
    }

    /**
     * Display group members management portal.
     * Deprecated: Now consolidated into show() / groups.details
     */
    public function members($id)
    {
        return redirect()->route('groups.show', $id);
    }

    /**
     * Store a newly created group in storage.
     * Price: Rp 249.000 (One-time)
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|in:Corporate,Family,Community,Other',
            'simulated_payment_verified' => 'required|accepted',
        ]);

        try {
            return DB::transaction(function() use ($request) {
                // 1. Create the Group
                $group = Group::create([
                    'name' => $request->name,
                    'type' => $request->type,
                    'invite_code' => Str::random(8),
                    'admin_id' => Auth::id(),
                    'is_paid' => true,
                ]);

                // 2. Attach creator as Admin
                $group->members()->attach(Auth::id(), [
                    'role' => 'admin',
                    'status' => 'Active'
                ]);

                // 3. Switch User context
                $user = Auth::user();
                $user->current_group_id = $group->id;
                $user->save();

                return response()->json([
                    'success' => true,
                    'message' => 'Grup berhasil dibuat! Biaya Rp 249.000 telah terverifikasi.',
                    'group' => $group
                ]);
            });
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Switch active group context.
     */
    public function switch(Request $request, $id)
    {
        $user = Auth::user();
        
        if ($id == 0) {
            $user->current_group_id = null;
            $user->save();
            return response()->json([
                'success' => true,
                'message' => 'Berhasil berpindah ke konteks Pribadi',
                'group_id' => null
            ]);
        }

        $group = Group::findOrFail($id);

        if (!$group->members()->where('user_id', $user->id)->exists()) {
            return response()->json(['success' => false, 'message' => 'Anda bukan anggota grup ini.'], 403);
        }

        $user->current_group_id = $group->id;
        $user->save();

        // Clear AI cache and generate "Quick Insight" for dashboard
        \Illuminate\Support\Facades\Cache::forget('ai_insight_' . $user->id . '_' . $group->id);
        \Illuminate\Support\Facades\Cache::forget('ai_smart_pick_' . $user->id);
        
        $aiService = new \App\Services\AiFinancialInsight();
        $quickInsight = $aiService->getSmartPick(); // Fresh data for new context

        // Flash to session so dashboard can show it as a "Quick Insight" toast/banner
        session()->flash('quick_insight', $quickInsight['title'] . ": " . $quickInsight['description']);

        return response()->json([
            'success' => true,
            'message' => 'Berhasil berpindah ke konteks ' . $group->name,
            'group_id' => $group->id,
            'quick_insight' => $quickInsight['description'] ?? 'Analitik grup sedang disiapkan...'
        ]);
    }

    /**
     * Accept a pending member into the group.
     */
    public function acceptMember(Request $request, $groupId, $userId)
    {
        $group = Group::findOrFail($groupId);
        if (!$group->isAdmin(Auth::id())) {
            return response()->json(['success' => false, 'message' => 'Hanya Admin yang dapat menyetujui anggota.'], 403);
        }

        $group->members()->updateExistingPivot($userId, ['status' => 'Active']);

        // Notify User
        \App\Models\Notification::create([
            'user_id' => $userId,
            'title' => 'Permintaan Disetujui!',
            'message' => "Selamat! Anda kini resmi menjadi anggota grup {$group->name}.",
            'type' => 'success',
            'link' => route('groups.show', $group->id)
        ]);

        return response()->json(['success' => true, 'message' => 'Anggota berhasil diterima.']);
    }

    /**
     * Reject/Remove a member from the group.
     */
    public function rejectMember(Request $request, $groupId, $userId)
    {
        $group = Group::findOrFail($groupId);
        if (!$group->isAdmin(Auth::id())) {
            return response()->json(['success' => false, 'message' => 'Hanya Admin yang dapat menolak anggota.'], 403);
        }

        $group->members()->detach($userId);

        // Notify User
        \App\Models\Notification::create([
            'user_id' => $userId,
            'title' => 'Permintaan Bergabung',
            'message' => "Maaf, permintaan Anda untuk bergabung ke grup {$group->name} belum dapat disetujui saat ini.",
            'type' => 'warning'
        ]);

        return response()->json(['success' => true, 'message' => 'Permintaan ditolak / Anggota dikeluarkan.']);
    }

    /**
     * Join a group via invite link/code (Landing Page).
     */
    public function join($code)
    {
        $group = Group::where('invite_code', $code)->firstOrFail();
        $user = Auth::user();

        // If user already a member, just go to dashboard
        if ($user && $group->members()->where('user_id', $user->id)->exists()) {
            return redirect()->route('admin.dashboard')->with('info', "Anda sudah terdaftar di grup {$group->name}.");
        }

        return view('groups.join', compact('group', 'user'));
    }

    /**
     * Submit a join request to a group.
     */
    public function requestToJoin(Request $request, $code)
    {
        $group = Group::where('invite_code', $code)->firstOrFail();
        $user = Auth::user();

        // 1. Requirement: Email Verified
        if (!$user->hasVerifiedEmail()) {
            return back()->with('error', 'Silakan verifikasi email Anda terlebih dahulu untuk bergabung ke grup.');
        }

        // 2. Already requested or member?
        if ($group->members()->where('user_id', $user->id)->exists()) {
            return redirect()->route('admin.dashboard')->with('info', "Anda sudah terdaftar atau memiliki permintaan pending di grup ini.");
        }

        // 3. Create Pending Request via Pivot
        $group->members()->attach($user->id, [
            'role' => 'member',
            'status' => 'Pending'
        ]);

        // 4. Notify Admins
        $admins = $group->members()->wherePivot('role', 'admin')->get();
        foreach ($admins as $admin) {
            \App\Models\Notification::create([
                'user_id' => $admin->id,
                'title' => 'Permintaan Join Baru',
                'message' => "{$user->name} ingin bergabung ke grup {$group->name}.",
                'type' => 'info',
                'link' => route('groups.show', $group->id)
            ]);
        }

        return redirect()->route('admin.dashboard')->with('status', "Permintaan join ke {$group->name} berhasil dikirim! Silakan tunggu persetujuan admin.");
    }
    public function personalWorkspace()
    {
        $user = Auth::user();
        $aiService = new \App\Services\AiFinancialInsight();
        
        // Fetch personal wallets
        $wallets = \App\Models\KategoriNamaTabungan::whereNull('group_id')
            ->where('user_id', $user->id)
            ->get();

        // Fetch personal stats
        $personalStats = \App\Models\Tabungan::whereNull('group_id')
            ->where('user_id', $user->id)
            ->selectRaw('SUM(CASE WHEN jenis = 2 THEN nominal ELSE 0 END) as total_income')
            ->selectRaw('SUM(CASE WHEN jenis = 1 THEN nominal ELSE 0 END) as total_expense')
            ->first();

        // 1. Weekly Chart Data (Last 7 Days)
        $weeklyChart = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = \Carbon\Carbon::now()->subDays($i);
            $total = \App\Models\Tabungan::whereNull('group_id')
                ->where('user_id', $user->id)
                ->whereDate('created_at', $date->toDateString())
                ->where('jenis', 1) // Pengeluaran
                ->sum('nominal');
            
            $weeklyChart[] = [
                'day' => $date->translatedFormat('D'),
                'total' => $total,
                'is_today' => $i === 0
            ];
        }

        // 2. AI Management Insights
        $aiInsights = $aiService->getManagementInsights();

        return view('personal.details', compact('user', 'wallets', 'personalStats', 'weeklyChart', 'aiInsights'));
    }
}
