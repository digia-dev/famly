<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="light scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0">
    
    <!-- SEO Meta Tags -->
    <title>Famly — The Modern Family Office & Wealth Orchestrator</title>
    <meta name="description" content="Platform pengelolaan kekayaan keluarga tercanggih di Indonesia. Dilengkapi AI untuk budgeting, manajemen aset bersama, dan protokol pewarisan digital yang aman.">
    <meta name="keywords" content="family office, kekayaan keluarga, asisten keuangan ai, aplikasi tabungan legacy, manajemen aset digital">
    <meta name="author" content="PT Digi Antara Masa">
    
    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url('/') }}">
    <meta property="og:title" content="Famly — Rencanakan, Kelola, Wariskan">
    <meta property="og:description" content="Transformasi cara keluarga Anda mengelola harta dengan standar perbankan institusional.">
    <meta property="og:image" content="{{ asset('brain/52b50d60-746a-4c9f-8d12-da3561afb9f7/famly_premium_hero_visual_1776647529505.png') }}">

    <!-- Fonts & Icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,400;0,500;0,600;0,700;0,800;1,400;1,500;1,600;1,700;1,800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet" />
    
    <!-- Alpine.js & Tailwind -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.tailwindcss.com?plugins=forms,typography"></script>

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'primary': '#00AA13',
                        'primary-dark': '#006d36',
                        'slate-950': '#0F172A',
                    },
                    fontFamily: {
                        'jakarta': ['Plus Jakarta Sans', 'sans-serif'],
                    }
                }
            }
        }
    </script>

    <style>
        [x-cloak] { display: none !important; }
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #ffffff; color: #0f172a; }
        .glass-nav { background: rgba(255, 255, 255, 0.8); backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px); }
        .bento-gradient { background: linear-gradient(135deg, rgba(0,170,19,0.03) 0%, rgba(255,255,255,1) 100%); }
        .text-balance { text-wrap: balance; }
        .gradient-text { background: linear-gradient(to right, #00AA13, #006d36); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        
        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
            100% { transform: translateY(0px); }
        }
        .float-anim { animation: float 6s ease-in-out infinite; }
    </style>
</head>
<body class="antialiased overflow-x-hidden" x-data="{ scrolled: false }" @scroll.window="scrolled = (window.pageYOffset > 50)">

    <!-- Navigation -->
    <nav class="fixed top-0 inset-x-0 z-[100] transition-all duration-500" :class="scrolled ? 'glass-nav border-b border-slate-100 py-4 shadow-sm' : 'bg-transparent py-6'">
        <div class="max-w-7xl mx-auto px-6 flex justify-between items-center">
            <div class="flex items-center gap-2.5">
                <div class="w-9 h-9 bg-primary rounded-xl flex items-center justify-center shadow-lg shadow-primary/20">
                    <span class="text-white font-bold text-xl">F</span>
                </div>
                <span class="font-extrabold text-2xl tracking-tighter text-slate-900">Famly.</span>
            </div>
            
            <div class="hidden lg:flex items-center gap-10">
                <a href="#features" class="text-[11px] font-bold tracking-[0.1em] text-slate-500 hover:text-primary transition-all uppercase">Ekosistem</a>
                <a href="#ai" class="text-[11px] font-bold tracking-[0.1em] text-slate-500 hover:text-primary transition-all uppercase">Intelligence</a>
                <a href="#security" class="text-[11px] font-bold tracking-[0.1em] text-slate-500 hover:text-primary transition-all uppercase">Keamanan</a>
                <a href="#pricing" class="text-[11px] font-bold tracking-[0.1em] text-slate-500 hover:text-primary transition-all uppercase">Akses</a>
            </div>

            <div class="flex items-center gap-4">
                @auth
                    <a href="{{ route('admin.dashboard') }}" class="bg-slate-900 text-white px-6 py-2.5 rounded-full text-xs font-bold hover:bg-primary transition-all shadow-xl shadow-slate-900/10">Dashboard</a>
                @else
                    <a href="{{ route('login') }}" class="text-[11px] font-bold tracking-[0.1em] text-slate-900 uppercase">Masuk</a>
                    <a href="{{ route('register') }}" class="bg-primary text-white px-7 py-3 rounded-full text-xs font-extrabold hover:bg-primary-dark transition-all shadow-xl shadow-primary/20 scale-100 active:scale-95 uppercase tracking-widest">Mulai Sekarang</a>
                @endauth
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <header class="relative pt-40 pb-24 lg:pt-56 lg:pb-40 overflow-hidden bg-[#F8FAFC]">
        <div class="absolute top-0 right-0 -mr-20 -mt-20 w-[600px] h-[600px] bg-primary/5 rounded-full blur-3xl"></div>
        
        <div class="max-w-7xl mx-auto px-6 relative z-10">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-20 items-center">
                <div class="max-w-3xl">
                    <div class="inline-flex items-center gap-2 bg-white border border-slate-200 px-4 py-1.5 rounded-full mb-8 shadow-sm">
                        <span class="w-1.5 h-1.5 rounded-full bg-primary animate-pulse"></span>
                        <span class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Digital Private Bank For Home</span>
                    </div>
                    
                    <h1 class="text-6xl lg:text-8xl font-extrabold text-slate-950 leading-[1] mb-10 tracking-tighter text-balance">
                        Rencanakan. <br/>
                        Kelola. <br/>
                        <span class="gradient-text">Wariskan.</span>
                    </h1>
                    
                    <p class="text-xl text-slate-500 mb-12 leading-relaxed max-w-xl font-medium">
                        Standardisasi manajemen kekayaan keluarga dengan presisi institusional. Gabungkan asisten AI, kolaborasi lintas generasi, dan keamanan militer dalam satu ekosistem.
                    </p>
                    
                    <div class="flex flex-col sm:flex-row gap-5 items-center">
                        <a href="{{ route('register') }}" class="w-full sm:w-auto bg-primary text-white px-10 py-5 rounded-2xl font-extrabold text-sm hover:translate-y-[-4px] hover:shadow-2xl hover:shadow-primary/30 transition-all text-center uppercase tracking-widest leading-none">
                            Reservasi Akses Eksklusif
                        </a>
                        <div class="flex -space-x-4">
                            <div class="w-10 h-10 rounded-full border-4 border-white bg-slate-200 overflow-hidden">
                                <img src="https://i.pravatar.cc/150?u=1" class="w-full h-full object-cover">
                            </div>
                            <div class="w-10 h-10 rounded-full border-4 border-white bg-slate-100 overflow-hidden">
                                <img src="https://i.pravatar.cc/150?u=2" class="w-full h-full object-cover">
                            </div>
                            <div class="w-10 h-10 rounded-full border-4 border-white bg-slate-200 overflow-hidden">
                                <img src="https://i.pravatar.cc/150?u=3" class="w-full h-full object-cover">
                            </div>
                            <div class="px-6 flex flex-col justify-center">
                                <p class="text-[10px] font-bold text-slate-900 leading-tight uppercase tracking-widest">+1.2k Families Joined</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="relative lg:scale-110">
                    <div class="relative z-10 float-anim">
                        <img src="{{ asset('brain/52b50d60-746a-4c9f-8d12-da3561afb9f7/famly_premium_hero_visual_1776647529505.png') }}" 
                             alt="Famly App Interface" 
                             class="rounded-[3rem] shadow-[0_50px_100px_-20px_rgba(0,0,0,0.15)] border-[12px] border-slate-950 w-full max-w-[420px] mx-auto overflow-hidden">
                    </div>
                    
                    <!-- Floating Stat 1 -->
                    <div class="absolute -top-10 -right-10 bg-white p-5 rounded-3xl shadow-2xl border border-slate-50 z-20 hidden lg:block">
                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1 leading-none">Efficiency Scan</p>
                        <h4 class="text-2xl font-bold text-primary">98.2%</h4>
                        <p class="text-[10px] font-bold text-slate-500 mt-1">AI Data Extraction</p>
                    </div>

                    <!-- Floating Stat 2 -->
                    <div class="absolute -bottom-10 -left-10 bg-white p-6 rounded-3xl shadow-2xl border border-slate-50 z-20 hidden lg:block">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="w-2 h-2 rounded-full bg-primary"></div>
                            <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest leading-none">Safety Protocol</p>
                        </div>
                        <h4 class="text-xl font-bold text-slate-900 leading-tight">AES-256 <br/>Military Grade</h4>
                    </div>
                    
                    <div class="absolute inset-0 bg-primary/20 blur-[120px] -z-10 rounded-full scale-75"></div>
                </div>
            </div>
        </div>
    </header>

    <!-- Bento Ecosystem Section -->
    <section id="features" class="py-32 lg:py-48 bg-white overflow-hidden">
        <div class="max-w-7xl mx-auto px-6">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 items-end mb-24">
                <div class="lg:col-span-8">
                    <p class="text-[11px] font-black text-primary uppercase tracking-[0.3em] mb-6">Unified Framework</p>
                    <h2 class="text-5xl lg:text-7xl font-extrabold text-slate-950 tracking-tighter leading-none text-balance">
                        Keputusan Finansial <br/> Tanpa Keraguan.
                    </h2>
                </div>
                <div class="lg:col-span-4">
                    <p class="text-lg text-slate-500 font-medium leading-relaxed mb-6">
                        Kami mengintegrasikan instrumen manajemen kekayaan kelas dunia ke dalam genggaman keluarga Anda.
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- Card 1 -->
                <div class="md:col-span-2 bento-gradient p-12 lg:p-16 rounded-[3rem] border border-slate-100 hover:shadow-2xl hover:shadow-primary/5 transition-all group overflow-hidden relative">
                    <div class="relative z-10 max-w-lg">
                        <div class="w-16 h-16 bg-primary rounded-2xl flex items-center justify-center mb-10 shadow-lg shadow-primary/20 text-white">
                            <span class="material-symbols-outlined text-[32px]">hub</span>
                        </div>
                        <h3 class="text-3xl font-extrabold mb-5 tracking-tight">Kedaulatan Data Berbasis Keluarga</h3>
                        <p class="text-slate-500 text-lg font-medium leading-relaxed mb-10">
                            Sinkronisasi tak terbatas antara rekening bank, investasi, dan agenda tagihan rutin. Anda memegang kendali penuh atas visibilitas data tiap anggota keluarga.
                        </p>
                    </div>
                    <!-- Abstract Visualization -->
                    <div class="absolute bottom-[-10%] right-[-10%] w-1/2 h-full opacity-5 group-hover:opacity-10 transition-all group-hover:scale-110">
                        <div class="w-full h-full border-[40px] border-primary rounded-full"></div>
                    </div>
                </div>

                <!-- Card 2 -->
                <div class="bg-slate-950 p-12 rounded-[3rem] flex flex-col justify-between text-white hover:translate-y-[-8px] transition-all duration-500">
                    <div>
                        <div class="w-14 h-14 bg-white/10 rounded-2xl flex items-center justify-center mb-10 text-primary">
                            <span class="material-symbols-outlined text-[28px]">payments</span>
                        </div>
                        <h3 class="text-2xl font-bold mb-4 tracking-tight">Multi-Wallet Hub</h3>
                        <p class="text-slate-400 text-sm font-medium leading-relaxed">
                            Alokasikan dana untuk pendidikan, liburan, atau zakat dengan presisi nol rupiah.
                        </p>
                    </div>
                    <div class="mt-12 pt-8 border-t border-white/10">
                        <div class="flex justify-between items-center text-[10px] font-black uppercase tracking-widest text-slate-500">
                            <span>Status</span>
                            <span class="text-emerald-400">Optimized</span>
                        </div>
                    </div>
                </div>

                <!-- Card 3 -->
                <div class="bg-emerald-light p-12 rounded-[3rem] border border-emerald-100 hover:translate-y-[-8px] transition-all">
                    <div class="w-14 h-14 bg-white rounded-2xl flex items-center justify-center mb-10 text-primary shadow-sm">
                        <span class="material-symbols-outlined text-[28px]">family_restroom</span>
                    </div>
                    <h3 class="text-2xl font-bold mb-4 tracking-tight text-slate-900 leading-tight">Kolaborasi Lintas <br/>Generasi</h3>
                    <p class="text-slate-600 text-sm font-medium leading-relaxed">
                        Platform pertama yang dirancang untuk kakek, orang tua, hingga cucu berinteraksi dalam satu peta kekayaan.
                    </p>
                </div>

                <!-- Card 4 -->
                <div class="md:col-span-2 bento-gradient p-12 lg:p-16 rounded-[3rem] border border-slate-100 hover:shadow-2xl transition-all flex flex-col lg:flex-row gap-12 items-center">
                    <div class="lg:w-1/2">
                        <h3 class="text-3xl font-extrabold mb-6 tracking-tight">Analitik Visual Deep-Dive</h3>
                        <p class="text-slate-500 text-lg font-medium leading-relaxed mb-6">
                            Bukan sekadar angka, tapi narasi finansial. Lihat visualisasi tren mingguan yang memisahkan antara kebutuhan krusial dan gaya hidup.
                        </p>
                        <a href="#" class="text-primary font-bold text-sm uppercase tracking-widest hover:gap-4 transition-all flex items-center gap-2">
                            Explore Dashboards <span class="material-symbols-outlined">north_east</span>
                        </a>
                    </div>
                    <div class="lg:w-1/2 w-full h-48 bg-white rounded-2xl border border-slate-100 p-6 shadow-xl relative overflow-hidden flex items-end gap-3">
                        <div class="flex-1 bg-primary/20 h-[40%] rounded-t-lg"></div>
                        <div class="flex-1 bg-primary/40 h-[60%] rounded-t-lg"></div>
                        <div class="flex-1 bg-primary h-[95%] rounded-t-lg"></div>
                        <div class="flex-1 bg-primary/60 h-[70%] rounded-t-lg"></div>
                        <div class="flex-1 bg-primary/30 h-[50%] rounded-t-lg"></div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- AI Intelligence Section -->
    <section id="ai" class="py-32 lg:py-56 bg-slate-950 text-white relative overflow-hidden">
        <div class="absolute top-1/2 left-0 w-[500px] h-[500px] bg-primary/10 rounded-full blur-[150px] -translate-x-1/2 -translate-y-1/2"></div>
        
        <div class="max-w-7xl mx-auto px-6 relative z-10">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-24 items-center">
                <div>
                    <span class="inline-block bg-primary px-4 py-1 rounded-full text-[10px] font-black uppercase tracking-[0.2em] mb-10">Neural Intelligence</span>
                    <h2 class="text-5xl lg:text-7xl font-extrabold tracking-tighter mb-10 leading-[1] text-balance">
                        Asisten Keuangan <br/><span class="text-primary italic">Cerdas Personal.</span>
                    </h2>
                    <p class="text-xl text-slate-400 font-medium leading-relaxed mb-16 max-w-xl">
                        AI kami dilatih khusus untuk memahami konteks keuangan rumah tangga di Indonesia. Scan struk belanja, catat suara transaksi, atau tanyakan saran investasi — semua dilakukan secara instan.
                    </p>

                    <div class="space-y-10">
                        <div class="flex gap-6">
                            <div class="w-12 h-12 rounded-xl bg-white/10 flex items-center justify-center text-primary shrink-0 mt-1">
                                <span class="material-symbols-outlined">scan_delete</span>
                            </div>
                            <div>
                                <h4 class="text-xl font-bold mb-2">Omni-Channel Capture</h4>
                                <p class="text-slate-500 font-medium">Capture transaksi via teks, audio voice-note, hingga scan struk belanja fisik dengan presisi 99%.</p>
                            </div>
                        </div>
                        <div class="flex gap-6">
                            <div class="w-12 h-12 rounded-xl bg-white/10 flex items-center justify-center text-primary shrink-0 mt-1">
                                <span class="material-symbols-outlined">insights</span>
                            </div>
                            <div>
                                <h4 class="text-xl font-bold mb-2">Predictive Wealth Planning</h4>
                                <p class="text-slate-500 font-medium">Menganalisis pola historis untuk memberikan prediksi saldo akhir bulan dan saran alokasi dana darurat.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="relative">
                    <div class="bg-gradient-to-br from-slate-900 to-slate-800 p-1 rounded-[3rem] shadow-3xl">
                        <div class="bg-slate-900 rounded-[2.8rem] p-10 lg:p-14 overflow-hidden relative">
                            <div class="flex justify-between items-center mb-10 border-b border-white/10 pb-8">
                                <div class="flex items-center gap-4">
                                    <div class="w-12 h-12 bg-primary rounded-full flex items-center justify-center font-black text-xs">AI</div>
                                    <div>
                                        <p class="text-[10px] font-black text-slate-500 uppercase tracking-widest leading-none mb-1">Si Famly</p>
                                        <p class="text-xs font-bold">Active Assistant</p>
                                    </div>
                                </div>
                                <div class="w-2.5 h-2.5 rounded-full bg-emerald-500 shadow-[0_0_15px_rgba(16,185,129,0.5)]"></div>
                            </div>

                            <div class="space-y-8 min-h-[300px]">
                                <div class="bg-white/5 p-6 rounded-2xl rounded-tl-none max-w-[85%] border border-white/5">
                                    <p class="text-sm text-slate-300 font-medium leading-relaxed italic">"Bapak, scan struk tadi terdeteksi pengeluaran Rp 450.000 untuk 'Groceries'. Tabungan pendidikan anak masih aman di sisa Rp 12.4M."</p>
                                </div>
                                <div class="flex justify-end">
                                    <div class="bg-primary/20 p-6 rounded-2xl rounded-tr-none max-w-[85%] border border-primary/20">
                                        <p class="text-sm text-emerald-100 font-bold leading-relaxed">"Terima kasih. Masukkan ke pos operasional dapur."</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mt-12 bg-white/5 rounded-2xl p-5 flex items-center gap-4 border border-white/5">
                                <div class="w-4 h-4 rounded-full bg-primary/50 animate-ping"></div>
                                <p class="text-xs font-bold text-slate-500 uppercase tracking-widest leading-none">Mendengarkan Transaksi...</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Trust & Legacy Section -->
    <section id="security" class="py-32 lg:py-48 bg-[#F8FAFC]">
        <div class="max-w-7xl mx-auto px-6 text-center mb-32">
            <h2 class="text-4xl lg:text-6xl font-extrabold text-slate-950 mb-10 tracking-tighter">Standar Keamanan Institusional.</h2>
            <div class="flex flex-wrap justify-center gap-16 opacity-40 grayscale hover:grayscale-0 transition-all duration-700">
                <span class="text-xl font-bold tracking-widest">ISO 27001</span>
                <span class="text-xl font-bold tracking-widest">AES-256</span>
                <span class="text-xl font-bold tracking-widest">TLS 1.3</span>
                <span class="text-xl font-bold tracking-widest">2FA</span>
            </div>
        </div>

        <div class="max-w-7xl mx-auto px-6 grid grid-cols-1 lg:grid-cols-2 gap-20 items-center">
            <div class="relative order-2 lg:order-1">
                <div class="bg-white p-12 lg:p-20 rounded-[3rem] shadow-xl border border-slate-100 relative z-10">
                    <div class="w-16 h-16 bg-slate-100 rounded-2xl flex items-center justify-center text-slate-900 mb-10">
                        <span class="material-symbols-outlined text-[32px]">verified_user</span>
                    </div>
                    <h3 class="text-4xl font-extrabold mb-6 tracking-tight text-slate-900 leading-none">Waris Digital <br/>(The Heritage Protocol)</h3>
                    <p class="text-slate-500 text-lg font-medium leading-relaxed mb-10">
                        Satu-satunya platform yang menyediakan protokol transfer aset digital otomatis bagi keluarga jika terjadi hal di luar dugaan. Memastikan harta Anda tetap terjaga bagi generasi penerus.
                    </p>
                    <ul class="space-y-6">
                        <li class="flex items-center gap-4 text-slate-800 font-bold">
                            <span class="material-symbols-outlined text-primary">security</span> Multi-Factor Vault Authorization
                        </li>
                        <li class="flex items-center gap-4 text-slate-800 font-bold">
                            <span class="material-symbols-outlined text-primary">gavel</span> Validasi Hukum & Protokol Waris
                        </li>
                    </ul>
                </div>
                <div class="absolute inset-0 bg-primary/5 rounded-[3rem] rotate-3 scale-105 -z-10 border border-primary/20"></div>
            </div>

            <div class="order-1 lg:order-2">
                <p class="text-[11px] font-black text-primary uppercase tracking-[0.3em] mb-6">Trust & Reliability</p>
                <h3 class="text-5xl font-extrabold text-slate-900 mb-10 leading-[1.1] tracking-tighter">Bukan Sekadar Aplikasi, <br/>Tapi Amanah Digital.</h3>
                <p class="text-xl text-slate-500 font-medium leading-relaxed mb-12">
                    Kami memahami bahwa data keuangan adalah privasi paling sakral dalam keluarga. Famly menggunakan enkripsi *zero-knowledge* — bahkan tim internal kami tidak dapat melihat isi pundi-pundi Anda.
                </p>
                <div class="grid grid-cols-2 gap-8 pt-10 border-t border-slate-200">
                    <div>
                        <h4 class="text-3xl font-extrabold text-slate-900 mb-2 tracking-tighter leading-none">100%</h4>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Privacy Guarantee</p>
                    </div>
                    <div>
                        <h4 class="text-3xl font-extrabold text-slate-900 mb-2 tracking-tighter leading-none">24/7</h4>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">AI Audit Monitoring</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Pricing Section -->
    <section id="pricing" class="py-32 lg:py-56 bg-white relative overflow-hidden">
        <div class="max-w-7xl mx-auto px-6">
            <div class="bg-primary-dark rounded-[4rem] p-12 lg:p-24 text-white flex flex-col lg:flex-row items-center justify-between shadow-3xl relative overflow-hidden">
                <div class="absolute top-0 right-0 w-[400px] h-[400px] bg-white opacity-5 rounded-full -translate-y-1/2 translate-x-1/2 blur-3xl"></div>
                
                <div class="max-w-2xl relative z-10 text-center lg:text-left mb-16 lg:mb-0">
                    <h2 class="text-5xl lg:text-6xl font-extrabold tracking-tighter mb-8 leading-none">Akses Premium. <br/>Masa Depan Terjamin.</h2>
                    <p class="text-emerald-100/70 text-xl font-medium leading-relaxed max-w-lg mb-10">
                        Gantikan ratusan spreadsheet manual dengan satu solusi cerdas seharga secangkir kopi boutique.
                    </p>
                    <div class="flex flex-wrap gap-4 justify-center lg:justify-start">
                        <div class="bg-white/10 px-5 py-3 rounded-2xl flex items-center gap-3">
                            <span class="material-symbols-outlined text-primary">done_all</span>
                            <span class="text-xs font-bold uppercase tracking-widest leading-none">7 Days Trial</span>
                        </div>
                        <div class="bg-white/10 px-5 py-3 rounded-2xl flex items-center gap-3">
                            <span class="material-symbols-outlined text-primary">done_all</span>
                            <span class="text-xs font-bold uppercase tracking-widest leading-none">No Credit Card</span>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-[3rem] p-12 lg:p-16 text-slate-900 w-full max-w-md shadow-2xl relative z-10 shrink-0">
                    <span class="bg-primary/10 text-primary text-[10px] font-black px-4 py-1.5 rounded-full mb-8 inline-block tracking-widest uppercase">Best Value</span>
                    <h3 class="text-4xl font-extrabold mb-2 tracking-tight">Family Premium</h3>
                    <div class="flex items-baseline gap-2 mb-10 border-b border-slate-100 pb-10">
                        <span class="text-5xl font-extrabold tracking-tighter text-slate-900">Rp 24,9k</span>
                        <span class="text-slate-400 font-bold">/ bulan</span>
                    </div>
                    
                    <ul class="space-y-6 mb-12">
                        <li class="flex items-center gap-4 text-sm font-bold text-slate-700">
                            <span class="material-symbols-outlined text-primary">check_circle</span> Unlimited AI Assistant
                        </li>
                        <li class="flex items-center gap-4 text-sm font-bold text-slate-700">
                            <span class="material-symbols-outlined text-primary">check_circle</span> Unlimited Budgets & Wallets
                        </li>
                        <li class="flex items-center gap-4 text-sm font-bold text-slate-700">
                            <span class="material-symbols-outlined text-primary">check_circle</span> Digital Heritage Vault
                        </li>
                        <li class="flex items-center gap-4 text-sm font-bold text-slate-700">
                            <span class="material-symbols-outlined text-primary">check_circle</span> All Family Member Access
                        </li>
                    </ul>

                    <a href="{{ route('register') }}" class="w-full bg-primary text-white py-5 rounded-2xl font-extrabold text-xs uppercase tracking-[0.2em] shadow-xl shadow-primary/20 hover:bg-primary-dark transition-all inline-block text-center active:scale-95">
                        Mulai Sekarang
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- App Install Section (PWA) -->
    <section class="py-24 bg-white">
        <div class="max-w-7xl mx-auto px-6 border-t border-slate-100 pt-24 text-center">
            <h3 class="text-3xl font-extrabold text-slate-900 mb-6 tracking-tight leading-none text-balance max-w-2xl mx-auto">Tersedia untuk Seluruh Perangkat. <br/> Tanpa Download, Cukup Pasang.</h3>
            <p class="text-slate-500 font-medium mb-12">Tambahkan ke Home Screen untuk pengalaman native penuh.</p>
            
            <div class="flex flex-wrap justify-center gap-6">
                <div class="flex items-center gap-3 bg-slate-950 text-white px-8 py-4 rounded-2xl hover:bg-primary transition-all cursor-default">
                    <span class="material-symbols-outlined">apple</span>
                    <div class="text-left">
                        <p class="text-[8px] font-bold uppercase opacity-50 leading-none mb-1">Add to</p>
                        <p class="text-sm font-bold leading-none">Apple iOS</p>
                    </div>
                </div>
                <div class="flex items-center gap-3 bg-slate-950 text-white px-8 py-4 rounded-2xl hover:bg-primary transition-all cursor-default text-left">
                    <span class="material-symbols-outlined">phone_android</span>
                    <div class="text-left">
                        <p class="text-[8px] font-bold uppercase opacity-50 leading-none mb-1">Install on</p>
                        <p class="text-sm font-bold leading-none">Android OS</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-white pt-32 pb-16 border-t border-slate-100">
        <div class="max-w-7xl mx-auto px-6">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-20 mb-24 items-start">
                <div class="lg:col-span-5">
                    <div class="flex items-center gap-2.5 mb-8">
                        <div class="w-10 h-10 bg-primary rounded-xl flex items-center justify-center text-white font-bold text-xl">F</div>
                        <span class="font-extrabold text-2xl tracking-tighter text-slate-900">Famly.</span>
                    </div>
                    <p class="text-slate-500 font-medium text-lg leading-relaxed mb-10 max-w-md">
                        Digital Private Bank for Home. Memberdayakan keluarga Indonesia untuk mengelola warisan dengan teknologi masa depan.
                    </p>
                    <div class="flex gap-4">
                        <a href="#" class="w-10 h-10 bg-slate-100 rounded-full flex items-center justify-center text-slate-600 hover:bg-primary hover:text-white transition-all"><span class="material-symbols-outlined text-sm">alternate_email</span></a>
                        <a href="#" class="w-10 h-10 bg-slate-100 rounded-full flex items-center justify-center text-slate-600 hover:bg-primary hover:text-white transition-all"><span class="material-symbols-outlined text-sm">share</span></a>
                    </div>
                </div>

                <div class="lg:col-span-7 grid grid-cols-2 md:grid-cols-3 gap-10">
                    <div>
                        <h5 class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-8">Ekosistem</h5>
                        <ul class="space-y-4 text-sm font-bold text-slate-600">
                            <li><a href="#" class="hover:text-primary transition-all">Asisten AI</a></li>
                            <li><a href="#" class="hover:text-primary transition-all">Manajemen Pos</a></li>
                            <li><a href="#" class="hover:text-primary transition-all">Analitik Lanjut</a></li>
                        </ul>
                    </div>
                    <div>
                        <h5 class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-8">Perusahaan</h5>
                        <ul class="space-y-4 text-sm font-bold text-slate-600">
                            <li><a href="#" class="hover:text-primary transition-all">Privasi</a></li>
                            <li><a href="#" class="hover:text-primary transition-all">Syarat Layanan</a></li>
                            <li><a href="#" class="hover:text-primary transition-all">Kontak</a></li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="flex flex-col md:flex-row justify-between items-center gap-8 pt-12 border-t border-slate-100 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">
                <p>© 2026 PT DIGI ANTARA MASA - TERDAFTAR SECARA RESMI DI JAKARTA.</p>
                <div class="flex items-center gap-4">
                    <span class="w-2 h-2 rounded-full bg-primary"></span>
                    <p>Designed for Legacy. For Indonesia.</p>
                </div>
            </div>
        </div>
    </footer>

</body>
</html>
