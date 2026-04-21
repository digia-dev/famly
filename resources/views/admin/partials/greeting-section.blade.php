<!-- Total Balance Card -->
<div class="md:col-span-8 p-6 bg-gradient-to-br from-primary to-primary-container text-white rounded-sm relative overflow-hidden h-48 flex flex-col justify-between">
    <div class="z-10">
        <p class="font-['Helvetica'] text-[10px] font-bold tracking-widest opacity-80 uppercase">Saldo Total Keseluruhan</p>
        <h1 class="font-['Helvetica'] text-4xl font-extrabold tracking-tighter mt-1">
            {{ \App\Helpers\DashboardGreetingHelper::formatRupiah($saldoSaatIni) }}
        </h1>
    </div>
    <div class="z-10 flex gap-4">
        <div class="bg-white/10 backdrop-blur-md p-2 rounded-sm border border-white/5">
            <p class="text-[10px] opacity-70 uppercase font-bold">Pemasukan Bulan Ini</p>
            <p class="font-bold text-sm">+ {{ \App\Helpers\DashboardGreetingHelper::formatRupiah($pemasukanBulanIni) }}</p>
        </div>
        <div class="bg-white/10 backdrop-blur-md p-2 rounded-sm border border-white/5">
            <p class="text-[10px] opacity-70 uppercase font-bold">Pengeluaran Bulan Ini</p>
            <p class="font-bold text-sm">- {{ \App\Helpers\DashboardGreetingHelper::formatRupiah($pengeluaranBulanIni) }}</p>
        </div>
    </div>
</div>
