<section 
    class="bg-white p-4 rounded-[28px] shadow-sm border border-slate-100 overflow-hidden"
    x-data="{ 
        savings: {{ $savingsList->map(function($s) {
            return [
                'id' => $s->id,
                'name' => $s->nama,
                'balance' => 'Rp ' . number_format($s->balance, 0, ',', '.'),
                'target' => 'Rp ' . number_format($s->target, 0, ',', '.'),
                'shortfall' => 'Rp ' . number_format($s->shortfall, 0, ',', '.'),
                'percent' => $s->percent,
                'icon' => $s->icon,
                'color' => $s->color,
                'url' => route('management.index', ['type' => 'tabungan', 'id' => $s->id])
            ];
        })->toJson() }},
        currentIndex: 0,
        timer: null,
        init() {
            if(this.savings.length > 1) this.startTimer();
        },
        startTimer() {
            if (this.timer) clearInterval(this.timer);
            this.timer = setInterval(() => { this.next(); }, 5000);
        },
        next() { 
            this.currentIndex = (this.currentIndex + 1) % this.savings.length; 
            this.startTimer();
        },
        prev() { 
            this.currentIndex = (this.currentIndex - 1 + this.savings.length) % this.savings.length; 
            this.startTimer();
        }
    }"
>
    <!-- Card Header / Title -->
    <div class="flex items-center justify-between mb-4 px-1">
        <h3 class="text-[14px] font-black text-slate-800 tracking-tight">Progres Tabungan</h3>
        <!-- Mini Navigation -->
        <div class="flex items-center gap-1.5" x-show="savings.length > 1">
            <button @click="prev()" class="w-7 h-7 flex items-center justify-center bg-slate-50 border border-slate-100 rounded-full hover:bg-slate-100 text-slate-400 transition-all active:scale-90">
                <span class="material-symbols-outlined text-[16px]">chevron_left</span>
            </button>
            <button @click="next()" class="w-7 h-7 flex items-center justify-center bg-slate-50 border border-slate-100 rounded-full hover:bg-slate-100 text-slate-400 transition-all active:scale-90">
                <span class="material-symbols-outlined text-[16px]">chevron_right</span>
            </button>
        </div>
    </div>

    <!-- Main Content -->
    <div class="relative min-h-[100px]">
        @forelse($savingsList as $index => $saving)
            <div 
                x-show="currentIndex === {{ $index }}"
                x-transition:enter="transition ease-out duration-500 transform"
                x-transition:enter-start="opacity-0 translate-y-4 scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                class="cursor-pointer group"
                onclick="window.location.href = '{{ route('management.index', ['type' => 'tabungan', 'id' => $saving->id]) }}'"
            >
                <div class="flex items-start justify-between mb-3 px-1">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-2xl flex items-center justify-center shadow-sm border border-slate-50 transition-transform group-hover:scale-110 duration-300"
                             style="background-color: {{ $saving->color }}15; color: {{ $saving->color }};">
                            <span class="material-symbols-outlined text-2xl" style="font-variation-settings: 'FILL' 1;">{{ $saving->icon }}</span>
                        </div>
                        <div>
                            <h4 class="text-[13px] font-bold text-slate-800 leading-tight group-hover:text-primary transition-colors">{{ $saving->nama }}</h4>
                            <div class="flex items-center gap-1.5 mt-0.5">
                                <span class="text-[16px] font-black text-slate-900 tracking-tighter">Rp {{ number_format($saving->balance, 0, ',', '.') }}</span>
                                <span class="text-[10px] font-bold text-slate-400">/ Rp {{ number_format($saving->target, 0, ',', '.') }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="text-[18px] font-black tracking-tighter" style="color: {{ $saving->color }};">{{ $saving->percent }}%</div>
                        <p class="text-[9px] font-bold text-slate-400 uppercase tracking-tighter">Tercapai</p>
                    </div>
                </div>

                <!-- Custom Progress Bar (Super App Style) -->
                <div class="px-1 space-y-2">
                    <div class="w-full h-3 bg-slate-50 rounded-full overflow-hidden border border-slate-100/50 p-[1.5px]">
                        <div class="h-full rounded-full transition-all duration-1000 ease-out shadow-sm"
                             style="width: {{ $saving->percent }}%; background-color: {{ $saving->color }};">
                            <div class="w-full h-full bg-white/20 animate-pulse"></div>
                        </div>
                    </div>
                    <div class="flex justify-between items-center px-0.5">
                        <span class="text-[10px] font-bold text-slate-500">Kekurangan: <span class="text-rose-500">Rp {{ number_format($saving->shortfall, 0, ',', '.') }}</span></span>
                        <div class="flex items-center gap-1 text-primary group-hover:translate-x-1 transition-transform">
                            <span class="text-[10px] font-black uppercase tracking-widest">Detail</span>
                            <span class="material-symbols-outlined text-[14px] font-bold">east</span>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="py-8 text-center bg-slate-50 rounded-3xl border border-dashed border-slate-100">
                <span class="material-symbols-outlined text-4xl text-slate-300 mb-2">potted_plant</span>
                <p class="text-[11px] font-bold text-slate-400">Mulai menabung untuk masa depan keluarga Anda.</p>
                <a href="{{ route('management.create', ['type' => 'tabungan']) }}" class="text-[10px] font-black text-primary uppercase mt-2 inline-block hover:underline">Tambah Tabungan Baru</a>
            </div>
        @endforelse
    </div>

    <!-- Dots Mini -->
    @if($savingsList->count() > 1)
    <div class="flex justify-center gap-1.5 mt-5">
        <template x-for="(s, index) in savings" :key="'dot-'+s.id">
            <button 
                @click="currentIndex = index"
                class="h-1.5 rounded-full transition-all duration-500"
                :class="currentIndex === index ? 'w-6' : 'w-1.5 bg-slate-100'"
                :style="currentIndex === index ? 'background-color: ' + s.color : ''"
            ></button>
        </template>
    </div>
    @endif
</section>
