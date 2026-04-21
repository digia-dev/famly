<x-app-layout>
    <div class="min-h-screen bg-[#F6F7F8]">
        <main class="max-w-screen-xl mx-auto px-4 pt-4 pb-24 space-y-4">
            {{-- Compact Header --}}
            <section class="px-1 flex justify-between items-end">
                <div>
                    <h2 class="text-[18px] font-extrabold tracking-tighter text-slate-900 leading-tight">Analisis Keuangan</h2>
                    <p class="text-slate-400 text-[10px] font-bold mt-0.5">Laporan pengeluaran rutin keluarga</p>
                </div>
                <div class="w-9 h-9 bg-white rounded-xl shadow-sm border border-slate-100 flex items-center justify-center text-primary">
                    <span class="material-symbols-outlined text-[20px]">calendar_month</span>
                </div>
            </section>

            {{-- Month Selector (Compact Pills) --}}
            <section class="flex gap-2 overflow-x-auto no-scrollbar py-0.5">
                @php
                    $monthOptions = [];
                    $current = now();
                    for($i = -2; $i <= 1; $i++) {
                        $monthOptions[] = $current->copy()->addMonths($i);
                    }
                @endphp
                @foreach($monthOptions as $mOption)
                    <a href="?month={{ $mOption->month }}&year={{ $mOption->year }}" 
                       class="shrink-0 px-4 py-1.5 rounded-xl text-[11px] font-bold transition-all border
                       {{ $selectedDate->month == $mOption->month && $selectedDate->year == $mOption->year 
                          ? 'bg-primary text-white border-primary shadow-md shadow-primary/10' 
                          : 'bg-white text-slate-400 border-slate-100 active:scale-95' }}">
                        {{ $mOption->translatedFormat('F') }}
                    </a>
                @endforeach
            </section>

            {{-- UIUX AI Analysis Card (Reference Style) --}}
            <section class="bg-white rounded-2xl border-2 border-blue-100 p-4 shadow-sm relative overflow-hidden group">
                <div class="flex items-center gap-2 mb-2">
                    <div class="w-6 h-6 rounded-lg bg-blue-900 flex items-center justify-center text-white">
                        <span class="material-symbols-outlined text-[14px]">auto_awesome</span>
                    </div>
                    <span class="text-[12px] font-bold text-slate-900 tracking-tight">Analisis Transaksi dengan AI <span class="text-slate-400 font-medium">• FinGPT</span></span>
                </div>
                <p class="text-[11px] text-slate-500 font-medium leading-relaxed mb-4">
                    FinGPT siap analisis transaksi kamu bulan {{ $selectedDate->translatedFormat('F') }} nih. Saatnya lihat bagaimana hasilnya!
                </p>
                <button class="w-full py-2.5 bg-[#002B5B] text-white rounded-xl text-[12px] font-bold active:scale-[0.98] transition-all shadow-md">
                    Mulai Analisis
                </button>
            </section>

            {{-- Summary Hero Card (High Density) --}}
            <section class="bg-emerald-700 p-4 rounded-3xl text-white shadow-sm relative overflow-hidden">
                <div class="relative z-10">
                    <p class="text-[10px] font-bold text-white/60 mb-1">TOTAL KEBUTUHAN</p>
                    <div class="flex justify-between items-center">
                        <h3 class="text-2xl font-extrabold tracking-tighter">
                            Rp {{ number_format($totalSpending, 0, ',', '.') }}
                        </h3>
                        <div class="flex items-center gap-1 text-[10px] font-bold {{ $percentageChange > 0 ? 'bg-red-500/20 text-red-100' : 'bg-white/20 text-white' }} px-2 py-1 rounded-full backdrop-blur-md">
                            <span class="material-symbols-outlined text-[12px]">{{ $percentageChange > 0 ? 'trending_up' : 'trending_down' }}</span>
                            {{ abs($percentageChange) }}%
                        </div>
                    </div>
                </div>
                <div class="absolute -right-4 -top-4 w-24 h-24 bg-white/10 rounded-full blur-2xl"></div>
            </section>

            {{-- Comparison Chart (Slim) --}}
            <section class="bg-white p-4 rounded-3xl border border-slate-100 shadow-sm space-y-4">
                <div class="flex justify-between items-center">
                    <h4 class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Aktivitas Mingguan</h4>
                    <div class="flex items-center gap-3">
                        <div class="flex items-center gap-1">
                            <div class="w-1.5 h-1.5 bg-slate-100 rounded-full"></div>
                            <span class="text-[9px] font-bold text-slate-400">LALU</span>
                        </div>
                        <div class="flex items-center gap-1">
                            <div class="w-1.5 h-1.5 bg-primary rounded-full"></div>
                            <span class="text-[9px] font-bold text-slate-400">KINI</span>
                        </div>
                    </div>
                </div>
                
                <div class="flex items-end justify-between h-24 px-2">
                    @foreach($weeklyData as $wk => $val)
                        <div class="flex flex-col items-center gap-1.5 w-full">
                            <div class="flex gap-1 items-end h-16 w-full justify-center">
                                <div class="w-2.5 bg-slate-50 rounded-t-lg" style="height: {{ ($weeklyPrevData[$wk] / ($maxVal ?: 1)) * 100 }}%"></div>
                                <div class="w-2.5 bg-primary rounded-t-lg" style="height: {{ ($val / ($maxVal ?: 1)) * 100 }}%"></div>
                            </div>
                            <span class="text-[9px] font-bold text-slate-300">MG {{ $wk }}</span>
                        </div>
                    @endforeach
                </div>
            </section>

            {{-- Top Spending List (High Density) --}}
            <section class="space-y-2">
                <h4 class="text-[12px] font-bold text-slate-800 tracking-tight px-1">Pengeluaran Terbesar</h4>
                <div class="grid grid-cols-1 gap-2">
                    @forelse($topCategories as $cat)
                        <div class="bg-white p-3 rounded-2xl flex items-center justify-between border border-slate-100 shadow-sm active:scale-[0.99] transition-all">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-slate-50 flex items-center justify-center rounded-xl text-primary">
                                    <span class="material-symbols-outlined text-[20px]" style="font-variation-settings: 'FILL' 1;">{{ $cat->kategoriNama->icon ?? 'shopping_basket' }}</span>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="text-[13px] font-bold text-slate-900 leading-tight truncate">{{ $cat->kategoriNama->nama ?? 'Lainnya' }}</p>
                                    <p class="text-[10px] text-slate-400 font-medium mt-0.5 truncate">{{ $cat->kategoriNama->kategori_kas ?? 'Kebutuhan' }}</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-[13px] font-extrabold text-slate-900 leading-none">Rp {{ number_format($cat->total, 0, ',', '.') }}</p>
                                <div class="flex items-center justify-end gap-1 mt-1.5">
                                    <span class="text-[9px] font-bold {{ $cat->growth > 0 ? 'text-red-500 bg-red-50' : 'text-emerald-600 bg-emerald-50' }} px-1.5 py-0.5 rounded-lg">
                                        {{ $cat->growth > 0 ? '+' : '' }}{{ $cat->growth }}%
                                    </span>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="p-8 text-center bg-white rounded-2xl border border-dashed border-slate-200">
                            <p class="text-[11px] font-bold text-slate-400">Belum ada data pengeluaran</p>
                        </div>
                    @endforelse
                </div>
            </section>

            {{-- Insights (Floating Mini Card) --}}
            <section class="bg-white p-4 rounded-3xl border border-slate-100 shadow-sm flex items-start gap-4">
                <div class="w-10 h-10 rounded-2xl bg-amber-50 flex items-center justify-center text-amber-600 shrink-0">
                    <span class="material-symbols-outlined text-[20px]" style="font-variation-settings: 'FILL' 1;">auto_awesome</span>
                </div>
                <div class="space-y-1">
                    <h5 class="text-[11px] font-bold text-slate-800 leading-none">Insight AI Famly</h5>
                    <p class="text-[10px] text-slate-400 font-medium leading-relaxed">{{ $aiInsight }}</p>
                </div>
            </section>

            {{-- Action Banner --}}
            <button class="w-full py-3 bg-slate-900 text-white rounded-2xl shadow-lg active:scale-[0.98] transition-all flex items-center justify-center gap-2 group">
                <span class="text-[11px] font-bold uppercase tracking-wider">Unduh Laporan Lengkap</span>
                <span class="material-symbols-outlined text-[18px] group-hover:translate-y-0.5 transition-transform">download</span>
            </button>

        </main>
    </div>
</x-app-layout>
