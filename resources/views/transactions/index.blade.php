<x-app-layout>
    @section('title', 'Log Keuangan')

    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif !important; background-color: #F7F9FA; }
        .no-line-card { background-color: #FFFFFF; border: none; box-shadow: 0 4px 20px -10px rgba(0,0,0,0.05); }
        .tonal-bg { background-color: #F0F2F5; }
        .sticky-custom { position: sticky; top: 0; z-index: 50; }
    </style>

    <div class="min-h-screen pb-40">
        <!-- Dashboard Style Header -->
        <div class="bg-white px-6 pt-12 pb-6 rounded-b-[40px] shadow-sm mb-6">
            <div class="max-w-2xl mx-auto flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-extrabold text-slate-800 tracking-tight">Log Keuangan</h1>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1">
                        {{ $activeGroup ? $activeGroup->name . ' (Grup)' : 'Pribadi' }}
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    @foreach(['week' => 'Minggu', 'month' => 'Bulan'] as $key => $label)
                        <a href="{{ route('transaction.index', ['filter' => $key]) }}" 
                           class="px-4 py-2 text-[10px] font-black uppercase tracking-widest rounded-2xl transition-all {{ $filter == $key ? 'bg-[#00AA13] text-white shadow-lg shadow-emerald-500/20' : 'bg-slate-50 text-slate-400' }}">
                            {{ $label }}
                        </a>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="max-w-2xl mx-auto px-6 space-y-8">
            @forelse($groupedTransactions as $dateLabel => $data)
                <div>
                    <div class="flex items-center justify-between mb-4 sticky top-16 bg-[#F7F9FA]/80 backdrop-blur-md py-2 z-30">
                        <h3 class="text-[11px] font-black text-slate-400 uppercase tracking-widest">
                            {{ $data['date']->isToday() ? 'Hari Ini' : ($data['date']->isYesterday() ? 'Kemarin' : $dateLabel) }}
                        </h3>
                        <div class="px-3 py-1 tonal-bg rounded-full text-[10px] font-extrabold text-slate-600">
                            Net: <span class="{{ $data['net'] >= 0 ? 'text-[#00AA13]' : 'text-rose-500' }}">
                                {{ $data['net'] >= 0 ? '+' : '-' }}Rp{{ number_format(abs($data['net']), 0, ',', '.') }}
                            </span>
                        </div>
                    </div>

                    <div class="space-y-3">
                        @foreach($data['items'] as $item)
                            <div class="no-line-card p-4 rounded-[28px] flex items-center justify-between group active:scale-[0.98] transition-all cursor-pointer">
                                <div class="flex items-center gap-4">
                                    <div class="w-12 h-12 rounded-[20px] {{ $item->kategoriJenis->jenis == 'Pemasukan' ? 'bg-emerald-50 text-[#00AA13]' : 'bg-slate-50 text-slate-500' }} flex items-center justify-center transition-colors">
                                        <span class="material-symbols-outlined text-[22px]" style="font-variation-settings: 'FILL' 1;">
                                            {{ $item->kategoriNama->icon ?? ($item->kategoriJenis->jenis == 'Pemasukan' ? 'add_card' : 'payments') }}
                                        </span>
                                    </div>
                                    <div>
                                        <p class="text-[13px] font-extrabold text-slate-800 leading-tight">
                                            {{ $item->keterangan ?? ($item->kategoriNama->nama ?? 'Tanpa Keterangan') }}
                                        </p>
                                        <div class="flex items-center gap-2 mt-1">
                                            <span class="text-[10px] font-bold text-slate-400">{{ $item->kategoriNama->nama ?? 'Umum' }}</span>
                                            <span class="w-1 h-1 rounded-full bg-slate-200"></span>
                                            <span class="text-[10px] font-bold text-slate-300 uppercase tracking-tighter">{{ $item->created_at->format('H:i') }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="text-[14px] font-black {{ $item->kategoriJenis->jenis == 'Pemasukan' ? 'text-[#00AA13]' : 'text-slate-900' }}">
                                        {{ $item->kategoriJenis->jenis == 'Pemasukan' ? '+' : '-' }}Rp{{ number_format($item->nominal, 0, ',', '.') }}
                                    </p>
                                    @if($item->wallet)
                                        <p class="text-[9px] font-black text-slate-300 uppercase mt-0.5">{{ $item->wallet->nama }}</p>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @empty
                <div class="py-20 text-center">
                    <div class="w-20 h-20 bg-slate-50 rounded-[32px] flex items-center justify-center mx-auto mb-6">
                        <span class="material-symbols-outlined text-[32px] text-slate-200">history_toggle_off</span>
                    </div>
                    <p class="text-[13px] font-extrabold text-slate-800">Belum ada catatan</p>
                    <p class="text-[11px] font-bold text-slate-400 mt-1 leading-relaxed max-w-[200px] mx-auto">
                        Mulai catat transaksi Anda melalui @Fams atau tombol tambah.
                    </p>
                </div>
            @endforelse
        </div>

        <!-- Floating Insight CTA -->
        <div class="fixed bottom-24 left-6 right-6 z-50">
            <a href="{{ route('reports.analysis') }}" class="max-w-xl mx-auto bg-slate-900 py-4 px-6 rounded-[30px] shadow-2xl flex items-center justify-between group active:scale-95 transition-all">
                <div class="flex items-center gap-4">
                    <div class="w-10 h-10 rounded-2xl bg-white/10 flex items-center justify-center text-white">
                        <span class="material-symbols-outlined text-[20px]">smart_toy</span>
                    </div>
                    <div>
                        <p class="text-[12px] font-black text-white tracking-tight">Butuh Insight AI?</p>
                        <p class="text-[10px] font-bold text-white/40">Lihat analisis pengeluaran mingguan Anda</p>
                    </div>
                </div>
                <span class="material-symbols-outlined text-white/50 group-hover:text-white group-hover:translate-x-1 transition-all">arrow_forward</span>
            </a>
        </div>
    </div>
</x-app-layout>
