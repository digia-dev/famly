<div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-3 flex items-center justify-between gap-3 overflow-hidden" 
     x-data="{ 
        visible: true, 
        currentIdx: 0,
        items: [
            { label: 'Saldo Utama', raw: {{ $saldoSaatIni }}, icon: 'wallet', color: 'text-primary', progress: '+{{ number_format($overallGrowth, 1) }}% Bulan Ini', progColor: 'bg-emerald-50 text-emerald-600', progIcon: 'trending_up' },
            { label: 'Dompet Digital', raw: {{ $totalWallet ?? 0 }}, icon: 'payments', color: 'text-blue-600', progress: 'Saldo Aktif', progColor: 'bg-blue-50 text-blue-600', progIcon: 'verified' },
            { label: 'Pos Anggaran', raw: {{ $totalPos ?? 0 }}, icon: 'account_balance_wallet', color: 'text-emerald-600', progress: 'Usage: {{ number_format($posUsage, 0) }}%', progColor: 'bg-amber-50 text-amber-600', progIcon: 'shopping_bag' },
            { label: 'Tabungan Aktif', raw: {{ $totalSavings ?? 0 }}, icon: 'savings', color: 'text-amber-600', progress: 'Progress: {{ number_format($savingsProgress, 0) }}%', progColor: 'bg-emerald-50 text-emerald-600', progIcon: 'target' }
        ],
        formatRupiah(val) {
            return 'Rp ' + new Intl.NumberFormat('id-ID').format(val);
        },
        init() {
            setInterval(() => { this.currentIdx = (this.currentIdx + 1) % this.items.length; }, 4000);
        }
     }">
    <!-- Left: Balance Info (Ticker Style) -->
    <div class="flex-1 min-w-0 flex items-center gap-3">
        <!-- Interactive Vertical Ticker with Tight Gaps -->
        <div class="flex-1 relative h-11 overflow-hidden">
            <template x-for="(item, index) in items" :key="index">
                <div x-show="currentIdx === index" 
                     x-transition:enter="transition ease-out duration-500"
                     x-transition:enter-start="opacity-0 translate-y-3"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     x-transition:leave="transition ease-in duration-300 absolut"
                     x-transition:leave-start="opacity-100 translate-y-0"
                     x-transition:leave-end="opacity-0 -translate-y-3"
                     class="absolute inset-0 flex flex-col justify-center">
                    <div class="flex items-center gap-1.5 mb-0">
                        <span class="material-symbols-outlined text-[12px] font-bold" :class="item.color" x-text="item.icon" style="font-variation-settings: 'FILL' 1;"></span>
                        <span class="text-[11px] font-bold text-slate-800 tracking-tight leading-none" x-text="item.label"></span>
                    </div>
                    <div class="flex items-center gap-2">
                        <h2 class="text-[17px] font-extrabold text-slate-900 tracking-tighter truncate leading-none py-0.5">
                            <template x-if="visible">
                                <span x-text="formatRupiah(item.raw)"></span>
                            </template>
                            <template x-if="!visible">
                                <span>••••••••</span>
                            </template>
                        </h2>
                    </div>
                    <!-- Ultra-Compact Progress Pill -->
                    <div class="flex items-center gap-1 px-1.5 py-0.5 rounded-md border border-opacity-20 w-fit mt-0.5" :class="item.progColor">
                        <span class="material-symbols-outlined text-[8px] font-bold" x-text="item.progIcon"></span>
                        <span class="text-[8px] font-bold tracking-tight leading-none uppercase" x-text="item.progress"></span>
                    </div>
                </div>
            </template>
        </div>

        <!-- Privacy Toggle (Small & Neat) -->
        <button @click="visible = !visible" class="w-7 h-7 rounded-lg bg-slate-50 flex items-center justify-center text-slate-300 active:scale-90 transition-all">
            <span class="material-symbols-outlined text-[18px]" x-text="visible ? 'visibility' : 'visibility_off'"></span>
        </button>
    </div>

    <!-- Right: Quick Actions (Vertical Tight Stack) -->
    <div class="flex items-center gap-1.5">
        <button class="flex flex-col items-center gap-0.5 group" onclick="window.location.href='{{ route('transaction.create') }}'">
            <div class="w-10 h-10 bg-primary text-white rounded-xl flex items-center justify-center shadow-md shadow-primary/10 active:scale-95 transition-all">
                <span class="material-symbols-outlined text-lg" style="font-variation-settings: 'FILL' 1;">add</span>
            </div>
            <span class="text-[10px] font-bold text-slate-500 tracking-tight">Catat</span>
        </button>
        <button class="flex flex-col items-center gap-0.5 group" onclick="window.location.href='/transaction'">
            <div class="w-10 h-10 bg-slate-50 text-slate-600 rounded-xl flex items-center justify-center border border-slate-100 hover:bg-slate-100 active:scale-95 transition-all">
                <span class="material-symbols-outlined text-lg">history</span>
            </div>
            <span class="text-[10px] font-bold text-slate-500 tracking-tight">Riwayat</span>
        </button>
        <button class="flex flex-col items-center gap-0.5 group" onclick="window.location.href='/manajemen'">
            <div class="w-10 h-10 bg-slate-50 text-slate-600 rounded-xl flex items-center justify-center border border-slate-100 hover:bg-slate-100 active:scale-95 transition-all">
                <span class="material-symbols-outlined text-lg">more_horiz</span>
            </div>
            <span class="text-[10px] font-bold text-slate-500 tracking-tight">Lainnya</span>
        </button>
    </div>
</div>

<!-- Marquee Banner (Ultra-Thin) -->
<div class="mt-1.5 bg-white rounded-2xl p-2 flex items-center gap-2 border border-slate-100/50 shadow-sm overflow-hidden group">
    <div class="w-6 h-6 rounded-lg bg-emerald-50 flex items-center justify-center text-emerald-600 flex-shrink-0">
        <span class="material-symbols-outlined text-[14px]" style="font-variation-settings: 'FILL' 1;">auto_awesome</span>
    </div>
    <div class="flex-1 min-w-0">
        <div class="marquee-container w-full whitespace-nowrap overflow-hidden relative">
            <span class="marquee-content inline-block text-[10px] font-bold text-slate-600 leading-tight">
                {{ $greeting['pesan'] ?? 'Tetap konsisten menabung untuk masa depan!' }} &nbsp;&bull;&nbsp; 
                {{ $greeting['pesan'] ?? 'Tetap konsisten menabung untuk masa depan!' }} &nbsp;&bull;&nbsp;
            </span>
        </div>
    </div>
    <span class="material-symbols-outlined text-slate-200 text-xs">chevron_right</span>
</div>

<style>
    .marquee-content {
        animation: marquee 35s linear infinite;
        min-width: 100%;
    }
    @keyframes marquee {
        0% { transform: translateX(0); }
        100% { transform: translateX(-50%); }
    }
</style>
