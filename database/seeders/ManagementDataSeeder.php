<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\KategoriNamaTabungan;
use App\Models\Tabungan;
use App\Models\KategoriJenisTabungan;
use App\Models\User;

class ManagementDataSeeder extends Seeder
{
    public function run()
    {
        $user = User::first();
        if (!$user) return;
        
        $family_id = $user->family_id;
        $jenisPemasukan = KategoriJenisTabungan::where('jenis', 'Pemasukan')->first();
        $jenisPengeluaran = KategoriJenisTabungan::where('jenis', 'Pengeluaran')->first();

        $data = [
            // POS KEUANGAN
            [
                'nama' => 'Belanja Sembako',
                'kategori_kas' => 'Operasional Harian',
                'target_saldo' => 2000000,
                'icon' => 'shopping_basket',
                'wallet_type' => 'pos',
                'status' => 'AKTIF',
                'color' => '#006d36',
                'description' => 'Alokasi bulanan untuk kebutuhan dapur',
                'initial_balance' => 1200000,
            ],
            [
                'nama' => 'Listrik & WiFi',
                'kategori_kas' => 'Operasional Harian',
                'target_saldo' => 750000,
                'icon' => 'bolt',
                'wallet_type' => 'pos',
                'status' => 'AKTIF',
                'color' => '#fbc02d',
                'description' => 'Tagihan rutin bulanan',
                'initial_balance' => 300000,
            ],
            // DOMPET / AKUN
            [
                'nama' => 'BCA Utama',
                'kategori_kas' => 'Bank',
                'target_saldo' => 15000000,
                'icon' => 'account_balance',
                'wallet_type' => 'wallet',
                'status' => 'TERHUBUNG',
                'color' => '#00428d',
                'description' => 'Rekening payroll dan transaksi utama',
                'initial_balance' => 15000000,
            ],
            [
                'nama' => 'GoPay',
                'kategori_kas' => 'E-Wallet',
                'target_saldo' => 500000,
                'icon' => 'account_balance_wallet',
                'wallet_type' => 'wallet',
                'status' => 'AKTIF',
                'color' => '#0081a0',
                'description' => 'Saldo untuk jajan dan transportasi online',
                'initial_balance' => 500000,
            ],
            // TABUNGAN / TARGET
            [
                'nama' => 'Pendidikan Anak',
                'kategori_kas' => 'Pendidikan',
                'target_saldo' => 50000000,
                'icon' => 'school',
                'wallet_type' => 'savings',
                'status' => 'AKTIF',
                'color' => '#9c27b0',
                'description' => 'Target tabungan untuk masuk SD',
                'initial_balance' => 12500000,
            ],
            [
                'nama' => 'Liburan Jepang',
                'kategori_kas' => 'Liburan',
                'target_saldo' => 30000000,
                'icon' => 'flight_takeoff',
                'wallet_type' => 'savings',
                'status' => 'AKTIF',
                'color' => '#ff5722',
                'description' => 'Rencana liburan akhir tahun 2026',
                'initial_balance' => 3000000,
            ],
        ];

        foreach ($data as $item) {
            $initialBalance = $item['initial_balance'];
            unset($item['initial_balance']);
            
            $wallet = KategoriNamaTabungan::updateOrCreate(
                ['nama' => $item['nama'], 'family_id' => $family_id],
                array_merge($item, ['family_id' => $family_id])
            );

            // Create initial balance transaction if not exists
            if ($initialBalance > 0 && $jenisPemasukan) {
                Tabungan::updateOrCreate(
                    [
                        'nama' => $wallet->id,
                        'keterangan' => 'Saldo Awal: ' . $wallet->nama,
                        'family_id' => $family_id
                    ],
                    [
                        'jenis' => $jenisPemasukan->id,
                        'nominal' => $initialBalance,
                        'created_at' => now()->subDays(1),
                    ]
                );
            }
        }
    }
}
