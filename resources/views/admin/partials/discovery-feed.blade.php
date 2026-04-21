<!-- AI Discovery Feed (High Density Super App Style) -->
@php
    $discoveryItems = app(\App\Http\Controllers\DiscoveryController::class)->getItems();
@endphp

<div class="space-y-1.5">
    <div class="flex items-center justify-between px-1">
        <h3 class="text-[12px] font-bold text-slate-800 tracking-tight">Eksplor Keuangan</h3>
        <a href="{{ route('eksplor.index') }}" class="text-[10px] font-bold text-primary">Lihat Semua</a>
    </div>
    
    <!-- Tight Discovery Scroll (Limited to 3) -->
    <div class="flex overflow-x-auto gap-3 pb-2 no-scrollbar -mx-4 px-4">
        @foreach(collect($discoveryItems)->take(3) as $item)
            <div class="flex-shrink-0 w-[240px] {{ $item['bg'] }} rounded-2xl shadow-sm border border-slate-100/50 overflow-hidden flex flex-col group cursor-pointer active:scale-95 transition-all" onclick="window.location.href='{{ $item['action_url'] }}'">
                <div class="p-3">
                    <div class="flex items-center gap-2 mb-2">
                        <span class="text-[8px] font-bold uppercase tracking-widest px-1.5 py-0.5 rounded-full {{ $item['bg'] == 'bg-white' ? 'bg-slate-100 text-slate-500' : 'bg-white/10 text-white/80' }}">
                            {{ $item['category'] }}
                        </span>
                    </div>
                    <h4 class="text-[12px] font-bold {{ $item['text'] }} leading-tight mb-1 truncate">{{ $item['title'] }}</h4>
                    <p class="text-[10px] {{ $item['bg'] == 'bg-white' ? 'text-slate-400' : 'text-white/70' }} font-medium leading-normal mb-2 line-clamp-2 h-[30px]">{{ $item['description'] }}</p>
                    
                    <div class="flex items-center justify-between border-t {{ $item['bg'] == 'bg-white' ? 'border-slate-50' : 'border-white/10' }} pt-2">
                        <span class="text-[10px] font-bold {{ $item['bg'] == 'bg-white' ? 'text-primary' : 'text-white' }}">{{ $item['action_label'] }}</span>
                        <span class="material-symbols-outlined {{ $item['bg'] == 'bg-white' ? 'text-primary' : 'text-white' }} text-[14px]">chevron_right</span>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
