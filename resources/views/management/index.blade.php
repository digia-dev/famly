<x-app-layout>
    @section('title', 'Manajemen')

    <div class="bg-[#F6F7F8] min-h-screen pb-32">
        <!-- Top Tab Navigation Section (Compact) -->
        <header class="bg-white sticky top-14 z-50 border-b border-slate-100 h-12 flex items-center">
            <div class="max-w-screen-xl mx-auto w-full px-4 flex justify-between items-center">
                <div class="flex gap-4 overflow-x-auto no-scrollbar">
                    @foreach(['pos' => 'Pos Budget', 'dompet' => 'Dompet', 'tabungan' => 'Tabungan'] as $key => $label)
                        <a href="{{ route('management.index', ['type' => $key]) }}" 
                           class="relative py-3 px-1 text-[12px] transition-all whitespace-nowrap {{ $type === $key ? 'font-bold text-primary active-tab' : 'font-medium text-slate-400 hover:text-slate-600' }}">
                            {{ $label }}
                            @if($type === $key)
                                <div class="absolute bottom-0 left-0 right-0 h-0.5 bg-primary rounded-full"></div>
                            @endif
                        </a>
                    @endforeach
                </div>
                <a href="{{ route('reports.analysis') }}" class="text-primary font-bold text-[10px] uppercase tracking-wider bg-primary/5 px-2 py-1 rounded-lg">Analitik</a>
            </div>
        </header>

        <main class="max-w-screen-xl mx-auto px-4 py-4 space-y-4">
            
            {{-- Overview Banner (High-Density Financial Cockpit) --}}
            <header class="p-5 rounded-3xl bg-emerald-700 text-white shadow-lg shadow-emerald-900/20 relative overflow-hidden flex flex-col justify-center min-h-[150px]">
                <div class="relative z-10 space-y-4">
                    <div>
                        <p class="text-[10px] font-bold tracking-tight text-white/60 mb-1 uppercase tracking-widest leading-none">
                            {{ $type === 'pos' ? 'Kapasitas Anggaran' : ($type === 'dompet' ? 'Total Aset Keluarga' : 'Total Tabungan') }}
                        </p>
                        <h2 class="text-2xl font-black tracking-tighter leading-none mb-1">
                            Rp {{ number_format($totalBalance, 0, ',', '.') }}
                        </h2>
                        <div class="flex items-center gap-1.5 opacity-60">
                            <span class="material-symbols-outlined text-[12px]">calendar_today</span>
                            <p class="text-[9px] font-bold uppercase tracking-tighter">{{ now()->translatedFormat('F Y') }}</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4 pt-4 border-t border-white/10">
                        <!-- Monthly Income Stats -->
                        <div class="space-y-1">
                            <p class="text-[9px] font-bold text-white/50 uppercase tracking-tighter">Pemasukan</p>
                            <div class="flex items-baseline gap-2">
                                <h4 class="text-[13px] font-black leading-none">Rp{{ number_format($monthlyIncome, 0, ',', '.') }}</h4>
                                <span class="text-[8px] font-black px-1 py-0.5 rounded {{ $incomeGrowth >= 0 ? 'bg-emerald-400/20 text-emerald-300' : 'bg-rose-400/20 text-rose-300' }} leading-none">
                                    {{ $incomeGrowth >= 0 ? '↑' : '↓' }} {{ abs(round($incomeGrowth, 1)) }}%
                                </span>
                            </div>
                        </div>

                        <!-- Monthly Expense Stats -->
                        <div class="space-y-1">
                            <p class="text-[9px] font-bold text-white/50 uppercase tracking-tighter">Pengeluaran</p>
                            <div class="flex items-baseline gap-2">
                                <h4 class="text-[13px] font-black leading-none">Rp{{ number_format($monthlyExpense, 0, ',', '.') }}</h4>
                                <span class="text-[8px] font-black px-1 py-0.5 rounded {{ $expenseGrowth <= 0 ? 'bg-emerald-400/20 text-emerald-300' : 'bg-rose-400/20 text-rose-300' }} leading-none">
                                    {{ $expenseGrowth >= 0 ? '↑' : '↓' }} {{ abs(round($expenseGrowth, 1)) }}%
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Decorative Elements -->
                <div class="absolute -right-4 -top-8 w-32 h-32 bg-white/10 rounded-full blur-3xl"></div>
                <div class="absolute -left-10 -bottom-10 w-40 h-40 bg-emerald-400/10 rounded-full blur-3xl"></div>
            </header>

            {{-- List Section --}}
            <section class="space-y-2">
                <div class="flex justify-between items-center px-1">
                    <h3 class="text-[12px] font-bold text-slate-800 tracking-tight">Rincian {{ ucfirst($type) }}</h3>
                    <a href="{{ route('management.create', ['type' => $type]) }}" class="w-7 h-7 bg-primary text-white rounded-lg flex items-center justify-center shadow-sm active:scale-90 transition-transform">
                        <span class="material-symbols-outlined text-[18px]">add</span>
                    </a>
                </div>
                
                <div class="grid grid-cols-1 gap-2">
                    @foreach($wallets as $wallet)
                    <a href="{{ route('management.index', ['type' => $type, 'id' => $wallet->id]) }}" class="bg-white p-3 rounded-2xl shadow-sm border border-slate-100 flex items-center gap-3 group active:scale-[0.99] transition-all">
                        <div class="w-10 h-10 rounded-xl bg-slate-50 flex items-center justify-center text-slate-500 group-hover:bg-primary/10 group-hover:text-primary transition-all">
                            <span class="material-symbols-outlined text-xl" style="font-variation-settings: 'FILL' 1;">{{ $wallet->icon }}</span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h4 class="font-bold text-slate-900 text-[13px] tracking-tight leading-tight">{{ $wallet->nama }}</h4>
                            <p class="text-[10px] text-slate-400 font-medium truncate mt-0.5">{{ $wallet->description ?? $wallet->kategori_kas ?? 'Budget keluarga' }}</p>
                            
                            @if($type === 'tabungan')
                            <div class="w-full bg-slate-100 h-1 rounded-full mt-2 overflow-hidden">
                                <div class="h-full bg-emerald-500" style="width: {{ min(100, round(($wallet->balance / ($wallet->target_saldo ?: 1)) * 100)) }}%"></div>
                            </div>
                            @endif
                        </div>
                        <div class="text-right">
                            <p class="font-bold text-slate-900 text-[13px] leading-none">Rp {{ number_format($type === 'pos' ? $wallet->target_saldo : ($type === 'dompet' ? $wallet->balance : $wallet->balance), 0, ',', '.') }}</p>
                            @if($type === 'pos' || $type === 'tabungan')
                            <p class="text-[9px] font-bold text-primary mt-1.5 bg-primary/5 px-1.5 py-0.5 rounded-lg inline-block">
                                {{ $type === 'pos' ? 'Sisa: Rp '.number_format($wallet->remaining,0) : round(($wallet->balance / ($wallet->target_saldo ?: 1)) * 100).'% Target' }}
                            </p>
                            @endif
                        </div>
                    </a>
                    @endforeach
                </div>
            </section>

            {{-- High Density Insight Cards --}}
            <section class="grid grid-cols-2 gap-3">
                <div class="bg-white p-3 rounded-2xl border border-slate-100 shadow-sm flex flex-col gap-2">
                    <div class="w-8 h-8 rounded-xl bg-amber-50 flex items-center justify-center text-amber-600">
                        <span class="material-symbols-outlined text-lg">lightbulb</span>
                    </div>
                    <div>
                        <h5 class="font-bold text-slate-800 text-[11px] mb-0.5">Saran Finansial</h5>
                        <p class="text-[10px] text-slate-400 font-medium leading-relaxed line-clamp-3">
                            {{ $aiInsight['pos_advice'] ?? $aiInsight['asset_advice'] ?? $aiInsight['savings_advice'] }}
                        </p>
                    </div>
                </div>
                <div class="bg-white p-3 rounded-2xl border border-slate-100 shadow-sm flex flex-col gap-2">
                    <div class="w-8 h-8 rounded-xl bg-blue-50 flex items-center justify-center text-blue-600">
                        <span class="material-symbols-outlined text-lg">security</span>
                    </div>
                    <div>
                        <h5 class="font-bold text-slate-800 text-[11px] mb-0.5">Skor Kesehatan</h5>
                        <p class="text-[10px] text-slate-400 font-medium leading-relaxed">
                            Skor: <span class="text-primary font-bold">{{ $aiInsight['score'] ?? 85 }}/100</span>. Kondisi dompet keluarga sangat sehat dan aman.
                        </p>
                    </div>
                </div>
            </section>

        </main>

        @include('components.ai-bottom-sheet')
    </div>
</x-app-layout>
