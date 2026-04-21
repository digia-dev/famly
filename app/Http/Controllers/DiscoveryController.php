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
        $cacheKey = 'discovery_items_' . $userId;

        // Use cache to avoid slow AI API calls on every page load
        return cache()->remember($cacheKey, 60 * 6, function () { // 6 hours
            try {
                $aiItems = app(\App\Services\AiFinancialInsight::class)->getDiscoveryInsights();
                if (is_array($aiItems) && count($aiItems) > 3) {
                    return array_slice($aiItems, 0, 3);
                }
            } catch (\Exception $e) {
                Log::error("Discovery AI failed: " . $e->getMessage());
            }

            // Fallback to high-quality static items if AI fails or hasn't loaded
            return [
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
                    'bg' => 'bg-slate-900',
                    'text' => 'text-white'
                ]
            ];
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
        
        return view('ai.index', compact('statusAnggaran', 'tabunganAi', 'wallets'));
    }

    /**
     * Process AI Chat requests
     */
    public function processAI(Request $request)
    {
        $request->validate(['message' => 'required|string']);
        
        try {
            $aiService = new AiFinancialInsight();
            $response = $aiService->chat($request->message);
            
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
        $familyId = $user->family_id;

        // Optimized balance fetching with a single query
        $allCategories = KategoriNamaTabungan::where('family_id', $familyId)->get();
        $categoryIds = $allCategories->pluck('id')->toArray();

        $stats = Tabungan::query()
            ->join('kategori_jenis_tabungans', 'tabungans.jenis', '=', 'kategori_jenis_tabungans.id')
            ->where('tabungans.family_id', $familyId)
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
        });

        $wallets = $allCategories->where('wallet_type', 'wallet');
        $posItems = $allCategories->where('wallet_type', 'pos');
        $savings = $allCategories->where('wallet_type', 'savings');

        return view('admin.ai.scan-struk', compact('wallets', 'posItems', 'savings'));
    }

    /**
     * View for AI Voice Recording
     */
    public function aiVoice()
    {
        $user = Auth::user();
        $familyId = $user->family_id;

        // Reuse the same optimized logic
        $allCategories = KategoriNamaTabungan::where('family_id', $familyId)->get();
        $categoryIds = $allCategories->pluck('id')->toArray();

        $stats = Tabungan::query()
            ->join('kategori_jenis_tabungans', 'tabungans.jenis', '=', 'kategori_jenis_tabungans.id')
            ->where('tabungans.family_id', $familyId)
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
        });

        $wallets = $allCategories->where('wallet_type', 'wallet');
        $posItems = $allCategories->where('wallet_type', 'pos');
        $savings = $allCategories->where('wallet_type', 'savings');

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
                'family_id' => Auth::user()->family_id,
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
                'message' => 'Berhasil disimpan!',
                'data' => $tabungan
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
