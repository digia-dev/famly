<!-- Transactions Section (Gojek Style) -->
<section class="bg-white p-4 rounded-2xl shadow-sm border border-slate-100">
    <div class="flex justify-between items-center mb-4">
        <div>
            <h2 class="text-[13px] font-bold text-slate-900 tracking-tight">Transaksi Terakhir</h2>
            <p class="text-[11px] text-slate-400 mt-0.5 font-medium">Histori pengeluaran Anda</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('transaction.create') }}" class="w-7 h-7 flex items-center justify-center bg-primary/10 text-primary rounded-lg hover:bg-primary hover:text-white transition-all">
                <span class="material-symbols-outlined text-[18px]">add</span>
            </a>
            <a href="{{ url('/transaction') }}" class="text-[11px] font-bold text-primary hover:underline px-1">Lihat Semua</a>
        </div>
    </div>

    <div class="space-y-3">
        @forelse($transaksiTerakhir ?? [] as $transaction)
            @php
                $isPemasukan = $transaction->kategoriJenis && $transaction->kategoriJenis->jenis == 'Pemasukan';
                $icon = $isPemasukan ? 'add_circle' : 'remove_circle';
                $colorClass = $isPemasukan ? 'text-emerald-500 bg-emerald-50' : 'text-slate-400 bg-slate-50';
            @endphp
            <div class="flex items-center justify-between p-3 bg-white hover:bg-slate-50 transition-all rounded-xl border border-transparent hover:border-slate-100 group">
                <div class="flex items-center gap-3 min-w-0">
                    <div class="w-10 h-10 rounded-xl {{ $colorClass }} flex items-center justify-center transition-all group-hover:scale-105">
                        <span class="material-symbols-outlined text-lg">{{ $icon }}</span>
                    </div>
                    <div class="min-w-0">
                        <p class="text-[12px] font-semibold text-slate-800 tracking-tight truncate">
                            {{ $transaction->kategoriNama->nama ?? 'Umum' }}
                        </p>
                        <p class="text-[10px] text-slate-400 font-medium truncate mt-0.5">
                            {{ \Carbon\Carbon::parse($transaction->created_at)->format('d M') }} • {{ $transaction->keterangan ?? ($transaction->kategoriJenis->jenis ?? '-') }}
                        </p>
                    </div>
                </div>
                <div class="text-right">
                    <p class="text-[12px] font-bold {{ $isPemasukan ? 'text-emerald-600' : 'text-slate-900' }} tracking-tight">
                        {{ $isPemasukan ? '+' : '-' }} {{ \App\Helpers\DashboardGreetingHelper::formatRupiah($transaction->nominal) }}
                    </p>
                </div>
            </div>
        @empty
            <div class="py-12 flex flex-col items-center justify-center opacity-30">
                <span class="material-symbols-outlined text-3xl mb-1 text-slate-300">receipt_long</span>
                <p class="text-[11px] font-medium text-slate-400">Belum ada transaksi</p>
            </div>
        @endforelse
    </div>
</section>
