<div x-data="{ 
    visible: {{ auth()->user()->last_check_in_at && \Carbon\Carbon::parse(auth()->user()->last_check_in_at)->isToday() ? 'false' : 'true' }},
    showMilestone: false,
    milestoneValue: 0,
    streak: {{ auth()->user()->streak_count ?? 0 }},
    loading: false,
    posX: window.innerWidth - 80,
    posY: window.innerHeight - 250,
    draggit: false,
    hasAnimated: false,
    
    init() {
        // Prevent showing if already done in this session
        if (sessionStorage.getItem('streak_done_{{ date('Y-m-d') }}')) {
            this.visible = false;
        }

        try {
            if (!sessionStorage.getItem('streak_animated')) {
                this.hasAnimated = false;
                sessionStorage.setItem('streak_animated', 'true');
            } else {
                this.hasAnimated = true;
            }
        } catch (e) {
            this.hasAnimated = true;
        }
    },

    async performCheckIn() {
        if(!this.visible || this.loading) return;
        
        this.loading = true;
        this.visible = false; // Disappear immediately to avoid double clicks
        
        try {
            const resp = await fetch('{{ route('user.check-in') }}', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
            });
            const data = await resp.json();
            
            if(data.success) {
                this.streak = data.streak;
                sessionStorage.setItem('streak_done_{{ date('Y-m-d') }}', 'true');
                
                if(data.milestone) {
                    this.milestoneValue = data.milestone;
                    setTimeout(() => { this.showMilestone = true; }, 600);
                }
            } else {
                this.visible = true; // Re-show if not really checked in
            }
        } catch(e) {
            this.visible = true;
        } finally {
            this.loading = false;
        }
    }
}" 
class="fixed z-[9999] pointer-events-none select-none inset-0 md:hidden"
x-init="init()">

    <!-- Floating Draggable Fire Icon -->
    <div x-show="visible" 
         x-transition:enter="transition ease-out duration-500"
         x-transition:enter-start="opacity-0 scale-50 translate-y-10"
         x-transition:enter-end="opacity-100 scale-100 translate-y-0"
         x-transition:leave="transition ease-in duration-500 transform scale-150 opacity-0 -translate-y-20"
         class="absolute pointer-events-auto cursor-pointer flex flex-col items-center"
         :class="{ 'transition-none': hasAnimated }"
         :style="`left: ${posX}px; top: ${posY}px;`"
         @mousedown="draggit = true"
         @touchstart="draggit = true"
         @window:mousemove.prevent="if(draggit) { posX = Math.min(Math.max($event.clientX - 30, 10), window.innerWidth - 70); posY = Math.min(Math.max($event.clientY - 30, 80), window.innerHeight - 80); }"
         @window:touchmove.prevent="if(draggit) { posX = Math.min(Math.max($event.touches[0].clientX - 30, 10), window.innerWidth - 70); posY = Math.min(Math.max($event.touches[0].clientY - 30, 80), window.innerHeight - 80); }"
         @window:mouseup="draggit = false"
         @window:touchend="draggit = false"
         @click="performCheckIn()">
        
        <!-- Tooltip Label -->
        <div class="mb-1 bg-slate-900 text-white text-[8px] px-2 py-0.5 rounded-full font-black uppercase tracking-tighter shadow-xl animate-bounce">Ambil XP</div>

        <!-- Animated Fire Element -->
        <div class="relative w-14 h-14 flex items-center justify-center">
            <div class="absolute inset-0 bg-amber-500/30 rounded-full blur-xl animate-pulse"></div>
            <div class="absolute -inset-1 bg-gradient-to-tr from-amber-500 to-rose-500 rounded-full blur-md opacity-20"></div>
            
            <!-- Shadow Number (Floating Indicator) -->
            <div class="absolute -top-1 -right-1 z-10 font-black text-amber-600 text-[10px] bg-white w-5 h-5 flex items-center justify-center rounded-full shadow-lg" x-text="streak + 1"></div>
            
            <div class="relative bg-white rounded-2xl p-2.5 shadow-2xl">
                <span class="material-symbols-outlined text-3xl text-amber-500 filter drop-shadow-[0_2px_5px_rgba(245,158,11,0.5)]" 
                      style="font-variation-settings: 'FILL' 1, 'wght' 700;">
                    local_fire_department
                </span>
            </div>
        </div>
    </div>

    <!-- Duolingo-style Milestone Celebration -->
    <div x-show="showMilestone" 
         x-transition:enter="transition ease-out duration-500"
         x-transition:enter-start="opacity-0 scale-90"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-300"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-110"
         class="fixed inset-0 bg-slate-900/90 backdrop-blur-md flex items-center justify-center p-6 pointer-events-auto">
        
        <div class="max-w-xs w-full bg-white rounded-[40px] p-8 text-center relative overflow-hidden shadow-[0_0_50px_rgba(245,158,11,0.3)]">
            <!-- Ray Effect -->
            <div class="absolute -top-20 inset-0 flex justify-center opacity-20">
                <div class="w-1 bg-gradient-to-t from-amber-500 to-transparent h-[400px] rotate-[15deg]"></div>
                <div class="w-1 bg-gradient-to-t from-amber-500 to-transparent h-[400px] rotate-[45deg]"></div>
                <div class="w-1 bg-gradient-to-t from-amber-500 to-transparent h-[400px] rotate-[75deg]"></div>
                <div class="w-1 bg-gradient-to-t from-amber-500 to-transparent h-[400px] rotate-[135deg]"></div>
            </div>

            <div class="relative z-10 space-y-4">
                <div class="w-32 h-32 bg-amber-50 rounded-full flex items-center justify-center mx-auto shadow-inner">
                    <span class="material-symbols-outlined text-7xl text-amber-500 drop-shadow-lg" style="font-variation-settings: 'FILL' 1;">local_fire_department</span>
                </div>
                
                <div>
                    <h2 class="text-3xl font-black text-slate-900 tracking-tighter" x-text="milestoneValue + ' DAY STREAK!'"></h2>
                    <p class="text-[13px] font-bold text-slate-400 mt-2">Dahsyat! Kamu konsisten mengelola keuangan keluarga selama <span x-text="milestoneValue"></span> hari tanpa putus.</p>
                </div>

                <div class="pt-4">
                    <button @click="showMilestone = false" 
                            class="w-full bg-amber-500 hover:bg-amber-600 text-white font-black py-4 rounded-2xl shadow-lg shadow-amber-500/30 active:scale-95 transition-all text-sm uppercase tracking-widest">
                        Lanjut Berjuang
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    @keyframes bounce-slow {
        0%, 100% { transform: translateY(-5%); animation-timing-function: cubic-bezier(0.8, 0, 1, 1); }
        50% { transform: translateY(0); animation-timing-function: cubic-bezier(0, 0, 0.2, 1); }
    }
</style>
