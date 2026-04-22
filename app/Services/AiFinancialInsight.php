<?php

namespace App\Services;

use App\Models\Tabungan;
use App\Models\KategoriNamaTabungan;
use App\Models\User;
use App\Models\Group;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class AiFinancialInsight
{
    protected $apiKey;
    protected $model;
    protected $provider;
    private static $requestCache = null;

    public function __construct()
    {
        $this->provider = env('AI_PROVIDER', 'gemini');
        $this->apiKey = match($this->provider) {
            'gemini' => env('GEMINI_API_KEY'),
            'groq' => env('GROQ_API_KEY'),
            default => env('OPENAI_API_KEY')
        };
        $this->model = match($this->provider) {
            'gemini' => env('GEMINI_MODEL', 'gemini-1.5-flash'),
            'groq' => env('GROQ_MODEL', 'llama3-8b-8192'),
            default => env('OPENAI_MODEL', 'gpt-4o')
        };
    }

    /**
     * Memeriksa kuota harian penggunaan AI.
     * Aturan: Unlimited untuk Grup/Premium, 3x/hari untuk Personal Trial.
     */
    public function checkDailyQuota($isGroupContext = false)
    {
        $user = Auth::user();
        if (!$user) return false;

        // Premium users have no limits
        if ($user->isPremium()) return true;

        // Group contexts (@Fams or active group) are unlimited for now
        if ($isGroupContext) return true;

        // Personal context: Limit to 3x per day for Trial
        return $user->ai_usage_count < 3;
    }

    /**
     * Increment the AI usage count for the user.
     */
    public function incrementUsage()
    {
        $user = Auth::user();
        if ($user && !$user->isPremium()) {
            $user->increment('ai_usage_count');
        }
    }

    /**
     * Detect financial anomalies (Spikes in spending).
     */
    public function detectAnomalies()
    {
        $data = $this->prepareFinancialData();
        
        if ($data['anomali_detected']) {
            $diff = $data['pengeluaran_hari_ini'] - ($data['total_pengeluaran'] / max(1, now()->day));
            return "Waspada! Pengeluaran hari ini (Rp " . number_format($data['pengeluaran_hari_ini']) . ") jauh di atas rata-rata harian Anda. Ada lonjakan sekitar Rp " . number_format($diff) . " kawan!";
        }
        
        return null; // All good
    }

    public function getInsight()
    {
        $groupId = Auth::user()->current_group_id;
        $isGroup = (bool)$groupId;

        return cache()->remember('ai_insight_' . Auth::id() . '_' . $groupId, 60 * 6, function() use ($isGroup) {
            if (!$this->checkDailyQuota($isGroup)) {
                return "Kuota harian AI Personal Anda telah habis (Maks 3). Gunakan di Grup atau Upgrade ke Premium untuk akses tanpa batas!";
            }

            $dataContext = $this->prepareFinancialData();
            
            if (empty($this->apiKey)) {
                return "AI Insight: Keuangan Anda terlihat stabil bulan ini. Terus pantau pengeluaran untuk mencapai target tabungan!";
            }

            return $this->askAi($dataContext);
        });
    }

    /**
     * Menghasilkan wawasan khusus untuk halaman Manajemen (Pos, Dompet, Tabungan).
     */
    public function getManagementInsights()
    {
        $data = $this->prepareFinancialData();
        
        return cache()->remember('ai_mgmt_insights_' . Auth::id(), 60 * 6, function() use ($data) {
            $prompt = "
            Anda adalah Si Famly, asisten keuangan cerdas. Analisis data keluarga ini:
            - Saldo: Rp " . number_format($data['saldo_real']) . "
            - Pengeluaran Bulan Ini: Rp " . number_format($data['total_pengeluaran']) . "
            - Kategori Terboros: " . $data['kategori_boros'] . "
            
            Berikan 3 saran singkat (maks 15 kata per saran) dalam format JSON untuk:
            1. pos_advice: Saran untuk pengaturan budget harian/pos.
            2. asset_advice: Saran untuk pengelolaan aset/dompet.
            3. savings_advice: Saran untuk mencapai target tabungan.
            4. health_score: Angka 1-100 (integer) menggambarkan kesehatan keuangan.
            
            Format Output JSON:
            {
                \"pos_advice\": \"...\",
                \"asset_advice\": \"...\",
                \"savings_advice\": \"...\",
                \"health_score\": 85
            }
            Jangan berikan teks lain selain JSON.
            ";

            try {
                if (!empty($this->apiKey)) {
                    $response = $this->askAi($prompt);
                    $response = preg_replace('/```json|```/', '', $response);
                    $result = json_decode(trim($response), true);
                    
                    if ($result && isset($result['pos_advice'])) {
                        $score = isset($result['health_score']) ? (int)$result['health_score'] : 85;
                        $healthLabel = $score >= 80 ? 'Sangat Sehat' : ($score >= 60 ? 'Cukup Sehat' : 'Perlu Perhatian');
                        
                        return array_merge($result, [
                            'score' => $score,
                            'health_label' => $healthLabel
                        ]);
                    }
                }
            } catch (\Exception $e) {}

            // Fallback Logic based on real data
            $score = 85;
            if ($data['total_pengeluaran'] > $data['total_pemasukan']) $score -= 20;

            return [
                'score' => $score,
                'health_label' => $score >= 80 ? 'Sangat Sehat' : 'Cukup Sehat',
                'pos_advice' => "Pertahankan pengeluaran di kategori {$data['kategori_boros']} agar tetap dalam batas aman.",
                'asset_advice' => "Saldo kas Anda stabil. Pertimbangkan menabung 10% lebih banyak bulan ini.",
                'savings_advice' => "Target tabungan bisa tercapai lebih cepat jika konsisten di bulan " . now()->translatedFormat('F') . "."
            ];
        });
    }

    /**
     * Menghasilkan tip singkat untuk halaman pembuatan item baru.
     */
    public function getCreatePageTip($type)
    {
        $data = $this->prepareFinancialData();
        
        return cache()->remember('ai_create_tip_' . $type . '_' . Auth::id(), 60 * 6, function() use ($type, $data) {
            $prompt = "Buat 1 tip singkat (maks 10 kata) untuk user yang sedang membuat '" . $type . "' baru. Hubungkan dengan pengeluaran mereka di '" . $data['kategori_boros'] . "' jika relevan. Gaya santai.";
            
            try {
                if (!empty($this->apiKey)) {
                    $response = $this->askAi($prompt);
                    return trim(strip_tags($response));
                }
            } catch (\Exception $e) {}

            return match($type) {
                'tabungan' => 'Tentukan target realistis agar motivasi menabung tetap terjaga.',
                'dompet' => 'Pilih warna ikon yang kontras untuk membedakan jenis dompet.',
                default => 'Alokasikan dana pos sesuai kebutuhan prioritas keluarga Anda.'
            };
        });
    }

    /**
     * Menyiapkan konteks data keuangan untuk dianalisis oleh AI.
     */
    private function prepareFinancialData()
    {
        if (self::$requestCache) {
            return self::$requestCache;
        }

        $now = Carbon::now();
        $user = Auth::user();
        $groupId = $user->current_group_id;

        // 1. Consolidated Financial Metrics (Single Query)
        $query = Tabungan::query()
            ->join('kategori_jenis_tabungans', 'tabungans.jenis', '=', 'kategori_jenis_tabungans.id');

        if ($groupId) {
            $query->where('tabungans.group_id', $groupId);
        } else {
            $query->where('tabungans.user_id', $user->id)
                  ->whereNull('tabungans.group_id');
        }

        $sums = $query->selectRaw("
                SUM(CASE WHEN kategori_jenis_tabungans.jenis = 'Pemasukan' THEN tabungans.nominal ELSE 0 END) as total_in_all,
                SUM(CASE WHEN kategori_jenis_tabungans.jenis = 'Pengeluaran' THEN tabungans.nominal ELSE 0 END) as total_out_all,
                SUM(CASE WHEN kategori_jenis_tabungans.jenis = 'Pemasukan' AND MONTH(tabungans.created_at) = ? AND YEAR(tabungans.created_at) = ? THEN tabungans.nominal ELSE 0 END) as month_in,
                SUM(CASE WHEN kategori_jenis_tabungans.jenis = 'Pengeluaran' AND MONTH(tabungans.created_at) = ? AND YEAR(tabungans.created_at) = ? THEN tabungans.nominal ELSE 0 END) as month_out,
                SUM(CASE WHEN kategori_jenis_tabungans.jenis = 'Pengeluaran' AND DATE(tabungans.created_at) = ? THEN tabungans.nominal ELSE 0 END) as today_out,
                SUM(CASE WHEN kategori_jenis_tabungans.jenis = 'Pengeluaran' AND MONTH(tabungans.created_at) = ? AND YEAR(tabungans.created_at) = ? THEN tabungans.nominal ELSE 0 END) as last_month_out,
                SUM(CASE WHEN kategori_jenis_tabungans.jenis = 'Pengeluaran' AND tabungans.created_at >= ? THEN tabungans.nominal ELSE 0 END) as last_14_days_out
            ", [
                $now->month, $now->year,
                $now->month, $now->year,
                $now->toDateString(),
                $now->copy()->subMonth()->month, $now->copy()->subMonth()->year,
                $now->copy()->subDays(14)->toDateTimeString()
            ])
            ->first();

        $saldoReal = ($sums->total_in_all ?? 0) - ($sums->total_out_all ?? 0);
        $pemasukanBulanIni = $sums->month_in ?? 0;
        $pengeluaranBulanIni = $sums->month_out ?? 0;
        $pengeluaranHariIni = $sums->today_out ?? 0;
        $pengeluaranBulanLalu = $sums->last_month_out ?? 0;
        $pengeluaran14Hari = $sums->last_14_days_out ?? 0;
        
        $avgDailySpend = $pengeluaran14Hari / 14;
        if ($avgDailySpend <= 0) {
            $avgDailySpend = $pengeluaranBulanIni / max(1, $now->day);
        }

        // 2. Optimized Activity Today (Limited)
        $activityQuery = Tabungan::query()
            ->join('kategori_nama_tabungans', 'tabungans.nama', '=', 'kategori_nama_tabungans.id')
            ->join('kategori_jenis_tabungans', 'tabungans.jenis', '=', 'kategori_jenis_tabungans.id')
            ->where('kategori_jenis_tabungans.jenis', 'Pengeluaran')
            ->whereDate('tabungans.created_at', $now->today());

        if ($groupId) {
            $activityQuery->where('tabungans.group_id', $groupId);
        } else {
            $activityQuery->where('tabungans.user_id', $user->id)
                          ->whereNull('tabungans.group_id');
        }

        $transaksiHariIni = $activityQuery->take(3)
            ->get()
            ->map(fn($t) => ($t->nama ?? 'Umum') . ": " . ($t->keterangan ?? 'Transaksi'))
            ->implode(', ');

        // 3. Optimized Top Category (Joined with Name)
        $topCatQuery = Tabungan::query()
            ->join('kategori_jenis_tabungans', 'tabungans.jenis', '=', 'kategori_jenis_tabungans.id')
            ->join('kategori_nama_tabungans', 'tabungans.nama', '=', 'kategori_nama_tabungans.id')
            ->select('kategori_nama_tabungans.nama', \DB::raw('SUM(tabungans.nominal) as total'))
            ->where('kategori_jenis_tabungans.jenis', 'Pengeluaran')
            ->whereMonth('tabungans.created_at', $now->month)
            ->whereYear('tabungans.created_at', $now->year);

        if ($groupId) {
            $topCatQuery->where('tabungans.group_id', $groupId);
        } else {
            $topCatQuery->where('tabungans.user_id', $user->id)
                        ->whereNull('tabungans.group_id');
        }

        $topCategory = $topCatQuery->groupBy('kategori_nama_tabungans.nama')
            ->orderByDesc('total')
            ->first();

        $namaKategoriTop = $topCategory->nama ?? 'Lainnya';

        // Hitung Tren
        $selisih = $pengeluaranBulanIni - $pengeluaranBulanLalu;
        if ($pengeluaranBulanLalu > 0) {
            $persen = round(($selisih / $pengeluaranBulanLalu) * 100);
        } else {
            $persen = $pengeluaranBulanIni > 0 ? 100 : 0;
        }
        
        $statusNaikTurun = $selisih > 0 ? "MENINGKAT" : "MENURUN";

        self::$requestCache = [
            'nama_user' => Auth::user()->name,
            'konteks_nama' => $groupId ? Auth::user()->currentGroup->name : 'Pribadi',
            'konteks_tipe' => $groupId ? 'Grup' : 'Pribadi',
            'bulan_ini' => $now->translatedFormat('F Y'),
            'saldo_real' => $saldoReal,
            'total_pemasukan' => $pemasukanBulanIni,
            'total_pengeluaran' => $pengeluaranBulanIni,
            'pengeluaran_hari_ini' => $pengeluaranHariIni,
            'transaksi_hari_ini' => $transaksiHariIni,
            'status_pengeluaran' => $statusNaikTurun,
            'persentase_perubahan' => abs($persen) . '%',
            'kategori_boros' => $namaKategoriTop,
            'anomali_detected' => $pengeluaranHariIni > ($pengeluaranBulanIni / max(1, $now->day) * 2.5), // Spike 2.5x vs avg
            'hero_name' => User::whereHas('tabungan', function($q) use ($now, $groupId, $user) {
                    $q->whereBetween('created_at', [$now->copy()->startOfWeek(), $now->copy()->endOfWeek()]);
                    if ($groupId) $q->where('group_id', $groupId);
                    else $q->where('user_id', $user->id);
                })
                ->withCount(['tabungan' => function($q) use ($now, $groupId, $user) {
                    $q->whereBetween('created_at', [$now->copy()->startOfWeek(), $now->copy()->endOfWeek()]);
                    if ($groupId) $q->where('group_id', $groupId);
                    else $q->where('user_id', $user->id);
                }])
                ->orderByDesc('tabungan_count')
                ->first()->name ?? 'N/A',
            'days_remaining' => $saldoReal > 0 ? floor($saldoReal / max(1, $avgDailySpend)) : 0
        ];

        return self::$requestCache;
    }

    /**
     * Menghasilkan daftar penemuan (Discovery Items) via AI untuk Dashboard.
     */
    public function getDiscoveryInsights()
    {
        return cache()->remember('ai_discovery_insights_' . Auth::id(), 60 * 6, function() {
            $data = $this->prepareFinancialData();
            
            $prompt = "
            Anda adalah Famly AI. Berdasarkan data user:
            - Saldo Total: Rp " . number_format($data['saldo_real']) . "
            - Kategori Terboros: {$data['kategori_boros']}
            - Pengeluaran Bulan Ini: Rp " . number_format($data['total_pengeluaran']) . "
            - Tren: {$data['status_pengeluaran']} ({$data['persentase_perubahan']})
            - Prediksi Saldo Habis (Burn Rate): {$data['days_remaining']} hari lagi
            
            TUGAS:
            Hasilkan 4 item eksplorasi keuangan yang berbeda.
            Salah satu item HARUS membahas tentang 'Prediksi Saldo Habis' (Burn Rate) jika angkanya di bawah 30 hari.
            Setiap item harus unik (1 tips, 1 kesimpulan AI, 1 penawaran fiktif, 1 info fitur).
            Format output HARUS JSON ARRAY:
            [
              {
                \"id\": \"ID Unik (ai_summary / fin_plan / new_feature / help_center)\",
                \"icon\": \"Nama Icon Material (auto_awesome / account_balance / rocket_launch / help_outline)\",
                \"category\": \"Kategori (Kesimpulan AI/Tips/Penawaran)\",
                \"title\": \"Judul Menarik\",
                \"description\": \"Penjelasan singkat\",
                \"bg\": \"Warna (bg-white / bg-emerald-700 / bg-amber-600 / bg-blue-600)\",
                \"text\": \"Warna Teks (text-white / text-slate-900)\",
                \"action_label\": \"Label Tombol\",
                \"action_url\": \"URL (biarkan # atau route yang relevan)\"
              }
            ]
            Jangan gunakan basa-basi, hanya JSON saja.
            ";

            try {
                $response = $this->askAi($prompt);
                $response = preg_replace('/```json|```/', '', $response);
                $items = json_decode(trim($response), true);
                if (is_array($items) && count($items) > 0) return $items;
            } catch (\Exception $e) {}

            return []; // Fallback handled by DiscoveryController
        });
    }

    /**
     * Menghasilkan teks tunggal untuk \"Smart Pick\" (Daily Tip) bergaya Super App.
     */
    public function getSmartPick()
    {
        return cache()->remember('ai_smart_pick_' . Auth::id(), 60 * 6, function() {
            $data = $this->prepareFinancialData();
            
            $prompt = "
            Anda adalah Famly AI, asisten finansial jenius untuk aplikasi super-app keluarga.
            Berdasarkan data user:
            - Saldo: Rp " . number_format($data['saldo_real']) . "
            - Kategori Boros: {$data['kategori_boros']}
            - Pengeluaran Hari Ini: Rp " . number_format($data['pengeluaran_hari_ini']) . "
            
            TUGAS:
            Buat 1 tips finansial singkat, kreatif, dan praktis bergaya Gojek/Grab (Smart Pick).
            Format output HARUS JSON murni:
            {
              \"id\": \"ai_summary\",
              \"icon\": \"auto_awesome\",
              \"title\": \"Judul singkat (maks 4 kata)\",
              \"description\": \"Penjelasan/tips (maks 12 kata)\"
            }
            Jangan berikan teks lain selain JSON. Contoh: {\"id\": \"ai_summary\", \"icon\": \"auto_awesome\", \"title\": \"Kopi Bisa Nanti\", \"description\": \"Saldo kamu di bawah target. Hemat jajan kopi hari ini ya!\"}
            ";

            try {
                $response = $this->askAi($prompt);
                // Bersihkan jika ada backticks markdown
                $response = preg_replace('/```json|```/', '', $response);
                $json = json_decode(trim($response), true);
                if ($json && isset($json['title'])) return $json;
            } catch (\Exception $e) {}

            return [
                'title' => 'Tips: Aturan 50/30/20',
                'description' => 'Gunakan 50% untuk kebutuhan, 30% keinginan, dan 20% untuk tabungan.'
            ];
        });
    }

    /**
     * Mengirim instruksi ke AI untuk menghasilkan analisis keuangan.
     */
    private function askAi($prompt)
    {
        try {
            return match($this->provider) {
                'gemini' => $this->callGemini($prompt),
                'groq' => $this->callGroq($prompt),
                default => $this->callOpenAi($prompt),
            };
        } catch (\Exception $e) {
            return "Wawasan AI saat ini tidak tersedia.";
        }
    }

    private function callGroq($prompt)
    {
        $response = Http::timeout(60)
            ->withToken($this->apiKey)
            ->post('https://api.groq.com/openai/v1/chat/completions', [
            'model' => $this->model,
            'messages' => [
                ['role' => 'system', 'content' => 'Anda adalah Famly AI, asisten transaksional jenius. Balas hanya dengan JSON jika diminta.'],
                ['role' => 'user', 'content' => $prompt],
            ],
            'temperature' => 0.6,
        ]);

        if ($response->successful()) {
            return $response->json()['choices'][0]['message']['content'];
        }

        throw new \Exception("Gagal menghubungi Groq Service.");
    }

    private function callGemini($prompt)
    {
        $baseUrl = "https://generativelanguage.googleapis.com/v1beta/models/{$this->model}:generateContent?key={$this->apiKey}";

        $response = Http::timeout(60)->post($baseUrl, [
            'contents' => [['parts' => [['text' => $prompt]]]],
            'generationConfig' => [
                'temperature' => 0.7,
                'maxOutputTokens' => 2048,
            ]
        ]);

        if ($response->successful()) {
            return $response->json()['candidates'][0]['content']['parts'][0]['text'];
        }

        throw new \Exception("Gagal menghubungi Gemini Service.");
    }

    private function callOpenAi($prompt)
    {
        $baseUrl = env('OPENAI_BASE_URL', 'https://api.openai.com/v1/chat/completions');

        $response = Http::timeout(60)
            ->withToken($this->apiKey)
            ->post($baseUrl, [
            'model' => $this->model,
            'messages' => [
                ['role' => 'system', 'content' => 'Anda adalah Famly AI, asisten keuangan profesional.'],
                ['role' => 'user', 'content' => $prompt],
            ],
            'temperature' => 0.7,
            'max_tokens' => 2048,
        ]);

        if ($response->successful()) {
            return $response->json()['choices'][0]['message']['content'];
        }

        throw new \Exception("Gagal menghubungi AI Service.");
    }

    /**
     * Memproses chat dari user dengan konteks keuangan lengkap.
     */
    public function chat($message)
    {
        $isGroupContext = str_contains($message, '@Fams') || Auth::user()->current_group_id;

        if (!$this->checkDailyQuota($isGroupContext)) {
            return "Maaf, kuota bertanya pada AI Personal hari ini sudah mencapai batas (3x). Gunakan di Grup (Tag @Fams) atau tanya lagi besok!";
        }

        if (!$isGroupContext) {
            $this->incrementUsage();
        }

        $data = $this->prepareFinancialData();
        
        // Ambil daftar semua dompet/pos/tabungan yang tersedia (Scoped by GroupScope)
        $wallets = KategoriNamaTabungan::all()->map(function($w) {
            $totalIn = Tabungan::where('nama', $w->id)->whereHas('kategoriJenis', fn($q) => $q->where('jenis', 'Pemasukan'))->sum('nominal');
            $totalOut = Tabungan::where('nama', $w->id)->whereHas('kategoriJenis', fn($q) => $q->where('jenis', 'Pengeluaran'))->sum('nominal');
            $balance = $totalIn - $totalOut;
            return "- {$w->nama} (Tipe: {$w->wallet_type}): Rp " . number_format($balance);
        })->implode("\n");

        $recentTransactions = Tabungan::with(['kategoriNama', 'kategoriJenis'])
            ->latest()
            ->take(10)
            ->get()
            ->map(function($t) {
                return "[{$t->created_at->format('d M')}] {$t->kategoriJenis->jenis}: " . ($t->kategoriNama->nama ?? 'Umum') . " sebesar Rp " . number_format($t->nominal) . " ({$t->keterangan})";
            })->implode("\n");

        $prompt = "
        PRIBADI ANDA:
        Nama Anda adalah Si Famly. Anda adalah asisten keuangan yang FUN, LUGAS, TO THE POINT, dan gaul (tapi tetap sopan).
        Jangan gunakan kata-kata yang terlalu kaku atau formal. Gunakan gaya mengobrol yang santai tapi cerdas.
        Jika ditanya solusi, berikan solusi yang praktis dan langsung bisa dilakukan.

        ATURAN OUTPUT WAJIB:
        1. Jika mencatat transaksi, gunakan [RECEIPT_CARD] dengan struktur PERSIS seperti ini:
           [RECEIPT_CARD]{ \"merchant\": \"Nama Toko\", \"items\": [{\"name\": \"Nama Barang\", \"price\": 10000}], \"total\": 10000, \"suggested_wallet\": \"Nama Dompet\", \"smart_tip\": \"Tips hemat\" }[RECEIPT_CARD]
           *CATATAN: Gunakan tag penutup yang SAMA [RECEIPT_CARD], jangan gunakan [/RECEIPT_CARD].*
           
        2. Jika bertanya saldo/laporan, gunakan [DATA_CARD] dengan struktur PERSIS seperti ini:
           [DATA_CARD]{ \"title\": \"Judul Laporan\", \"items\": [{\"label\": \"Nama Pos\", \"value\": 50000}], \"footer\": \"Total: Rp ...\" }[DATA_CARD]

        3. Minimalisir teks. JANGAN sertakan data saldo ([DATA_CARD]) jika Anda sedang memberikan konfirmasi belanja ([RECEIPT_CARD]). Cukup 1 kartu saja yang relevan.
        4. JANGAN gunakan markdown code blocks (```json).

        KONTEKS DATA KEUANGAN KELUARGA:
        - Nama User: {$data['nama_user']}
        - Total Saldo (Semua): Rp " . number_format($data['saldo_real']) . "
        - Pengeluaran Bulan Ini: Rp " . number_format($data['total_pengeluaran']) . "
        
        DAFTAR POS/TABUNGAN:
        {$wallets}
        
        PERTANYAAN USER: \"{$message}\"
        
        INFO TEKNIS (KHUSUS SUBSCRIBER):
        - Deteksi Anomali: " . ($data['anomali_detected'] ? 'ADA LONJAKAN' : 'NORMAL') . "
        - Family Hero (Mingguan): {$data['hero_name']}
        - Prediksi Saldo Habis (Burn Rate): {$data['days_remaining']} hari lagi
        ";

        // Logic @Fams for Group Orchestration
        if (str_contains($message, '@Fams')) {
            $prompt .= "
            CATATAN KHUSUS @Fams:
            User menggunakan tag @Fams. Ini berarti Anda sedang berbicara dalam konteks KONTEKS GRUP/KOMUNITAS.
            TUGAS TAMBAHAN:
            1. Puji atau Mention **{$data['hero_name']}** jika performanya bagus agar yang lain termotivasi.
            2. PERIKSA ANOMALI: Jika 'ADA LONJAKAN', tanyakan ke grup apakah ini pengeluaran yang sudah direncanakan atau 'tamu tak diundang'.
            3. Fokus pada kolaborasi, bukan hanya pengeluaran individu. Berikan Analisis Transparansi.
            ";
        }

        return $this->askAi($prompt);
    }

    /**
     * Menganalisis gambar struk menggunakan AI Vision.
     */
    public function analyzeReceipt($imageBase64)
    {
        $groupId = Auth::user()->current_group_id;
        if (!$this->checkDailyQuota((bool)$groupId)) {
            throw new \Exception("Kuota analisis struk personal harian habis (3x).");
        }

        if (!$groupId) $this->incrementUsage();

        // Pastikan menggunakan provider yang mendukung Vision (Gemini 1.5 Flash)
        $visionModel = env('GEMINI_MODEL', 'gemini-1.5-flash');
        $apiKey = env('GEMINI_API_KEY');

        if (empty($apiKey)) {
            throw new \Exception("GEMINI_API_KEY tidak ditemukan untuk analisis Vision.");
        }

        $baseUrl = "https://generativelanguage.googleapis.com/v1beta/models/{$visionModel}:generateContent?key={$apiKey}";

        // Bersihkan prefix base64 jika ada
        $data = preg_replace('#^data:image/\w+;base64,#i', '', $imageBase64);

        $prompt = "
        TUGAS: Analisis gambar struk ini dan ekstrak informasi keuangan.
        Anda harus mengembalikan data dalam format JSON murni:
        {
          \"merchant\": \"Nama Toko/Merchant\",
          \"suggested_wallet\": \"Pilihan Pos yang cocok (Makanan / Transportasi / Hiburan / Belanja / Lainnya)\",
          \"items\": [
            { \"name\": \"Nama Barang\", \"price\": \"Harga (Angka saja tanpa titik/koma)\" }
          ],
          \"total\": \"Total Akhir (Angka saja tanpa titik/koma)\",
          \"smart_tip\": \"Satu kalimat saran seru terkait pengeluaran ini (misal: Makan enak boleh, tapi jaga dompet tetap sehat ya!)\"
        }
        Jangan berikan teks lain selain JSON.
        ";

        $response = Http::timeout(60)->post($baseUrl, [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt],
                        [
                            'inline_data' => [
                                'mime_type' => 'image/jpeg',
                                'data' => $data
                            ]
                        ]
                    ]
                ]
            ],
            'generationConfig' => [
                'temperature' => 0.1,
                'maxOutputTokens' => 1000,
            ]
        ]);

        if ($response->successful()) {
            $text = $response->json()['candidates'][0]['content']['parts'][0]['text'];
            
            // Extract JSON from potential conversational text
            if (preg_match('/\{(?:[^{}]|(?R))*\}/s', $text, $matches)) {
                $cleanJson = $matches[0];
                return json_decode($cleanJson, true);
            }
        }

        throw new \Exception("Gagal menganalisis struk: AI tidak memberikan rincian data.");
    }

    /**
     * Menganalisis transkrip suara menjadi data transaksi terstruktur.
     */
    public function parseVoiceCommand($text)
    {
        $groupId = \Illuminate\Support\Facades\Auth::user()->current_group_id;
        if (!$this->checkDailyQuota((bool)$groupId)) {
             throw new \Exception("Kuota harian AI personal habis.");
        }

        if (!$groupId) $this->incrementUsage();

        $user = \Illuminate\Support\Facades\Auth::user();
        
        // Helper to fetch categories with balances (Scoped by GroupScope)
        $getCategoriesWithBalance = function($type) {
            return KategoriNamaTabungan::where('wallet_type', $type)
                ->get()
                ->map(function($cat) {
                    $totalIn = \App\Models\Tabungan::where('nama', $cat->id)->whereHas('kategoriJenis', fn($q) => $q->where('jenis', 'Pemasukan'))->sum('nominal');
                    $totalOut = \App\Models\Tabungan::where('nama', $cat->id)->whereHas('kategoriJenis', fn($q) => $q->where('jenis', 'Pengeluaran'))->sum('nominal');
                    $balance = $totalIn - $totalOut;
                    return "{$cat->nama} (Saldo: Rp{$balance})";
                })->implode(', ');
        };

        $posList = $getCategoriesWithBalance('pos');
        $walletList = $getCategoriesWithBalance('wallet');

        $prompt = "
        TUGAS: Analisis perintah suara user berikut dan ubah menjadi data transaksi terstruktur.
        
        ATURAN KATEGORI (Pilih dari daftar ini):
        1. PRIORITAS (Pos Anggaran): [{$posList}]
        2. CADANGAN (Jika tidak ada Pos yang cocok): [{$walletList}]
        
        PERINTAH USER: \"{$text}\"
        
        FORMAT OUTPUT HARUS JSON MURNI:
        {
          \"merchant\": \"Nama Toko/Tempat (kosongkan jika tidak ada)\",
          \"description\": \"Apa yang dibeli / Nama Barang / Kegiatan (Sangat pentng)\",
          \"suggested_wallet\": \"Pilih satu nama yang PALING COCOK. Utamakan dari daftar POS ANGGARAN.\",
          \"items\": [
            { \"name\": \"Barang/Kegiatan\", \"price\": 0 }
          ],
          \"total\": 0,
          \"smart_tip\": \"Satu saran finansial singkat\"
        }
        Jika user tidak menyebutkan nominal, set total ke 0. Jangan berikan teks lain selain JSON.
        ";

        $response = $this->askAi($prompt);
        
        // Extract JSON from potential conversational text
        if (preg_match('/\{(?:[^{}]|(?R))*\}/s', $response, $matches)) {
            $cleanJson = $matches[0];
            return json_decode($cleanJson, true);
        }

        return null;
    }
}