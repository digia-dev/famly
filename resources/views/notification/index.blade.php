<x-app-layout>
    @section('title', 'Notifikasi')

    <main class="pt-4 pb-24 px-4 max-w-screen-xl mx-auto space-y-4">
        <!-- Floating Mark as Read Action -->
        <div class="flex justify-end px-1">
            <button class="text-primary text-[10px] font-black uppercase tracking-wider bg-white border border-slate-100 shadow-sm px-3 py-1.5 rounded-xl active:scale-95 transition-all">
                Tandai Dibaca
            </button>
        </div>

        <div class="flex flex-col gap-4">
            
            {{-- AKTIVITAS KEUANGAN (High Density) --}}
            @if(count($notifications['financial']) > 0)
                <div class="space-y-2">
                    <div class="flex items-center gap-1.5 px-1">
                        <span class="material-symbols-outlined text-primary text-[16px]">notifications_active</span>
                        <span class="text-[11px] font-bold text-slate-800 tracking-tight">Transaksi & Aset</span>
                    </div>

                    <div class="grid grid-cols-1 gap-2">
                        @foreach($notifications['financial'] as $item)
                            <div class="bg-white p-3 rounded-2xl border border-slate-100 shadow-sm flex items-center gap-3 transition-all active:scale-[0.99] cursor-pointer">
                                <div class="w-10 h-10 rounded-xl bg-emerald-50 flex items-center justify-center text-emerald-600 flex-shrink-0">
                                    <span class="material-symbols-outlined text-[20px]" style="font-variation-settings: 'FILL' 1;">payments</span>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex justify-between items-start">
                                        <h3 class="text-[12px] font-bold text-slate-900 truncate pr-2">{{ $item['title'] }}</h3>
                                        <span class="text-[9px] font-bold text-slate-400 whitespace-nowrap">{{ $item['time'] }}</span>
                                    </div>
                                    <p class="text-[10px] text-slate-500 font-medium leading-tight mt-0.5 line-clamp-1">
                                        {{ $item['amount'] }} via {{ $item['category'] }}
                                    </p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- PENGINGAT TAGIHAN (High Density) --}}
            @if(count($notifications['bills']) > 0)
                <div class="space-y-2">
                    <div class="flex items-center gap-1.5 px-1">
                        <span class="material-symbols-outlined text-red-500 text-[16px]">event_busy</span>
                        <span class="text-[11px] font-bold text-slate-800 tracking-tight">Tagihan & Jadwal</span>
                    </div>

                    <div class="space-y-2">
                        @foreach($notifications['bills'] as $bill)
                            <div class="bg-white p-3 rounded-2xl border border-slate-100 shadow-sm flex gap-3 items-center group cursor-pointer active:scale-[0.99] transition-all">
                                <div class="w-10 h-10 flex-shrink-0 {{ $bill['status_type'] == 'error' ? 'bg-red-50 text-red-600' : 'bg-amber-50 text-amber-600' }} flex items-center justify-center rounded-xl">
                                    <span class="material-symbols-outlined text-[20px]">{{ $bill['icon'] }}</span>
                                </div>
                                <div class="flex-grow min-w-0">
                                    <div class="flex justify-between items-center">
                                        <span class="text-[12px] font-bold text-slate-900 truncate pr-2">{{ $bill['title'] }}</span>
                                        <span class="text-[9px] font-bold {{ $bill['status_type'] == 'error' ? 'text-red-500 bg-red-50' : 'text-amber-600 bg-amber-50' }} px-1.5 py-0.5 rounded-lg ml-2">{{ $bill['status'] }}</span>
                                    </div>
                                    <p class="text-[10px] text-slate-400 font-medium mt-0.5 truncate">{{ $bill['detail'] }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- UPDATE TUGAS (High Density) --}}
            @if(count($notifications['tasks']) > 0)
                <div class="space-y-2 pb-4">
                    <div class="flex items-center gap-1.5 px-1">
                        <span class="material-symbols-outlined text-blue-500 text-[16px]">stars</span>
                        <span class="text-[11px] font-bold text-slate-800 tracking-tight">Kinerja Keluarga</span>
                    </div>

                    <div class="grid grid-cols-1 gap-2">
                        @foreach($notifications['tasks'] as $task)
                            <div class="bg-white p-2.5 rounded-2xl border border-slate-100 flex items-center gap-3 active:scale-[0.99] transition-all cursor-pointer">
                                <img class="w-9 h-9 rounded-xl object-cover" src="{{ $task['avatar'] }}" alt="{{ $task['user'] }}">
                                <div class="flex-grow min-w-0">
                                    <p class="text-[11px] text-slate-800 leading-tight">
                                        <span class="font-bold text-slate-900">{{ $task['user'] }}</span> <span class="text-slate-500">menyelesaikan</span> {{ $task['task'] }}
                                    </p>
                                    <span class="text-[9px] text-slate-400 font-bold mt-1 inline-block">{{ $task['time'] }}</span>
                                </div>
                                <div class="flex flex-col items-center justify-center bg-blue-50/50 px-2 py-1 rounded-xl min-w-[40px]">
                                    <span class="text-[10px] font-bold text-blue-600">+{{ $task['points'] }}</span>
                                    <span class="text-[8px] font-bold text-blue-400 tracking-tighter uppercase">POIN</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Weekly Analysis Insight Card --}}
            <div class="bg-emerald-900 p-5 rounded-3xl text-center relative overflow-hidden shadow-xl shadow-emerald-900/10">
                <div class="absolute -right-4 -top-0 w-24 h-24 bg-white/5 rounded-full blur-2xl"></div>
                <div class="w-10 h-10 bg-white/10 mx-auto mb-3 flex items-center justify-center rounded-xl backdrop-blur-md">
                    <span class="material-symbols-outlined text-white text-[20px]">insights</span>
                </div>
                <h4 class="text-[13px] font-bold text-white mb-1">Laporan Mingguan Siap!</h4>
                <p class="text-[10px] text-white/60 font-medium mb-4 leading-normal">Kami telah menyusun analisis efisiensi keuangan keluarga untuk minggu ini.</p>
                <button class="w-full bg-white text-emerald-900 py-2.5 rounded-2xl text-[11px] font-bold active:scale-[0.98] transition-all">
                    Buka Laporan
                </button>
            </div>
        </div>
    </main>
</x-app-layout>
