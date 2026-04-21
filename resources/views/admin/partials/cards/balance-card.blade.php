<div class="group relative bg-gradient-to-br from-primary to-primary-container rounded-2xl shadow-sm p-6 hover:shadow-[0px_12px_32px_rgba(26,28,31,0.06)] transition-all duration-500 hover:-translate-y-1">
    <div class="relative z-10">
        <div class="flex items-center justify-between mb-4">
            <div class="bg-white/20 backdrop-blur-sm rounded-xl p-3 shadow-inner">
                <i class="fas fa-wallet text-white text-xl"></i>
            </div>
            <div class="text-right">
                <div class="w-2 h-2 bg-white/40 rounded-full"></div>
            </div>
        </div>
        <div>
            <p class="text-white/80 text-xs uppercase tracking-widest font-semibold mb-2">Total Saldo</p>
            <p class="text-white text-2xl lg:text-3xl font-bold mb-3">
                {{ App\Helpers\DashboardGreetingHelper::formatRupiah($saldoSaatIni) }}
            </p>
            <div class="flex items-center text-white/80 text-xs">
                <i class="fa-solid fa-coins mr-2"></i>
                <span>Current Balance</span>
            </div>
        </div>
    </div>
</div>