<x-app-layout>
    @section('title', 'Manajemen Grup')

    <div class="py-12" x-data="{ 
        showCreateModal: false,
        paymentVerified: false,
        creating: false,
        groupName: '',
        groupType: 'Family',
        
        async createGroup() {
            if (!this.paymentVerified) {
                alert('Silakan verifikasi pembayaran Rp 249.000 terlebih dahulu.');
                return;
            }
            this.creating = true;
            try {
                const response = await fetch('{{ route('groups.store') }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: JSON.stringify({
                        name: this.groupName,
                        type: this.groupType,
                        simulated_payment_verified: true
                    })
                });
                const data = await response.json();
                if (data.success) {
                    window.location.reload();
                } else {
                    alert(data.message);
                }
            } catch (e) {
                alert('Gagal membuat grup. Silakan coba lagi.');
            } finally {
                this.creating = false;
            }
        },

        async switchGroup(id) {
            try {
                const response = await fetch(`/groups/switch/${id}`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                });
                const data = await response.json();
                if (data.success) {
                    window.location.href = '{{ route('admin.dashboard') }}';
                }
            } catch (e) {
                alert('Gagal berpindah grup.');
            }
        }
    }">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Header Section -->
            <div class="bg-white overflow-hidden shadow-sm rounded-3xl mb-6 border border-slate-100">
                <div class="p-8 flex flex-col sm:flex-row justify-between items-center gap-4">
                    <div>
                        <h2 class="text-2xl font-black text-slate-800 tracking-tight">Koleksi Grup</h2>
                        <p class="text-sm font-medium text-slate-500 mt-1">Kelola orkestrasi keuangan komunitas dan keluarga Anda.</p>
                    </div>
                    <button @click="showCreateModal = true" class="px-6 py-3.5 bg-primary text-white rounded-2xl font-extrabold shadow-lg shadow-emerald-500/20 active:scale-95 transition-all text-sm flex items-center gap-2">
                        <span class="material-symbols-outlined text-[20px]">add</span>
                        Buat Grup Baru
                    </button>
                </div>
            </div>

            <!-- Group Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($groups as $group)
                <div class="bg-white rounded-3xl p-6 border border-slate-100 shadow-sm hover:shadow-md transition-all relative overflow-hidden group">
                    <div class="absolute top-0 right-0 p-4">
                         <span class="px-3 py-1 bg-slate-50 text-slate-400 text-[10px] font-black uppercase rounded-full border border-slate-100">
                            {{ $group->type }}
                         </span>
                    </div>

                    <div class="flex items-center gap-4 mb-6">
                        <div class="w-14 h-14 rounded-2xl bg-primary/10 text-primary flex items-center justify-center font-black text-xl shadow-inner">
                            {{ substr($group->name, 0, 1) }}
                        </div>
                        <div>
                            <h3 class="font-extrabold text-slate-800 text-lg leading-tight">{{ $group->name }}</h3>
                            <p class="text-[11px] font-bold text-slate-400 uppercase tracking-widest mt-1">
                                {{ $group->members->count() }} Anggota
                            </p>
                        </div>
                    </div>

                    <div class="space-y-3 mb-6">
                        <div class="flex items-center justify-between text-[12px] font-bold">
                            <span class="text-slate-400">Invite Code</span>
                            <span class="text-slate-800 font-black tracking-widest">{{ $group->invite_code }}</span>
                        </div>
                        <div class="flex items-center justify-between text-[12px] font-bold">
                            <span class="text-slate-400">Status</span>
                            <span class="text-emerald-500 flex items-center gap-1">
                                <span class="w-1.5 h-1.5 bg-emerald-500 rounded-full"></span>
                                Terbayar (Active)
                            </span>
                        </div>
                    </div>

                    <div class="flex gap-2">
                        <button @click="switchGroup({{ $group->id }})" 
                                class="flex-1 py-3 bg-slate-50 text-slate-600 rounded-xl text-[12px] font-extrabold hover:bg-primary/10 hover:text-primary transition-all active:scale-95">
                            Buka Grup
                        </button>
                        @if($group->isAdmin(Auth::id()))
                        <a href="{{ route('groups.members', $group->id) }}" class="w-12 h-12 bg-slate-900 text-white rounded-xl flex items-center justify-center active:scale-95 transition-all shadow-lg shadow-slate-900/20" title="Kelola Anggota">
                            <span class="material-symbols-outlined text-[20px]">groups</span>
                        </a>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>

            <!-- Create Group Modal -->
            <div x-show="showCreateModal" 
                 class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-slate-900/40 backdrop-blur-sm"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100">
                
                <div class="bg-white w-full max-w-md rounded-[32px] shadow-2xl overflow-hidden" @click.away="showCreateModal = false">
                    <div class="p-8">
                        <div class="flex justify-between items-start mb-6">
                            <div>
                                <h3 class="text-xl font-black text-slate-800 mb-1">Mulai Orkestrasi</h3>
                                <p class="text-xs font-bold text-slate-400">Buat ekosistem finansial baru Anda.</p>
                            </div>
                            <button @click="showCreateModal = false" class="text-slate-300 hover:text-slate-500 transition-colors">
                                <span class="material-symbols-outlined">close</span>
                            </button>
                        </div>

                        <div class="space-y-4 mb-8">
                            <div>
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest pl-1">Nama Grup</label>
                                <input x-model="groupName" type="text" class="w-full mt-1 px-4 py-4 bg-slate-50 border-none rounded-2xl font-bold text-slate-800 focus:ring-2 focus:ring-primary/20 placeholder:text-slate-300" placeholder="Contoh: Keluarga Sultan / Tim Biz">
                            </div>
                            <div>
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest pl-1">Tipe Entitas</label>
                                <select x-model="groupType" class="w-full mt-1 px-4 py-4 bg-slate-50 border-none rounded-2xl font-bold text-slate-800 focus:ring-2 focus:ring-primary/20">
                                    <option value="Family">Keluarga</option>
                                    <option value="Corporate">Korporat / Bisnis</option>
                                    <option value="Community">Komunitas</option>
                                    <option value="Other">Lainnya</option>
                                </select>
                            </div>
                        </div>

                        <!-- One-time Payment Paywall -->
                        <div class="bg-emerald-50 rounded-3xl p-6 border border-emerald-100 mb-8">
                            <div class="flex justify-between items-center mb-4">
                                <span class="text-[10px] font-black text-emerald-600 uppercase tracking-widest">Aktivasi Lifetime</span>
                                <span class="text-lg font-black text-emerald-800 italic">Rp 249.000</span>
                            </div>
                            <button @click="paymentVerified = true" 
                                    :class="paymentVerified ? 'bg-emerald-500 text-white' : 'bg-white text-emerald-600'"
                                    class="w-full py-4 rounded-2xl border border-emerald-100 font-extrabold text-[12px] shadow-sm active:scale-95 transition-all flex items-center justify-center gap-2">
                                <span class="material-symbols-outlined text-[18px]" x-text="paymentVerified ? 'verified' : 'account_balance_wallet'"></span>
                                <span x-text="paymentVerified ? 'Pembayaran Terverifikasi' : 'Simulasi Pembayaran 249k'"></span>
                            </button>
                        </div>

                        <button @click="createGroup()" 
                                :disabled="!paymentVerified || creating || !groupName"
                                class="w-full py-4 bg-primary text-white rounded-2xl font-black shadow-xl shadow-emerald-500/20 active:scale-95 transition-all disabled:opacity-50 disabled:grayscale">
                            <span x-show="!creating">Aktifkan Grup Sekarang</span>
                            <span x-show="creating" class="flex items-center justify-center gap-2">
                                <span class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"></span>
                                Memproses...
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
