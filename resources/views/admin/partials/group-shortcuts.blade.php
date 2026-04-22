<div class="px-2 pb-1" x-data="{
    async switchGroup(id) {
        try {
            const response = await fetch(`/groups/switch/${id}`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
            });
            const data = await response.json();
            if (data.success) {
                // Smooth reload to apply new GroupScope context
                window.location.reload();
            }
        } catch (e) { console.error('Gagal switch grup'); }
    }
}">
    <div class="flex items-center gap-3 overflow-x-auto no-scrollbar py-2 px-2">
        <!-- Personal Mode Shortcut -->
        <button @click="switchGroup(0)" class="flex flex-col items-center gap-1.5 flex-shrink-0 group">
            <div class="relative">
                <div class="w-14 h-14 rounded-full flex items-center justify-center transition-all duration-300 {{ !Auth::user()->current_group_id ? 'bg-primary ring-4 ring-primary/10 scale-105 shadow-lg' : 'bg-slate-100 border border-slate-100' }}">
                    <span class="material-symbols-outlined text-[24px] {{ !Auth::user()->current_group_id ? 'text-white' : 'text-slate-400' }}">person</span>
                </div>
                @if(!Auth::user()->current_group_id)
                <div class="absolute -bottom-1 -right-1 w-5 h-5 bg-emerald-500 border-2 border-white rounded-full flex items-center justify-center">
                    <span class="material-symbols-outlined text-white text-[10px] font-black">check</span>
                </div>
                @endif
            </div>
            <span class="text-[9px] font-black uppercase tracking-tighter {{ !Auth::user()->current_group_id ? 'text-primary' : 'text-slate-400' }}">Pribadi</span>
        </button>

        <!-- Divider -->
        <div class="w-px h-10 bg-slate-100 flex-shrink-0 mx-1"></div>

        <!-- Group Shortcuts -->
        @foreach($userGroups as $group)
        <button @click="switchGroup({{ $group->id }})" class="flex flex-col items-center gap-1.5 flex-shrink-0 group">
            <div class="relative">
                <div class="w-14 h-14 rounded-full flex items-center justify-center transition-all duration-300 {{ Auth::user()->current_group_id == $group->id ? 'bg-indigo-600 ring-4 ring-indigo-500/10 scale-105 shadow-lg shadow-indigo-500/20' : 'bg-slate-50 border border-slate-100 group-hover:border-indigo-200' }}">
                    <span class="text-lg font-black {{ Auth::user()->current_group_id == $group->id ? 'text-white' : 'text-indigo-400' }}">
                        {{ substr($group->name, 0, 1) }}
                    </span>
                </div>
                @if(Auth::user()->current_group_id == $group->id)
                <div class="absolute -bottom-1 -right-1 w-5 h-5 bg-indigo-500 border-2 border-white rounded-full flex items-center justify-center">
                    <span class="material-symbols-outlined text-white text-[10px] font-black">check</span>
                </div>
                @endif
                @if($group->members()->where('user_id', Auth::id())->first()->pivot->role == 'admin')
                <div class="absolute -top-1 -right-1 w-5 h-5 bg-amber-400 border-2 border-white rounded-full flex items-center justify-center shadow-sm">
                    <span class="material-symbols-outlined text-white text-[10px] font-black">shield</span>
                </div>
                @endif
            </div>
            <span class="text-[9px] font-black uppercase tracking-tighter truncate w-14 text-center {{ Auth::user()->current_group_id == $group->id ? 'text-indigo-600' : 'text-slate-400' }}">
                {{ $group->name }}
            </span>
        </button>
        @endforeach

        <!-- Add Group Button -->
        <a href="{{ route('groups.index') }}" class="flex flex-col items-center gap-1.5 flex-shrink-0 group">
            <div class="w-14 h-14 rounded-full flex items-center justify-center border-2 border-dashed border-slate-100 text-slate-300 group-hover:border-primary group-hover:text-primary transition-all">
                <span class="material-symbols-outlined text-[24px]">add</span>
            </div>
            <span class="text-[9px] font-black uppercase tracking-tighter text-slate-300 group-hover:text-primary">Grup Baru</span>
        </a>
    </div>
</div>
