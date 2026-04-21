<x-app-layout>
    @section('title', 'Eksplor Keuangan')

    <div class="max-w-screen-xl mx-auto space-y-6 pb-24">
        
        <!-- 1. Featured Insight (The "Hero" Card) -->
        @if(isset($items) && count($items) > 0)
            @php $hero = $items[0]; @endphp
            <div class="px-4 pt-4">
                <div class="relative overflow-hidden rounded-[2.5rem] {{ $hero['bg'] }} p-8 shadow-2xl shadow-emerald-900/10 group cursor-pointer active:scale-[0.98] transition-all" 
                     onclick="window.location.href='{{ $hero['action_url'] }}'">
                    
                    <!-- Abstract Background Decorative -->
                    <div class="absolute -right-10 -top-10 w-40 h-40 bg-white/10 rounded-full blur-3xl group-hover:scale-125 transition-transform duration-700"></div>
                    
                    <div class="relative z-10">
                        <div class="inline-flex items-center gap-2 bg-white/20 backdrop-blur-md px-3 py-1 rounded-full mb-6">
                            <span class="material-symbols-outlined text-[14px] text-white">auto_awesome</span>
                            <span class="text-[10px] font-black text-white uppercase tracking-widest">{{ $hero['category'] }}</span>
                        </div>
                        
                        <h2 class="text-2xl font-black text-white leading-tight mb-3 tracking-tighter">{{ $hero['title'] }}</h2>
                        <p class="text-white/80 text-sm font-medium leading-relaxed mb-8 max-w-[80%]">{{ $hero['description'] }}</p>
                        
                        <div class="flex items-center gap-2 text-white font-black text-[11px] uppercase tracking-widest">
                            {{ $hero['action_label'] }}
                            <span class="material-symbols-outlined text-sm">arrow_forward</span>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- 2. Insight Grid (High Density) -->
        <div class="px-4">
            <div class="flex items-center justify-between mb-4 px-1">
                <h3 class="text-[11px] font-black text-slate-400 uppercase tracking-[0.2em]">Wawasan Lainnya</h3>
                <span class="text-[10px] font-bold text-slate-300 tracking-tighter">{{ count($items) - 1 }} Temuan</span>
            </div>
            
            <div class="grid grid-cols-2 gap-3">
                @foreach(collect($items)->skip(1) as $item)
                    <div class="bg-white rounded-[2rem] p-5 border border-slate-100 shadow-sm flex flex-col justify-between group cursor-pointer active:scale-95 transition-all h-[180px]"
                         onclick="window.location.href='{{ $item['action_url'] }}'">
                        
                        <div>
                            <div class="w-10 h-10 {{ $item['bg'] ?? 'bg-slate-50' }} rounded-2xl flex items-center justify-center mb-4 shadow-sm border border-slate-100/50">
                                <span class="material-symbols-outlined {{ $item['text'] ?? 'text-slate-400' }} text-[22px]">
                                    @if(isset($item['icon']))
                                        {{ $item['icon'] }}
                                    @else
                                        {{ ($item['id'] ?? '') == 'ai_summary' ? 'auto_awesome' : 
                                           (($item['id'] ?? '') == 'fin_plan' ? 'account_balance' : 
                                           (($item['id'] ?? '') == 'new_feature' ? 'rocket_launch' : 
                                           (($item['id'] ?? '') == 'help_center' ? 'help_outline' : 'lightbulb'))) }}
                                    @endif
                                </span>
                            </div>
                            <h4 class="text-[12px] font-bold text-slate-900 leading-tight mb-2 line-clamp-2">{{ $item['title'] }}</h4>
                        </div>
                        
                        <div class="pt-3 border-t border-slate-50 flex items-center justify-between mt-auto">
                            <span class="text-[8px] font-black text-slate-400 uppercase tracking-widest">{{ $item['category'] }}</span>
                            <span class="material-symbols-outlined text-slate-300 text-sm group-hover:text-primary transition-colors">chevron_right</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- 3. Knowledge Base Shortcuts -->
        <div class="px-4 pt-4">
            <div class="bg-slate-950 rounded-[2.5rem] p-8 text-white relative overflow-hidden shadow-2xl">
                <div class="absolute right-[-20%] bottom-[-20%] opacity-[0.03]">
                    <span class="material-symbols-outlined text-[200px]">menu_book</span>
                </div>
                <div class="relative z-10">
                    <div class="w-12 h-12 bg-white/10 rounded-2xl flex items-center justify-center mb-6">
                        <span class="material-symbols-outlined text-primary text-2xl font-bold">menu_book</span>
                    </div>
                    <h4 class="text-xl font-bold mb-2 tracking-tight">Pusat Pengetahuan</h4>
                    <p class="text-slate-400 text-xs mb-8 max-w-[200px] leading-relaxed">Cek panduan lengkap pengelolaan keluarga sultan dan keamanan dana di sini.</p>
                    <a href="{{ route('help') }}" class="inline-flex items-center gap-3 bg-white text-slate-950 px-8 py-4 rounded-2xl text-[10px] font-black uppercase tracking-[0.2em] shadow-xl hover:bg-primary hover:text-white transition-all active:scale-95">
                        Buka Panduan
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
