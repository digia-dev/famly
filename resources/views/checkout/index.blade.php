<x-app-layout>
    @section('title', 'Pembayaran Premium')

    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif !important; background-color: #F7F9FA; }
        .payment-card { background: white; border: 2px solid transparent; transition: all 0.2s; cursor: pointer; }
        .payment-card.selected { border-color: #00AA13; background: #F0FAF1; }
        .premium-text { background: linear-gradient(135deg, #00aa13 0%, #008910 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
    </style>

    <div class="min-h-screen pb-32 pt-8" x-data="{ selectedMethod: 'BCA Virtual Account' }">
        <div class="max-w-md mx-auto px-6">
            <!-- Header -->
            <div class="text-center mb-10">
                <h1 class="text-2xl font-black text-slate-900 tracking-tight">Checkout Premium</h1>
                <p class="text-[13px] font-bold text-slate-500 mt-2">Buka seluruh potensi finansial keluarga Anda.</p>
            </div>

            <!-- Plan Summary -->
            <div class="bg-white p-6 rounded-[32px] shadow-sm mb-8 relative overflow-hidden border border-slate-50">
                <div class="absolute top-0 right-0 p-4">
                    <span class="px-3 py-1 bg-emerald-50 text-[#00AA13] text-[9px] font-black uppercase tracking-widest rounded-full">Best Value</span>
                </div>
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-emerald-500 flex items-center justify-center text-white">
                        <span class="material-symbols-outlined text-[24px]">workspace_premium</span>
                    </div>
                    <div>
                        <h2 class="text-[14px] font-black text-slate-800">Famly Subscriber</h2>
                        <p class="text-[11px] font-bold text-slate-400">Langganan 1 Bulan</p>
                    </div>
                </div>
                <div class="mt-6 pt-6 border-t border-slate-50 flex items-center justify-between">
                    <p class="text-[12px] font-bold text-slate-500">Total Bayar</p>
                    <p class="text-[18px] font-black text-slate-900">Rp 19.900</p>
                </div>
            </div>

            <!-- Payment Methods -->
            <h3 class="text-[11px] font-black text-slate-400 uppercase tracking-widest mb-4 pl-2">Pilih Metode Pembayaran</h3>
            <div class="space-y-3">
                @foreach([
                    ['name' => 'BCA Virtual Account', 'icon' => 'account_balance', 'sub' => 'Transfer bank otomatis'],
                    ['name' => 'GoPay / QRIS', 'icon' => 'qr_code_scanner', 'sub' => 'Scan langsung aplikasi'],
                    ['name' => 'Mandiri VA', 'icon' => 'account_balance', 'sub' => 'Konfirmasi instan 24 jam']
                ] as $method)
                    <div @click="selectedMethod = '{{ $method['name'] }}'" 
                         :class="{ 'selected animate-pulse-subtle': selectedMethod === '{{ $method['name'] }}' }"
                         class="payment-card p-4 rounded-[24px] flex items-center justify-between group active:scale-[0.98]">
                        <div class="flex items-center gap-4">
                            <div class="w-10 h-10 rounded-xl bg-slate-50 flex items-center justify-center text-slate-400"
                                 :class="{ 'bg-emerald-50 text-[#00AA13]': selectedMethod === '{{ $method['name'] }}' }">
                                <span class="material-symbols-outlined text-[20px]">{{ $method['icon'] }}</span>
                            </div>
                            <div>
                                <p class="text-[13px] font-black text-slate-800">{{ $method['name'] }}</p>
                                <p class="text-[10px] font-bold text-slate-400">{{ $method['sub'] }}</p>
                            </div>
                        </div>
                        <span class="material-symbols-outlined text-[#00AA13] text-[20px]" x-show="selectedMethod === '{{ $method['name'] }}'">check_circle</span>
                    </div>
                @endforeach
            </div>

            <!-- Action -->
            <div class="mt-10">
                <form action="{{ route('checkout.processing') }}" method="GET">
                    <input type="hidden" name="method" :value="selectedMethod">
                    <button type="submit" class="w-full bg-[#00AA13] py-4 rounded-2xl text-white font-black text-[13px] uppercase tracking-widest shadow-xl shadow-emerald-500/20 active:scale-95 transition-all">
                        Bayar Sekarang
                    </button>
                </form>
                <p class="text-center text-[10px] font-bold text-slate-400 mt-6 px-4">
                    Dengan membayar, Anda menyetujui Ketentuan Layanan Famly Orchestration.
                </p>
            </div>
        </div>
    </div>
</x-app-layout>
