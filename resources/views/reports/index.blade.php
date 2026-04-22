@extends('layouts.app')

@section('title', 'Laporan Keuangan')

@section('content')
<div class="min-h-screen bg-[#F7F9FA] pb-24">
    <!-- Compact FamlyUI Header -->
    <div class="bg-white px-4 pt-6 pb-3 sticky top-0 z-30 shadow-[0_1px_0_0_rgba(0,0,0,0.02)]">
        <div class="flex items-center justify-between mb-3 px-1">
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.dashboard') }}" class="w-8 h-8 rounded-full hover:bg-slate-50 flex items-center justify-center text-slate-800 transition-all">
                    <span class="material-symbols-outlined text-[22px]">arrow_back</span>
                </a>
                <h1 class="text-[17px] font-black tracking-tight text-slate-900">Laporan</h1>
            </div>
            <div class="flex gap-1.5">
                <button class="w-8 h-8 bg-slate-50 rounded-full flex items-center justify-center text-slate-500 hover:bg-slate-100 transition-all">
                    <span class="material-symbols-outlined text-[18px]">share</span>
                </button>
                @php $hasPaidThisMonth = session('paid_month_' . $month . '_' . $year); @endphp
                @if($isPremium || $hasPaidThisMonth)
                <a href="{{ route('reports.download', ['month' => $month, 'year' => $year]) }}" class="w-8 h-8 bg-primary/10 rounded-full flex items-center justify-center text-primary hover:bg-primary/20 transition-all">
                    <span class="material-symbols-outlined text-[18px]">download</span>
                </a>
                @else
                <button onclick="window.location.hash = 'download-cta'" class="w-8 h-8 bg-slate-50 rounded-full flex items-center justify-center text-slate-400">
                    <span class="material-symbols-outlined text-[18px]">lock</span>
                </button>
                @endif
            </div>
        </div>

        <!-- Month Filter -->
        <div class="flex items-center gap-1.5 overflow-x-auto no-scrollbar py-1">
            @php $months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des']; @endphp
            @foreach($months as $idx => $m)
                @php 
                    $mIdx = $idx + 1; 
                    $isLockedMonth = !$isPremium && 
                                     \Carbon\Carbon::createFromDate($year, $mIdx, 1)->lt(now()->startOfMonth()) && 
                                     !session('paid_month_' . $mIdx . '_' . $year);
                @endphp
                <a href="{{ $isLockedMonth ? '#' : route('reports.index', ['month' => $mIdx, 'year' => $year]) }}" 
                   @if($isLockedMonth) onclick="window.location.hash = 'download-cta'; return false;" @endif
                   class="flex-none px-4 py-1.5 rounded-full text-[12px] font-bold transition-all flex items-center gap-1.5 {{ $month == $mIdx ? 'bg-primary text-white shadow-md' : 'bg-white border border-slate-50 text-slate-400 hover:bg-slate-100' }} {{ $isLockedMonth ? 'opacity-50 grayscale-[0.5]' : '' }}">
                    @if($isLockedMonth)
                        <span class="material-symbols-outlined text-[12px]">lock</span>
                    @endif
                    {{ $m }}
                </a>
            @endforeach
        </div>
    </div>

    <div class="px-4 pt-4 space-y-4">
        <!-- Flash Messages -->
        @if(is_array(session('success')) || (session('success') && is_string(session('success'))))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)" 
             class="bg-emerald-50 p-4 rounded-2xl flex items-center gap-3 shadow-sm border border-emerald-100/50">
            <span class="material-symbols-outlined text-emerald-500">check_circle</span>
            <p class="text-[12px] font-bold text-emerald-700">{{ is_array(session('success')) ? 'Berhasil!' : session('success') }}</p>
        </div>
        @endif

        @if(session('error'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)" 
             class="bg-rose-50 p-4 rounded-2xl flex items-center gap-3 shadow-sm border border-rose-100/50">
            <span class="material-symbols-outlined text-rose-500">warning</span>
            <p class="text-[12px] font-bold text-rose-700">{{ session('error') }}</p>
        </div>
        @endif

        <!-- Dashboard Summary Grid -->
        <div class="grid grid-cols-2 gap-3">
            <div class="bg-white p-4 rounded-2xl border border-slate-50 shadow-sm">
                <div class="flex items-center gap-2 mb-2">
                    <span class="w-1 h-3 bg-emerald-500 rounded-full"></span>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest leading-none">Inflow</p>
                </div>
                <div class="text-[16px] font-black text-slate-900 tracking-tighter">Rp {{ number_format($monthlySums->total_in, 0, ',', '.') }}</div>
            </div>
            <div class="bg-white p-4 rounded-2xl border border-slate-50 shadow-sm">
                <div class="flex items-center gap-2 mb-2">
                    <span class="w-1 h-3 bg-rose-500 rounded-full"></span>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest leading-none">Outflow</p>
                </div>
                <div class="text-[16px] font-black text-slate-900 tracking-tighter">Rp {{ number_format($monthlySums->total_out, 0, ',', '.') }}</div>
            </div>
        </div>

        <!-- Net Balance Banner -->
        <div class="bg-white px-5 py-4 rounded-2xl border border-slate-50 flex items-center justify-between shadow-sm">
            <div>
                <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mb-0.5">Selisih Bersih</p>
                <p class="text-[15px] font-black {{ ($monthlySums->total_in - $monthlySums->total_out) >= 0 ? 'text-emerald-500' : 'text-rose-500' }}">
                    {{ ($monthlySums->total_in - $monthlySums->total_out) >= 0 ? '+' : '' }} Rp {{ number_format($monthlySums->total_in - $monthlySums->total_out, 0, ',', '.') }}
                </p>
            </div>
            <div class="flex items-center gap-1.5 bg-primary/5 px-2.5 py-1.5 rounded-xl border border-primary/10">
                <span class="material-symbols-outlined text-primary text-[16px]">verified_user</span>
                <span class="text-[9px] font-black text-primary uppercase">Healthy</span>
            </div>
        </div>

        <!-- AI Insight -->
        <div class="bg-primary px-5 py-4 rounded-2xl text-white relative overflow-hidden shadow-lg shadow-primary/10 transition-all duration-500 group">
            <div class="absolute inset-0 bg-white/5 opacity-0 group-hover:opacity-100 transition-opacity"></div>
            <div class="flex items-center gap-2 mb-2">
                <span class="material-symbols-outlined text-[16px] text-white/80 animate-pulse">auto_awesome</span>
                <h3 class="text-[12px] font-black tracking-tight">AI Advisor</h3>
            </div>
            <p class="text-[12px] leading-snug font-medium opacity-90">
                {{ $aiInsight }}
            </p>
        </div>

        <!-- Download CTA -->
        <div id="download-cta" class="bg-white p-5 rounded-2xl border border-slate-50 shadow-sm text-center">
            @if($isPremium || $hasPaidThisMonth)
                <div class="w-10 h-10 bg-emerald-50 rounded-full flex items-center justify-center mx-auto mb-3 text-emerald-500">
                    <span class="material-symbols-outlined">description</span>
                </div>
                <h3 class="text-[13px] font-black text-slate-900 mb-1">Laporan PDF Siap</h3>
                <p class="text-[11px] font-medium text-slate-400 mb-4">Unduh detail transaksi bulan {{ $startDate->locale('id')->isoFormat('MMMM') }}.</p>
                <a href="{{ route('reports.download', ['month' => $month, 'year' => $year]) }}" target="_blank" class="block w-full bg-primary py-3 rounded-xl text-[11px] font-black text-white uppercase tracking-widest shadow-md shadow-primary/10 active:scale-95 transition-all">
                    Unduh Sekarang
                </a>
            @else
                <div class="w-10 h-10 bg-amber-50 rounded-full flex items-center justify-center mx-auto mb-3 text-amber-500">
                    <span class="material-symbols-outlined">lock_clock</span>
                </div>
                <h3 class="text-[13px] font-black text-slate-900 mb-1">Simpan History</h3>
                <p class="text-[10px] font-medium text-slate-400 mb-4 italic">Buka laporan bulan lampau untuk arsip abadi.</p>
                <div class="flex gap-2">
                    <a href="{{ route('checkout.index') }}" class="flex-[2] bg-primary py-3 rounded-xl text-[11px] font-black text-white uppercase tracking-widest shadow-md shadow-primary/10 transition-all flex items-center justify-center">
                        Premium 19k
                    </a>
                    <form action="{{ route('reports.buy-one-off') }}" method="POST" class="flex-1">
                        @csrf
                        <input type="hidden" name="month" value="{{ $month }}">
                        <input type="hidden" name="year" value="{{ $year }}">
                        <button type="submit" class="w-full bg-slate-50 border border-slate-50 py-3 rounded-xl text-[10px] font-black text-slate-500 uppercase tracking-widest transition-all">
                            Buka 4.9k
                        </button>
                    </form>
                </div>
            @endif
        </div>

        <!-- Compact Financial Structure -->
        <div class="space-y-2.5">
            <div class="flex items-center justify-between px-1">
                <h2 class="text-[14px] font-black text-slate-800 tracking-tight">Struktur Manajemen</h2>
                <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest">{{ $startDate->format('F Y') }}</span>
            </div>
            
            <div class="grid grid-cols-1 gap-2.5">
                @foreach($breakdown as $type => $data)
                    @php
                        $urlType = $type === 'wallet' ? 'dompet' : ($type === 'savings' ? 'tabungan' : 'pos');
                        $config = [
                            'pos' => ['label' => 'Pos Budgeting', 'icon' => 'query_stats', 'color' => 'bg-emerald-500'],
                            'wallet' => ['label' => 'Dompet & Tunai', 'icon' => 'account_balance_wallet', 'color' => 'bg-amber-500'],
                            'savings' => ['label' => 'Tabungan Impian', 'icon' => 'add_task', 'color' => 'bg-indigo-500'],
                        ][$type];
                    @endphp
                    <a href="{{ route('management.index', ['type' => $urlType]) }}" class="bg-white p-4 rounded-xl border border-slate-50 shadow-sm flex items-center justify-between group active:scale-[0.98] transition-all">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-xl {{ $config['color'] }} flex items-center justify-center text-white">
                                <span class="material-symbols-outlined text-[18px]">{{ $config['icon'] }}</span>
                            </div>
                            <div>
                                <h4 class="text-[13px] font-black text-slate-900 leading-none mb-1">{{ $config['label'] }}</h4>
                                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest leading-none">{{ $data['count'] }} Item</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <div class="text-right">
                                <div class="text-[14px] font-black text-slate-900 leading-none mb-1">Rp {{ number_format($data['income'] - $data['expense'], 0, ',', '.') }}</div>
                            </div>
                            <span class="material-symbols-outlined text-slate-300 text-[18px] group-hover:text-primary transition-colors">chevron_right</span>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>

        <!-- Transaction Log Compact -->
        <div class="space-y-3 pb-8">
            <div class="flex items-center justify-between px-1">
                <h2 class="text-[14px] font-black text-slate-800 tracking-tight">Log Terbaru</h2>
                <a href="{{ route('transaction.index') }}" class="text-[10px] font-bold text-slate-400 uppercase tracking-widest underline decoration-slate-200">History</a>
            </div>

            <div class="bg-white rounded-2xl overflow-hidden shadow-sm border border-slate-50">
                @forelse($transactions as $trx)
                    <div class="flex items-center gap-3 p-3.5 {{ !$loop->last ? 'border-b border-slate-50' : '' }} active:bg-slate-50 transition-colors">
                        <div class="w-8 h-8 rounded-lg bg-slate-50 flex items-center justify-center text-slate-400 border border-slate-100">
                            <span class="material-symbols-outlined text-[18px]">{{ $trx->kategoriNama->icon ?? 'payments' }}</span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h4 class="text-[12px] font-bold text-slate-800 truncate leading-none mb-0.5">{{ $trx->keterangan ?: ($trx->kategoriNama->nama ?? 'Trx') }}</h4>
                            <p class="text-[9px] text-slate-400 font-bold uppercase tracking-widest">{{ $trx->created_at->format('d M') }}</p>
                        </div>
                        <div class="text-right">
                            <div class="text-[13px] font-black {{ ($trx->kategoriJenis->jenis ?? '') === 'Pemasukan' ? 'text-primary' : 'text-rose-500' }} tracking-tighter mb-0.5">
                                {{ ($trx->kategoriJenis->jenis ?? '') === 'Pemasukan' ? '+' : '-' }}{{ number_format($trx->nominal, 0, ',', '.') }}
                            </div>
                            <p class="text-[8px] font-black text-slate-300 uppercase tracking-widest">{{ $trx->kategoriNama->nama ?? 'Other' }}</p>
                        </div>
                    </div>
                @empty
                    <div class="p-10 text-center text-slate-300">
                        <p class="text-[10px] font-black uppercase tracking-widest">Kosong</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

<style>
    .no-scrollbar::-webkit-scrollbar { display: none; }
    .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
</style>
@endsection
