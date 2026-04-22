<x-app-layout>
    @section('title', 'Pusat Pribadi')

    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif !important; background-color: #F7F9FA; }
        .no-line-card { background: white; border: none; box-shadow: 0 10px 30px -15px rgba(0,0,0,0.05); }
        .premium-bg { background: linear-gradient(135deg, #00aa13 0%, #008910 100%); }
        .health-score-gradient { background: conic-gradient(#00AA13 var(--score), #F1F5F9 0deg); }
    </style>

    <div class="min-h-screen pb-32">
        <!-- Header Section -->
        <div class="bg-white px-6 pt-12 pb-8 rounded-b-[40px] shadow-sm mb-6 relative overflow-hidden">
            <div class="absolute top-0 right-0 w-32 h-32 bg-[#00AA13] opacity-5 rounded-full -mr-16 -mt-16"></div>
            
            <div class="max-w-screen-xl mx-auto flex items-center justify-between relative z-10">
                <div class="flex items-center gap-4">
                    <div class="w-16 h-16 rounded-[24px] premium-bg flex items-center justify-center text-white shadow-lg shadow-emerald-500/20">
                        <span class="material-symbols-outlined text-[32px]">person</span>
                    </div>
                    <div>
                        <h1 class="text-xl font-extrabold text-slate-800 tracking-tight">Konteks Pribadi</h1>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1">Orchestra Individual Aktif</p>
                    </div>
                </div>
                <div class="text-right">
                    <p class="text-[10px] font-black text-slate-300 uppercase tracking-tighter">Terakhir Sinkron</p>
                    <p class="text-[12px] font-extrabold text-slate-800">{{ now()->format('H:i') }} WIB</p>
                </div>
            </div>
        </div>

        <main class="max-w-screen-xl mx-auto px-6 space-y-6">
            <!-- Financial Health & Summary -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- Health Score -->
                <div class="no-line-card p-6 rounded-[32px] flex items-center gap-5">
                    <div class="relative w-20 h-20 rounded-full flex items-center justify-center health-score-gradient" style="--score: {{ ($aiInsights['score'] ?? 85) * 3.6 }}deg">
                        <div class="absolute inset-2 bg-white rounded-full flex flex-col items-center justify-center">
                            <span class="text-[16px] font-black text-slate-800">{{ $aiInsights['score'] ?? 85 }}</span>
                            <span class="text-[7px] font-bold text-slate-300 uppercase">Score</span>
                        </div>
                    </div>
                    <div>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Financial Health</p>
                        <h3 class="text-[13px] font-extrabold text-[#00AA13]">{{ $aiInsights['health_label'] ?? 'Cukup Sehat' }}</h3>
                        <p class="text-[10px] font-bold text-slate-400 mt-1 leading-tight">Berdasarkan pola transaksi 7 hari terakhir.</p>
                    </div>
                </div>

                <!-- Total Balance -->
                <div class="no-line-card p-6 rounded-[32px] md:col-span-2">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Net Flow Pribadi</p>
                            <h2 class="text-3xl font-black text-slate-900 tracking-tighter">
                                Rp {{ number_format(($personalStats->total_income ?? 0) - ($personalStats->total_expense ?? 0), 0, ',', '.') }}
                            </h2>
                        </div>
                        <div class="flex gap-2">
                            <div class="px-3 py-1 bg-emerald-50 text-[#00AA13] rounded-full text-[9px] font-black uppercase">+{{ number_format($personalStats->total_income ?? 0, 0, ',', '.') }}</div>
                            <div class="px-3 py-1 bg-rose-50 text-rose-500 rounded-full text-[9px] font-black uppercase">-{{ number_format($personalStats->total_expense ?? 0, 0, ',', '.') }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Weekly Chart Section -->
            <div class="no-line-card p-6 rounded-[32px]">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-[11px] font-black text-slate-400 uppercase tracking-widest">Arus Kas Mingguan</h3>
                    <div class="flex items-center gap-1.5">
                        <div class="w-2.5 h-2.5 rounded-full bg-[#00AA13]"></div>
                        <span class="text-[9px] font-bold text-slate-400 uppercase">Pengeluaran</span>
                    </div>
                </div>
                <div class="flex items-end justify-between h-40 gap-3 px-2">
                    @php 
                        $maxWeekly = collect($weeklyChart)->max('total') ?: 1;
                    @endphp
                    @foreach($weeklyChart as $data)
                        <div class="flex-1 flex flex-col items-center gap-3 group">
                            <div class="w-full bg-slate-50 relative rounded-2xl overflow-hidden h-32">
                                <div class="absolute bottom-0 w-full transition-all duration-700 bg-emerald-100 group-hover:bg-[#00AA13]/20"
                                     style="height: {{ ($data['total'] / $maxWeekly) * 100 }}%">
                                    @if($data['total'] > 0)
                                        <div class="absolute top-0 w-full h-1.5 bg-[#00AA13] rounded-t-full"></div>
                                    @endif
                                </div>
                            </div>
                            <span class="text-[10px] font-bold {{ $data['is_today'] ? 'text-[#00AA13]' : 'text-slate-400' }} uppercase">{{ $data['day'] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Insight Si Famly -->
            <div class="no-line-card p-6 rounded-[32px] bg-slate-900 border-none relative overflow-hidden">
                <div class="absolute top-0 right-0 p-8 opacity-10">
                    <span class="material-symbols-outlined text-[120px] text-white">smart_toy</span>
                </div>
                
                <div class="relative z-10">
                    <div class="flex items-center gap-3 mb-6">
                        <div class="w-10 h-10 rounded-2xl bg-gradient-to-br from-emerald-400 to-emerald-600 flex items-center justify-center shadow-lg shadow-emerald-500/30">
                            <span class="material-symbols-outlined text-white text-[24px]">auto_awesome</span>
                        </div>
                        <div>
                            <h3 class="text-[14px] font-black text-white tracking-tight">Insight Si Famly</h3>
                            <p class="text-[10px] font-bold text-white/40 uppercase tracking-widest">Asisten Financial Jenius</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="bg-white/5 p-4 rounded-2xl border border-white/5">
                            <p class="text-[9px] font-black text-emerald-400 uppercase tracking-widest mb-1.5">Pos Anggaran</p>
                            <p class="text-[12px] font-bold text-white/80 leading-relaxed">{{ $aiInsights['pos_advice'] ?? 'Analisis pengeluaran harian sedang diproses...' }}</p>
                        </div>
                        <div class="bg-white/5 p-4 rounded-2xl border border-white/5">
                            <p class="text-[9px] font-black text-blue-400 uppercase tracking-widest mb-1.5">Pengelolaan Aset</p>
                            <p class="text-[12px] font-bold text-white/80 leading-relaxed">{{ $aiInsights['asset_advice'] ?? 'Menghitung rasio tabungan optimal...' }}</p>
                        </div>
                        <div class="bg-white/5 p-4 rounded-2xl border border-white/5">
                            <p class="text-[9px] font-black text-amber-400 uppercase tracking-widest mb-1.5">Target Tabungan</p>
                            <p class="text-[12px] font-bold text-white/80 leading-relaxed">{{ $aiInsights['savings_advice'] ?? 'Menyiapkan strategi pencapaian target...' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Wallets Grid -->
            <div>
                <div class="flex items-center justify-between px-2 mb-4">
                    <h3 class="text-[11px] font-black text-slate-800 uppercase tracking-widest">Dompet Pribadi</h3>
                    <button class="text-[11px] font-bold text-[#00AA13] font-black uppercase tracking-widest">+ Tambah</button>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    @forelse($wallets as $wallet)
                        <div class="no-line-card p-4 rounded-[28px] group active:scale-[0.98] transition-all">
                            <div class="w-10 h-10 rounded-2xl bg-slate-50 flex items-center justify-center mb-3 text-slate-400 group-hover:bg-[#00AA13]/10 group-hover:text-[#00AA13] transition-colors">
                                <span class="material-symbols-outlined text-[20px]">{{ $wallet->icon ?? 'account_balance_wallet' }}</span>
                            </div>
                            <p class="text-[11px] font-black text-slate-800 leading-tight">{{ $wallet->nama }}</p>
                            <p class="text-[10px] font-bold text-slate-400 mt-0.5 capitalize">{{ $wallet->wallet_type }}</p>
                        </div>
                    @empty
                        <div class="col-span-2 py-10 text-center bg-slate-50 rounded-[32px] border border-dashed border-slate-100">
                            <p class="text-[11px] font-bold text-slate-400 italic">Belum ada dompet pribadi</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </main>
    </div>
</x-app-layout>
