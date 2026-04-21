<x-app-layout>
    @section('title', 'Riwayat Transaksi')

    <div class="bg-[#F6F7F8] min-h-screen pb-32">
        <!-- Compact Filter Section (Super App Style) -->
        <div class="bg-white/80 backdrop-blur-md px-4 pt-4 pb-3 sticky top-12 z-40">
            <div class="max-w-screen-xl mx-auto">
                <div class="flex gap-2 bg-slate-50 p-1 rounded-2xl border border-slate-100">
                    @foreach(['week' => 'Minggu', 'month' => 'Bulan', 'year' => 'Tahun'] as $key => $label)
                        <a href="{{ route('transaction.index', ['filter' => $key]) }}" 
                           class="flex-1 py-1.5 text-[11px] font-bold text-center rounded-xl transition-all {{ $filter == $key ? 'bg-white text-primary shadow-sm' : 'text-slate-400 hover:text-slate-600' }}">
                            {{ $label }}
                        </a>
                    @endforeach
                </div>
            </div>
        </div>

        <main class="max-w-screen-xl mx-auto px-4 py-4 space-y-4">
            @forelse($groupedTransactions as $dateLabel => $data)
                <!-- Group Section (Neat & Tidy) -->
                <section class="space-y-2">
                    <div class="flex items-center justify-between px-1">
                        <h2 class="text-[12px] font-bold text-slate-800 tracking-tight">
                            {{ $data['date']->isToday() ? 'Hari Ini' : ($data['date']->isYesterday() ? 'Kemarin' : $dateLabel) }}
                        </h2>
                        <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest">
                            {{ $data['net'] > 0 ? '+' : '-' }}Rp {{ number_format(abs($data['net']), 0, ',', '.') }}
                        </span>
                    </div>

                    <div class="grid grid-cols-1 gap-1.5">
                        @foreach($data['items'] as $item)
                            <div class="bg-white p-3 rounded-2xl border border-slate-100 flex items-center justify-between group active:scale-[0.99] transition-all cursor-pointer">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-slate-50 flex items-center justify-center text-slate-400 group-hover:bg-primary/5 group-hover:text-primary transition-all">
                                        <span class="material-symbols-outlined text-[20px]" style="font-variation-settings: 'FILL' 1;">
                                            {{ $item->kategoriNama->icon ?? ($item->kategoriJenis->jenis == 'Pemasukan' ? 'add_card' : 'payments') }}
                                        </span>
                                    </div>
                                    <div>
                                        <p class="text-[13px] font-bold text-slate-900 leading-tight">{{ $item->keterangan ?? ($item->kategoriNama->nama ?? 'Lainnya') }}</p>
                                        <p class="text-[10px] text-slate-400 font-medium mt-0.5">
                                            {{ $item->kategoriNama->nama ?? 'Umum' }} • {{ $item->created_at->format('H:i') }}
                                        </p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="text-[13px] font-extrabold {{ $item->kategoriJenis->jenis == 'Pemasukan' ? 'text-primary' : 'text-slate-900' }}">
                                        {{ $item->kategoriJenis->jenis == 'Pemasukan' ? '+' : '-' }}Rp{{ number_format($item->nominal, 0, ',', '.') }}
                                    </p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </section>
            @empty
                <div class="p-10 text-center bg-white rounded-3xl border border-dashed border-slate-200">
                    <span class="material-symbols-outlined text-3xl text-slate-200 mb-2">history</span>
                    <p class="text-[11px] font-bold text-slate-400 italic">Belum ada aktivitas transaksi</p>
                </div>
            @endforelse
        </main>

        <!-- Dynamic Analysis CTA (Small & Professional) -->
        <div class="fixed bottom-24 left-0 right-0 px-4 z-40">
            <a href="{{ route('reports.analysis') }}" class="max-w-screen-xl mx-auto bg-slate-900 p-3 rounded-2xl shadow-xl flex justify-between items-center group active:scale-[0.98] transition-all">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-white/10 flex items-center justify-center text-white">
                        <span class="material-symbols-outlined text-[18px]">query_stats</span>
                    </div>
                    <div>
                        <p class="text-[11px] font-bold text-white tracking-tight">Analisis Keuangan Lengkap</p>
                        <p class="text-[9px] font-medium text-white/50">Optimalkan pengeluaran keluarga Anda</p>
                    </div>
                </div>
                <span class="material-symbols-outlined text-white text-sm group-hover:translate-x-1 transition-transform">chevron_right</span>
            </a>
        </div>
    </div>
</x-app-layout>
