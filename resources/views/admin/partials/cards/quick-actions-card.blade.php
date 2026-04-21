<div class="group relative bg-surface-lowest rounded-2xl shadow-sm p-6 hover:shadow-[0px_12px_32px_rgba(26,28,31,0.06)] transition-all duration-500 hover:-translate-y-1">
    <div class="text-center mb-4">
        <div class="bg-surface-low w-14 h-14 rounded-2xl flex items-center justify-center mx-auto mb-3 group-hover:scale-110 transition-transform duration-300">
            <i class="fas fa-bolt text-tertiary text-xl"></i>
        </div>
        <h3 class="font-bold text-on-surface text-lg">Quick Actions</h3>
        <p class="text-on-surface-variant/80 text-xs mt-1">Aksi Cepat</p>
    </div>
    
    <div class="space-y-3">
        <a href="{{ route('tabungan.index') }}" class="block w-full text-center bg-surface-low text-primary font-semibold py-3 px-4 rounded-full hover:bg-surface-lowest border border-transparent hover:border-primary/20 transition-all duration-300">
            <i class="fas fa-wallet mr-2"></i>Kelola Tabungan
        </a>
        
        <button onclick="openModal()" class="w-full text-center bg-primary text-white font-semibold py-3 px-4 rounded-full hover:bg-primary-container transition-all duration-300 shadow-lg shadow-primary/20">
            <i class="fas fa-plus mr-2"></i>Tambah Transaksi
        </button>
    </div>
</div>