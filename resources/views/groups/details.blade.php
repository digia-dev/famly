<x-app-layout>
    @section('title', $group->name . ' - Workspace')

    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif !important; background-color: #F7F9FA; }
        .glass-card { background: rgba(255, 255, 255, 0.8); backdrop-filter: blur(10px); border: 1px solid rgba(255, 255, 255, 0.5); }
        .tab-active { color: #00AA13; border-bottom: 2px solid #00AA13; }
    </style>

    <div class="min-h-screen pb-32">
        <!-- Header -->
        <div class="bg-white px-6 pt-12 pb-8 rounded-b-[40px] shadow-sm border-b border-slate-50">
            <div class="max-w-2xl mx-auto">
                <div class="flex items-center justify-between mb-6">
                    <a href="{{ route('groups.index') }}" class="w-10 h-10 rounded-full bg-slate-50 flex items-center justify-center text-slate-400">
                        <span class="material-symbols-outlined">arrow_back</span>
                    </a>
                    <div class="px-3 py-1 bg-emerald-50 text-[#00AA13] rounded-full text-[10px] font-black uppercase tracking-widest">
                        {{ $group->type }}
                    </div>
                </div>

                <div class="flex items-center gap-4 mb-6">
                    <div class="w-16 h-16 rounded-[24px] bg-slate-100 flex items-center justify-center text-2xl font-black text-slate-300 shadow-inner">
                        {{ substr($group->name, 0, 1) }}
                    </div>
                    <div>
                        <h1 class="text-xl font-extrabold text-slate-800">{{ $group->name }}</h1>
                        <p class="text-[11px] font-bold text-slate-400 mt-0.5">Dibuat pada {{ $group->created_at->format('d M Y') }}</p>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div class="p-3 bg-slate-50 rounded-2xl border border-slate-100">
                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">Anggota Aktif</p>
                        <p class="text-lg font-black text-slate-800">{{ $group->members->count() }}</p>
                    </div>
                    <div class="p-3 bg-slate-50 rounded-2xl border border-slate-100">
                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">Status Grup</p>
                        <div class="flex items-center gap-1.5 mt-1">
                            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                            <p class="text-xs font-extrabold text-slate-700">Verified</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="max-w-2xl mx-auto px-6 mt-8">
            <!-- Invite Section -->
            <div class="glass-card p-6 rounded-[32px] shadow-sm mb-8 relative overflow-hidden">
                <div class="absolute top-0 right-0 p-4 opacity-10">
                    <span class="material-symbols-outlined text-[64px]">share</span>
                </div>
                <h3 class="text-sm font-extrabold text-slate-800 mb-2">Undang Anggota</h3>
                <p class="text-[11px] font-bold text-slate-400 mb-4 leading-relaxed">
                    Bagikan tautan berikut untuk mengajak anggota baru bergabung. Admin harus menyetujui setiap permintaan.
                </p>
                <div class="flex items-center gap-2">
                    <div class="flex-1 px-4 py-3 bg-slate-50 rounded-2xl border border-slate-100 text-[11px] font-bold text-slate-600 truncate">
                        {{ url('/join/' . $group->invite_code) }}
                    </div>
                    <button onclick="copyInviteLink('{{ url('/join/' . $group->invite_code) }}')" class="w-12 h-12 rounded-2xl bg-[#00AA13] text-white flex items-center justify-center shadow-lg shadow-emerald-500/20 active:scale-90 transition-all">
                        <span class="material-symbols-outlined text-[20px]">content_copy</span>
                    </button>
                </div>
            </div>

            <!-- Members List -->
            @if($pendingRequests && $pendingRequests->count() > 0)
                <div class="mb-8">
                    <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-4 px-2">Permintaan Bergabung ({{ $pendingRequests->count() }})</h3>
                    <div class="space-y-3">
                        @foreach($pendingRequests as $pending)
                            <div class="bg-white p-4 rounded-3xl border border-amber-100 flex items-center justify-between shadow-sm">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-amber-50 flex items-center justify-center text-amber-600 font-bold uppercase">
                                        {{ substr($pending->name, 0, 1) }}
                                    </div>
                                    <div>
                                        <p class="text-[12px] font-extrabold text-slate-800">{{ $pending->name }}</p>
                                        <p class="text-[10px] font-bold text-slate-400">{{ $pending->email }}</p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <form action="{{ route('groups.accept', [$group->id, $pending->id]) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="w-10 h-10 rounded-xl bg-emerald-500 text-white flex items-center justify-center shadow-md">
                                            <span class="material-symbols-outlined text-[18px]">check</span>
                                        </button>
                                    </form>
                                    <form action="{{ route('groups.reject', [$group->id, $pending->id]) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="w-10 h-10 rounded-xl bg-slate-100 text-slate-400 flex items-center justify-center">
                                            <span class="material-symbols-outlined text-[18px]">close</span>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="mb-8">
                <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-4 px-2">Daftar Anggota</h3>
                <div class="bg-white rounded-[32px] border border-slate-50 shadow-sm overflow-hidden">
                    @foreach($group->members as $member)
                        <div class="flex items-center justify-between p-4 border-b border-slate-50 last:border-0">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-slate-50 flex items-center justify-center text-slate-300 font-bold uppercase overflow-hidden">
                                    @if($member->avatar)
                                        <img src="{{ $member->avatar }}" class="w-full h-full object-cover">
                                    @else
                                        {{ substr($member->name, 0, 1) }}
                                    @endif
                                </div>
                                <div>
                                    <p class="text-[12px] font-extrabold text-slate-800 flex items-center gap-1.5">
                                        {{ $member->name }}
                                        @if($member->pivot->role == 'admin')
                                            <span class="px-1.5 py-0.5 bg-slate-100 text-slate-400 rounded text-[8px] font-black uppercase tracking-tighter">Admin</span>
                                        @endif
                                    </p>
                                    <p class="text-[10px] font-bold text-slate-400">Bergabung pada {{ $member->pivot->created_at ? $member->pivot->created_at->format('M Y') : 'Member Awal' }}</p>
                                </div>
                            </div>
                            @if(Auth::user()->can('manage', $group) && $member->id != Auth::id())
                                <button class="text-slate-300 hover:text-red-400 transition-colors">
                                    <span class="material-symbols-outlined text-[18px]">more_vert</span>
                                </button>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Danger Zone -->
            @if($group->isAdmin(Auth::id()))
                <div class="mt-12 p-6 rounded-[32px] border border-red-50 bg-red-50/20">
                    <h3 class="text-[11px] font-black text-red-500 uppercase tracking-widest mb-2">Zona Berbahaya</h3>
                    <p class="text-[10px] font-bold text-slate-400 mb-4">Menghapus grup akan menghilangkan seluruh data transaksi dan akses anggota secara permanen.</p>
                    <button class="px-6 py-3 bg-white border border-red-100 text-red-500 text-[11px] font-black rounded-2xl shadow-sm active:scale-95 transition-all">
                        HAPUS GRUP SELAMANYA
                    </button>
                </div>
            @endif
        </div>
    </div>

    <script>
        function copyInviteLink(link) {
            navigator.clipboard.writeText(link);
            alert('Link undangan berhasil disalin!');
        }
    </script>
</x-app-layout>
