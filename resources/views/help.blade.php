<x-app-layout>
    @section('title', 'Bantuan & Dukungan')

    <div class="bg-surface min-h-screen text-on-surface">
        <main class="max-w-5xl mx-auto px-6 py-12 space-y-16">
            {{-- Hero Search Section --}}
            <section class="space-y-8 animate-fade-in">
                <div class="max-w-2xl">
                    <h2 class="text-4xl md:text-[3.5rem] font-black leading-[1.1] tracking-tight text-on-surface mb-6">
                        Ada yang bisa <span class="text-primary">kami</span> bantu?
                    </h2>
                    <p class="text-on-surface-variant text-lg leading-relaxed max-w-xl font-medium">
                        Temukan jawaban cepat atau hubungi tim kami untuk bantuan yang lebih personal mengenai aset keluarga Anda.
                    </p>
                </div>
                <div class="relative max-w-xl group">
                    <div class="absolute inset-y-0 left-4 flex items-center pointer-events-none text-on-surface-variant">
                        <span class="material-symbols-outlined">search</span>
                    </div>
                    <input class="w-full bg-white border border-surface-variant/20 py-4 pl-12 pr-4 focus:outline-none focus:border-primary transition-all rounded-xl shadow-sm text-sm font-bold" 
                           placeholder="Apa yang sedang Anda cari?" type="text" />
                </div>
            </section>

            {{-- Bento Grid: Quick Links & Guide --}}
            <section class="grid grid-cols-1 md:grid-cols-12 gap-6">
                {{-- Featured Guide --}}
                <div class="md:col-span-8 bg-white p-8 rounded-2xl flex flex-col justify-between min-h-[320px] relative overflow-hidden group border border-surface-variant/10 shadow-sm">
                    <div class="relative z-10">
                        <span class="text-tertiary font-black tracking-widest text-[10px] uppercase mb-4 block">Panduan Populer</span>
                        <h3 class="text-3xl font-black mb-4 tracking-tight text-on-surface">Menjaga Warisan Digital Keluarga</h3>
                        <p class="text-on-surface-variant max-w-md font-medium text-sm leading-relaxed">Langkah mudah untuk mengatur akses keluarga agar aset Anda tetap aman dan terkelola dengan baik.</p>
                    </div>
                    <div class="relative z-10 mt-8">
                        <button class="bg-primary text-white px-8 py-3 rounded-xl font-black text-xs uppercase tracking-widest flex items-center gap-2 hover:brightness-110 active:scale-95 transition-all shadow-lg shadow-primary/20">
                            Pelajari Selengkapnya <span class="material-symbols-outlined text-sm">arrow_forward</span>
                        </button>
                    </div>
                    {{-- Abstract decorative element --}}
                    <div class="absolute -right-20 -bottom-20 w-80 h-80 bg-primary/5 rounded-full blur-3xl group-hover:bg-primary/10 transition-colors"></div>
                </div>

                {{-- CS Contact --}}
                <div class="md:col-span-4 bg-primary text-white p-8 rounded-2xl flex flex-col justify-center items-center text-center space-y-6 shadow-xl shadow-primary/20">
                    <div class="w-16 h-16 bg-white/10 rounded-full flex items-center justify-center">
                        <span class="material-symbols-outlined text-3xl">support_agent</span>
                    </div>
                    <div>
                        <h3 class="text-xl font-black mb-2 uppercase tracking-tight">Butuh Bantuan Personal?</h3>
                        <p class="text-white/80 text-xs font-medium uppercase tracking-tighter">Tim kami siap menemani dan menjawab pertanyaan Anda kapan saja.</p>
                    </div>
                    <button class="w-full bg-white text-primary py-4 rounded-xl font-black text-xs uppercase tracking-widest hover:bg-surface transition-colors active:scale-95 shadow-lg">
                        Hubungi Kami Sekarang
                    </button>
                </div>
            </section>

            {{-- FAQ Section --}}
            <section class="space-y-10">
                <div class="flex justify-between items-end border-b border-surface-variant/10 pb-4">
                    <h2 class="text-2xl font-black tracking-tight text-primary uppercase">Pertanyaan Umum</h2>
                    <a class="text-tertiary text-[10px] font-black uppercase tracking-widest flex items-center gap-1 hover:underline decoration-2" href="#">
                        Lihat Semua <span class="material-symbols-outlined text-sm">open_in_new</span>
                    </a>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-12 gap-y-4">
                    {{-- FAQ Item Wrapper with Alpine --}}
                    @php
                        $faqs = [
                            ['q' => 'Bagaimana cara mengundang keluarga?', 'a' => "Buka menu 'Manajemen Keluarga' dan masukkan email mereka. Kami akan mengirimkan undangan aman langsung ke inbox mereka."],
                            ['q' => 'Apakah data saya benar-benar aman?', 'a' => "Tentu. Kami menggunakan sistem keamanan berlapis untuk memastikan hanya Anda dan keluarga yang bisa mengakses informasi penting tersebut."],
                            ['q' => 'Berapa biaya langganannya?', 'a' => "Kami menawarkan paket yang fleksibel sesuai kebutuhan aset Anda. Silakan hubungi kami untuk mendapatkan penawaran yang paling sesuai."],
                            ['q' => 'Apa itu Fitur Warisan?', 'a' => "Ini adalah cara otomatis untuk memberikan petunjuk penting kepada keluarga jika terjadi keadaan darurat yang sudah terverifikasi."]
                        ];
                    @endphp

                    @foreach($faqs as $faq)
                    <div class="p-6 bg-white rounded-2xl border border-surface-variant/10 shadow-sm hover:border-primary/20 transition-all group" 
                         x-data="{ open: false }">
                        <button @click="open = !open" class="w-full flex justify-between items-center text-left">
                            <h4 class="text-sm font-black text-on-surface group-hover:text-primary transition-colors uppercase tracking-tight">
                                {{ $faq['q'] }}
                            </h4>
                            <span class="material-symbols-outlined text-on-surface-variant/40 transition-transform duration-300" 
                                  :class="open ? 'rotate-180 text-primary' : ''">expand_more</span>
                        </button>
                        <div x-show="open" 
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 -translate-y-2"
                             x-transition:enter-end="opacity-100 translate-y-0"
                             class="mt-4">
                            <p class="text-xs text-on-surface-variant leading-relaxed font-medium">
                                {{ $faq['a'] }}
                            </p>
                        </div>
                    </div>
                    @endforeach
                </div>
            </section>

            {{-- Categories Section --}}
            <section class="bg-surface-low p-10 rounded-2xl border border-surface-variant/10">
                <h3 class="text-xl font-black mb-8 text-primary uppercase tracking-tight">Pilih Topik Bantuan</h3>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                    @php
                        $topics = [
                            ['icon' => 'account_balance', 'label' => 'Urus Aset'],
                            ['icon' => 'security', 'label' => 'Keamanan Akun'],
                            ['icon' => 'payments', 'label' => 'Pembayaran'],
                            ['icon' => 'family_restroom', 'label' => 'Fitur Keluarga']
                        ];
                    @endphp
                    @foreach($topics as $topic)
                    <div class="bg-white p-6 rounded-2xl shadow-sm hover:shadow-md transition-all cursor-pointer flex flex-col items-center text-center border border-surface-variant/5 group">
                        <div class="w-12 h-12 bg-primary/5 rounded-full flex items-center justify-center mb-4 group-hover:bg-primary group-hover:text-white transition-all">
                            <span class="material-symbols-outlined">{{ $topic['icon'] }}</span>
                        </div>
                        <span class="text-[10px] font-black uppercase tracking-widest text-on-surface-variant group-hover:text-primary">{{ $topic['label'] }}</span>
                    </div>
                    @endforeach
                </div>
            </section>

            {{-- Footer Contact Info --}}
            <footer class="pt-12 border-t border-surface-variant/10 text-center pb-24">
                <p class="text-on-surface-variant text-[10px] font-black uppercase tracking-[0.2em] mb-6">Masih butuh bantuan lainnya?</p>
                <div class="flex flex-col md:flex-row justify-center gap-4 md:gap-12">
                    <div class="flex items-center justify-center gap-3 text-primary font-black text-sm group cursor-pointer">
                        <div class="w-10 h-10 bg-primary/5 rounded-full flex items-center justify-center transition-colors group-hover:bg-primary group-hover:text-white">
                            <span class="material-symbols-outlined text-lg">mail</span>
                        </div>
                        <span class="tracking-tight">halo@famly.id</span>
                    </div>
                    <div class="flex items-center justify-center gap-3 text-primary font-black text-sm group cursor-pointer">
                        <div class="w-10 h-10 bg-primary/5 rounded-full flex items-center justify-center transition-colors group-hover:bg-primary group-hover:text-white">
                            <span class="material-symbols-outlined text-lg">call</span>
                        </div>
                        <span class="tracking-tight">0800-1-FAMLY</span>
                    </div>
                </div>
            </footer>
        </main>
    </div>

    <style>
        .animate-fade-in {
            animation: fadeIn 0.8s ease-out forwards;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</x-app-layout>
