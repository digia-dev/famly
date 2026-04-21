<?php

namespace App\Helpers;

use Illuminate\Support\Carbon;

class DashboardGreetingHelper
{
    /**
     * Generate greeting message lengkap
     */
    public static function generateDailyGreeting($pengeluaranHariIni, $rataRataHarian, $saldoSaatIni, $financialHealth = [])
    {
        // 1. Ambil Salam Waktu (Pagi/Siang/Sore/Malam)
        $timeGreeting = self::getTimeGreeting();
 
        // 2. Analisa Kebiasaan Belanja & Status Finansial
        $spendingAnalysis = self::analyzeSpending($pengeluaranHariIni, $rataRataHarian, $saldoSaatIni, $financialHealth);

        // 3. Hitung Persentase Realistis
        $persentase = 0;
        if ($rataRataHarian > 0) {
            $persentase = round(($pengeluaranHariIni / $rataRataHarian) * 100, 0);
        } elseif ($pengeluaranHariIni > 0) {
            $persentase = 100;
        }

        return array_merge($timeGreeting, $spendingAnalysis, [
            'pengeluaran_hari_ini' => $pengeluaranHariIni,
            'rata_rata' => $rataRataHarian,
            'tanggal' => now()->locale('id')->isoFormat('dddd, D MMMM Y'),
            'persentase_vs_rata' => $persentase
        ]);
    }

    /**
     * Logika Salam Waktu
     */
    private static function getTimeGreeting()
    {
        $jam = now()->hour;

        if ($jam >= 3 && $jam < 11) {
            return ['waktu' => 'Pagi', 'icon_waktu' => 'fa-cloud-sun', 'salam' => 'Selamat Pagi'];
        } elseif ($jam >= 11 && $jam < 15) {
            return ['waktu' => 'Siang', 'icon_waktu' => 'fa-sun', 'salam' => 'Selamat Siang'];
        } elseif ($jam >= 15 && $jam < 18) {
            return ['waktu' => 'Sore', 'icon_waktu' => 'fa-cloud-sun', 'salam' => 'Selamat Sore'];
        } else {
            return ['waktu' => 'Malam', 'icon_waktu' => 'fa-moon', 'salam' => 'Selamat Malam'];
        }
    }

    /**
     * Core Logic: Analisis Pengeluaran
     * REVISI WARNA: Menggunakan style Glassmorphism (Teks Putih) agar kontras di semua background.
     */
    private static function analyzeSpending($current, $average, $balance, $health = [])
    {
        $savings = $health['total_savings'] ?? 0;
        $liquid = $health['total_liquid'] ?? 0;
        $lastMonthExpenses = $health['expenses_last_month'] ?? 0;
        $last2MonthsExpenses = $health['expenses_last_2_months'] ?? 0;

        // --- 1. LOGIKA STATUS FINANSIAL (Berdasarkan Hirarki Keamanan) ---
        
        // KONDISI BAHAYA: Tidak ada tabungan DAN dana cair < kebutuhan bln lalu
        if ($savings <= 0 && $liquid < $lastMonthExpenses) {
            $statusType = 'danger';
            $statusLabel = 'Bahaya';
            $badgeColor = 'bg-error-container text-on-error-container border-error';
            $iconBadge = 'error';
            $statusPesan = "Alert! Dana cair Anda di bawah kebutuhan bulanan & belum ada tabungan. Segera evaluasi pengeluaran. 🚨";
        }
        // KONDISI WASPADA: Tidak ada tabungan ATAU dana cair sisa sedikit (misal < 1.5x kebutuhan bln lalu)
        elseif ($savings <= 0 || ($lastMonthExpenses > 0 && $liquid < ($lastMonthExpenses * 1.5))) {
            $statusType = 'warning';
            $statusLabel = 'Waspada';
            $badgeColor = 'bg-secondary-container text-on-secondary-container border-secondary';
            $iconBadge = 'warning';
            $statusPesan = "Perhatian: Cadangan kas menipis atau tabungan kosong. Waktunya memperketat anggaran harian. ⚠️";
        }
        // KONDISI AMAN: Dana lebih banyak 100% dari pengeluaran 2 bulan terakhir
        elseif ($last2MonthsExpenses > 0 && $liquid > ($last2MonthsExpenses * 2)) {
            $statusType = 'excellent';
            $statusLabel = 'Aman';
            $badgeColor = 'bg-primary-container text-on-primary-container border-primary';
            $iconBadge = 'stars';
            $statusPesan = "Luar biasa! Ketahanan finansial Anda sangat kuat. Dana cadangan melebihi target aman. 🛡️";
        }
        else {
            $statusType = 'good';
            $statusLabel = 'Stabil';
            $badgeColor = 'bg-surface-container-highest text-on-surface-variant border-outline-variant';
            $iconBadge = 'check_circle';
            $statusPesan = "Kondisi keuangan stabil. Tetap konsisten menabung untuk mencapai target finansial. ✅";
        }

        // --- 2. LOGIKA PESAN HARIAN (Contextual Daily Info) ---
        $ratio = ($average > 0) ? $current / $average : 0;
        $dailyInfo = "";

        if ($current == 0) {
            $dailyInfo = "Alhamdulillah, belum ada pengeluaran hari ini. Dompet tetap tebal! ✨";
        } elseif ($ratio > 1.2) {
            $dailyInfo = "Pengeluaran hari ini agak tinggi (" . round($ratio * 100) . "% dari rata-rata).";
        } else {
            $dailyInfo = "Aktivitas belanja hari ini terlihat wajar.";
        }

        return [
            'tipe' => $statusType,
            'badge_text' => $statusLabel,
            'badge_color' => $badgeColor,
            'icon_badge_color' => '', 
            'badge_icon' => $iconBadge,
            'pesan' => $statusPesan . " " . $dailyInfo,
            'color' => $statusType == 'danger' ? 'from-rose-500 to-red-600' : ($statusType == 'warning' ? 'from-amber-400 to-orange-500' : 'from-emerald-500 to-teal-600'),
        ];
    }

    public static function formatRupiah($angka)
    {
        return 'Rp' . number_format($angka, 0, ',', '.');
    }

    private static function getRandomMessage(array $messages)
    {
        return $messages[array_rand($messages)];
    }
}