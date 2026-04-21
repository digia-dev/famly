<x-app-layout>
    @section('title', 'Catat Transaksi')

    @php
        $walletType = $activeWallet->wallet_type ?? 'wallet';
        $incomeId = $types->where('jenis', 'Pemasukan')->first()?->id;
        $expenseId = $types->where('jenis', 'Pengeluaran')->first()?->id;
        
        // Dynamic AI Tip (Daily Logic)
        $dailyTip = app(\App\Http\Controllers\DiscoveryController::class)->getDailyTip();
        
        // Fetch real Wallets for Balance Integration
        $user = Auth::user();
        $allWallets = \App\Models\KategoriNamaTabungan::where('family_id', $user->family_id)->get();
        $totalBalance = $allWallets->where('wallet_type', 'wallet')->sum('balance');

        $walletList = \App\Models\KategoriNamaTabungan::where('family_id', $user->family_id)->where('wallet_type', 'wallet')->get();
    @endphp

    <div class="bg-[#F8FAFC] min-h-screen font-jakarta" 
         x-data="{ 
            walletType: '{{ $walletType }}',
            jenisId: '{{ $walletType == 'savings' ? $incomeId : $expenseId }}',
            walletId: '{{ $selectedWalletId ?? '' }}',
            sourceWalletId: '',
            sourceWalletNama: 'Pilih Dompet...',
            showSourceMenu: false,
            linkedPosId: '',
            role: '{{ Auth::user()->role }}',
            nominalRaw: '',
            
            formatRupiah(val) {
                if(!val) return '0';
                return val.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
            },
            switchType(type) {
                this.walletType = type;
                this.walletId = '';
                if(type === 'savings') this.jenisId = '{{ $incomeId }}';
                else this.jenisId = '{{ $expenseId }}';
            },
            addAmount(amt) {
                let current = parseInt(this.nominalRaw.replace(/\./g, '')) || 0;
                this.nominalRaw = this.formatRupiah(current + amt);
            },
            selectSource(id, nama) {
                this.sourceWalletId = id;
                this.sourceWalletNama = nama;
                this.showSourceMenu = false;
            }
         }">
        
        <form method="POST" action="{{ route('transactions.store') }}">
            @csrf
        
        <!-- UI Tabs (Moved inside Main for cleaner look without header) -->
        <div class="flex bg-white px-4 border-b border-slate-100/50">
            @foreach(['wallet' => 'Dompet', 'pos' => 'Pos Budget', 'savings' => 'Tabungan'] as $key => $label)
            <button type="button" @click="switchType('{{ $key }}')"
                    :class="walletType == '{{ $key }}' ? 'text-primary border-b-2 border-primary' : 'text-slate-400'"
                    class="flex-1 py-3 text-[10px] font-black uppercase tracking-widest transition-all">
                {{ $label }}
            </button>
            @endforeach
        </div>

        <main class="max-w-screen-xl mx-auto px-4 pt-4 pb-32 space-y-4">
            
            <!-- AI Recommendation (AT THE TOP) -->
            <div class="bg-emerald-600 rounded-xl p-3 shadow-md relative overflow-hidden group cursor-pointer active:scale-95 transition-all" 
                 onclick="window.location.href='{{ $dailyTip['action_url'] ?? '#' }}'">
                <div class="absolute -right-4 -bottom-4 w-16 h-16 bg-white/10 rounded-full blur-xl animate-pulse"></div>
                <div class="flex items-start gap-3 relative z-10">
                    <div class="w-8 h-8 rounded-lg bg-white/20 flex items-center justify-center text-white shrink-0">
                        <span class="material-symbols-outlined text-[18px]">auto_fix_high</span>
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center justify-between mb-0.5">
                            <span class="text-[8px] font-black text-emerald-100 uppercase tracking-widest px-1.5 py-0.5 bg-black/10 rounded-md">Smart Pick • Hari Ini</span>
                            <span class="material-symbols-outlined text-white/40 text-[14px]">close</span>
                        </div>
                        <h4 class="text-[11px] font-bold text-white mb-0.5 leading-tight">{{ $dailyTip['title'] }}</h4>
                        <p class="text-[9px] text-emerald-100/70 font-medium leading-normal line-clamb-2">{{ $dailyTip['description'] }}</p>
                    </div>
                </div>
            </div>

            <!-- Global Search/Notification Header remains in Layout, removing internal one above -->

            <!-- Nominal Entry -->
            <div class="bg-white rounded-xl p-4 border border-slate-100/50 shadow-sm transition-all duration-300">
                <div class="flex items-center justify-between mb-3 border-b border-slate-50 pb-2">
                    <span class="text-[9px] font-black uppercase tracking-widest text-slate-400">Dapat/Keluar Berapa?</span>
                    <div class="flex gap-1">
                        @foreach([10000, 50000, 100000] as $amt)
                        <button type="button" @click="addAmount({{ $amt }})"
                                class="bg-slate-50 text-slate-500 px-2 py-0.5 rounded-md text-[8px] font-black border border-slate-100 active:scale-95">
                            +{{ number_format($amt/1000, 0) }}rb
                        </button>
                        @endforeach
                    </div>
                </div>
                
                <div class="flex items-baseline gap-2">
                    <span class="text-xl font-bold text-slate-300">Rp</span>
                    <input type="text" placeholder="0" required autofocus x-model="nominalRaw"
                           @input="nominalRaw = formatRupiah(nominalRaw.replace(/[^0-9]/g, ''))"
                           class="w-full bg-transparent border-none p-0 text-3xl font-black text-slate-800 placeholder:text-slate-100 focus:ring-0 outline-none tracking-tighter">
                    <input type="hidden" name="nominal" :value="nominalRaw.replace(/\./g, '')">
                </div>
                
                <!-- Type Toggle (Compact) -->
                <div class="flex gap-1.5 mt-4 p-1 bg-slate-50 rounded-lg">
                    @foreach($types as $type)
                        <button type="button" @click="jenisId = '{{ $type->id }}'"
                                x-show="walletType != 'savings' || '{{ $type->jenis }}' == 'Pemasukan' || role == 'dins'"
                                :class="jenisId == '{{ $type->id }}' ? '{{ $type->jenis == 'Pemasukan' ? 'bg-emerald-500 text-white' : 'bg-slate-800 text-white' }} shadow-md' : 'text-slate-400'"
                                class="flex-1 py-1.5 rounded-md font-bold text-[9px] uppercase transition-all flex items-center justify-center gap-1.5">
                            <span class="material-symbols-outlined text-[12px]">{{ $type->jenis == 'Pemasukan' ? 'trending_up' : 'trending_down' }}</span>
                            {{ $type->jenis }}
                        </button>
                    @endforeach
                    <input type="hidden" name="jenis_id" :value="jenisId">
                </div>
            </div>

            <!-- Account Selection -->
            <div class="bg-white rounded-xl p-3 border border-slate-100 shadow-sm">
                <h5 class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-3 px-1" x-text="walletType == 'wallet' ? 'Sumber Pembayaran' : (walletType == 'pos' ? 'Pos Anggaran' : 'Rencana Tabungan')"></h5>
                
                <div class="flex gap-2 overflow-x-auto no-scrollbar pb-1">
                    <template x-if="walletType == 'wallet'">
                        <div class="flex gap-2">
                            @foreach($wallets as $w)
                            <label class="cursor-pointer group shrink-0">
                                <input type="radio" name="wallet_id" value="{{ $w->id }}" x-model="walletId" class="peer sr-only">
                                <div class="w-32 p-3 rounded-xl bg-slate-50 border border-slate-100 peer-checked:bg-primary peer-checked:border-primary peer-checked:text-white transition-all active:scale-95">
                                    <div class="flex items-center justify-between mb-4">
                                        <span class="material-symbols-outlined text-[16px]">account_balance_wallet</span>
                                        <span class="material-symbols-outlined text-[14px] opacity-0 peer-checked:opacity-100">check_circle</span>
                                    </div>
                                    <h6 class="text-[10px] font-bold leading-none mb-1 truncate">{{ $w->nama }}</h6>
                                    <p class="text-[8px] font-medium opacity-60 leading-none">Rp{{ number_format($w->balance ?? 0, 0, ',', '.') }}</p>
                                </div>
                            </label>
                            @endforeach
                        </div>
                    </template>

                    <template x-if="walletType == 'pos'">
                        <div class="flex gap-2">
                            @foreach($posItems as $p)
                            <label class="cursor-pointer group shrink-0">
                                <input type="radio" name="wallet_id" value="{{ $p->id }}" x-model="walletId" class="peer sr-only">
                                <div class="w-28 p-3 rounded-xl bg-slate-50 border border-slate-100 peer-checked:bg-primary peer-checked:border-primary peer-checked:text-white transition-all active:scale-95">
                                    <div class="w-8 h-8 rounded-full bg-white/20 flex items-center justify-center mb-3">
                                        <span class="material-symbols-outlined text-[16px]">shopping_bag</span>
                                    </div>
                                    <h6 class="text-[10px] font-bold leading-none mb-1 truncate">{{ $p->nama }}</h6>
                                    <p class="text-[8px] font-medium opacity-60 leading-none">Limit: Rp{{ number_format($p->balance ?? 0, 0, ',', '.') }}</p>
                                </div>
                            </label>
                            @endforeach
                        </div>
                    </template>

                    <template x-if="walletType == 'savings'">
                        <div class="flex gap-2">
                            @foreach($savings as $s)
                            <label class="cursor-pointer group shrink-0">
                                <input type="radio" name="wallet_id" value="{{ $s->id }}" x-model="walletId" class="peer sr-only">
                                <div class="w-32 p-3 rounded-xl bg-slate-50 border border-slate-100 peer-checked:bg-amber-500 peer-checked:border-amber-500 peer-checked:text-white transition-all active:scale-95">
                                    <div class="flex items-center gap-2 mb-4">
                                        <span class="material-symbols-outlined text-[16px]">savings</span>
                                        <span class="text-[8px] font-black opacity-40 uppercase">Goal</span>
                                    </div>
                                    <h6 class="text-[10px] font-bold leading-none mb-1 truncate">{{ $s->nama }}</h6>
                                    <p class="text-[8px] font-medium opacity-60">Aktif: Rp{{ number_format($s->balance ?? 0, 0, ',', '.') }}</p>
                                </div>
                            </label>
                            @endforeach
                        </div>
                    </template>
                </div>
            </div>

            <!-- Custom Dropdown Row -->
            <div class="grid grid-cols-1 gap-2">
                <div class="bg-white rounded-xl px-4 py-2 border border-slate-100 shadow-sm flex items-center gap-3">
                    <span class="material-symbols-outlined text-slate-300 text-[18px]">edit_note</span>
                    <input name="keterangan" type="text" placeholder="Keterangan transaksi..." required
                           class="flex-1 bg-transparent border-none py-1.5 text-[11px] font-bold text-slate-700 placeholder:text-slate-300 focus:ring-0">
                </div>

                <div x-show="walletType == 'pos' && jenisId == '{{ $expenseId }}'" x-transition 
                     class="relative">
                    <div @click="showSourceMenu = !showSourceMenu" 
                         class="bg-amber-50/50 rounded-xl px-4 py-2.5 border border-amber-100 shadow-sm flex items-center justify-between cursor-pointer active:scale-[0.99] transition-all">
                        <div class="flex items-center gap-2 text-amber-600">
                            <span class="material-symbols-outlined text-[18px]">wallet</span>
                            <span class="text-[10px] font-black uppercase tracking-widest leading-none">Input Dari</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <span class="text-[11px] font-bold text-slate-900" x-text="sourceWalletNama"></span>
                            <span class="material-symbols-outlined text-[16px] text-slate-400 transition-transform" :class="showSourceMenu ? 'rotate-180' : ''">expand_more</span>
                        </div>
                    </div>

                    <div x-show="showSourceMenu" @click.away="showSourceMenu = false"
                         x-transition:enter="transition-all duration-200"
                         x-transition:enter-start="opacity-0 translate-y-2 scale-95"
                         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                         class="absolute left-0 right-0 mt-2 bg-white rounded-xl border border-slate-100 shadow-xl z-[150] overflow-hidden">
                        <div class="max-h-48 overflow-y-auto">
                            @foreach($walletList as $w)
                            <div @click="selectSource('{{ $w->id }}', '{{ $w->nama }}')"
                                 class="px-4 py-3 hover:bg-slate-50 flex items-center justify-between cursor-pointer border-b border-slate-50 last:border-0 group">
                                <div class="flex flex-col">
                                    <span class="text-[11px] font-bold text-slate-900">{{ $w->nama }}</span>
                                    <span class="text-[9px] font-medium text-slate-400">Saldo: Rp{{ number_format($w->balance ?? 0, 0, ',', '.') }}</span>
                                </div>
                                <span class="material-symbols-outlined text-primary text-[16px] opacity-0 group-hover:opacity-100" 
                                      x-show="sourceWalletId == '{{ $w->id }}'" style="display: none;">check_circle</span>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    <input type="hidden" name="source_wallet_id" x-model="sourceWalletId">
                </div>

                <div class="bg-white rounded-xl px-4 py-2 border border-slate-100 shadow-sm flex items-center justify-between">
                    <div class="flex items-center gap-2 text-slate-300">
                        <span class="material-symbols-outlined text-[18px]">calendar_today</span>
                        <span class="text-[10px] font-black uppercase">Waktu</span>
                    </div>
                    <input name="created_at" type="datetime-local" value="{{ now()->format('Y-m-d\TH:i') }}"
                           class="bg-transparent border-none p-0 text-[11px] font-bold text-slate-900 focus:ring-0 text-right">
                </div>
            </div>
        </main>

        <!-- Final Boxy Action -->
        <div class="fixed bottom-0 left-0 right-0 p-4 bg-white/80 backdrop-blur-md border-t border-slate-100 z-[120]">
            <button type="submit" class="w-full bg-slate-900 text-white py-3.5 rounded-xl shadow-lg active:scale-95 transition-all text-[11px] font-black tracking-[0.2em] flex items-center justify-center gap-3">
                SIMPAN TRANSAKSI
                <span class="material-symbols-outlined text-[16px]">verified</span>
            </button>
            <div class="h-2"></div>
        </div>
        </form>
    </div>
</x-app-layout>
