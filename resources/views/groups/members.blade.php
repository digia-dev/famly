<x-app-layout>
    @section('title', 'Manajemen Anggota')

    <div class="py-12" x-data="{
        async accept(userId) {
            if (!confirm('Terima anggota ini ke dalam grup?')) return;
            try {
                const response = await fetch(`/groups/{{ $group->id }}/accept/${userId}`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                });
                const data = await response.json();
                if (data.success) window.location.reload();
            } catch (e) { alert('Gagal memproses.'); }
        },
        async reject(userId) {
            if (!confirm('Tolak permintaan atau keluarkan anggota ini?')) return;
            try {
                const response = await fetch(`/groups/{{ $group->id }}/reject/${userId}`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                });
                const data = await response.json();
                if (data.success) window.location.reload();
            } catch (e) { alert('Gagal memproses.'); }
        }
    }">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white rounded-[32px] shadow-sm border border-slate-100 overflow-hidden mb-8">
                <div class="p-8 border-b border-slate-50 flex justify-between items-center bg-slate-50/50">
                    <div class="flex items-center gap-4">
                         <a href="{{ route('groups.index') }}" class="w-10 h-10 rounded-xl bg-white border border-slate-100 flex items-center justify-center text-slate-400 hover:text-primary transition-all active:scale-95">
                            <span class="material-symbols-outlined text-[20px]">arrow_back</span>
                         </a>
                         <div>
                            <h2 class="text-xl font-black text-slate-800">{{ $group->name }}</h2>
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1">Registry Anggota & Izin</p>
                         </div>
                    </div>
                    <div class="px-4 py-2 bg-primary/10 rounded-full border border-primary/20">
                         <span class="text-[10px] font-black text-primary uppercase tracking-tighter italic">Orchestration Hub Active</span>
                    </div>
                </div>

                <!-- Pending Approvals Section -->
                @php $pending = $group->members()->wherePivot('status', 'Pending')->get(); @endphp
                @if($pending->count() > 0)
                <div class="p-8 bg-amber-50/30 border-b border-slate-100">
                    <div class="flex items-center gap-2 mb-6">
                        <span class="material-symbols-outlined text-amber-500">group_add</span>
                        <h3 class="text-xs font-black text-slate-500 uppercase tracking-widest">Permintaan Bergabung ({{ $pending->count() }})</h3>
                    </div>
                    <div class="space-y-4">
                        @foreach($pending as $p)
                        <div class="flex items-center justify-between bg-white p-4 rounded-2xl border border-amber-100">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-slate-100 overflow-hidden border border-slate-200">
                                    <img src="{{ $p->profile_photo_url ?? 'https://ui-avatars.com/api/?name='.$p->name }}" class="w-full h-full object-cover">
                                </div>
                                <div>
                                    <p class="text-sm font-bold text-slate-800">{{ $p->name }}</p>
                                    <p class="text-[10px] font-bold text-slate-400">{{ $p->email }}</p>
                                </div>
                            </div>
                            <div class="flex gap-2">
                                <button @click="accept({{ $p->id }})" class="px-4 py-2 bg-emerald-500 text-white rounded-xl text-[11px] font-black shadow-lg shadow-emerald-500/20 active:scale-95 transition-all">Terima</button>
                                <button @click="reject({{ $p->id }})" class="px-4 py-2 bg-slate-100 text-slate-500 rounded-xl text-[11px] font-black active:scale-95 transition-all">Tolak</button>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                <!-- Active Members Section -->
                <div class="p-8">
                    <div class="flex items-center gap-2 mb-6">
                        <span class="material-symbols-outlined text-slate-400">groups</span>
                        <h3 class="text-xs font-black text-slate-500 uppercase tracking-widest">Anggota Aktif</h3>
                    </div>
                    <div class="divide-y divide-slate-50">
                        @foreach($group->members()->wherePivot('status', 'Active')->get() as $m)
                        <div class="py-4 flex items-center justify-between group">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 rounded-2xl bg-slate-50 overflow-hidden border border-slate-100 transition-all group-hover:scale-105">
                                    <img src="{{ $m->profile_photo_url ?? 'https://ui-avatars.com/api/?name='.$m->name }}" class="w-full h-full object-cover">
                                </div>
                                <div>
                                    <p class="text-sm font-extrabold text-slate-800">{{ $m->name }}</p>
                                    <div class="flex items-center gap-2 mt-1">
                                        <span class="px-2 py-0.5 rounded-md text-[9px] font-black uppercase tracking-tight {{ $m->pivot->role == 'admin' ? 'bg-indigo-50 text-indigo-600' : 'bg-slate-100 text-slate-500' }}">
                                            {{ $m->pivot->role }}
                                        </span>
                                        <span class="text-[9px] font-bold text-slate-300">Bergabung {{ $m->pivot->created_at->format('M Y') }}</span>
                                    </div>
                                </div>
                            </div>
                            
                            @if(Auth::id() != $m->id)
                            <button @click="reject({{ $m->id }})" class="opacity-0 group-hover:opacity-100 transition-all w-9 h-9 rounded-xl bg-red-50 text-red-400 flex items-center justify-center hover:bg-red-500 hover:text-white active:scale-95 shadow-sm">
                                <span class="material-symbols-outlined text-[18px]">person_remove</span>
                            </button>
                            @else
                            <span class="text-[10px] font-black text-slate-300 italic">Anda</span>
                            @endif
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Invitation Widget -->
            <div class="bg-indigo-600 rounded-[32px] p-8 text-white relative overflow-hidden shadow-xl shadow-indigo-500/20">
                <div class="relative z-10">
                    <h3 class="text-lg font-black tracking-tight mb-2">Ajak Rekan Berkolaborasi</h3>
                    <p class="text-sm text-indigo-100 font-medium mb-6 opacity-80">Bagikan kode undangan unik ini. Pendaftaran anggota baru wajib disetujui oleh Admin grup.</p>
                    
                    <div class="flex flex-col gap-3">
                        <div class="flex items-center gap-3">
                            <div class="flex-1 bg-white/10 backdrop-blur-md rounded-2xl p-4 border border-white/20 flex items-center justify-center">
                                <span class="text-2xl font-black tracking-[10px] font-mono">{{ $group->invite_code }}</span>
                            </div>
                            <button onclick="navigator.clipboard.writeText('{{ $group->invite_code }}'); alert('Kode disalin!')" 
                                    class="w-14 h-14 bg-white text-indigo-600 rounded-2xl flex items-center justify-center active:scale-90 transition-all shadow-lg"
                                    title="Salin Kode">
                                <span class="material-symbols-outlined">content_copy</span>
                            </button>
                        </div>
                        
                        <!-- Full Invitation Link -->
                        <button onclick="navigator.clipboard.writeText('{{ route('groups.join', ['code' => $group->invite_code]) }}'); alert('Link undangan disalin!')" 
                                class="w-full bg-white/10 hover:bg-white/20 border border-white/20 py-3 rounded-xl text-[11px] font-black uppercase tracking-widest transition-all flex items-center justify-center gap-2 active:scale-95">
                            <span class="material-symbols-outlined text-[16px]">link</span>
                            Salin Link Undangan
                        </button>
                    </div>
                </div>
                <!-- Abstract Design Elements -->
                <div class="absolute -right-8 -bottom-8 w-48 h-48 bg-white/10 rounded-full blur-3xl"></div>
                <div class="absolute -left-8 -top-8 w-24 h-24 bg-indigo-400/20 rounded-full blur-2xl"></div>
            </div>
        </div>
    </div>
</x-app-layout>
