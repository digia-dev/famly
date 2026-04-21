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
    <!-- Section: Daily Ritual Status -->
    <div class="col-span-4 bg-white rounded-2xl p-2.5 border border-slate-100/50 shadow-sm flex flex-col justify-between items-center text-center relative overflow-hidden group active:scale-[0.98] transition-all cursor-default">
        <div class="relative z-10 w-full">
            <div class="flex items-center justify-between mb-2">
                <span class="material-symbols-outlined text-[16px] text-amber-500" style="font-variation-settings: 'FILL' 1;">local_fire_department</span>
                <span class="text-[8px] font-black text-slate-300 uppercase tracking-tighter">Record: {{ auth()->user()->highest_streak }}</span>
            </div>
            <div class="text-2xl font-black text-slate-900 leading-none tracking-tighter mb-1">{{ auth()->user()->streak_count }}</div>
            <h4 class="text-[11px] font-bold text-slate-800 leading-none tracking-tight">Streak</h4>
            
            <div class="mt-2 pt-2 border-t border-slate-50">
                 <p class="text-[8px] font-bold text-slate-400">Terus konsisten!</p>
            </div>
        </div>
    </div>

    <!-- Section: Mini Agenda Hub -->
    <div class="col-span-8 bg-white rounded-2xl p-2.5 border border-slate-100/50 shadow-sm flex flex-col gap-2">
        <div class="flex items-center justify-between px-0.5">
            <div class="flex items-center gap-1.5">
                <span class="material-symbols-outlined text-[14px] text-primary" style="font-variation-settings: 'FILL' 1;">checklist</span>
                <h4 class="text-[11px] font-bold text-slate-800 tracking-tight leading-none">Agenda Hari Ini</h4>
            </div>
            <a href="{{ route('agenda.index') }}" class="text-[10px] font-bold text-primary">Lihat Semua</a>
        </div>
        
        <div class="space-y-2 pt-1">
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
                            <div class="w-4.5 h-4.5 rounded-[5px] border-1.5 border-slate-200 flex items-center justify-center transition-all"
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
                                <div class="w-1.5 h-1.5 rounded-full {{ $agenda->is_priority ? 'bg-red-500' : 'bg-slate-200' }}"></div>
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
                                     :class="step.done ? 'bg-blue-400 border-blue-400 text-white' : 'bg-white border-slate-200'">
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
