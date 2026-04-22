<?php

namespace App\Http\Controllers;

use App\Models\Tabungan;
use App\Models\User;
use App\Models\KategoriNamaTabungan;
use App\Models\KategoriJenisTabungan;
use App\Models\PlannedTransaction;
use App\Services\AiFinancialInsight;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon;

class DiscoveryController extends Controller
{
    /**
     * Generate discovery items for the dashboard (Gojek Style Tips & Insights).
     */
    public function index()
    {
        $items = $this->getItems();
        return view('admin.eksplor', compact('items'));
    }

    public function getItems()
    {
        $userId = Auth::id();
        $cacheKey = 'discovery_items_v2_' . $userId;

        // Use cache to avoid slow AI API calls on every page load
        return cache()->remember($cacheKey, 60 * 6, function () { // 6 hours
            $finalItems = [];
            
            try {
                $aiService = app(\App\Services\AiFinancialInsight::class);
                
                // 1. Check for Anomalies first (High Priority)
                $anomaly = $aiService->detectAnomalies();
                if ($anomaly) {
                    $finalItems[] = [
                        'category' => 'Anomali Terdeteksi',
                        'title' => 'Lonjakan Pengeluaran!',
                        'description' => $anomaly,
                        'action_label' => 'Cek Transaksi',
                        'action_url' => route('management.index'),
                        'bg' => 'bg-emerald-700',
                        'text' => 'text-white'
                    ];
                }

                // 2. Get standard AI insights
                $aiInsights = $aiService->getDiscoveryInsights();
                if (is_array($aiInsights)) {
                    $finalItems = array_merge($finalItems, $aiInsights);
                }

            } catch (\Exception $e) {
                Log::error("Discovery AI failed: " . $e->getMessage());
            }

            // Fill with default items if we have less than 3
            $fallbacks = [
                [
                    'category' => 'Kesimpulan AI',
                    'title' => 'Arus kasmu sehat hari ini!',
                    'description' => 'Tidak ada pengeluaran impulsif terdeteksi. Kamu hemat 12% dibanding kemarin.',
                    'action_label' => 'Detail Analisa',
                    'action_url' => route('reports.analysis'),
                    'bg' => 'bg-emerald-700',
                    'text' => 'text-white'
                ],
                [
                    'category' => 'Penawaran',
                    'title' => 'Fokus ke tabungan BCA Utama?',
                    'description' => 'Tabungan ini paling sering diisi. Mau tambah target bulanan?',
                    'action_label' => 'Atur Target',
                    'action_url' => route('management.index'),
                    'bg' => 'bg-white',
                    'text' => 'text-slate-900'
                ],
                [
                    'category' => 'Fitur Baru',
                    'title' => 'Laporan Mingguan',
                    'description' => 'Kini kamu bisa terima otomatis ringkasan pengeluaran setiap Senin pagi.',
                    'action_label' => 'Coba Sekarang',
                    'action_url' => route('profile.edit'),
                    'bg' => 'bg-amber-600',
                    'text' => 'text-white'
                ]
            ];

            foreach ($fallbacks as $fb) {
                if (count($finalItems) >= 3) break;
                $finalItems[] = $fb;
            }

            return array_slice($finalItems, 0, 3);
        });
    }

    public function getDailyTip()
    {
        try {
            return app(AiFinancialInsight::class)->getSmartPick();
        } catch (\Exception $e) {
            $items = $this->getItems();
            $seed = (int) date('Ymd');
            srand($seed);
            $pick = $items[rand(0, count($items) - 1)];
            srand();
            return $pick;
        }
    }

