<x-app-layout>
    @section('title', 'Selamat! Anda Premium')

    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js"></script>
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif !important; background-color: #FFFFFF; }
        .success-accent { background: linear-gradient(135deg, #00aa13 0%, #008910 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
    </style>

    <div class="min-h-screen flex flex-col items-center justify-center text-center px-8 pb-32" 
         x-data="{}" x-init="
            confetti({
                particleCount: 150,
                spread: 70,
                origin: { y: 0.6 },
                colors: ['#00AA13', '#008910', '#F7F9FA']
            });
         ">
        
        <div class="w-24 h-24 bg-emerald-50 rounded-[40px] flex items-center justify-center text-[#00AA13] mb-10 shadow-2xl shadow-emerald-500/10">
            <span class="material-symbols-outlined text-[48px]">workspace_premium</span>
        </div>

        <h1 class="text-3xl font-black text-slate-800 tracking-tighter mb-4">You're Now Premium!</h1>
        <p class="text-[14px] font-bold text-slate-400 leading-relaxed max-w-[280px] mb-12">
            Selamat datang di level eksklusif **Famly Orchestration**. Seluruh fitur premium telah aktif di akun Anda.
        </p>

        <div class="bg-slate-50 p-6 rounded-[32px] w-full max-w-xs mb-10 border border-slate-100/50">
            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-4">Fitur Terbuka</p>
            <ul class="space-y-4 text-left">
                <li class="flex items-center gap-3">
                    <span class="material-symbols-outlined text-[#00AA13] text-[18px]">history</span>
                    <span class="text-[12px] font-extrabold text-slate-700">Akses Seluruh Riwayat</span>
                </li>
                <li class="flex items-center gap-3">
                    <span class="material-symbols-outlined text-[#00AA13] text-[18px]">smart_toy</span>
                    <span class="text-[12px] font-extrabold text-slate-700">Unlimited AI Assistance</span>
                </li>
                <li class="flex items-center gap-3">
                    <span class="material-symbols-outlined text-[#00AA13] text-[18px]">download</span>
                    <span class="text-[12px] font-extrabold text-slate-700">Laporan Eksklusif PDF</span>
                </li>
            </ul>
        </div>

        <div class="space-y-4 w-full max-w-xs">
            <a href="{{ route('subscription.index') }}" class="block w-full bg-[#00AA13] py-4 rounded-2xl text-white font-black text-[13px] uppercase tracking-widest shadow-xl shadow-emerald-500/20 active:scale-95 transition-all">
                Masuk ke Hub Premium
            </a>
            <a href="{{ route('admin.dashboard') }}" class="block text-[11px] font-black text-slate-400 uppercase tracking-widest">Kembali ke Beranda</a>
        </div>
    </div>
</x-app-layout>
