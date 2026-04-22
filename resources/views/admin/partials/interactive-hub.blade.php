<div class="grid grid-cols-12 gap-2" x-data="{ 
    checkedIn: {{ auth()->user()->last_check_in_at && \Carbon\Carbon::parse(auth()->user()->last_check_in_at)->isToday() ? 'true' : 'false' }},
    streak: {{ auth()->user()->streak_count ?? 0 }},
    loading: false,
    async checkIn() {
        if(this.checkedIn || this.loading) return;
        this.loading = true;
        try {
            const resp = await fetch('{{ route('user.check-in') }}', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
            });
            const data = await resp.json();
            if(data.success) {
                this.checkedIn = true;
                this.streak = data.streak;
            }
        } finally { this.loading = false; }
    },
    async toggleTask(id) {
        const resp = await fetch(`/agenda/${id}/toggle`, {
            method: 'PATCH',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
        });
        const data = await resp.json();
    },
    async toggleSubTask(id, index, context) {
        const resp = await fetch(`/agenda/${id}/timeline`, {
            method: 'PATCH',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body: JSON.stringify({ index: index })
        });
        const data = await resp.json();
        if(data.task_done) context.done = true;
    }
}">
    <!-- Section: Daily Ritual Status (Gamification) -->
    <div @click="checkIn()" 
         :class="checkedIn ? 'bg-slate-900 border-slate-900' : 'bg-white border-slate-100/50 cursor-pointer '"
         class="col-span-4 rounded-[28px] p-3 border shadow-sm flex flex-col justify-between items-center text-center relative overflow-hidden group active:scale-95 transition-all duration-500">
        
        <div class="absolute inset-0 bg-gradient-to-br from-amber-500/10 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
        
        <div class="relative z-10 w-full">
            <div class="flex items-center justify-between mb-2">
                <span class="material-symbols-outlined text-[18px] transition-transform duration-700" 
                      :class="checkedIn ? 'text-primary scale-110 animate-bounce-subtle' : 'text-slate-300 group-hover:rotate-12'"
                      style="font-variation-settings: 'FILL' 1;">local_fire_department</span>
                <span class="text-[8px] font-black uppercase tracking-tighter" :class="checkedIn ? 'text-slate-500' : 'text-slate-300'">Top: {{ auth()->user()->highest_streak }}</span>
            </div>
            
            <div class="text-3xl font-black leading-none tracking-tighter mb-1" :class="checkedIn ? 'text-white' : 'text-slate-900'" x-text="streak"></div>
            <h4 class="text-[10px] font-black uppercase tracking-widest leading-none mb-4" :class="checkedIn ? 'text-slate-400' : 'text-slate-500'">HARI</h4>
            
            <div class="w-full h-8 flex items-center justify-center rounded-xl bg-slate-50/10" :class="checkedIn ? 'bg-white/5' : 'bg-slate-50'">
                 <p class="text-[9px] font-black uppercase tracking-widest" :class="checkedIn ? 'text-primary' : 'text-slate-400'">
                     <span x-show="!checkedIn">Absen</span>
                     <span x-show="checkedIn">Ready</span>
                 </p>
            </div>
        </div>
    </div>

    <!-- Section: Mini Agenda Hub -->
    <div class="col-span-8 bg-white rounded-[28px] p-3 border border-slate-100/50 shadow-sm flex flex-col gap-2">
        <div class="flex items-center justify-between px-0.5">
            <div class="flex items-center gap-1.5">
                <span class="material-symbols-outlined text-[14px] text-primary" style="font-variation-settings: 'FILL' 1;">checklist</span>
                <h4 class="text-[11px] font-bold text-slate-800 tracking-tight leading-none">Agenda Hari Ini</h4>
            </div>
            <a href="{{ route('agenda.index') }}" class="text-[10px] font-bold text-primary">Lihat Semua</a>
        </div>
        
        <div class="pt-1 overflow-y-auto no-scrollbar" style="max-height: 115px;">
            <div class="space-y-2.5 px-0.5">
            @forelse($agendas as $agenda)
            @php $isRen = str_contains(strtolower($agenda->keterangan), '[pengingat]'); @endphp
            <div class="flex flex-col group/task" 
                 x-data="{ 
                     done: '{{ $agenda->status }}' === 'done', 
                     showTimeline: false,
                     isReminder: {{ $isRen ? 'true' : 'false' }},
                     timeline: {{ json_encode($agenda->timeline_data ?? []) }}
                 }">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2.5 min-w-0 flex-1 cursor-pointer" @click="if(timeline.length > 0) showTimeline = !showTimeline">
                        <label class="relative flex items-center cursor-pointer" @click.stop>
                            <input type="checkbox" x-model="done" @change="toggleTask({{ $agenda->id }})" class="peer hidden">
                            <div class="w-4.5 h-4.5 rounded-[5px] border-1.5 border-slate-100 flex items-center justify-center transition-all"
                                 :class="done ? (isReminder ? 'bg-amber-500 border-amber-500' : 'bg-blue-500 border-blue-500') : ''">
                                <span class="material-symbols-outlined text-white text-[10px] font-bold hidden" :class="done ? 'block' : ''">check</span>
                            </div>
                        </label>
                        <div class="flex flex-col min-w-0">
                            <span class="text-[11px] font-bold text-slate-700 truncate tracking-tight transition-all"
                                  :class="done ? 'line-through text-slate-400 opacity-50' : ''">
                                {{ str_replace('[Pengingat] ', '', explode("\nTimeline:", $agenda->keterangan)[0]) ?? ($agenda->kategoriNama->nama ?? 'Agenda') }}
                            </span>
                            <div class="flex items-center gap-1.5 mt-0.5">
                                <div class="w-1.5 h-1.5 rounded-full {{ $agenda->is_priority ? 'bg-red-500' : 'bg-slate-100' }}"></div>
                                <span class="text-[8px] font-bold {{ $agenda->is_priority ? 'text-red-400' : 'text-slate-300' }} tracking-tighter uppercase" x-text="timeline.length > 0 ? (timeline.filter(i => i.done).length + '/' + timeline.length) : '{{ $agenda->is_priority ? 'Penting' : 'Normal' }}'"></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Timeline Dropdown in Dashboard -->
                <div x-show="showTimeline" x-collapse x-cloak class="mt-2 ml-7 pl-3 border-l border-slate-100 space-y-1.5 pb-1">
                    <template x-for="(step, idx) in timeline" :key="idx">
                        <div class="flex items-center gap-2">
                            <button @click="step.done = !step.done; toggleSubTask({{ $agenda->id }}, idx, $data)" class="flex-shrink-0">
                                <div class="w-3.5 h-3.5 rounded-sm border flex items-center justify-center transition-all"
                                     :class="step.done ? 'bg-blue-400 border-blue-400 text-white' : 'bg-white border-slate-100'">
                                    <span class="material-symbols-outlined text-[8px] font-bold" x-show="step.done">check</span>
                                </div>
                            </button>
                            <span class="text-[9px] font-medium truncate" :class="step.done ? 'text-slate-300 line-through' : 'text-slate-500'" x-text="step.text"></span>
                        </div>
                    </template>
                </div>
            </div>
            @empty
            <div class="py-2 text-center">
                <p class="text-[9px] font-bold text-slate-300 italic">Bersih! Tidak ada tugas hari ini.</p>
            </div>
            @endforelse
            </div>
        </div>
    </div>
</div>

<style>
    .no-scrollbar::-webkit-scrollbar { display: none; }
    .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
</style>
