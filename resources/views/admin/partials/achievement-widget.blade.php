<!-- Achievement Widget (Emerald & Gold Edition) -->
@php
    $type = $greeting['tipe'] ?? 'good';
    $statusLabel = $greeting['badge_text'] ?? 'Stabil';
    
    // Warna dot tetap kontekstual (Merah jika danger, dll)
    $config = match($type) {
        'danger' => ['dot' => 'bg-red-400', 'icon' => 'warning'],
        'warning' => ['dot' => 'bg-amber-400', 'icon' => 'error'],
        'excellent' => ['dot' => 'bg-blue-400', 'icon' => 'verified'],
        default => ['dot' => 'bg-white/40', 'icon' => 'verified'],
    };
    
    $insight = $greeting['pesan'] ?? 'Menganalisis data...';
@endphp

<div class="md:col-span-4 px-3 py-1.5 archival-gradient rounded-sm flex flex-col gap-1 border border-white/10 shadow-lg relative overflow-hidden group">
    <div class="flex items-center justify-between mb-0.5">
        <div class="flex items-center gap-1">
            <span class="material-symbols-outlined text-[10px] text-white">verified</span>
            <p class="font-['Helvetica'] text-[9px] font-black uppercase tracking-[0.2em] text-white">
                {{ $statusLabel }}
            </p>
        </div>
        <div class="h-1 w-1 rounded-full {{ $config['dot'] }} animate-pulse shadow-[0_0_5px_white]"></div>
    </div>

    <!-- Gold Box (White Text Marquee) -->
    <div class="relative h-6 bg-white/10 rounded-lg flex items-center overflow-hidden border border-[#D4AF37]/30">
        {{-- Inner Gold Frame Effect --}}
        <div class="absolute inset-[1px] border border-[#D4AF37]/40 rounded-md pointer-events-none"></div>
        
        <div class="marquee-container w-full whitespace-nowrap overflow-hidden relative">
            <span class="marquee-content inline-block text-[10px] font-bold tracking-tight text-white leading-none">
                {{ $insight }} &nbsp;&bull;&nbsp; {{ $insight }} &nbsp;&bull;&nbsp;
            </span>
        </div>
    </div>
</div>

<style>
    .marquee-content {
        animation: marquee 25s linear infinite;
        min-width: 100%;
        padding-left: 20px;
    }
    
    @keyframes marquee {
        0% { transform: translateX(0); }
        100% { transform: translateX(-50%); }
    }
</style>
