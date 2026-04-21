<x-app-layout>
    @section('title', 'Edit Agenda')

    <main class="pt-14 pb-24 px-4 max-w-screen-xl mx-auto space-y-6">
        <!-- Header -->
        <div class="flex items-center gap-4">
            <a href="javascript:history.back()" class="w-10 h-10 rounded-full bg-white flex items-center justify-center text-slate-400 shadow-sm border border-slate-100 active:scale-90 transition-transform">
                <span class="material-symbols-outlined">arrow_back</span>
            </a>
            <div>
                <h1 class="text-[20px] font-black text-slate-900 tracking-tight">Edit Agenda</h1>
                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">Ubah detail atau jadwal agenda</p>
            </div>
        </div>

        <form action="{{ route('agenda.update', $plannedTransaction->id) }}" method="POST" class="space-y-4">
            @csrf
            @method('PUT')
            
            <!-- Category Selection (Interactive Tiles) -->
            <div class="space-y-2">
                <label class="text-[11px] font-black text-slate-400 uppercase tracking-widest px-1">Pilih Jenis Agenda</label>
                <div class="grid grid-cols-2 gap-2">
                    @foreach($jenisKategori as $jenis)
                        <label class="relative cursor-pointer group">
                            <input type="radio" name="jenis" value="{{ $jenis->id }}" class="peer sr-only" {{ $plannedTransaction->jenis == $jenis->id ? 'checked' : '' }}>
                            <div class="p-4 bg-white rounded-2xl border-2 border-slate-100 peer-checked:border-primary peer-checked:bg-primary/5 transition-all flex flex-col items-center gap-2 text-center group-active:scale-95">
                                <div class="w-8 h-8 rounded-lg bg-slate-100 flex items-center justify-center text-slate-400 peer-checked:bg-primary/20 peer-checked:text-primary transition-colors">
                                    <span class="material-symbols-outlined text-[20px]">
                                        @if(str_contains(strtolower($jenis->nama), 'tagihan')) payments @elseif(str_contains(strtolower($jenis->nama), 'tabungan')) account_balance_wallet @else event_repeat @endif
                                    </span>
                                </div>
                                <span class="text-[12px] font-bold text-slate-600 peer-checked:text-primary transition-colors">{{ $jenis->nama }}</span>
                            </div>
                        </label>
                    @endforeach
                </div>
            </div>

            <!-- Detail Information -->
            <div class="bg-white p-5 rounded-3xl border border-slate-100 shadow-sm space-y-6">
                <!-- Select Kategori Nama -->
                <div class="space-y-2">
                    <label class="text-[11px] font-black text-slate-400 uppercase tracking-widest">Kategori / Pos Dompet</label>
                    <div class="relative">
                        <select name="nama" class="w-full bg-slate-50 border-none rounded-2xl py-3.5 pl-11 pr-4 text-[13px] font-bold text-slate-800 focus:ring-2 focus:ring-primary/20 appearance-none">
                            @foreach($namaKategori as $nama)
                                <option value="{{ $nama->id }}" {{ $plannedTransaction->nama == $nama->id ? 'selected' : '' }}>{{ $nama->nama }}</option>
                            @endforeach
                        </select>
                        <span class="material-symbols-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-[20px]">label</span>
                        <span class="material-symbols-outlined absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none">expand_more</span>
                    </div>
                </div>

                <!-- Nominal -->
                <div class="space-y-2">
                    <label class="text-[11px] font-black text-slate-400 uppercase tracking-widest">Nominal</label>
                    <div class="relative">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 font-black text-slate-300 text-[13px]">Rp</span>
                        <input type="number" name="nominal" placeholder="0" value="{{ (int)$plannedTransaction->nominal }}"
                               class="w-full bg-slate-50 border-none rounded-2xl py-3.5 pl-11 pr-4 text-[18px] font-black text-slate-900 focus:ring-2 focus:ring-primary/20 placeholder:text-slate-200">
                    </div>
                </div>

                <!-- Jatuh Tempo -->
                <div class="space-y-2">
                    <label class="text-[11px] font-black text-slate-400 uppercase tracking-widest">Jatuh Tempo / Tanggal Tugas</label>
                    <div class="relative">
                        <input type="date" name="jatuh_tempo" value="{{ $plannedTransaction->jatuh_tempo->format('Y-m-d') }}"
                               class="w-full bg-slate-50 border-none rounded-2xl py-3.5 pl-11 pr-4 text-[13px] font-bold text-slate-800 focus:ring-2 focus:ring-primary/20">
                        <span class="material-symbols-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-[20px]">calendar_today</span>
                    </div>
                </div>

                <!-- Keterangan -->
                <div class="space-y-2">
                    <label class="text-[11px] font-black text-slate-400 uppercase tracking-widest">Catatan / Detail Tugas</label>
                    <div class="relative">
                        <textarea name="keterangan" rows="3" placeholder="Detail tugas..."
                                  class="w-full bg-slate-50 border-none rounded-2xl py-4 pl-11 pr-4 text-[13px] font-bold text-slate-800 focus:ring-2 focus:ring-primary/20 placeholder:text-slate-300">{{ $plannedTransaction->keterangan }}</textarea>
                        <span class="material-symbols-outlined absolute left-3.5 top-4 text-slate-400 text-[20px]">notes</span>
                    </div>
                </div>
            </div>

            <!-- Delete Button (New Action) -->
            <div class="flex flex-col gap-3">
                <button type="submit" class="w-full bg-primary text-white py-4 rounded-2xl font-black text-[14px] uppercase tracking-widest shadow-lg shadow-primary/20 active:scale-95 transition-all">
                    Simpan Perubahan
                </button>
            </div>
        </form>

        <form action="{{ route('agenda.destroy', $plannedTransaction->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus agenda ini?');">
            @csrf
            @method('DELETE')
            <button type="submit" class="w-full bg-red-50 text-red-600 py-3 rounded-2xl font-bold text-[11px] uppercase tracking-[0.2em] border border-red-100 active:scale-95 transition-all">
                Hapus Agenda
            </button>
        </form>
    </main>
</x-app-layout>
