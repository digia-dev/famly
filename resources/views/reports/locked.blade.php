<x-app-layout>
    @section('title', 'Akses Terbatas')

    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif !important; background-color: #F7F9FA; }
        .premium-gradient { background: linear-gradient(135deg, #00aa13 0%, #008910 100%); }
        .glass-card { background: rgba(255, 255, 255, 0.9); backdrop-filter: blur(20px); border: 1px solid rgba(255, 255, 255, 0.8); }
    </style>

    <div class="min-h-screen bg-slate-50 relative overflow-hidden flex flex-col items-center justify-center px-6 pb-32">
        <!-- Background Accents -->
        <div class="absolute top-0 left-0 w-full h-64 premium-gradient opacity-10 blur-3xl -mt-32"></div>
        <div class="absolute bottom-0 right-0 w-64 h-64 bg-[#00AA13] opacity-5 blur-3xl -mb-32 -mr-32"></div>

        <div class="max-w-md w-full relative z-10 text-center">
            <!-- Icon -->
            <div class="w-24 h-24 bg-white rounded-[40px] shadow-2xl shadow-emerald-500/10 flex items-center justify-center mx-auto mb-10 border border-slate-50">
                <span class="material-symbols-outlined text-[48px] text-[#00AA13] animate-pulse">lock_person</span>
            </div>

            <!-- Text Content -->
            <h1 class="text-2xl font-extrabold text-slate-800 mb-4 tracking-tight">Riwayat Terkunci</h1>
            <p class="text-[14px] font-bold text-slate-500 leading-relaxed mb-10 px-4">
                Laporan untuk bulan <span class="text-slate-900">{{ $startDate->locale('id')->isoFormat('MMMM YYYY') }}</span> hanya tersedia bagi anggota <span class="text-[#00AA13]">Famly Premium</span>.
            </p>

            <!-- Feature Card -->
            <div class="glass-card rounded-[32px] p-6 mb-8 text-left shadow-xl shadow-slate-200/50">
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-4">Manfaat Upgrade</p>
                <div class="space-y-4">
                    <div class="flex items-start gap-4">
                        <div class="w-8 h-8 rounded-xl bg-emerald-50 text-[#00AA13] flex items-center justify-center shrink-0">
                            <span class="material-symbols-outlined text-[18px]">history</span>
                        </div>
                        <div>
                            <h4 class="text-[12px] font-extrabold text-slate-800">Akses Tanpa Batas</h4>
                            <p class="text-[10px] font-bold text-slate-400 mt-0.5">Buka seluruh riwayat keuangan tanpa batasan waktu.</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-4">
                        <div class="w-8 h-8 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                            <span class="material-symbols-outlined text-[18px]">download</span>
                        </div>
                        <div>
                            <h4 class="text-[12px] font-extrabold text-slate-800">Ekspor Eksklusif</h4>
                            <p class="text-[10px] font-bold text-slate-400 mt-0.5">Unduh PDF/Excel untuk arsip profesional Anda.</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-4">
                        <div class="w-8 h-8 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center shrink-0">
                            <span class="material-symbols-outlined text-[18px]">smart_toy</span>
                        </div>
                        <div>
                            <h4 class="text-[12px] font-extrabold text-slate-800">AI Unlimited</h4>
                            <p class="text-[10px] font-bold text-slate-400 mt-0.5">Dapatkan insight finansial harian tanpa kuota batas.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="space-y-3 px-2">
                <a href="{{ route('checkout.index') }}" class="block w-full premium-gradient py-4 rounded-2xl text-white font-black text-[13px] uppercase tracking-widest shadow-lg shadow-emerald-500/20 active:scale-95 transition-all">
                    Langganan Premium — Rp 19.900/bln
                </a>

                <form action="{{ route('reports.buy-one-off') }}" method="POST">
                    @csrf
                    <input type="hidden" name="month" value="{{ $month }}">
                    <input type="hidden" name="year" value="{{ $year }}">
                    <button type="submit" class="block w-full bg-white py-4 rounded-2xl text-slate-800 font-black text-[13px] uppercase tracking-widest shadow-sm hover:shadow-md active:scale-95 transition-all border border-slate-100">
                        Buka Laporan Ini Saja — Rp 4.900
                    </button>
                </form>
                
                <a href="{{ route('reports.index') }}" class="flex items-center justify-center gap-2 pt-4 text-[11px] font-black text-slate-400 uppercase tracking-widest hover:text-slate-600 transition-colors">
                    <span class="material-symbols-outlined text-[16px]">arrow_back</span>
                    Kembali ke Laporan Trial
                </a>
            </div>
        </div>
    </div>
</x-app-layout>
