<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PlannedTransaction;
use App\Models\User;
use App\Models\KategoriNamaTabungan;
use App\Models\KategoriJenisTabungan;
use Carbon\Carbon;

class AgendaOrchestrationSeeder extends Seeder
{
    public function run()
    {
        $user = User::first();
        if (!$user) return;

        $groupId = $user->current_group_id;
        $jenisPengeluaran = KategoriJenisTabungan::where('jenis', 'Pengeluaran')->first();
        $targetWallet = KategoriNamaTabungan::where('wallet_type', 'pos')->first();

        $now = Carbon::now();

        $data = [
            // PERSONAL TASKS
            [
                'nama' => $targetWallet->id ?? 1,
                'jenis' => $jenisPengeluaran->id ?? 2,
                'nominal' => 0,
                'keterangan' => 'Bereskan Laporan Pajak Pribadi',
                'jatuh_tempo' => $now->copy()->addDays(2),
                'activity_type' => 'task',
                'is_priority' => true,
                'is_group' => false,
                'user_id' => $user->id,
                'group_id' => null,
                'timeline_data' => [
                    ['step' => 'Kumpulkan invoice', 'done' => true],
                    ['step' => 'Input ke spreadsheet', 'done' => false],
                    ['step' => 'Kirim ke konsultan', 'done' => false],
                ],
                'status' => 'pending',
            ],
            // GROUP REMINDER
            [
                'nama' => $targetWallet->id ?? 1,
                'jenis' => $jenisPengeluaran->id ?? 2,
                'nominal' => 250000,
                'keterangan' => 'Bayar Iuran Kebersihan Lingkungan',
                'jatuh_tempo' => $now->copy()->addDays(1),
                'activity_type' => 'reminder',
                'is_priority' => false,
                'is_group' => true,
                'user_id' => $user->id,
                'group_id' => $groupId,
                'status' => 'pending',
            ],
            // GROUP RITUAL
            [
                'nama' => $targetWallet->id ?? 1,
                'jenis' => $jenisPengeluaran->id ?? 2,
                'nominal' => 0,
                'keterangan' => 'Weekly Family Sync & Budgeting',
                'jatuh_tempo' => $now->copy()->addDays(5),
                'activity_type' => 'ritual',
                'is_priority' => true,
                'is_group' => true,
                'user_id' => $user->id,
                'group_id' => $groupId,
                'status' => 'pending',
            ],
            // PERSONAL REMINDER
            [
                'nama' => $targetWallet->id ?? 1,
                'jenis' => $jenisPengeluaran->id ?? 2,
                'nominal' => 0,
                'keterangan' => 'Ambil Paket di Kurir',
                'jatuh_tempo' => $now->copy()->addHours(5),
                'activity_type' => 'reminder',
                'is_priority' => false,
                'is_group' => false,
                'user_id' => $user->id,
                'group_id' => null,
                'status' => 'pending',
            ],
        ];

        foreach ($data as $item) {
            PlannedTransaction::create($item);
        }
    }
}
