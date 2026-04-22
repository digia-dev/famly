<x-guest-layout>
    <div class="w-full max-w-sm px-6">
        <!-- Invitation Card -->
        <div class="bg-white rounded-[32px] overflow-hidden shadow-sm border border-slate-100 flex flex-col items-center text-center p-8 relative">
            <!-- Tonal Background Decoration -->
            <div class="absolute top-0 inset-x-0 h-32 bg-[#00AA13]/5 z-0"></div>
            
            <!-- Group Avatar -->
            <div class="w-24 h-24 bg-white rounded-full flex items-center justify-center border-4 border-white shadow-md relative z-10 mb-6">
                <div class="w-20 h-20 bg-[#00AA13] rounded-full flex items-center justify-center text-white">
                    <span class="material-symbols-outlined text-4xl">
                        {{ $group->type === 'Family' ? 'family_history' : ($group->type === 'Corporate' ? 'business' : 'group') }}
                    </span>
                </div>
            </div>

            <!-- Group Info -->
            <div class="relative z-10">
                <div class="inline-flex items-center px-3 py-1 rounded-full bg-[#00AA13]/10 text-[#00AA13] text-xs font-bold uppercase tracking-wider mb-3">
                    {{ $group->type }}
                </div>
                <h1 class="text-2xl font-extrabold text-slate-900 mb-2 leading-tight">
                    {{ $group->name }}
                </div>
                <p class="text-sm text-slate-500 mb-8 max-w-[240px] mx-auto">
                    Undangan untuk bergabung dan mengelola keuangan bersama di Famly.
                </p>

                <!-- Admin Info -->
                <div class="flex items-center justify-center space-x-3 mb-10 bg-slate-50 py-3 px-4 rounded-2xl border border-slate-100">
                    <div class="w-8 h-8 rounded-full bg-slate-200 flex items-center justify-center">
                        <span class="material-symbols-outlined text-slate-500 text-sm">person</span>
                    </div>
                    <div class="text-left">
                        <p class="text-[10px] text-slate-400 uppercase font-bold leading-none mb-0.5">Admin</p>
                        <p class="text-xs font-bold text-slate-700">{{ $group->admin_id ? \App\Models\User::find($group->admin_id)->name : 'System Admin' }}</p>
                    </div>
                </div>

                <!-- Action Button -->
                <form action="{{ route('groups.join.request', $group->invite_code) }}" method="POST" class="w-full">
                    @csrf
                    @auth
                        <button type="submit" class="w-full h-14 bg-[#00AA13] hover:bg-[#008f10] text-white rounded-2xl font-bold flex items-center justify-center space-x-3 transition-all active:scale-95 shadow-lg shadow-[#00AA13]/20">
                            <span>Gabung ke Grup</span>
                            <span class="material-symbols-outlined">arrow_forward</span>
                        </button>
                    @else
                        <a href="{{ route('login', ['redirect' => route('groups.join', $group->invite_code)]) }}" class="w-full h-14 bg-slate-900 hover:bg-black text-white rounded-2xl font-bold flex items-center justify-center space-x-3 transition-all active:scale-95 shadow-lg shadow-black/20">
                            <span>Login untuk Gabung</span>
                            <span class="material-symbols-outlined">login</span>
                        </a>
                    @endauth
                </form>
            </div>
        </div>

        <!-- Footer Help -->
        <p class="text-center mt-8 text-[11px] text-slate-400 uppercase font-bold tracking-widest leading-relaxed">
            Keuangan Teratur, Keluarga Makmur.<br>
            <span class="text-[#00AA13]">FAMLY</span> INDONESIA
        </p>
    </div>
</x-guest-layout>
