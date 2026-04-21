<section 
    class="bg-white p-3 rounded-2xl shadow-sm border border-slate-100"
    x-data="{ 
        wallets: [],
        currentIndex: 0,
        timer: null,
        init() {
            this.wallets = {{ \App\Models\KategoriNamaTabungan::all()->map(function($wallet) {
                $in = \App\Models\Tabungan::where('nama', $wallet->id)->whereHas('kategoriJenis', fn($q) => $q->where('jenis', 'Pemasukan'))->sum('nominal');
                $out = \App\Models\Tabungan::where('nama', $wallet->id)->whereHas('kategoriJenis', fn($q) => $q->where('jenis', 'Pengeluaran'))->sum('nominal');
                return [
                    'id' => $wallet->id,
                    'name' => $wallet->nama,
                    'balance' => 'Rp ' . number_format($in - $out, 0, ',', '.'),
                    'icon' => $wallet->icon ?? 'account_balance_wallet',
                ];
            })->toJson() }};
            this.startTimer();
        },
        startTimer() {
            if (this.timer) clearInterval(this.timer);
            this.timer = setInterval(() => { this.next(); }, 5000);
        },
        next() { 
            this.currentIndex = (this.currentIndex + 1) % this.wallets.length; 
            this.startTimer(); // Reset timer on manual click
        },
        prev() { 
            this.currentIndex = (this.currentIndex - 1 + this.wallets.length) % this.wallets.length; 
            this.startTimer(); // Reset timer on manual click
        }
    }"
>
    <!-- Card Style Wallet (Gojek Version) -->
    <div class="flex items-center justify-between">
        <div class="flex-1 overflow-hidden">
            <template x-for="(wallet, index) in wallets" :key="wallet.id">
                <div 
                    x-show="currentIndex === index"
                    x-transition:enter="transition ease-out duration-300 transform"
                    x-transition:enter-start="opacity-0 translate-x-4"
                    x-transition:enter-end="opacity-100 translate-x-0"
                    class="flex items-center gap-3"
                >
                    <div class="w-11 h-11 bg-slate-50 border border-slate-200 rounded-xl flex items-center justify-center text-primary shadow-sm">
                        <span class="material-symbols-outlined text-lg" x-text="wallet.icon"></span>
                    </div>
                    <div class="min-w-0">
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest leading-none mb-1" x-text="wallet.name"></p>
                        <p class="text-[15px] font-bold text-slate-900 tracking-tight" x-text="wallet.balance"></p>
                    </div>
                </div>
            </template>
        </div>

        <!-- Mini Navigation -->
        <div class="flex items-center gap-1 ml-2">
            <button @click="prev()" class="w-8 h-8 flex items-center justify-center bg-slate-50 border border-slate-200 rounded-lg hover:bg-slate-100 text-slate-400 transition-colors">
                <span class="material-symbols-outlined text-sm">chevron_left</span>
            </button>
            <button @click="next()" class="w-8 h-8 flex items-center justify-center bg-slate-50 border border-slate-200 rounded-lg hover:bg-slate-100 text-slate-400 transition-colors">
                <span class="material-symbols-outlined text-sm">chevron_right</span>
            </button>
        </div>
    </div>

    <!-- Dots Mini -->
    <div class="flex justify-center gap-1 mt-3">
        <template x-for="(wallet, index) in wallets" :key="'dot-'+wallet.id">
            <div 
                class="h-1 rounded-full transition-all duration-300"
                :class="currentIndex === index ? 'w-4 bg-primary' : 'w-1 bg-slate-200'"
            ></div>
        </template>
    </div>
</section>
