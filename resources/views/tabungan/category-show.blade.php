<x-app-layout>
    <div class="architectural-grid min-h-screen bg-surface selection:bg-primary-container selection:text-on-primary-container">
        <!-- Main Content -->
        <main class="pt-6 sm:pt-20 pb-12 px-4 max-w-3xl mx-auto">
            
            <!-- Breadcrumb / Header (Mobile style but adaptive) -->
            <div class="flex items-center justify-between mb-6 sm:hidden">
                <div class="flex items-center gap-3">
                    <a href="javascript:history.back()" class="transition-opacity duration-150 active:opacity-70 text-primary flex items-center">
                        <span class="material-symbols-outlined">chevron_left</span>
                    </a>
                    <h1 class="font-bold tracking-widest text-sm text-on-surface uppercase">{{ $category->nama }}</h1>
                </div>
                <div class="flex items-center gap-4">
                    <span class="material-symbols-outlined text-primary">more_vert</span>
                </div>
            </div>

            <!-- Balance Hero Card -->
            <section class="bg-surface-container-lowest p-6 mb-4 border-l-4 border-primary shadow-sm relative overflow-hidden rounded-sm">
                <div class="absolute top-0 right-0 p-4 opacity-10">
                    <span class="material-symbols-outlined text-8xl">account_balance_wallet</span>
                </div>
                <p class="text-[10px] font-bold text-on-surface-variant/60 uppercase tracking-widest mb-1">Total Saldo Tersedia</p>
                <h2 class="text-3xl font-bold tracking-tighter text-on-surface mb-4">
                    {{ \App\Helpers\DashboardGreetingHelper::formatRupiah($saldoSaatIni) }}
                </h2>
                <div class="flex gap-2">
                    <div class="bg-primary/5 px-2 py-1 rounded-sm flex items-center gap-1 border border-primary/10">
                        <span class="material-symbols-outlined text-primary text-xs">trending_up</span>
                        <span class="text-[10px] font-bold text-primary">{{ $percentageChange >= 0 ? '+' : '' }}{{ number_format($percentageChange, 1) }}% bln ini</span>
                    </div>
                    <div class="bg-tertiary-container/30 px-2 py-1 rounded-sm flex items-center gap-1 border border-tertiary/10">
                        <span class="material-symbols-outlined text-tertiary text-xs" style="font-variation-settings: 'FILL' 1;">star</span>
                        <span class="text-[10px] font-bold text-on-tertiary-container">Target Terpantau</span>
                    </div>
                </div>
            </section>

            <!-- Bento Grid Layout for Insights -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-8">
                <!-- Growth Chart Module -->
                <div class="bg-surface-container p-4 flex flex-col justify-between rounded-sm">
                    <div>
                        <h3 class="text-xs font-bold text-on-surface-variant uppercase tracking-widest mb-4">Pertumbuhan Aset</h3>
                        <div class="h-32 flex items-end gap-1 px-1">
                            @php
                                $months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun'];
                                $maxGrowth = $growthData->max() ?: 1;
                            @endphp
                            @foreach($months as $month)
                                @php
                                    $val = $growthData->get($month, 0);
                                    $heightPercent = max(5, ($val / $maxGrowth) * 100);
                                @endphp
                                <div class="flex-1 bg-primary/{{ 20 + ($loop->index * 10) }} rounded-t-sm transition-all duration-500" style="height: {{ $heightPercent }}%;"></div>
                            @endforeach
                        </div>
                    </div>
                    <div class="flex justify-between mt-2 text-[9px] font-bold text-on-surface-variant/60 uppercase">
                        @foreach($months as $month)
                            <span>{{ $month }}</span>
                        @endforeach
                    </div>
                </div>

                <!-- Goal Details Module (Dummy data for now as per design) -->
                <div class="bg-surface-container-highest p-4 rounded-sm">
                    <h3 class="text-xs font-bold text-on-surface-variant uppercase tracking-widest mb-4">Rincian Target</h3>
                    <div class="space-y-4">
                        <div>
                            <div class="flex justify-between text-[11px] font-bold mb-1">
                                <span>{{ $category->nama }} Plan</span>
                                <span class="text-primary">90%</span>
                            </div>
                            <div class="w-full bg-surface-container-low h-1 rounded-full overflow-hidden">
                                <div class="bg-gradient-to-r from-primary to-primary-container h-full w-[90%]"></div>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-2 mt-4">
                            <div class="bg-surface-container-lowest p-2 rounded-sm border border-surface-variant/20">
                                <p class="text-[9px] text-on-surface-variant/60 uppercase font-bold">Kekurangan</p>
                                <p class="text-xs font-bold">Rp --</p>
                            </div>
                            <div class="bg-surface-container-lowest p-2 rounded-sm border border-surface-variant/20">
                                <p class="text-[9px] text-on-surface-variant/60 uppercase font-bold">Estimasi Selesai</p>
                                <p class="text-xs font-bold">Soon</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Transaction History -->
            <section>
                <div class="flex items-center justify-between mb-4 px-1">
                    <h3 class="text-xs font-bold text-on-surface uppercase tracking-widest">Riwayat Transaksi</h3>
                    <button class="text-[10px] font-bold text-primary uppercase border border-primary/20 px-2 py-1 hover:bg-primary/5 transition-colors">Filter</button>
                </div>
                <div class="space-y-1">
                    @forelse($transactions as $transaction)
                        <div class="bg-white p-4 flex items-center justify-between group hover:bg-surface-low transition-colors rounded-sm border border-surface-variant/10">
                            <div class="flex items-center gap-4">
                                <div class="w-10 h-10 bg-surface-container flex items-center justify-center rounded-sm">
                                    <span class="material-symbols-outlined text-primary">
                                        {{ $transaction->kategoriJenis->jenis == 'Pemasukan' ? 'account_balance' : 'shopping_bag' }}
                                    </span>
                                </div>
                                <div>
                                    <p class="text-xs font-bold text-on-surface">{{ $transaction->keterangan ?: $transaction->kategoriNama->nama }}</p>
                                    <p class="text-[10px] text-on-surface-variant/60">{{ $transaction->created_at->format('d M Y • H:i') }}</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-xs font-bold {{ $transaction->kategoriJenis->jenis == 'Pemasukan' ? 'text-primary' : 'text-error' }}">
                                    {{ $transaction->kategoriJenis->jenis == 'Pemasukan' ? '+' : '-' }} {{ \App\Helpers\DashboardGreetingHelper::formatRupiah($transaction->nominal) }}
                                </p>
                                <p class="text-[9px] text-on-surface-variant/60 uppercase font-medium">Internal</p>
                            </div>
                        </div>
                    @empty
                        <p class="text-center py-8 text-xs text-on-surface-variant/40 uppercase tracking-widest">Belum ada transaksi</p>
                    @endforelse
                </div>
            </section>

            <!-- CTA Actions -->
            <div class="mt-8 grid grid-cols-2 gap-2">
                <button class="bg-surface-container-highest text-on-surface text-[11px] font-bold uppercase tracking-widest py-4 border border-surface-variant/30 active:bg-surface-dim transition-colors rounded-sm">
                    Tarik Dana
                </button>
                <button class="bg-gradient-to-br from-primary to-primary-container text-white text-[11px] font-bold uppercase tracking-widest py-4 shadow-sm active:opacity-90 transition-opacity rounded-sm">
                    Tambah Saldo
                </button>
            </div>

            <!-- Footer -->
            <div class="mt-12 text-center pb-8 opacity-40">
                <p class="text-[9px] text-on-surface-variant uppercase tracking-widest font-bold mb-2">Dicadangkan Oleh Keamanan Famly</p>
                <div class="flex justify-center gap-2 grayscale">
                    <div class="w-8 h-4 bg-on-surface"></div>
                    <div class="w-8 h-4 bg-primary"></div>
                    <div class="w-8 h-4 bg-tertiary"></div>
                </div>
            </div>
        </main>
    </div>

    <style>
        .architectural-grid {
            background-size: 20px 20px;
            background-image: radial-gradient(circle, #E4E2E4 1px, transparent 1px);
        }
    </style>
</x-app-layout>
