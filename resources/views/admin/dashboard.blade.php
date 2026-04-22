<x-app-layout>
    @section('title', 'Beranda')
    
    <!-- Sticky Search & Profile Bar (Solid Style) -->
    <div x-data="{ scrolled: false }" 
         @scroll.window="scrolled = (window.pageYOffset > 10)"
         class="sticky top-0 z-[100] transition-all duration-300 px-4 py-2"
         :class="scrolled ? 'bg-white shadow-md border-b border-slate-100' : 'bg-transparent'">
        
        <div class="relative z-10 max-w-screen-xl mx-auto flex items-center justify-between gap-2" :class="scrolled ? 'mt-0' : 'mt-4'">
            <div class="flex-1 relative group" onclick="openSearchOverlay()">
                <span class="material-symbols-outlined absolute left-2.5 top-1/2 -translate-y-1/2 transition-colors text-[18px]" 
                      :class="scrolled ? 'text-slate-400' : 'text-primary/60'">search</span>
                <input type="text" placeholder="Cari layanan atau bantuan..." 
                       class="w-full border-none rounded-full py-2 pl-9 pr-4 text-[12px] font-medium placeholder:text-slate-400 focus:ring-0 shadow-sm cursor-pointer font-jakarta bg-white transition-all text-slate-800" readonly>
            </div>
            <div class="flex items-center gap-1.5">
                <a href="{{ route('notification.index') }}" 
                   class="w-8 h-8 rounded-full flex items-center justify-center transition-all active:scale-90 shadow-sm border border-slate-100"
                   :class="scrolled ? 'bg-slate-50 text-slate-600' : 'bg-white text-slate-400'">
                    <span class="material-symbols-outlined text-[18px]">notifications</span>
                </a>
                <a href="{{ route('profile.edit') }}" class="w-8 h-8 rounded-full bg-white p-0.5 shadow-md active:scale-95 transition-transform border border-slate-100">
                    <div class="w-full h-full rounded-full bg-emerald-500 flex items-center justify-center text-white font-bold text-[9px] uppercase tracking-tighter">
                        {{ substr(auth()->user()->name ?? 'AD', 0, 2) }}
                    </div>
                </a>
            </div>
        </div>
    </div>

    <!-- Hero Promo Section (Scrolls Away) - Higher for more visibility -->
    <div class="relative h-64 -mt-20 overflow-hidden">
        <img src="{{ asset('images/promo.jpg') }}" 
             class="w-full h-full object-cover bg-primary" 
             alt="Header Background">
        <div class="absolute inset-0 bg-primary/20"></div>
    </div>

    <!-- Main Content (High Density Style) - Pushed further down -->
    <div class="max-w-screen-xl mx-auto px-4 -mt-8 relative z-20 space-y-2 pb-24">
        @if(request()->get('success_ai'))
            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)"
                 class="mb-2 p-3 bg-emerald-600 rounded-2xl text-white shadow-xl shadow-emerald-500/20 flex items-center justify-between border border-emerald-400">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-white/20 flex items-center justify-center">
                        <span class="material-symbols-outlined text-[18px]">check_circle</span>
                    </div>
                    <div>
                        <p class="text-[11px] font-black tracking-tight uppercase">AI Scan Berhasil!</p>
                        <p class="text-[9px] font-bold opacity-80 uppercase leading-tight">Transaksi telah dicatat dan dianalisis.</p>
                    </div>
                </div>
            </div>
        @endif
        
        @if(session('quick_insight'))
            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 8000)"
                 class="mb-2 p-3 bg-white/95 backdrop-blur-xl rounded-[28px] shadow-xl shadow-slate-200/50 flex items-center justify-between border border-emerald-50">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-emerald-500 flex items-center justify-center text-white shadow-lg shadow-emerald-500/20">
                        <span class="material-symbols-outlined text-[20px]" style="font-variation-settings: 'FILL' 1;">auto_awesome</span>
                    </div>
                    <div>
                        <p class="text-[10px] font-black text-emerald-600 uppercase tracking-widest leading-none mb-1">Quick Insight @Fams</p>
                        <p class="text-[12px] font-extrabold text-slate-800 tracking-tight leading-tight">{{ session('quick_insight') }}</p>
                    </div>
                </div>
                <button @click="show = false" class="text-slate-300 hover:text-slate-500 p-1">
                    <span class="material-symbols-outlined text-sm">close</span>
                </button>
            </div>
        @endif

        <!-- Balance Card -->
        <div class="relative">
            @include('admin.partials.gojek-header')
        </div>


        <!-- Interactive Hub (Daily Rituals & Mini Agenda) -->
        @include('admin.partials.interactive-hub')

        <!-- Service Shortcut Hub (Horizontal Scroll & High Density) -->
        <div class="bg-white rounded-2xl py-3 px-1 shadow-sm border border-slate-100/30 overflow-hidden">
            <div class="flex items-center gap-1 overflow-x-auto px-3 scrollbar-hide no-scrollbar">
                @php
                    $links = [
                        ['icon' => 'add_card', 'label' => 'Catat', 'url' => route('transaction.create'), 'color' => 'emerald'],
                        ['icon' => 'account_balance_wallet', 'label' => 'Dompet', 'url' => route('management.index'), 'color' => 'amber'],
                        ['icon' => 'calendar_month', 'label' => 'Agenda', 'url' => route('agenda.index'), 'color' => 'rose'],
                        ['icon' => 'notifications', 'label' => 'Pesan', 'url' => route('notification.index'), 'color' => 'indigo'],
                    ];
                @endphp
                @foreach($links as $link)
                    <a href="{{ $link['url'] }}" class="flex flex-col items-center gap-1 group active:scale-[0.9] transition-all flex-shrink-0 w-16">
                        <div class="w-10 h-10 bg-{{ $link['color'] }}-50/50 text-{{ $link['color'] }}-600 rounded-xl flex items-center justify-center group-hover:bg-{{ $link['color'] }}-100 transition-all shadow-sm">
                            <span class="material-symbols-outlined text-[18px]" style="font-variation-settings: 'FILL' 1;">{{ $link['icon'] }}</span>
                        </div>
                        <span class="text-[9px] font-bold text-slate-500 tracking-tighter text-center leading-none">{{ $link['label'] }}</span>
                    </a>
                @endforeach
                
                <!-- Add Shortcut Button -->
                <button class="flex flex-col items-center gap-1 group active:scale-[0.9] transition-all flex-shrink-0 w-16">
                    <div class="w-10 h-10 bg-slate-50 text-slate-400 rounded-xl flex items-center justify-center border border-dashed border-slate-100">
                        <span class="material-symbols-outlined text-[18px]">add</span>
                    </div>
                    <span class="text-[9px] font-bold text-slate-400 tracking-tighter text-center leading-none">Tambah</span>
                </button>
            </div>
        </div>

        <!-- Recommendations -->
        @include('admin.partials.discovery-feed')

        <!-- Savings Section -->
        <div class="pt-0.5">
            <h3 class="text-[12px] font-bold text-slate-800 tracking-tight pl-1 mb-1.5">Tabungan Keluarga</h3>
            @include('admin.partials.dompet-list')
        </div>

        <!-- Data Charts & History -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
            @include('admin.partials.chart-section')
            @include('admin.partials.activity-section')
        </div>
    </div>

    @include('tabungan.partials.modal-tabungan')
    @include('components.ai-bottom-sheet')
    @include('admin.partials.floating-streak')
    @include('admin.partials.dashboard-scripts')
</x-app-layout>
