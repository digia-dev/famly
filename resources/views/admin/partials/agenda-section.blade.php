<!-- Agenda Section (Gojek Style) -->
<section class="bg-white p-4 rounded-2xl shadow-sm border border-slate-100">
    <div class="flex justify-between items-center mb-4">
        <div>
            <h2 class="text-[13px] font-bold text-slate-900 tracking-tight">Agenda Terdekat</h2>
            <p class="text-[11px] text-slate-400 mt-0.5 font-medium">Kegiatan terjadwal</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('agenda.index') }}" class="w-7 h-7 flex items-center justify-center bg-primary/10 text-primary rounded-lg hover:bg-primary hover:text-white transition-all">
                <span class="material-symbols-outlined text-[18px]">add</span>
            </a>
            <a href="{{ route('agenda.index') }}" class="text-[11px] font-bold text-primary hover:underline px-1">Lihat Semua</a>
        </div>
    </div>

    <div class="space-y-3">
        @forelse($agendas as $item)
            <div class="flex items-center justify-between p-3 bg-slate-50/50 rounded-xl border border-slate-100/50 {{ $item->status == 'done' ? 'opacity-40' : '' }}">
                <div class="flex items-center gap-3 w-full">
                    <div class="flex items-center justify-center">
                        @if($item->status != 'done')
                            <form action="{{ route('agenda.complete', $item->id) }}" method="POST" class="m-0">
                                @csrf
                                <input type="hidden" name="tanggal_peristiwa" value="{{ now()->format('Y-m-d') }}">
                                <button type="submit" class="w-5 h-5 rounded-md border-2 border-slate-200 hover:border-primary transition-colors"></button>
                            </form>
                        @else
                            <div class="w-5 h-5 bg-primary rounded-md flex items-center justify-center">
                                <span class="material-symbols-outlined text-white text-[12px] font-bold">check</span>
                            </div>
                        @endif
                    </div>
                    
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2">
                            <p class="text-[12px] font-semibold text-slate-800 {{ $item->status == 'done' ? 'line-through text-slate-400' : '' }} truncate">
                                {{ $item->nama }}
                            </p>
                            @if($item->status != 'done' && $item->jatuh_tempo->diffInDays(now()) <= 3)
                                <span class="text-[8px] bg-red-100 text-red-600 px-1.5 py-0.5 rounded font-black uppercase tracking-tighter">Urgent</span>
                            @endif
                        </div>
                        <p class="text-[10px] text-slate-400 mt-0.5 flex items-center gap-1 font-medium">
                            @if($item->status == 'done')
                                Selesai
                            @else
                                {{ $item->jatuh_tempo->locale('id')->isoFormat('D MMM') }} • <span class="text-primary/60">Tugas</span>
                            @endif
                        </p>
                    </div>
                </div>
            </div>
        @empty
            <div class="py-12 flex flex-col items-center justify-center opacity-30">
                <span class="material-symbols-outlined text-3xl mb-1">event_busy</span>
                <p class="text-[11px] font-medium text-slate-400">Belum ada agenda terdekat</p>
            </div>
        @endforelse
    </div>
</section>
