<x-app-layout>
    @section('title', 'Famly Premium Hub')

    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif !important; background-color: #F7F9FA; }
        .premium-gradient { background: linear-gradient(135deg, #00aa13 0%, #008910 100%); }
        .glass-card { background: rgba(255, 255, 255, 0.8); backdrop-filter: blur(10px); border: 1px solid rgba(255, 255, 255, 0.5); }
    </style>

    <div class="min-h-screen pb-32">
        <!-- Hero Header -->
        <div class="premium-gradient pt-16 pb-32 px-6 rounded-b-[40px] shadow-lg relative overflow-hidden">
            <div class="absolute top-0 right-0 w-64 h-64 bg-white/10 rounded-full -mr-20 -mt-20 blur-3xl"></div>
            <div class="max-w-2xl mx-auto relative z-10 text-center">
                <div class="w-16 h-16 bg-white/20 backdrop-blur-md rounded-2xl flex items-center justify-center mx-auto mb-6 border border-white/30 shadow-xl">
                    <span class="material-symbols-outlined text-white text-[32px]">workspace_premium</span>
                </div>
                <h1 class="text-2xl font-extrabold text-white mb-2">Famly Premium</h1>
                <p class="text-white/80 text-[13px] font-medium leading-relaxed">
                    Akses kontrol finansial tanpa batas untuk keluarga & komunitas Anda.
                </p>
            </div>
        </div>

        <!-- Status Card -->
        <div class="max-w-2xl mx-auto px-6 -mt-16 relative z-20">
            <div class="glass-card p-6 rounded-[32px] shadow-xl mb-8">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Status Akun</p>
                        <h2 class="text-xl font-bold {{ $isPremium ? 'text-[#00AA13]' : 'text-slate-800' }}">
                            {{ $isPremium ? 'Subscriber Premium' : 'Uji Coba (Trial)' }}
                        </h2>
                    </div>
                    @if($isPremium)
                        <div class="px-3 py-1 bg-[#00AA13]/10 text-[#00AA13] rounded-full text-[10px] font-black uppercase">Aktif</div>
                    @else
                        <div class="px-3 py-1 bg-amber-500/10 text-amber-500 rounded-full text-[10px] font-black uppercase">Terbatas</div>
                    @endif
                </div>

                @if(!$isPremium)
                    <div class="p-4 bg-slate-50 rounded-2xl border border-slate-100 flex items-start gap-3 mb-6">
                        <span class="material-symbols-outlined text-amber-500">info</span>
                        <p class="text-[11px] font-bold text-slate-500 leading-relaxed">
                            Batas history Trial hanya 1 bulan. Upgrade untuk membuka akses seluruh riwayat keuangan Anda.
                        </p>
                    </div>
                    <a href="{{ route('checkout.index') }}" class="block w-full text-center premium-gradient py-4 rounded-2xl text-white font-black text-[13px] uppercase tracking-widest shadow-lg shadow-emerald-500/20 active:scale-95 transition-all">
                        Aktifkan Premium — Rp 19.900/bln
                    </a>
                @else
                    <div class="p-4 bg-emerald-50 rounded-2xl border border-emerald-100 flex items-center gap-3">
                        <span class="material-symbols-outlined text-[#00AA13]">verified</span>
                        <p class="text-[11px] font-bold text-emerald-700">
                            Langganan Anda aktif hingga {{ $user->subscription_until->format('d M Y') }}.
                        </p>
                    </div>
                @endif
            </div>

            <!-- Features Grid -->
            <div class="grid grid-cols-1 gap-4 mb-8">
                <div class="flex items-center gap-4 p-4 bg-white rounded-3xl border border-slate-100 shadow-sm">
                    <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center">
                        <span class="material-symbols-outlined">history</span>
                    </div>
                    <div>
                        <h4 class="text-[13px] font-extrabold text-slate-800">Riwayat Tak Terbatas</h4>
                        <p class="text-[10px] font-bold text-slate-400 mt-0.5">Akses data dari tahun-tahun sebelumnya.</p>
                    </div>
                </div>
                <div class="flex items-center gap-4 p-4 bg-white rounded-3xl border border-slate-100 shadow-sm">
                    <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-[#00AA13] flex items-center justify-center">
                        <span class="material-symbols-outlined">smart_toy</span>
                    </div>
                    <div>
                        <h4 class="text-[13px] font-extrabold text-slate-800">AI Personal Assistant</h4>
                        <p class="text-[10px] font-bold text-slate-400 mt-0.5">Penggunaan AI tanpa batas harian di akun pribadi.</p>
                    </div>
                </div>
                <div class="flex items-center gap-4 p-4 bg-white rounded-3xl border border-slate-100 shadow-sm">
                    <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center">
                        <span class="material-symbols-outlined">download</span>
                    </div>
                    <div>
                        <h4 class="text-[13px] font-extrabold text-slate-800">Ekspor Laporan Lengkap</h4>
                        <p class="text-[10px] font-bold text-slate-400 mt-0.5">Unduh PDF & Excel untuk analisis mandiri.</p>
                    </div>
                </div>
            </div>

            <!-- Billing History -->
            <div class="bg-white rounded-[32px] p-6 border border-slate-100 shadow-sm overflow-hidden">
                <h3 class="text-sm font-extrabold text-slate-800 mb-6 flex items-center gap-2">
                    <span class="material-symbols-outlined text-slate-400">payments</span>
                    Riwayat Pembayaran
                </h3>
                <div class="space-y-4">
                    @forelse($billingHistory as $history)
                        <div class="flex items-center justify-between py-3 border-b border-slate-50 last:border-0">
                            <div>
                                <p class="text-[11px] font-extrabold text-slate-700">{{ $history->package }}</p>
                                <p class="text-[9px] font-bold text-slate-300 mt-0.5">{{ $history->date }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-[11px] font-black text-slate-900">{{ $history->amount }}</p>
                                <p class="text-[9px] font-bold text-[#00AA13]">{{ $history->status }}</p>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-6">
                            <p class="text-[11px] font-bold text-slate-300 italic">Belum ada riwayat transaksi.</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Help Contact -->
            <div class="mt-8 text-center">
                <p class="text-[10px] font-bold text-slate-400">Punya kendala pembayaran?</p>
                <a href="#" class="text-[10px] font-black text-[#00AA13] uppercase tracking-widest mt-1 block">Hubungi Tim Famly Hub</a>
            </div>
        </div>
    </div>
</x-app-layout>
