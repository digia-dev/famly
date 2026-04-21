<x-app-layout>
    @section('title', 'Dompet')

    <div class="bg-surface min-h-screen text-on-surface pb-32" x-data="{ activeTab: 'pos' }">
        <!-- Top Sticky Header -->
        <header class="bg-surface/80 backdrop-blur-md sticky top-0 z-50 border-b border-surface-container-high">
            <div class="max-w-xl mx-auto px-6 py-4 flex justify-between items-center">
                <div class="flex items-center gap-3">
                    <span class="material-symbols-outlined text-primary" style="font-variation-settings: 'FILL' 1;">account_balance_wallet</span>
                    <h1 class="text-primary font-black text-xl tracking-tight uppercase">Dompet</h1>
                </div>
                <a href="{{ route('management.create') }}" class="text-primary hover:bg-primary/5 p-2 rounded-full transition-colors active:scale-95">
                    <span class="material-symbols-outlined font-bold">add_circle</span>
                </a>
            </div>

            <!-- Tab Navigation -->
            <div class="max-w-xl mx-auto px-6 flex gap-8">
                <button 
                    @click="activeTab = 'pos'"
                    :class="activeTab === 'pos' ? 'text-primary border-primary border-b-2 font-black' : 'text-on-surface-variant/40 font-bold'"
                    class="pb-3 text-xs uppercase tracking-widest transition-all">
                    Pos Keuangan
                </button>
                <button 
                    @click="activeTab = 'savings'"
                    :class="activeTab === 'savings' ? 'text-primary border-primary border-b-2 font-black' : 'text-on-surface-variant/40 font-bold'"
                    class="pb-3 text-xs uppercase tracking-widest transition-all">
                    Tabungan
                </button>
            </div>
        </header>

        <main class="max-w-xl mx-auto px-6 mt-6">
            <!-- Hero Section: Total Aset Keluarga -->
            <section class="mb-8">
                <div class="relative overflow-hidden rounded-sm p-6 bg-gradient-to-br from-primary to-primary-container text-white shadow-[0px_12px_32px_rgba(0,109,54,0.15)]">
                    <div class="relative z-10">
                        <p class="text-white/80 font-bold text-[10px] tracking-widest mb-1 uppercase">Total Aset Keluarga</p>
                        <h2 class="text-3xl font-black tracking-tighter leading-none mb-4">
                            {{ \App\Helpers\DashboardGreetingHelper::formatRupiah($totalNetWorth) }}
                        </h2>
                        <div class="flex items-center gap-2 text-white/90">
                            <span class="material-symbols-outlined text-xs">trending_up</span>
                            <span class="text-[10px] font-black uppercase tracking-tight">{{ $percentageChange >= 0 ? '+' : '' }}{{ $percentageChange }}% Bulan ini</span>
                        </div>
                    </div>
                    <!-- Abstract Texture Overlay -->
                    <div class="absolute top-0 right-0 w-1/2 h-full opacity-10 pointer-events-none" style="background-image: url('https://www.transparenttextures.com/patterns/cubes.png');"></div>
                </div>
            </section>

            <!-- Tab Content: Pos Keuangan -->
            <div x-show="activeTab === 'pos'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0">
                <section class="mb-10">
                    <div class="flex justify-between items-end mb-4 px-1">
                        <h3 class="text-on-surface-variant font-black text-[10px] uppercase tracking-widest">Daftar Pos Keuangan</h3>
                        <a href="{{ route('management.index') }}" class="text-primary font-black text-[9px] hover:underline uppercase tracking-wider">Lihat Semua</a>
                    </div>
                    
                    <div class="space-y-3">
                        @forelse($posKeuangan as $wallet)
                            <div class="flex items-center justify-between p-4 bg-white rounded-sm border-l-[3px] shadow-sm hover:shadow-md transition-all group cursor-pointer" 
                                 onclick="window.location='{{ route('tabungan.category.show', $wallet->id) }}'"
                                 style="border-color: {{ $wallet->color ?? '#006d36' }}">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 flex items-center justify-center rounded-sm text-primary" style="background-color: {{ ($wallet->color ?? '#006d36') . '15' }}">
                                        <span class="material-symbols-outlined text-xl">{{ $wallet->icon ?? 'account_balance' }}</span>
                                    </div>
                                    <div>
                                        <p class="text-on-surface font-black text-sm tracking-tight capitalize">{{ $wallet->nama }}</p>
                                        <p class="text-on-surface-variant/40 text-[9px] font-bold uppercase tracking-widest">{{ $wallet->kategori_kas ?? 'Manual' }}</p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="text-on-surface font-black text-sm tracking-tighter">{{ \App\Helpers\DashboardGreetingHelper::formatRupiah($wallet->balance) }}</p>
                                    <p class="text-[8px] font-black tracking-widest uppercase" style="color: {{ $wallet->color ?? '#006d36' }}">{{ $wallet->status ?? 'AKTIF' }}</p>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-10 bg-surface-low rounded-sm border border-dashed border-surface-variant/20">
                                <p class="text-on-surface-variant/40 text-[10px] font-bold uppercase tracking-widest">Belum ada pos keuangan</p>
                            </div>
                        @endforelse
                    </div>
                </section>
            </div>

            <!-- Tab Content: Tabungan -->
            <div x-show="activeTab === 'savings'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0" style="display: none;">
                <section class="mb-10">
                    <div class="flex justify-between items-end mb-4 px-1">
                        <h3 class="text-on-surface-variant font-black text-[10px] uppercase tracking-widest">Goals & Tabungan</h3>
                    </div>
                    
                    <div class="space-y-3">
                        @forelse($tabunganAset as $wallet)
                            <div class="p-5 bg-white rounded-sm shadow-sm hover:shadow-md transition-all group cursor-pointer border-b-2" 
                                 onclick="window.location='{{ route('tabungan.category.show', $wallet->id) }}'"
                                 style="border-color: {{ $wallet->color ?? '#d3ae36' }}20">
                                <div class="flex justify-between items-start mb-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 flex items-center justify-center rounded-sm text-tertiary" style="background-color: {{ ($wallet->color ?? '#d3ae36') . '15' }}">
                                            <span class="material-symbols-outlined text-xl">{{ $wallet->icon ?? 'savings' }}</span>
                                        </div>
                                        <div>
                                            <p class="text-on-surface font-black text-sm tracking-tight capitalize">{{ $wallet->nama }}</p>
                                            <p class="text-on-surface-variant/40 text-[9px] font-bold uppercase tracking-widest">Target: {{ \App\Helpers\DashboardGreetingHelper::formatRupiah($wallet->target_saldo ?? 0) }}</p>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-on-surface font-black text-sm tracking-tighter">{{ \App\Helpers\DashboardGreetingHelper::formatRupiah($wallet->balance) }}</p>
                                        <p class="text-tertiary text-[8px] font-black tracking-widest uppercase">{{ $wallet->status ?? 'TERKUNCI' }}</p>
                                    </div>
                                </div>
                                
                                @if($wallet->target_saldo > 0)
                                    @php
                                        $progress = min(100, ($wallet->balance / $wallet->target_saldo) * 100);
                                    @endphp
                                    <div class="w-full bg-surface-low h-1 rounded-full overflow-hidden">
                                        <div class="h-full transition-all duration-1000" 
                                             style="width: {{ $progress }}%; background-color: {{ $wallet->color ?? '#d3ae36' }}"></div>
                                    </div>
                                    <div class="mt-2 flex justify-between items-center">
                                        <p class="text-[8px] font-black text-on-surface-variant/40 uppercase tracking-widest">Progress</p>
                                        <p class="text-[8px] font-black text-on-surface-variant uppercase tracking-widest">{{ round($progress, 1) }}%</p>
                                    </div>
                                @endif
                            </div>
                        @empty
                            <div class="text-center py-10 bg-surface-low rounded-sm border border-dashed border-surface-variant/20">
                                <p class="text-on-surface-variant/40 text-[10px] font-bold uppercase tracking-widest">Belum ada target tabungan</p>
                            </div>
                        @endforelse
                    </div>
                </section>
            </div>

            <!-- CTA Section -->
            <section class="flex flex-col gap-4 mt-10">
                <a href="{{ route('management.create') }}" class="w-full py-4 bg-primary text-white rounded-sm flex items-center justify-center gap-3 shadow-lg shadow-primary/20 hover:brightness-110 transition-all active:scale-[0.98] group">
                    <span class="material-symbols-outlined text-white/80 text-xl font-bold">add</span>
                    <span class="font-black text-xs uppercase tracking-widest">Tambah Pos Keuangan</span>
                </a>
                <p class="text-center text-on-surface-variant/40 text-[9px] font-bold uppercase tracking-widest">Kelola semua aset keluarga Anda dalam satu dasbor terintegrasi.</p>
            </section>

            <!-- Insight Bento Grid -->
            <section class="mt-12 grid grid-cols-2 gap-4 pb-10">
                <div class="bg-surface-low p-5 rounded-sm flex flex-col justify-between aspect-square border border-surface-variant/5">
                    <span class="material-symbols-outlined text-tertiary text-3xl" style="font-variation-settings: 'FILL' 1;">auto_awesome</span>
                    <div>
                        <h4 class="font-black text-[10px] text-on-surface uppercase tracking-widest mb-1">Saran AI</h4>
                        <p class="text-[10px] text-on-surface-variant/60 font-medium leading-relaxed">Alokasikan 15% dana darurat ke instrumen rendah risiko untuk hasil optimal.</p>
                    </div>
                </div>
                <div class="bg-white p-5 rounded-sm flex flex-col justify-between aspect-square shadow-sm border border-surface-variant/10">
                    <span class="material-symbols-outlined text-primary text-3xl" style="font-variation-settings: 'FILL' 1;">shield_with_heart</span>
                    <div>
                        <h4 class="font-black text-[10px] text-on-surface uppercase tracking-widest mb-1">Health Score</h4>
                        <p class="text-[10px] text-on-surface-variant/60 font-medium leading-relaxed">Skor keluarga Anda adalah 82/100. Kondisi sangat sehat dan terkendali.</p>
                    </div>
                </div>
            </section>
        </main>
    </div>
</x-app-layout>