    /**
     * AI Chat Assistant Interface
     */
    public function aiAssistant()
    {
        $statusAnggaran = Tabungan::whereHas('kategoriJenis', fn($q) => $q->where('jenis', 'Pengeluaran'))->where('created_at', '>=', now()->startOfMonth())->sum('nominal');
        $tabunganAi = Tabungan::whereHas('kategoriJenis', fn($q) => $q->where('jenis', 'Pemasukan'))->where('created_at', '>=', now()->startOfMonth())->sum('nominal');
        
        $wallets = KategoriNamaTabungan::all();
        $recentTransactions = Tabungan::with('kategoriNama')->orderBy('created_at', 'desc')->limit(15)->get();
        $activeGroup = Auth::user()->currentGroup;
        $userGroups = Auth::user()->groups;
        
        return view('ai.index', compact('statusAnggaran', 'tabunganAi', 'wallets', 'recentTransactions', 'activeGroup', 'userGroups'));
    }

    /**
     * Process AI Chat requests
     */
    public function processAI(Request $request)
    {
        $request->validate(['message' => 'required|string']);
        $user = auth()->user();

        // Freemium Check: Usage Limit for Trial Users
        if (!$user->isPremium()) {
            if ($user->ai_usage_count >= 3) {
                return response()->json([
                    'success' => true,
                    'response' => "Jatah tanya AI Anda hari ini sudah habis (Maks 3x). **Upgrade ke Premium** hanya Rp 19.900/bulan untuk akses tanpa batas, laporan lengkap, dan fitur eksklusif lainnya!"
                ]);
            }
        }

        // Restriction: Only Admin can use @Fams
        if (str_contains($request->message, '@Fams')) {
            $group = \App\Models\Group::find($user->current_group_id);
            if (!$group || !$group->isAdmin($user->id)) {
                return response()->json([
                    'success' => true,
                    'response' => "Maaf {$user->name}, tag **@Fams** khusus digunakan oleh **Admin Grup** untuk analisis finansial kolektif. Silakan hubungi admin Anda atau upgrade peran Anda untuk menggunakan fitur AI ini."
                ]);
            }
        }
        
        try {
            $aiService = new AiFinancialInsight();
            $response = $aiService->chat($request->message);
            
            // Increment Usage Count
            $user->increment('ai_usage_count');
            
            return response()->json([
                'success' => true,
                'response' => $response
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memproses permintaan AI: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * View for AI Receipt Scanning
     */
    public function aiScan()
    {
        $user = Auth::user();
        
        // Fetch ALL categories the user is entitled to (Personal + Their Groups)
        // We bypass GroupScope to allow context switching within the UI
        $allCategories = KategoriNamaTabungan::withoutGlobalScope(\App\Models\Scopes\GroupScope::class)
            ->where(function($q) use ($user) {
                // Personal wallets
                $q->where(function($sq) use ($user) {
                    $sq->whereNull('group_id')->where('user_id', $user->id);
                })
                // Group wallets
                ->orWhereIn('group_id', $user->groups->pluck('id'));
            })
            ->get();

        $categoryIds = $allCategories->pluck('id')->toArray();

        // Fetch balances for these categories
        $stats = Tabungan::withoutGlobalScope(\App\Models\Scopes\GroupScope::class)
            ->join('kategori_jenis_tabungans', 'tabungans.jenis', '=', 'kategori_jenis_tabungans.id')
            ->whereIn('tabungans.nama', $categoryIds)
            ->select('tabungans.nama as category_id', \DB::raw("
                SUM(CASE WHEN kategori_jenis_tabungans.jenis = 'Pemasukan' THEN tabungans.nominal ELSE 0 END) -
                SUM(CASE WHEN kategori_jenis_tabungans.jenis = 'Pengeluaran' THEN tabungans.nominal ELSE 0 END) as balance
            "))
            ->groupBy('tabungans.nama')
            ->get()
            ->keyBy('category_id');

        $allCategories->each(function($cat) use ($stats) {
            $cat->balance = $stats->get($cat->id)->balance ?? 0;
            // Add a friendly context name for the UI
            $cat->context_name = $cat->group_id ? ($cat->group->name ?? 'Grup') : 'Pribadi';
        });

        $wallets = $allCategories->where('wallet_type', 'wallet')->values();
        $posItems = $allCategories->where('wallet_type', 'pos')->values();
        $savings = $allCategories->where('wallet_type', 'savings')->values();

        return view('admin.ai.scan-struk', compact('wallets', 'posItems', 'savings'));
    }

    /**
     * View for AI Voice Recording
     */
    public function aiVoice()
    {
        $user = Auth::user();
        
        $allCategories = KategoriNamaTabungan::withoutGlobalScope(\App\Models\Scopes\GroupScope::class)
            ->where(function($q) use ($user) {
                $q->where(function($sq) use ($user) {
                    $sq->whereNull('group_id')->where('user_id', $user->id);
                })
                ->orWhereIn('group_id', $user->groups->pluck('id'));
            })
            ->get();

        $categoryIds = $allCategories->pluck('id')->toArray();

        $stats = Tabungan::withoutGlobalScope(\App\Models\Scopes\GroupScope::class)
            ->join('kategori_jenis_tabungans', 'tabungans.jenis', '=', 'kategori_jenis_tabungans.id')
            ->whereIn('tabungans.nama', $categoryIds)
            ->select('tabungans.nama as category_id', \DB::raw("
                SUM(CASE WHEN kategori_jenis_tabungans.jenis = 'Pemasukan' THEN tabungans.nominal ELSE 0 END) -
                SUM(CASE WHEN kategori_jenis_tabungans.jenis = 'Pengeluaran' THEN tabungans.nominal ELSE 0 END) as balance
            "))
            ->groupBy('tabungans.nama')
            ->get()
            ->keyBy('category_id');

        $allCategories->each(function($cat) use ($stats) {
            $cat->balance = $stats->get($cat->id)->balance ?? 0;
            $cat->context_name = $cat->group_id ? ($cat->group->name ?? 'Grup') : 'Pribadi';
        });

        $wallets = $allCategories->where('wallet_type', 'wallet')->values();
        $posItems = $allCategories->where('wallet_type', 'pos')->values();
        $savings = $allCategories->where('wallet_type', 'savings')->values();

        return view('admin.ai.voice-record', compact('wallets', 'posItems', 'savings'));
    }

    /**
     * Process Voice Command via AI
     */
    public function processVoice(Request $request)
    {
        $request->validate(['text' => 'required|string']);

        try {
            Log::info("Voice command received: " . $request->text);
            
            $aiService = new AiFinancialInsight();
            $data = $aiService->parseVoiceCommand($request->text);
            
            Log::info("AI Parsed Data: " . json_encode($data));

            if (!$data || !isset($data['total'])) {
                throw new \Exception("AI tidak dapat menangkap rincian transaksi dari rekaman bicara Anda. Coba sebutkan nominal dan keterangannya lebih jelas.");
            }

            return response()->json([
                'success' => true,
                'data' => $data
            ]);
        } catch (\Exception $e) {
            Log::error("Voice processing failed: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    /**
     * Analyze Receipt via AI Vision
     */
    public function processReceipt(Request $request)
    {
        $request->validate(['image' => 'required|string']); 

        try {
            $aiService = new AiFinancialInsight();
            $data = $aiService->analyzeReceipt($request->image);
            
            if (!$data || !isset($data['total'])) {
                throw new \Exception("AI tidak dapat membaca data struk dengan jelas. Pastikan foto struk terlihat terang dan fokus.");
            }

            return response()->json([
                'success' => true,
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Save AI-structured transaction to database
     */
    public function saveAiTransaction(Request $request)
    {
        // Pre-processing for numeric robustness
        if ($request->has('total')) {
            $total = $request->total;
            if (is_string($total)) {
                $total = preg_replace('/[^0-9]/', '', $total);
                $request->merge(['total' => $total]);
            }
        }

        $request->validate([
            'merchant' => 'nullable|string',
            'description' => 'required|string',
            'total' => 'required|numeric|min:1',
            'wallet_name' => 'required|string',
            'items' => 'nullable|array'
        ]);

        try {
            $walletName = trim($request->wallet_name);
            $category = KategoriNamaTabungan::where('nama', $walletName)->first();
            
            if (!$category) {
                $category = KategoriNamaTabungan::where('nama', 'LIKE', "%{$walletName}%")->first();
            }
            
            if (!$category) {
                $category = KategoriNamaTabungan::where('wallet_type', 'pos')->first();
            }

            if (!$category) {
                 return response()->json(['success' => false, 'message' => 'Kategori dompet tidak ditemukan.'], 422);
            }

            $type = KategoriJenisTabungan::where('jenis', 'Pengeluaran')->first();
            
            $label = $request->description;
            if ($request->merchant) {
                $label .= " di " . $request->merchant;
            }

            $tabungan = Tabungan::create([
                'nama' => $category->id,
                'jenis' => $type->id,
                'nominal' => $request->total,
                'keterangan' => $label,
                'user_id' => Auth::id(),
                'group_id' => ($request->group_id && $request->group_id !== 'personal') ? $request->group_id : null,
                'status' => 'success',
                'metadata_ai' => [
                    'merchant' => $request->merchant,
                    'description' => $request->description,
                    'items' => $request->items,
                    'is_ai' => true
                ]
            ]);

            return response()->json([
                'success' => true, 
                'message' => 'Transaksi berhasil dicatat ke ' . $category->nama . '!',
                'tabungan' => $tabungan
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    /**
     * Unified Global Search (Instant Results)
     */
    public function search(Request $request)
    {
        $q = $request->get('q');
        if (!$q || strlen($q) < 2) return response()->json(['results' => []]);

        $user = auth()->user();
        $results = collect();

        // 1. Groups & Wallets (Managed by GroupScope)
        $wallets = KategoriNamaTabungan::where('nama', 'LIKE', "%{$q}%")
            ->take(3)
            ->get()
            ->map(fn($w) => [
                'type' => 'wallet',
                'title' => $w->nama,
                'subtitle' => 'Dompet / Pos Keuangan',
                'icon' => $w->icon ?? 'account_balance_wallet',
                'url' => route('management.index', ['id' => $w->id])
            ]);
        $results = $results->concat($wallets);

        // 2. Transactions (Managed by GroupScope)
        $transactions = Tabungan::where('keterangan', 'LIKE', "%{$q}%")
            ->with('kategoriNama')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get()
            ->map(fn($t) => [
                'type' => 'transaction',
                'title' => $t->keterangan,
                'subtitle' => 'Transaksi ' . ($t->kategoriNama->nama ?? ''),
                'icon' => 'receipt_long',
                'url' => route('management.index') // Deep link logic could be improved
            ]);
        $results = $results->concat($transactions);

        // 3. Agenda & Rituals (Managed by GroupScope)
        $agenda = PlannedTransaction::where('keterangan', 'LIKE', "%{$q}%")
            ->orWhere('nama', 'LIKE', "%{$q}%")
            ->take(3)
            ->get()
            ->map(fn($a) => [
                'type' => 'agenda',
                'title' => $a->keterangan ?? $a->nama,
                'subtitle' => 'Agenda ' . ucfirst($a->activity_type),
                'icon' => 'event',
                'url' => route('agenda.index')
            ]);
        $results = $results->concat($agenda);

        // 4. Group Members
        if ($user->current_group_id) {
            $members = User::whereHas('groups', fn($query) => $query->where('group_id', $user->current_group_id))
                ->where('name', 'LIKE', "%{$q}%")
                ->take(3)
                ->get()
                ->map(fn($m) => [
                    'type' => 'user',
                    'title' => $m->name,
                    'subtitle' => 'Anggota Keluarga',
                    'icon' => 'group',
                    'url' => '#'
                ]);
            $results = $results->concat($members);
        }

        return response()->json(['results' => $results]);
    }

    /**
     * Get Search Suggestions & History
     */
    public function suggestions()
    {
        $user = auth()->user();

        // Popular/Recent Context items
        $popular = KategoriNamaTabungan::take(4)->get();
        
        // Mock history if empty
        $history = \App\Models\SearchHistory::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        return response()->json([
            'popular' => $popular,
            'history' => $history
        ]);
    }
}
