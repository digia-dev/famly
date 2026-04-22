<x-app-layout>
    @section('title', 'Agenda Keluarga')

    <div x-data="{ 
        tab: 'all', 
        selectedDate: '{{ request('date', '') }}',
        viewType: '{{ $viewType }}',
        showAddModal: false,
        agendaType: 'task',
        newAgenda: {
            name: '',
            date: '{{ now()->format('Y-m-d') }}',
            time: '08:00',
            timeline: [''],
            isPriority: false
        },
        datePickerOpen: false,
        dpMonth: {{ now()->month }},
        dpYear: {{ now()->year }},
        get dpDays() {
            let days = [];
            let firstDay = new Date(this.dpYear, this.dpMonth - 1, 1).getDay();
            let numDays = new Date(this.dpYear, this.dpMonth, 0).getDate();
            for (let i = 0; i < (firstDay === 0 ? 6 : firstDay - 1); i++) days.push({ d: '', full: '' });
            for (let i = 1; i <= numDays; i++) {
                let d = i.toString().padStart(2, '0');
                let m = this.dpMonth.toString().padStart(2, '0');
                days.push({ d: i, full: `${this.dpYear}-${m}-${d}` });
            }
            return days;
        },
        aiSuggestions: [
            { label: 'Token Listrik', time: '09:00' },
            { label: 'Minum Obat', time: '07:00' },
            { label: 'Jemput Anak', time: '15:30' },
            { label: 'Iuran Kas', time: '10:00' },
            { label: 'Beli Galon', time: '08:30' }
        ],
        applyAi(sug) {
            this.newAgenda.name = sug.label;
            this.newAgenda.time = sug.time;
            this.newAgenda.isPriority = sug.isPriority || false;
            if(sug.type) this.agendaType = sug.type;
        },
        addTimelineItem() { this.newAgenda.timeline.push(''); },
        removeTimelineItem(index) { this.newAgenda.timeline.splice(index, 1); },
        editMode: false,
        selectedIds: [],
        async bulkDelete() {
            if(!confirm('Hapus item terpilih?')) return;
            const resp = await fetch('{{ route('agenda.bulk-delete') }}', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify({ ids: this.selectedIds })
            });
            const data = await resp.json();
            if(data.success) window.location.reload();
        },
        async updateTask(id, content) {
            try {
                await fetch(`/agenda/${id}`, {
                    method: 'PATCH',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json', 'Accept': 'application/json' },
                    body: JSON.stringify({ keterangan: content })
                });
            } catch(e) { console.error(e); }
        },
        toggleSelect(id) {
            if(this.selectedIds.includes(id)) {
                this.selectedIds = this.selectedIds.filter(i => i !== id);
            } else {
                this.selectedIds.push(id);
            }
        },
        submitAgenda() { this.$refs.agendaForm.submit(); },
        async toggleTaskState(id) {
            try {
                const resp = await fetch(`/agenda/${id}/toggle`, {
                    method: 'PATCH',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
                });
                return await resp.json();
            } catch(e) { console.error(e); }
        },
        async toggleSubTask(id, index) {
            try {
                const resp = await fetch(`/agenda/${id}/timeline`, {
                    method: 'PATCH',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json', 'Accept': 'application/json' },
                    body: JSON.stringify({ index: index })
                });
                const data = await resp.json();
                if(data.task_done) this.done = true;
            } catch(e) { console.error(e); }
        },
        togglePriority() {
            this.newAgenda.isPriority = this.newAgenda.isPriority ? false : true;
        }
    }" class="max-w-screen-xl mx-auto pb-48">
        
        <!-- Fixed Header Area: Month, Actions & Calendar -->
        <div class="bg-white border-b border-slate-100 px-4 pt-6 pb-4 sticky top-0 z-[60] shadow-sm">
            <div class="flex items-center justify-between mb-5">
                <div class="relative" x-data="{ openDatePicker: false }">
                    <button @click="openDatePicker = !openDatePicker" class="flex items-center gap-1.5 group active:scale-95 transition-all">
                        <h2 class="text-2xl font-black text-slate-950 tracking-tighter capitalize leading-none">
                            {{ \Carbon\Carbon::create()->month($month)->translatedFormat('F') }} {{ $year }}
                        </h2>
                        <span class="material-symbols-outlined text-[20px] text-slate-300 group-hover:text-primary transition-colors">expand_more</span>
                    </button>
                    <div x-show="openDatePicker" @click.away="openDatePicker = false" x-transition class="absolute left-0 mt-2 w-64 bg-white rounded-3xl shadow-2xl border border-slate-100 p-4 z-50">
                        <div class="grid grid-cols-3 gap-2 text-center">
                             @foreach([1=>'Jan',2=>'Feb',3=>'Mar',4=>'Apr',5=>'Mei',6=>'Jun',7=>'Jul',8=>'Agu',9=>'Sep',10=>'Okt',11=>'Nov',12=>'Des'] as $num => $name)
                                <a href="{{ route('agenda.index', ['month' => $num, 'year' => $year, 'type' => $viewType]) }}" 
                                   class="py-2.5 text-[11px] font-bold rounded-xl {{ $num == $month ? 'bg-primary text-white shadow-lg' : 'text-slate-400 hover:bg-slate-50' }}">{{ $name }}</a>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <button @click="editMode = !editMode; selectedIds = []" 
                            :class="editMode ? 'bg-amber-500 text-white' : 'bg-slate-50 text-slate-400'"
                            class="w-10 h-10 rounded-full flex items-center justify-center shadow-sm active:scale-95 transition-all">
                        <span class="material-symbols-outlined text-[20px]" x-text="editMode ? 'done' : 'edit_note'"></span>
                    </button>
                    <a href="{{ route('agenda.index', ['type' => $viewType == 'month' ? 'week' : 'month', 'month' => $month, 'year' => $year]) }}" 
                       class="w-10 h-10 rounded-full flex items-center justify-center bg-slate-50 text-slate-400 shadow-sm active:scale-95 transition-all">
                        <span class="material-symbols-outlined text-[20px]">{{ $viewType == 'month' ? 'view_week' : 'grid_view' }}</span>
                    </a>
                    <button @click="showAddModal = true" class="w-10 h-10 rounded-full bg-primary text-white flex items-center justify-center shadow-lg active:scale-95 transition-all">
                        <span class="material-symbols-outlined text-[22px]">add</span>
                    </button>
                </div>
            </div>

            <!-- Calendar Display -->
            @if($viewType == 'month')
                <div class="grid grid-cols-7 gap-1 border border-slate-100 rounded-[2rem] p-3 bg-slate-50/50">
                    @foreach(['Sn', 'Sl', 'Rb', 'Km', 'Jm', 'Sb', 'Mg'] as $dayName)
                        <div class="text-center text-[10px] font-black text-slate-300 py-1.5">{{ $dayName }}</div>
                    @endforeach
                    @foreach($calendarDays as $day)
                        <button @click="selectedDate = (selectedDate === '{{ $day['full_date'] }}' ? '' : '{{ $day['full_date'] }}')"
                                class="aspect-square flex flex-col items-center justify-center rounded-2xl transition-all relative {{ $day['is_today'] ? 'border-2 border-primary/10 bg-white' : '' }} {{ !$day['is_current_month'] ? 'opacity-20 pointer-events-none' : '' }}"
                                :class="selectedDate === '{{ $day['full_date'] }}' ? 'bg-primary text-white shadow-xl z-10 scale-105' : 'hover:bg-white text-slate-700'">
                            <span class="text-xs font-bold">{{ $day['date'] }}</span>
                            @if($day['has_agenda'])
                                <div class="w-1.5 h-1.5 rounded-full bg-amber-500 mt-1" :class="selectedDate === '{{ $day['full_date'] }}' ? 'bg-white' : ''"></div>
                            @endif
                        </button>
                    @endforeach
                </div>
            @else
                <div class="flex overflow-x-auto gap-2 no-scrollbar pb-1">
                    @foreach($calendarDays as $day)
                        <button @click="selectedDate = (selectedDate === '{{ $day['full_date'] }}' ? '' : '{{ $day['full_date'] }}')"
                                class="flex flex-col items-center flex-shrink-0 w-11 py-3 rounded-2xl transition-all border"
                                :class="selectedDate === '{{ $day['full_date'] }}' ? 'bg-primary border-primary text-white shadow-xl scale-105' : 'bg-white border-slate-100 text-slate-400'">
                            <span class="text-[9px] font-bold mb-1 tracking-tighter">{{ $day['name'] }}</span>
                            <span class="text-sm font-black">{{ $day['date'] }}</span>
                        </button>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Filter Area -->
        <div class="px-4 pt-4 space-y-5">
            <div x-show="!selectedDate" class="flex items-center gap-2 overflow-x-auto no-scrollbar">
                @foreach(['all' => 'Semua', 'rituals' => 'Ritual', 'bills' => 'Pengingat', 'tasks' => 'Tugas'] as $key => $lbl)
                    <button @click="tab = '{{ $key }}'" :class="tab === '{{ $key }}' ? 'bg-slate-900 text-white shadow-md' : 'bg-white text-slate-400 border border-slate-100 shadow-sm'" 
                            class="px-4 py-2 rounded-full text-[11px] font-black transition-all whitespace-nowrap tracking-widest">{{ $lbl }}</button>
                @endforeach
            </div>

            <!-- Content Area -->
            <div class="space-y-6 pb-32">
                @php
                    $hasReminders = count($reminders) > 0;
                    $hasTasks = count($tasks) > 0;
                    $hasRituals = count($rituals) > 0;
                    $none = !$hasReminders && !$hasTasks && !$hasRituals;
                @endphp

                @if($none)
                    <div class="py-20 flex flex-col items-center justify-center text-center px-8">
                        <div class="w-20 h-20 rounded-[2.5rem] bg-slate-50 flex items-center justify-center mb-6">
                            <span class="material-symbols-outlined text-[40px] text-slate-200">event_busy</span>
                        </div>
                        <h3 class="text-lg font-black text-slate-900 tracking-tight mb-2">Belum ada agenda</h3>
                        <p class="text-[11px] font-bold text-slate-400 leading-relaxed max-w-[200px]">Semua rencana keluarga Anda akan muncul di sini. Mulai dengan menambah agenda baru.</p>
                        <button @click="showAddModal = true" class="mt-8 px-6 py-3 bg-white border border-slate-100 rounded-2xl text-[11px] font-black text-primary shadow-sm active:scale-95 transition-all">Tambah Sekarang</button>
                    </div>
                @endif

                <!-- Rituals Section -->
                <div x-show="(tab === 'all' || tab === 'rituals') && {{ $hasRituals ? 'true' : 'false' }}" x-transition>
                    <div class="space-y-2">
                        <div class="flex items-center gap-2 px-2 mb-3">
                            <span class="material-symbols-outlined text-[18px] text-primary">auto_graph</span>
                            <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Grup Ritualitas</h3>
                        </div>
                        @foreach($rituals as $ritual)
                            <div class="agenda-card bg-slate-900 rounded-3xl p-4 border-none shadow-xl relative overflow-hidden group">
                                <div class="absolute top-0 right-0 p-4 opacity-10">
                                    <span class="material-symbols-outlined text-[48px] text-white">celebration</span>
                                </div>
                                <div class="relative z-10">
                                    <div class="flex justify-between items-start mb-2">
                                        <div>
                                            <h4 class="text-[14px] font-black text-white tracking-tight">{{ $ritual->keterangan }}</h4>
                                            <div class="flex items-center gap-1.5 mt-1">
                                                <div class="px-2 py-0.5 bg-primary/20 text-primary rounded-lg text-[8px] font-black uppercase">Ritual</div>
                                                <span class="text-[9px] font-bold text-slate-500">{{ $ritual->jatuh_tempo->translatedFormat('d F') }}</span>
                                            </div>
                                        </div>
                                        <form action="{{ route('agenda.complete', $ritual->id) }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="tanggal_peristiwa" value="{{ now()->format('Y-m-d') }}">
                                            <button type="submit" class="px-4 py-2 bg-primary text-white rounded-xl text-[10px] font-black uppercase tracking-widest active:scale-95 transition-all">Selesai</button>
                                        </form>
                                    </div>
                                    <div class="flex items-center gap-2 mt-4">
                                        @if($ritual->group)
                                            <div class="flex -space-x-2">
                                                @foreach($ritual->group->members->take(3) as $m)
                                                    <div class="w-6 h-6 rounded-full border-2 border-slate-900 bg-slate-800 flex items-center justify-center overflow-hidden">
                                                        <img src="https://ui-avatars.com/api/?name={{ $m->name }}&background=random" class="w-full h-full object-cover">
                                                    </div>
                                                @endforeach
                                            </div>
                                            <p class="text-[9px] font-black text-slate-500 uppercase tracking-tighter">Shared with {{ $ritual->group->members->count() }} Anggota</p>
                                        @else
                                            <div class="w-6 h-6 rounded-full border-2 border-slate-900 bg-slate-800 flex items-center justify-center overflow-hidden">
                                                <img src="https://ui-avatars.com/api/?name={{ auth()->user()->name }}&background=random" class="w-full h-full object-cover">
                                            </div>
                                            <p class="text-[9px] font-black text-slate-500 uppercase tracking-tighter">Pribadi</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Bills Section -->
                <div x-show="(tab === 'all' || tab === 'bills') && {{ $hasReminders ? 'true' : 'false' }}" x-transition>
                    <div class="space-y-2">
                        @forelse($reminders as $reminder)
                             <div x-data="{ 
                                     done: {{ $reminder->status === 'done' ? 'true' : 'false' }}, 
                                     content: '{{ str_replace('[Pengingat] ', '', explode("\nTimeline:", $reminder->keterangan)[0] ?? ($reminder->kategoriNama->nama ?? 'Agenda')) }}' 
                                 }" 
                                 x-show="!selectedDate || selectedDate === '{{ $reminder->jatuh_tempo->format('Y-m-d') }}'" 
                                 class="agenda-card bg-white rounded-2xl p-3 border border-slate-50 shadow-sm flex items-start gap-3 transition-all"
                                 :class="done ? 'opacity-50' : (editMode ? 'ring-1 ring-slate-100 ring-offset-2' : '')">
                                
                                <!-- Select in Edit Mode -->
                                <template x-if="editMode">
                                    <button @click="toggleSelect({{ $reminder->id }})" class="mt-0.5">
                                        <div class="w-5 h-5 rounded-full border-2 flex items-center justify-center transition-all"
                                             :class="selectedIds.includes({{ $reminder->id }}) ? 'bg-amber-500 border-amber-500 text-white' : 'border-slate-100 bg-white'">
                                            <span class="material-symbols-outlined text-[14px] font-bold" x-show="selectedIds.includes({{ $reminder->id }})">check</span>
                                        </div>
                                    </button>
                                </template>

                                <!-- Toggle Done in Normal Mode -->
                                <template x-if="!editMode">
                                    <label class="relative flex items-center cursor-pointer mt-0.5">
                                        <input type="checkbox" x-model="done" @change="toggleTaskState({{ $reminder->id }})" class="peer hidden">
                                        <div class="w-5 h-5 rounded-[6px] border-2 border-slate-100 flex items-center justify-center transition-all peer-checked:bg-amber-500 peer-checked:border-amber-500">
                                            <span class="material-symbols-outlined text-white text-[14px] hidden peer-checked:block">check</span>
                                        </div>
                                    </label>
                                </template>

                                <div class="flex-1 min-w-0 pt-0">
                                    <template x-if="editMode">
                                        <input type="text" x-model="content" @blur="updateTask({{ $reminder->id }}, content)" 
                                               class="w-full bg-slate-50 border-none rounded-lg py-1 px-2 text-[13px] font-bold text-slate-800 focus:ring-1 focus:ring-primary/20">
                                    </template>
                                    <template x-if="!editMode">
                                        <h4 class="text-[13px] font-bold text-slate-800 truncate leading-tight transition-all" :class="done ? 'line-through text-slate-400' : ''" x-text="content"></h4>
                                    </template>
                                    <div class="flex items-center gap-1.5 mt-1">
                                        <div class="flex items-center gap-1">
                                            <div class="w-1.5 h-1.5 rounded-full {{ $reminder->is_priority ? 'bg-red-500 animate-pulse' : 'bg-slate-200' }}"></div>
                                            <span class="text-[9px] font-bold {{ $reminder->is_priority ? 'text-red-400' : 'text-slate-300' }} uppercase tracking-widest">{{ $reminder->is_priority ? 'Penting' : 'Normal' }}</span>
                                        </div>
                                        <div class="w-1 h-1 rounded-full bg-slate-100 mx-0.5"></div>
                                        <div class="px-1.5 py-0.5 rounded-md text-[8px] font-black uppercase {{ $reminder->is_group ? 'bg-emerald-50 text-emerald-600' : 'bg-slate-50 text-slate-400 uppercase' }}">
                                            {{ $reminder->is_group ? 'Grup' : 'Pribadi' }}
                                        </div>
                                    </div>
                                </div>
                             </div>
                        @empty
                            @if(!$none)
                                <div class="py-10 text-center">
                                    <p class="text-[10px] font-bold text-slate-300 uppercase tracking-widest">Tidak ada pengingat</p>
                                </div>
                            @endif
                        @endforelse
                    </div>
                </div>

                <!-- Tasks Section -->
                <div x-show="(tab === 'all' || tab === 'tasks') && {{ $hasTasks ? 'true' : 'false' }}" x-transition>
                    <div class="space-y-2">
                        @forelse($tasks as $task)
                            <div x-data="{ 
                                     done: {{ $task->status === 'done' ? 'true' : 'false' }}, 
                                     content: '{{ str_replace('[Pengingat] ', '', explode("\nTimeline:", $task->keterangan)[0] ?? ($task->kategoriNama->nama ?? 'Tugas')) }}',
                                     showTimeline: false,
                                     timeline: {{ json_encode($task->timeline_data ?? []) }}
                                 }" 
                                 x-show="!selectedDate || selectedDate === '{{ \Carbon\Carbon::parse($task->jatuh_tempo)->format('Y-m-d') }}'" 
                                 class="agenda-card bg-white rounded-2xl overflow-hidden border border-slate-50 shadow-sm hover:shadow-md transition-all"
                                 :class="done ? 'opacity-50' : (editMode ? 'ring-1 ring-slate-100 ring-offset-1' : '')">
                                
                                <div class="p-3">
                                    <div class="flex items-start gap-3">
                                        <!-- Select in Edit Mode -->
                                         <template x-if="editMode">
                                            <button @click="toggleSelect({{ $task->id }})" class="mt-0.5">
                                                <div class="w-5 h-5 rounded-full border-2 flex items-center justify-center transition-all"
                                                     :class="selectedIds.includes({{ $task->id }}) ? 'bg-blue-500 border-blue-500 text-white' : 'border-slate-100 bg-white'">
                                                    <span class="material-symbols-outlined text-[14px] font-bold" x-show="selectedIds.includes({{ $task->id }})">check</span>
                                                </div>
                                            </button>
                                        </template>

                                        <!-- Toggle Done in Normal Mode -->
                                        <template x-if="!editMode">
                                            <label class="relative flex items-center cursor-pointer mt-0.5">
                                                <input type="checkbox" x-model="done" @change="toggleTaskState({{ $task->id }})" class="peer hidden">
                                                <div class="w-5 h-5 rounded-[6px] border-2 border-slate-100 flex items-center justify-center transition-all peer-checked:bg-blue-500 peer-checked:border-blue-500">
                                                    <span class="material-symbols-outlined text-white text-[14px] hidden peer-checked:block">check</span>
                                                </div>
                                            </label>
                                        </template>

                                        <div class="flex-1 min-w-0 pointer-events-auto" @click="if(!editMode && timeline.length > 0) showTimeline = !showTimeline">
                                            <template x-if="editMode">
                                                <input type="text" x-model="content" @blur="updateTask({{ $task->id }}, content)" 
                                                       class="w-full bg-slate-50 border-none rounded-lg py-1 px-2 text-[13px] font-bold text-slate-800 focus:ring-1 focus:ring-primary/20">
                                            </template>
                                            <template x-if="!editMode">
                                                <div class="flex items-center justify-between gap-2">
                                                    <h4 class="text-[13px] font-bold text-slate-800 leading-tight transition-all" :class="done ? 'line-through text-slate-400' : ''" x-text="content"></h4>
                                                    <span x-show="timeline.length > 0" class="flex-shrink-0 text-[10px] font-black tracking-widest text-primary bg-slate-50 px-2 py-0.5 rounded-lg border border-slate-100 shadow-sm">
                                                        <span x-text="timeline.filter(i => i.done).length"></span>/<span x-text="timeline.length"></span>
                                                    </span>
                                                </div>
                                            </template>
                                            <div class="flex items-center gap-2 mt-1">
                                                <div class="flex items-center gap-1">
                                                    <div class="w-1.5 h-1.5 rounded-full {{ $task->is_priority ? 'bg-red-500 animate-pulse' : 'bg-slate-200' }}"></div>
                                                    <span class="text-[9px] font-bold {{ $task->is_priority ? 'text-red-400' : 'text-slate-300' }} tracking-tight">{{ $task->is_priority ? 'Prioritas' : 'Normal' }}</span>
                                                </div>
                                                <div class="w-1 h-1 rounded-full bg-slate-100"></div>
                                                <div class="px-1.5 py-0.5 rounded-md text-[8px] font-black uppercase {{ $task->is_group ? 'bg-emerald-50 text-emerald-600' : 'bg-slate-100 text-slate-400' }}">
                                                    {{ $task->is_group ? 'Grup' : 'Pribadi' }}
                                                </div>
                                                <div class="w-1 h-1 rounded-full bg-slate-100"></div>
                                                <p class="text-[9px] font-black text-slate-300 uppercase tracking-tight">{{ \Carbon\Carbon::parse($task->jatuh_tempo)->format('d M') }}</p>
                                                <template x-if="timeline.length > 0">
                                                    <span class="material-symbols-outlined text-[14px] text-slate-200 transition-transform" :class="showTimeline ? 'rotate-180' : ''">expand_more</span>
                                                </template>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Timeline Dropdown -->
                                <div x-show="showTimeline" x-collapse x-cloak>
                                    <div class="bg-slate-50/50 border-t border-slate-50 p-3 space-y-2">
                                        <template x-for="(step, idx) in timeline" :key="idx">
                                            <div class="flex items-center gap-2">
                                                <button @click="step.done = !step.done; toggleSubTask({{ $task->id }}, idx)" class="flex-shrink-0">
                                                    <div class="w-4 h-4 rounded border flex items-center justify-center transition-all"
                                                         :class="step.done ? 'bg-emerald-400 border-emerald-400 text-white' : 'bg-white border-slate-200'">
                                                        <span class="material-symbols-outlined text-[10px] font-bold" x-show="step.done">check</span>
                                                    </div>
                                                </button>
                                                <span class="text-[11px] font-medium transition-all" :class="step.done ? 'text-slate-300 line-through' : 'text-slate-600'" x-text="step.text"></span>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        @empty
                            @if(!$none)
                                <div class="py-10 text-center">
                                    <p class="text-[10px] font-bold text-slate-300 uppercase tracking-widest">Tidak ada tugas</p>
                                </div>
                            @endif
                        @endforelse
                    </div>
                </div>

                <!-- AI Smart Hub (Floating) -->
                <div x-data="{ 
                        aiMessage: '', 
                        aiData: null,
                        loading: false, 
                        async checkIn() { 
                            this.loading = true; 
                            this.aiMessage = '';
                            this.aiData = null;
                            try {
                                const resp = await fetch('{{ route('agenda.ai-check-in') }}', {
                                    method: 'POST',
                                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
                                });
                                const data = await resp.json();
                                let msg = data.message;
                                
                                // Parse [DATA_CARD] if exists
                                if (msg.includes('[DATA_CARD]')) {
                                    let parts = msg.split('[DATA_CARD]');
                                    msg = parts.filter(p => !p.trim().startsWith('{')).join(' ').trim();
                                    let jsonPart = parts.find(p => p.trim().startsWith('{'));
                                    if (jsonPart) {
                                        try { this.aiData = JSON.parse(jsonPart.trim()); } catch(e) {}
                                    }
                                }
                                this.aiMessage = msg;
                            } catch(e) { this.aiMessage = 'Gagal terhubung dengan Famly AI.'; }
                            this.loading = false;
                        } 
                    }" 
                    class="fixed bottom-32 right-6 z-[80]">
                    <template x-if="aiMessage || aiData">
                        <div x-transition class="absolute bottom-full right-0 mb-4 w-72 bg-white p-5 rounded-[2.5rem] shadow-2xl border border-emerald-100 flex flex-col gap-3">
                             <div class="flex items-center justify-between mb-1">
                                 <div class="flex items-center gap-2">
                                     <div class="w-7 h-7 rounded-full bg-emerald-500 flex items-center justify-center text-white">
                                         <span class="material-symbols-outlined text-[16px]">smart_toy</span>
                                     </div>
                                     <span class="text-[10px] font-black text-slate-800 uppercase tracking-widest leading-none">Smart Advisor</span>
                                 </div>
                                 <button @click="aiMessage = ''; aiData = null" class="text-slate-300 hover:text-slate-400"><span class="material-symbols-outlined text-[18px]">close</span></button>
                             </div>

                             <template x-if="aiData">
                                 <div class="bg-slate-50 p-4 rounded-2xl border border-slate-100 mb-1">
                                     <h4 class="text-[11px] font-black text-slate-900 uppercase tracking-tighter mb-2" x-text="aiData.title"></h4>
                                     <div class="space-y-2">
                                         <template x-for="item in aiData.items">
                                             <div class="flex justify-between items-center text-[11px] font-bold">
                                                 <span class="text-slate-400" x-text="item.label"></span>
                                                 <span class="text-emerald-600" x-text="item.value"></span>
                                             </div>
                                         </template>
                                     </div>
                                     <div x-show="aiData.footer" class="mt-3 pt-2 border-t border-slate-100 text-[10px] font-black text-slate-300 italic text-right" x-text="aiData.footer"></div>
                                 </div>
                             </template>
                             
                             <p class="text-[13px] font-bold text-slate-700 leading-snug px-1" x-text="aiMessage"></p>
                             
                             <div class="mt-2 flex justify-end">
                                 <button @click="aiMessage = ''; aiData = null" class="px-4 py-2 bg-emerald-50 text-emerald-600 rounded-xl text-[10px] font-black uppercase tracking-widest active:scale-95 transition-all">Oke, Paham</button>
                             </div>
                        </div>
                    </template>
                    <button @click="checkIn()" 
                            :disabled="loading"
                            class="w-14 h-14 rounded-[2rem] bg-slate-900 text-white shadow-2xl flex items-center justify-center border border-slate-800 active:scale-95 transition-all group overflow-hidden">
                        <span x-show="!loading" class="material-symbols-outlined text-[28px] text-primary group-hover:rotate-12 transition-transform">auto_awesome</span>
                        <div x-show="loading" class="w-6 h-6 border-2 border-primary border-t-transparent rounded-full animate-spin"></div>
                    </button>
                </div>
            </div>
        </div>

        <!-- Premium Bottom Sheet Modal (STILL COMPACT per User Request ealier) -->
        <div x-show="showAddModal" class="fixed inset-0 z-[100] flex items-end justify-center" x-cloak translate="no">
            <div @click="showAddModal = false; datePickerOpen = false" x-show="showAddModal" 
                 x-transition:enter="transition ease-out duration-500" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-300" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                 class="absolute inset-0 bg-slate-950/40 backdrop-blur-sm"></div>

            <div x-show="showAddModal" 
                 x-transition:enter="transition ease-out duration-500 transform" x-transition:enter-start="translate-y-full" x-transition:enter-end="translate-y-0"
                 x-transition:leave="transition ease-in duration-300 transform" x-transition:leave-start="translate-y-0" x-transition:leave-end="translate-y-full"
                 class="relative bg-white w-full max-w-xl rounded-t-[2.5rem] shadow-2xl p-4 pb-12 overflow-hidden">
                
                <div class="flex justify-center mb-3"><div class="w-8 h-1 bg-slate-100 rounded-full"></div></div>

                <div class="flex items-center justify-between mb-4 px-1">
                    <h3 class="text-base font-black text-slate-900 tracking-tight">Agenda Baru</h3>
                    <div class="flex items-center gap-3">
                        <button type="button" @click="togglePriority()" 
                                class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl transition-all"
                                :class="newAgenda.isPriority ? 'bg-red-50 text-red-500' : 'bg-slate-50 text-slate-300'">
                            <span class="material-symbols-outlined text-[18px]">priority_high</span>
                            <span class="text-[9px] font-black uppercase tracking-widest" x-text="newAgenda.isPriority ? 'Prioritas' : 'Normal'"></span>
                        </button>
                        <button @click="showAddModal = false" class="w-7 h-7 rounded-full bg-slate-50 text-slate-400 hover:text-slate-900 flex items-center justify-center transition-all active:scale-90"><span class="material-symbols-outlined text-[16px]">close</span></button>
                    </div>
                </div>

                <!-- Animated Sliding Toggle -->
                <div class="relative flex p-1 bg-slate-100 rounded-2xl mb-4 overflow-hidden h-10">
                    <div class="absolute inset-1 w-[calc(33.33%-4px)] bg-white rounded-xl shadow-sm transition-all duration-500 ease-out"
                         :style="agendaType === 'reminder' ? 'transform: translateX(100%)' : (agendaType === 'ritual' ? 'transform: translateX(200%)' : 'transform: translateX(0)')"></div>
                    <button type="button" @click="agendaType = 'task'" class="relative flex-1 z-10 flex items-center justify-center gap-1.5 text-[9px] font-black transition-all" :class="agendaType === 'task' ? 'text-primary' : 'text-slate-400'">Tugas</button>
                    <button type="button" @click="agendaType = 'reminder'" class="relative flex-1 z-10 flex items-center justify-center gap-1.5 text-[9px] font-black transition-all" :class="agendaType === 'reminder' ? 'text-primary' : 'text-slate-400'">Pengingat</button>
                    <button type="button" @click="agendaType = 'ritual'" class="relative flex-1 z-10 flex items-center justify-center gap-1.5 text-[9px] font-black transition-all" :class="agendaType === 'ritual' ? 'text-primary' : 'text-slate-400'">Ritual</button>
                </div>

                <div x-show="{{ auth()->user()->current_group_id ? 'true' : 'false' }}" class="mb-4">
                    <div class="flex items-center justify-between p-3 bg-slate-50 rounded-2xl border border-slate-100">
                        <div class="flex items-center gap-2">
                             <span class="material-symbols-outlined text-[18px] text-slate-400">group</span>
                             <p class="text-[10px] font-bold text-slate-600 uppercase tracking-widest">Bagikan ke Grup?</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="is_group" value="1" class="sr-only peer" checked>
                            <div class="w-9 h-5 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-primary"></div>
                        </label>
                    </div>
                </div>

                <form x-ref="agendaForm" action="{{ route('agenda.store') }}" method="POST" class="space-y-4">
                    @csrf
                    <input type="hidden" name="type_selection" :value="agendaType">
                    <input type="hidden" name="jatuh_tempo" x-model="newAgenda.date">
                    <input type="hidden" name="is_priority" :value="newAgenda.isPriority ? 1 : 0">
                    
                    <div>
                        <input type="text" name="nama_manual" x-model="newAgenda.name" placeholder="Nama kegiatan..." required
                               class="w-full bg-slate-50 border-none rounded-xl px-4 py-2.5 text-sm font-bold text-slate-900 focus:ring-1 focus:ring-primary/20 placeholder-slate-300">
                    </div>

                    <div class="relative min-h-[140px]">
                        <!-- REMINDER MODE (Faded) -->
                        <div x-show="agendaType === 'reminder'" 
                             x-transition:enter="transition ease-out duration-500 opacity-0"
                             x-transition:enter-start="opacity-0"
                             x-transition:enter-end="opacity-100"
                             x-transition:leave="transition ease-in duration-400 absolute inset-0"
                             class="space-y-3">
                             <div class="flex flex-wrap gap-1.5">
                                 <template x-for="sug in aiSuggestions">
                                    <button type="button" @click="applyAi(sug)" class="px-2.5 py-1.5 bg-white border border-slate-100 rounded-lg text-[9px] font-bold text-slate-400 hover:text-primary transition-all flex items-center gap-1 shadow-sm active:scale-95">
                                        <span class="material-symbols-outlined text-[10px] opacity-40">auto_awesome</span><span x-text="sug.label"></span>
                                    </button>
                                 </template>
                             </div>
                             <div class="grid grid-cols-2 gap-2">
                                <div class="relative">
                                    <button type="button" @click="datePickerOpen = !datePickerOpen" class="w-full bg-slate-50 border-none rounded-xl px-4 py-2.5 text-[11px] font-black text-slate-900 flex items-center justify-between shadow-inner">
                                        <span x-text="new Date(newAgenda.date).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' })"></span>
                                        <span class="material-symbols-outlined text-[16px] text-slate-300">calendar_month</span>
                                    </button>
                                    <div x-show="datePickerOpen" @click.away="datePickerOpen = false" x-transition class="absolute bottom-full left-0 mb-2 w-72 bg-white rounded-[2rem] shadow-2xl border border-slate-100 p-4 z-[110]">
                                        <div class="flex items-center justify-between mb-3 px-1">
                                            <span class="text-[10px] font-black text-slate-900" x-text="new Date(dpYear, dpMonth-1).toLocaleDateString('id-ID', { month: 'long', year: 'numeric' })"></span>
                                            <div class="flex items-center gap-2"><button type="button" @click="dpMonth === 1 ? (dpMonth = 12, dpYear--) : dpMonth--" class="p-1"><span class="material-symbols-outlined text-[16px]">chevron_left</span></button><button type="button" @click="dpMonth === 12 ? (dpMonth = 1, dpYear++) : dpMonth++" class="p-1"><span class="material-symbols-outlined text-[16px]">chevron_right</span></button></div>
                                        </div>
                                        <div class="grid grid-cols-7 gap-1 mb-1"><template x-for="dayName in ['Sn','Sl','Rb','Km','Jm','Sb','Mg']"><div class="text-[8px] font-black text-slate-200 text-center" x-text="dayName"></div></template></div>
                                        <div class="grid grid-cols-7 gap-1"><template x-for="day in dpDays"><button type="button" @click="if(day.full) { newAgenda.date = day.full; datePickerOpen = false; }" class="aspect-square rounded-lg text-[10px] font-bold" :class="!day.full ? 'opacity-0' : (newAgenda.date === day.full ? 'bg-primary text-white shadow-sm' : 'text-slate-500 hover:bg-slate-50')"><span x-text="day.d"></span></button></template></div>
                                    </div>
                                </div>
                                <input type="time" name="waktu" x-model="newAgenda.time" class="w-full bg-slate-50 border-none rounded-xl px-4 py-2.5 text-[11px] font-black text-slate-900 text-center shadow-inner">
                             </div>
                        </div>

                        <!-- TASK MODE (Faded) -->
                        <div x-show="agendaType === 'task'" 
                             x-transition:enter="transition ease-out duration-500 opacity-0"
                             x-transition:enter-start="opacity-0"
                             x-transition:enter-end="opacity-100"
                             x-transition:leave="transition ease-in duration-400 absolute inset-0"
                             class="space-y-3">
                            <div class="relative">
                                <button type="button" @click="datePickerOpen = !datePickerOpen" class="w-full bg-slate-50 border-none rounded-xl px-4 py-2.5 text-[11px] font-black text-slate-900 flex items-center justify-between shadow-inner">
                                    <span x-text="new Date(newAgenda.date).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' })"></span>
                                    <span class="material-symbols-outlined text-[16px] text-slate-300">calendar_month</span>
                                </button>
                            </div>
                            <div class="space-y-1.5">
                                <template x-for="(item, index) in newAgenda.timeline" :key="index">
                                    <div class="flex items-center gap-2 group p-1 bg-slate-50/50 rounded-lg">
                                        <input type="text" :name="'details[]'" x-model="newAgenda.timeline[index]" placeholder="Detail langkah..." class="flex-1 bg-transparent border-none py-1 px-2 text-[11px] font-bold text-slate-600 focus:ring-0 outline-none">
                                        <button type="button" @click="removeTimelineItem(index)" x-show="newAgenda.timeline.length > 1" class="text-slate-200 hover:text-red-400"><span class="material-symbols-outlined text-[16px]">do_not_disturb_on</span></button>
                                    </div>
                                </template>
                                <button type="button" @click="addTimelineItem()" class="text-primary text-[9px] font-black tracking-widest mt-1 hover:opacity-70 flex items-center gap-1 active:scale-90"><span class="material-symbols-outlined text-[14px]">add_circle</span> Tambah Item</button>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="w-full py-4 bg-primary text-white rounded-2xl font-black text-xs shadow-lg shadow-primary/20 active:scale-95 transition-all mt-4 tracking-widest">Simpan Agenda</button>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
