<div class="group relative bg-surface-lowest rounded-2xl shadow-sm p-6 hover:shadow-[0px_12px_32px_rgba(26,28,31,0.06)] transition-all duration-500 hover:-translate-y-1 flex flex-col justify-between">
    <div class="relative z-10">
        <div class="flex items-center justify-between mb-4">
            <div class="bg-rose-500/10 rounded-xl p-3">
                <i class="fas fa-arrow-trend-down text-rose-600 text-xl"></i>
            </div>
        </div>
        <div>
            <p class="text-on-surface-variant text-xs uppercase tracking-widest font-semibold mb-2">Pengeluaran</p>
            <p class="text-on-surface text-2xl lg:text-3xl font-bold mb-3">
                {{ App\Helpers\DashboardGreetingHelper::formatRupiah($pengeluaranBulanIni) }}
            </p>
            <div class="flex items-center text-on-surface-variant/80 text-xs">
                <i class="fas fa-calendar-alt mr-2"></i>
                <span>{{ \Carbon\Carbon::now()->locale('id')->isoFormat('MMMM Y') }}</span>
            </div>
        </div>
    </div>
</div>