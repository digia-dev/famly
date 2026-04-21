<x-app-layout>
    @section('title', $kategori->nama)

    <div class="bg-[#F6F7F8] min-h-screen" 
         x-data="{ 
            isEditing: false, 
            currentName: '{{ $kategori->nama }}',
            tempName: '{{ $kategori->nama }}',
            openMenu: false,
            showReminderModal: false,
            reminderSuccess: false,
            threshold: 80,
            saveName() {
                if (this.tempName.trim() === '') return;
                fetch('{{ route('management.update-name', $kategori->id) }}', {
                    method: 'PATCH',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: JSON.stringify({ nama: this.tempName })
                })
                .then(res => res.json())
                .then(data => { if (data.success) { this.currentName = data.nama; this.isEditing = false; } });
            },
            saveReminder() {
                this.reminderSuccess = true;
                setTimeout(() => { this.showReminderModal = false; this.reminderSuccess = false; }, 1500);
            }
         }">
        
        <!-- Compact Toolbar Header -->
        <header class="fixed top-0 w-full z-50 bg-white/80 backdrop-blur-lg flex items-center justify-between px-4 h-12 border-b border-slate-100">
            <div class="flex items-center gap-2 flex-1 min-w-0">
                <a href="{{ route('management.index', ['type' => $kategori->wallet_type == 'wallet' ? 'dompet' : ($kategori->wallet_type == 'savings' ? 'tabungan' : 'pos')]) }}" class="w-8 h-8 flex items-center justify-center text-slate-400">
                    <span class="material-symbols-outlined text-[20px]">chevron_left</span>
                </a>
                
                <div class="flex-1 min-w-0">
                    <h1 x-show="!isEditing" @click="isEditing = true; tempName = currentName" 
                        class="font-bold text-[13px] text-slate-900 truncate cursor-pointer tracking-tight"
                        x-text="currentName">
                    </h1>
                    <div x-show="isEditing" class="flex items-center gap-2" x-cloak>
                        <input type="text" x-model="tempName" @keyup.enter="saveName()" @keyup.escape="isEditing = false"
                               class="bg-slate-50 border-none px-2 py-0.5 text-[13px] font-bold text-slate-900 rounded-lg focus:ring-1 focus:ring-primary w-full max-w-[150px]"
                               x-ref="nameInput" @click.away="isEditing = false">
                        <button @click="saveName()" class="text-primary"><span class="material-symbols-outlined text-sm">check</span></button>
                    </div>
                </div>
            </div>
            
            <button @click="openMenu = !openMenu" @click.away="openMenu = false" class="w-8 h-8 flex items-center justify-center text-slate-400">
                <span class="material-symbols-outlined text-[20px]">more_vert</span>
            </button>
        </header>

        <!-- High Density Page Content -->
        <main class="pt-14 pb-32 px-4 max-w-screen-xl mx-auto space-y-3">
            
            <!-- Hero Metric Card (Rounded & Powerful) -->
            <section class="p-5 bg-emerald-700 rounded-3xl text-white shadow-lg shadow-emerald-700/10 relative overflow-hidden">
                <div class="absolute right-[-10%] top-[-20%] opacity-10">
                    <span class="material-symbols-outlined text-[120px] text-white">{{ $kategori->icon ?? 'account_balance_wallet' }}</span>
                </div>
                <div class="relative z-10">
                    <p class="text-[10px] font-bold text-white/60 mb-1">TOTAL SALDO TERSEDIA</p>
                    <h2 class="text-2xl font-extrabold tracking-tighter mb-4">
                        Rp {{ number_format($walletData->balance, 0, ',', '.') }}
                    </h2>
                    <div class="flex gap-2">
                        <div class="bg-white/10 px-2 py-1 flex items-center gap-1 rounded-full backdrop-blur-md">
                            <span class="material-symbols-outlined text-[10px] text-white">trending_up</span>
                            <span class="text-[9px] font-bold text-white">+2.4% Bulan Ini</span>
                        </div>
                        @if($kategori->target_saldo > 0)
                        <div class="bg-white/20 px-2 py-1 flex items-center gap-1 rounded-full backdrop-blur-md">
                            <span class="material-symbols-outlined text-[10px] text-white" style='font-variation-settings: "FILL" 1;'>stars</span>
                            <span class="text-[9px] font-bold text-white">{{ round($walletData->balance_percent) }}% Goal</span>
                        </div>
                        @endif
                    </div>
                </div>
            </section>

            <!-- Bento Analytics Grid (Small Scale) -->
            <div class="grid grid-cols-2 gap-3">
                <div class="bg-white p-3 rounded-2xl border border-slate-100 shadow-sm flex flex-col justify-between h-32">
                    <h3 class="text-[9px] font-bold text-slate-400 tracking-widest uppercase">Trend Asset</h3>
                    <div class="h-12 flex items-end gap-1 px-1">
                        @foreach([30, 45, 35, 70, 55, 90] as $h)
                            <div class="flex-1 bg-primary/{{ $loop->last ? '100' : '20' }} rounded-t-lg" style="height: {{ $h }}%"></div>
                        @endforeach
                    </div>
                    <div class="flex justify-between text-[7px] font-bold text-slate-300">
                        <span>JAN</span><span>MAR</span><span>MEI</span><span>JUN</span>
                    </div>
                </div>

                <div class="bg-white p-3 rounded-2xl border border-slate-100 shadow-sm flex flex-col justify-between h-32">
                    <h3 class="text-[9px] font-bold text-slate-400 tracking-widest uppercase">Target & Progres</h3>
                    <div class="flex flex-col gap-1.5">
                        <div class="flex justify-between items-end">
                            <span class="text-[12px] font-extrabold text-slate-900 tracking-tight">{{ round($walletData->balance_percent) }}%</span>
                            <span class="text-[8px] font-bold text-slate-400">DETAIL</span>
                        </div>
                        <div class="w-full bg-slate-50 h-1.5 rounded-full overflow-hidden">
                            <div class="bg-primary h-full" style="width: {{ min(100, $walletData->balance_percent) }}%"></div>
                        </div>
                        <p class="text-[9px] font-bold text-slate-500 mt-1">Sisa: <span class="text-primary">Rp{{ number_format(max(0, $kategori->target_saldo - $walletData->balance)) }}</span></p>
                    </div>
                </div>
            </div>

            <!-- Transaction History (Ultra Tidy) -->
            <section class="space-y-2">
                <div class="flex items-center justify-between px-1">
                    <h3 class="text-[11px] font-bold text-slate-800 tracking-tight">Riwayat Terakhir</h3>
                    <button class="text-[10px] font-bold text-primary">LIHAT SEMUA</button>
                </div>
                
                <div class="grid grid-cols-1 gap-1.5">
                    @forelse($transactions as $transaction)
                    <div class="bg-white p-3 rounded-2xl border border-slate-100 flex items-center justify-between group hover:bg-slate-50 active:scale-[0.99] transition-all">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-xl bg-slate-50 flex items-center justify-center text-slate-400">
                                <span class="material-symbols-outlined text-[18px]">
                                    {{ $transaction->kategoriJenis->jenis == 'Pemasukan' ? 'add_card' : 'payments' }}
                                </span>
                            </div>
                            <div>
                                <p class="text-[12px] font-bold text-slate-900 leading-tight">{{ $transaction->keterangan ?? ($transaction->kategoriNama->nama ?? 'Umum') }}</p>
                                <p class="text-[9px] font-medium text-slate-400 mt-0.5">{{ $transaction->created_at->translatedFormat('d F Y') }}</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-[12px] font-bold {{ $transaction->kategoriJenis->jenis == 'Pemasukan' ? 'text-primary' : 'text-red-500' }}">
                                {{ $transaction->kategoriJenis->jenis == 'Pemasukan' ? '+' : '-' }}Rp{{ number_format($transaction->nominal, 0, ',', '.') }}
                            </p>
                        </div>
                    </div>
                    @empty
                    <div class="bg-white p-8 text-center rounded-2xl border border-dashed border-slate-200">
                        <p class="text-[10px] font-bold text-slate-300 tracking-widest uppercase">Belum ada transaksi</p>
                    </div>
                    @endforelse
                </div>
            </section>
            <!-- Overlay Context Menu -->
            <div x-show="openMenu" x-cloak class="fixed inset-0 z-[100] bg-black/5" @click="openMenu = false">
                <div class="absolute right-4 top-14 w-48 bg-white rounded-2xl shadow-2xl border border-slate-100 py-1 overflow-hidden" @click.stop>
                    <button @click="isEditing = true; openMenu = false; $nextTick(() => $refs.nameInput.focus())" class="w-full flex items-center gap-3 px-4 py-2.5 hover:bg-slate-50 text-left transition-colors text-slate-700">
                        <span class="material-symbols-outlined text-primary text-[18px]">edit</span>
                        <span class="text-[11px] font-bold">Ubah Nama</span>
                    </button>
                    <button @click="showReminderModal = true; openMenu = false" class="w-full flex items-center gap-3 px-4 py-2.5 hover:bg-slate-50 text-left transition-colors text-slate-700">
                        <span class="material-symbols-outlined text-primary text-[18px]">notifications</span>
                        <span class="text-[11px] font-bold">Atur Pengingat</span>
                    </button>
                    <div class="h-[1px] bg-slate-100 my-1 mx-3"></div>
                    <form action="{{ route('management.destroy', $kategori->id) }}" method="POST" onsubmit="return confirm('Hapus item ini?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="w-full flex items-center gap-3 px-4 py-2.5 hover:bg-red-50 text-left text-red-500 transition-colors">
                            <span class="material-symbols-outlined text-[18px]">delete</span>
                            <span class="text-[11px] font-bold">Hapus Item</span>
                        </button>
                    </form>
                </div>
            </div>
        </main>
    </div>

    <!-- Floating Action Button -->
    <a href="{{ route('transaction.create', ['wallet_id' => $kategori->id]) }}" class="fixed bottom-24 right-6 w-12 h-12 bg-primary text-white rounded-2xl shadow-xl shadow-primary/20 flex items-center justify-center active:scale-90 transition-transform z-40">
        <span class="material-symbols-outlined text-2xl">add</span>
    </a>
</x-app-layout>
