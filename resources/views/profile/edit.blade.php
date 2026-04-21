<x-app-layout>
    @section('title', 'Profil & Pengaturan')

    <style>
        .emerald-gold-gradient {
            background: linear-gradient(135deg, #006D36 0%, #50C878 100%);
        }
        .high-density-shadow {
            box-shadow: 0 12px 12px -10px rgba(27, 27, 29, 0.04);
        }
    </style>

    <main class="pt-6 pb-12 px-4 max-w-2xl mx-auto space-y-6">
        <!-- Hero Profile Card (Bento Style) -->
        <section class="grid grid-cols-1 md:grid-cols-3 gap-1">
            <div class="md:col-span-2 bg-surface-container-lowest p-6 flex items-center gap-5 border-none rounded-sm high-density-shadow shadow-sm">
                <div class="relative">
                    <img alt="Profile" class="w-20 h-20 object-cover rounded-sm border-2 border-primary-container" src="{{ $user->profile_photo_url ?? 'https://ui-avatars.com/api/?name='.urlencode($user->name).'&background=006d36&color=fff' }}"/>
                    <div class="absolute -bottom-1 -right-1 bg-secondary-container p-1 rounded-sm">
                        <span class="material-symbols-outlined text-on-secondary-container text-xs" style="font-variation-settings: 'FILL' 1;">verified</span>
                    </div>
                </div>
                <div>
                    <h2 class="text-xl font-bold tracking-tight text-on-surface leading-tight">{{ $user->name }}</h2>
                    <p class="text-primary font-bold text-xs tracking-widest uppercase mt-1">{{ $user->role ?? 'Anggota Keluarga' }}</p>
                    <p class="text-outline text-[11px] mt-2">ID: FAM-{{ date('ym') }}-{{ $user->id }}</p>
                </div>
            </div>
            <div class="bg-primary p-6 flex flex-col justify-between rounded-sm emerald-gold-gradient text-on-primary">
                <div class="text-[10px] font-bold tracking-widest uppercase opacity-80">Status Akun</div>
                <div class="text-2xl font-bold tracking-tighter">Premium</div>
                <button class="mt-3 w-full py-2 px-4 bg-secondary-container text-on-secondary-container font-bold text-[10px] uppercase tracking-widest rounded-sm shadow-sm active:scale-95 transition-transform">Kelola Langganan</button>
                <div class="flex items-center gap-1 text-[11px] font-medium mt-2">
                    <span class="material-symbols-outlined text-xs" style="font-variation-settings: 'FILL' 1;">stars</span>
                    Aktif hingga {{ date('Y') + 1 }}
                </div>
            </div>
        </section>

        <!-- Family Members Section (Architectural Ledger Style) -->
        <section class="space-y-3">
            <div class="flex justify-between items-end px-1">
                <h3 class="text-xs font-bold tracking-widest uppercase text-outline">Anggota Keluarga</h3>
                <span class="text-primary font-bold text-[11px] cursor-pointer hover:underline">Kelola</span>
            </div>
            <div class="bg-surface-container rounded-sm overflow-hidden space-y-[px]">
                @forelse($familyMembers as $member)
                <!-- Member Item -->
                <div class="bg-surface-container-lowest p-4 flex items-center justify-between transition-colors hover:bg-surface-container-low border-b border-surface-container last:border-0">
                    <div class="flex items-center gap-3">
                        <img alt="{{ $member->name }}" class="w-10 h-10 object-cover rounded-sm" src="{{ $member->profile_photo_url ?? 'https://ui-avatars.com/api/?name='.urlencode($member->name).'&background=006d36&color=fff' }}"/>
                        <div>
                            <p class="text-sm font-bold text-on-surface">{{ $member->name }}</p>
                            <p class="text-[11px] text-outline capitalize">{{ $member->role ?? 'Anggota' }}</p>
                        </div>
                    </div>
                    <span class="material-symbols-outlined text-outline text-sm">chevron_right</span>
                </div>
                @empty
                <div class="bg-surface-container-lowest p-4 text-center">
                    <p class="text-xs text-outline italic">Belum ada anggota keluarga lain tertaut.</p>
                </div>
                @endforelse

                <!-- Add Button -->
                <div class="bg-surface-container-low p-3 flex items-center justify-center cursor-pointer transition-colors hover:bg-surface-container-high group">
                    <div class="flex items-center gap-2 text-primary font-bold text-xs uppercase tracking-tight">
                        <span class="material-symbols-outlined text-sm">add_circle</span>
                        Tambah Anggota
                    </div>
                </div>
            </div>
        </section>

        <!-- Settings Group 1: Account & Security -->
        <section class="space-y-3">
            <h3 class="text-xs font-bold tracking-widest uppercase text-outline px-1">Keamanan & Akun</h3>
            <div class="bg-surface-container-lowest rounded-sm overflow-hidden high-density-shadow shadow-sm">
                <!-- Setting Item -->
                <div class="p-4 flex items-center gap-4 transition-colors hover:bg-surface-container-low cursor-pointer border-b border-surface-container">
                    <div class="w-8 h-8 bg-surface-container flex items-center justify-center rounded-sm">
                        <span class="material-symbols-outlined text-primary text-sm">person</span>
                    </div>
                    <div class="flex-1">
                        <p class="text-sm font-bold text-on-surface">Informasi Pribadi</p>
                        <p class="text-[10px] text-outline">{{ $user->email }}</p>
                    </div>
                    <span class="material-symbols-outlined text-outline text-sm">navigate_next</span>
                </div>
                <!-- Setting Item -->
                <div class="p-4 flex items-center gap-4 transition-colors hover:bg-surface-container-low cursor-pointer border-b border-surface-container">
                    <div class="w-8 h-8 bg-surface-container flex items-center justify-center rounded-sm">
                        <span class="material-symbols-outlined text-primary text-sm">key</span>
                    </div>
                    <div class="flex-1">
                        <p class="text-sm font-bold text-on-surface">Ubah Kata Sandi</p>
                        <p class="text-[10px] text-outline">Terakhir diubah beberapa waktu lalu</p>
                    </div>
                    <span class="material-symbols-outlined text-outline text-sm">navigate_next</span>
                </div>
                <!-- Setting Item -->
                <div class="p-4 flex items-center gap-4 transition-colors hover:bg-surface-container-low cursor-pointer">
                    <div class="w-8 h-8 bg-surface-container flex items-center justify-center rounded-sm">
                        <span class="material-symbols-outlined text-primary text-sm">shield_person</span>
                    </div>
                    <div class="flex-1">
                        <p class="text-sm font-bold text-on-surface">Verifikasi Dua Faktor</p>
                        <p class="text-[10px] text-secondary font-bold uppercase tracking-tighter">Direkomendasikan</p>
                    </div>
                    <div class="w-10 h-5 bg-surface-container-highest rounded-full relative p-1 cursor-pointer">
                        <div class="w-3 h-3 bg-outline-variant rounded-full"></div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Settings Group 2: Preferences -->
        <section class="space-y-3">
            <h3 class="text-xs font-bold tracking-widest uppercase text-outline px-1">Preferensi</h3>
            <div class="bg-surface-container-lowest rounded-sm overflow-hidden high-density-shadow shadow-sm">
                <!-- Setting Item -->
                <div class="p-4 flex items-center gap-4 transition-colors hover:bg-surface-container-low cursor-pointer border-b border-surface-container">
                    <div class="w-8 h-8 bg-surface-container flex items-center justify-center rounded-sm">
                        <span class="material-symbols-outlined text-secondary text-sm" style="font-variation-settings: 'FILL' 1;">payments</span>
                    </div>
                    <div class="flex-1">
                        <p class="text-sm font-bold text-on-surface">Metode Pembayaran</p>
                        <p class="text-[10px] text-outline">Atur detail pembayaran digital</p>
                    </div>
                    <span class="material-symbols-outlined text-outline text-sm">navigate_next</span>
                </div>
                <!-- Setting Item -->
                <div class="p-4 flex items-center gap-4 transition-colors hover:bg-surface-container-low cursor-pointer">
                    <div class="w-8 h-8 bg-surface-container flex items-center justify-center rounded-sm">
                        <span class="material-symbols-outlined text-[#006D36] text-sm">notifications</span>
                    </div>
                    <div class="flex-1">
                        <p class="text-sm font-bold text-on-surface">Pemberitahuan</p>
                        <p class="text-[10px] text-outline">Push, Email, WhatsApp</p>
                    </div>
                    <span class="material-symbols-outlined text-outline text-sm">navigate_next</span>
                </div>
            </div>
        </section>

        <!-- Danger Zone -->
        <section class="pt-4">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="w-full py-4 px-6 bg-surface-container-low flex items-center justify-center gap-2 rounded-sm border border-error/10 text-error font-bold text-xs uppercase tracking-widest transition-all hover:bg-error-container hover:text-on-error-container active:scale-[0.98]">
                    <span class="material-symbols-outlined text-sm">logout</span> KELUAR APLIKASI
                </button>
            </form>
            <p class="text-center text-[10px] text-outline mt-6 font-medium">Famly Architecture Ledger v2.4.0 • {{ date('Y') }}</p>
        </section>
    </main>

    <!-- Bottom Spacing for No Navbar constraint but visual balance -->
    <div class="h-20"></div>
</x-app-layout>
