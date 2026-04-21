<x-app-layout>
    @section('title', 'Tambah ' . ($type === 'tabungan' ? 'Target' : ($type === 'dompet' ? 'Dompet' : 'Pos')))

    @php
        $containerColor = $type === 'tabungan' ? 'amber-600' : ($type === 'dompet' ? 'blue-600' : 'emerald-700');
        $lightBg = $type === 'tabungan' ? 'bg-amber-50' : ($type === 'dompet' ? 'bg-blue-50' : 'bg-emerald-50');
        $textColor = $type === 'tabungan' ? 'text-amber-600' : ($type === 'dompet' ? 'text-blue-600' : 'text-emerald-700');
    @endphp

    <div class="bg-slate-50 min-h-screen pb-24" x-data="{ 
        walletType: '{{ $type === 'dompet' ? 'wallet' : ($type === 'tabungan' ? 'savings' : 'pos') }}',
        selectedCategory: 'Pilih kategori',
        selectedIcon: 'category',
        selectedColor: '{{ $type === 'tabungan' ? '#D97706' : ($type === 'dompet' ? '#2563EB' : '#047857') }}',
        nominal: 0
    }">
        <!-- TOP STICKY HEADER (Internal Page Style) -->
        <header class="fixed top-0 left-0 right-0 sm:left-64 z-[80] bg-white border-b border-slate-100 flex items-center h-14 px-4">
            <div class="flex items-center gap-4 w-full max-w-screen-xl mx-auto">
                <a href="javascript:history.back()" class="w-9 h-9 flex items-center justify-center text-slate-400 bg-slate-50 rounded-xl active:scale-95 transition-all">
                    <span class="material-symbols-outlined text-[18px]">arrow_back</span>
                </a>
                <div>
                    <h1 class="text-[14px] font-bold text-slate-800 tracking-tight leading-none">
                        Tambah {{ $type === 'tabungan' ? 'Target Tabungan' : ($type === 'dompet' ? 'Dompet Baru' : 'Pos Budget') }}
                    </h1>
                </div>
            </div>
        </header>

        <main class="pt-16 px-4 max-w-screen-xl mx-auto space-y-3">
            <form method="POST" action="{{ route('management.store') }}" class="space-y-3">
                @csrf
                <input type="hidden" name="wallet_type" :value="walletType">
                <input type="hidden" name="kategori_kas" :value="selectedCategory">
                <input type="hidden" name="icon" :value="selectedIcon">
                <input type="hidden" name="color" :value="selectedColor">

                <!-- 1. HERO NOMINAL CARD (Compact Premium) -->
                <section class="bg-{{ $containerColor }} rounded-[2rem] p-5 shadow-lg shadow-{{ $containerColor }}/10 relative overflow-hidden text-white border-2 border-white/10">
                    <div class="absolute -right-8 -top-8 w-24 h-24 bg-white/10 rounded-full blur-2xl"></div>
                    
                    <div class="relative z-10 space-y-3">
                        <div>
                            <label class="block text-[10px] font-bold text-white/70 tracking-tight mb-2">
                                {{ $type === 'tabungan' ? 'Target total tabungan' : 'Saldo awal tersedia' }}
                            </label>
                            <div class="flex items-center gap-2">
                                <span class="text-xl font-bold opacity-40">Rp</span>
                                <input name="target_saldo" 
                                       class="w-full bg-transparent border-none text-3xl font-bold text-white placeholder:text-white/20 focus:ring-0 p-0 outline-none tracking-tighter" 
                                       placeholder="0" type="number" required x-model="nominal">
                            </div>
                        </div>

                        <div class="pt-3 border-t border-white/10 flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <div class="w-7 h-7 rounded-lg bg-white/20 flex items-center justify-center">
                                    <span class="material-symbols-outlined text-[16px]" x-text="selectedIcon"></span>
                                </div>
                                <span class="text-[11px] font-bold tracking-tight" x-text="selectedCategory"></span>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- 2. IDENTIFICATION (Compact Input) -->
                <section class="bg-white p-4 rounded-3xl border border-slate-100 shadow-sm space-y-3">
                    <div class="space-y-1.5">
                        <label class="text-[10px] font-bold text-slate-400 tracking-tight px-1 uppercase">Nama identitas</label>
                        <div class="relative">
                            <input name="nama" 
                                   class="w-full bg-slate-50 border-none rounded-2xl py-3 pl-11 pr-4 text-[13px] font-bold text-slate-800 placeholder:text-slate-200 focus:ring-2 focus:ring-{{ $containerColor }}/10" 
                                   placeholder="Contoh: {{ $type === 'tabungan' ? 'Dana Haji 2028' : ($type === 'dompet' ? 'Bank Mandiri' : 'Belanja Bulanan') }}" type="text" required>
                            <span class="material-symbols-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-300 text-[18px]">edit_square</span>
                        </div>
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-[10px] font-bold text-slate-400 tracking-tight px-1 uppercase">Catatan tambahan (opsional)</label>
                        <div class="relative">
                            <input name="description" 
                                   class="w-full bg-slate-50 border-none rounded-2xl py-3 pl-11 pr-4 text-[12px] font-bold text-slate-800 placeholder:text-slate-200 focus:ring-2 focus:ring-{{ $containerColor }}/10" 
                                   placeholder="Keterangan singkat tentang pos ini...">
                            <span class="material-symbols-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-300 text-[18px]">notes</span>
                        </div>
                    </div>
                </section>

                <!-- 3. CATEGORY SELECTOR (Compact Tiles) -->
                <section class="space-y-2">
                    <div class="flex items-center justify-between px-1">
                        <label class="text-[10px] font-bold text-slate-400 tracking-tight uppercase">Pilih kategori ikon</label>
                        <span class="text-[9px] font-bold text-primary px-2 py-0.5 bg-primary/5 rounded-full uppercase">6 pilihan</span>
                    </div>

                    <div class="grid grid-cols-3 gap-2">
                        @php
                            $options = $type === 'tabungan' ? [
                                'Pendidikan' => 'school',
                                'Kendaraan' => 'directions_car',
                                'Liburan' => 'flight_takeoff',
                                'Darurat' => 'emergency',
                                'Rumah' => 'home',
                                'Lainnya' => 'more_horiz'
                            ] : [
                                'Bank' => 'account_balance',
                                'E-Wallet' => 'account_balance_wallet',
                                'Dompet' => 'payments',
                                'Investasi' => 'show_chart',
                                'Bisnis' => 'business_center',
                                'Lainnya' => 'category'
                            ];
                        @endphp
                        
                        @foreach($options as $name => $icon)
                            <div @click="selectedCategory = '{{ $name }}'; selectedIcon = '{{ $icon }}'" 
                                 :class="selectedCategory == '{{ $name }}' ? 'border-primary bg-primary/5 shadow-sm' : 'bg-white border-slate-100'"
                                 class="flex flex-col items-center justify-center p-3 rounded-2xl border transition-all cursor-pointer active:scale-95 group h-20">
                                <div class="w-8 h-8 rounded-lg mb-1.5 flex items-center justify-center transition-colors shadow-sm"
                                     :class="selectedCategory == '{{ $name }}' ? 'bg-primary text-white' : 'bg-slate-50 text-slate-400'">
                                    <span class="material-symbols-outlined text-[18px]" :class="selectedCategory == '{{ $name }}' ? 'fill-1' : ''">{{ $icon }}</span>
                                </div>
                                <span class="text-[9px] font-bold text-center tracking-tight leading-none group-hover:text-primary transition-colors"
                                      :class="selectedCategory == '{{ $name }}' ? 'text-primary' : 'text-slate-400'">{{ $name }}</span>
                            </div>
                        @endforeach
                    </div>
                </section>

                <!-- INFO TIP (Ultra Thin) -->
                <div class="p-3 {{ $lightBg }} rounded-2xl border border-{{ $containerColor }}/10 flex items-center gap-3">
                    <div class="w-7 h-7 rounded-lg bg-white flex items-center justify-center {{ $textColor }} shrink-0 shadow-sm border border-{{ $containerColor }}/5">
                        <span class="material-symbols-outlined text-[16px]">auto_awesome</span>
                    </div>
                    <div>
                        <p class="text-[10px] font-medium text-slate-600 leading-tight">
                            {{ $aiTip }}
                        </p>
                    </div>
                </div>

                <!-- FIXED BOTTOM ACTION -->
                <div class="fixed bottom-0 left-0 right-0 p-4 bg-white/80 backdrop-blur-md border-t border-slate-100 sm:left-64 z-[90]">
                    <div class="max-w-xl mx-auto flex gap-3">
                        <button type="submit" class="w-full bg-slate-900 text-white py-3.5 rounded-xl font-bold text-[12px] tracking-widest shadow-lg shadow-slate-900/10 active:scale-95 transition-all flex items-center justify-center gap-3">
                            Simpan {{ $type === 'tabungan' ? 'target' : ($type === 'dompet' ? 'dompet' : 'pos') }}
                            <span class="material-symbols-outlined text-[18px]">verified</span>
                        </button>
                    </div>
                </div>
            </form>
        </main>
    </div>
</x-app-layout>
