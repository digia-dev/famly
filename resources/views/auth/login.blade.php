<x-guest-layout>
    <main class="w-full max-w-md bg-white min-h-[600px] flex flex-col mx-auto px-6 py-8 relative overflow-hidden">
        
        <!-- Navigation -->
        <div class="mb-10">
            <a href="/" class="w-10 h-10 rounded-full bg-slate-50 flex items-center justify-center hover:bg-slate-100 transition-all">
                <span class="material-symbols-outlined text-slate-600">arrow_back</span>
            </a>
        </div>

        <!-- Brand Header -->
        <header class="mb-12">
            <div class="flex items-center gap-3 mb-6">
                <div class="w-12 h-12 bg-[#00AA13] rounded-2xl flex items-center justify-center shadow-lg shadow-[#00AA13]/20">
                    <span class="text-white font-black text-2xl tracking-tighter">F</span>
                </div>
                <div>
                    <h2 class="text-2xl font-black text-slate-900 tracking-tighter">Famly</h2>
                    <p class="text-[10px] font-bold text-[#00AA13] tracking-widest uppercase">Smart Household Hub</p>
                </div>
            </div>
            
            <h1 class="text-4xl font-extrabold text-slate-900 tracking-tight leading-none">Selamat Datang</h1>
            <p class="text-slate-500 text-sm font-medium mt-3">Silakan masuk untuk melanjutkan aktivitas keluarga Anda.</p>
        </header>

        <x-auth-session-status class="mb-6" :status="session('status')" />

        <form method="POST" action="{{ route('login') }}" class="flex-1 flex flex-col">
            @csrf

            <div class="space-y-5">
                <!-- Input Module: Email -->
                <div class="space-y-2">
                    <label class="text-[11px] font-bold text-slate-500 uppercase tracking-widest ml-1">Email</label>
                    <div class="relative flex items-center">
                        <span class="material-symbols-outlined absolute left-4 text-slate-400 text-[20px]">alternate_email</span>
                        <input name="email" value="{{ old('email') }}" required autofocus autocomplete="username"
                               class="w-full h-14 pl-12 pr-6 bg-slate-50 border-none rounded-2xl text-sm font-bold placeholder:text-slate-300 focus:ring-2 focus:ring-[#00AA13] transition-all text-slate-900" 
                               placeholder="admin@famly.id" type="email" />
                    </div>
                    <x-input-error :messages="$errors->get('email')" class="mt-1 ml-1" />
                </div>

                <!-- Input Module: Password -->
                <div class="space-y-2">
                    <label class="text-[11px] font-bold text-slate-500 uppercase tracking-widest ml-1">Kata Sandi</label>
                    <div class="relative flex items-center">
                        <span class="material-symbols-outlined absolute left-4 text-slate-400 text-[20px]">lock</span>
                        <input name="password" required autocomplete="current-password"
                               class="w-full h-14 pl-12 pr-6 bg-slate-50 border-none rounded-2xl text-sm font-bold placeholder:text-slate-300 focus:ring-2 focus:ring-[#00AA13] transition-all text-slate-900" 
                               placeholder="••••••••" type="password" />
                    </div>
                    <x-input-error :messages="$errors->get('password')" class="mt-1 ml-1" />
                </div>

                <div class="flex items-center justify-between py-2">
                    <label for="remember_me" class="inline-flex items-center cursor-pointer group">
                        <input id="remember_me" type="checkbox" class="rounded-md border-slate-200 text-[#00AA13] focus:ring-[#00AA13] w-5 h-5 transition-all" name="remember">
                        <span class="ml-3 text-xs font-bold text-slate-500 group-hover:text-slate-700">Ingat Saya</span>
                    </label>

                    @if (Route::has('password.request'))
                        <a class="text-xs font-bold text-[#00AA13] hover:underline" href="{{ route('password.request') }}">
                            Lupa Sandi?
                        </a>
                    @endif
                </div>
            </div>

            <!-- Primary Action -->
            <div class="mt-auto pt-10 pb-6 space-y-6">
                <button type="submit" class="w-full h-14 bg-[#00AA13] hover:bg-[#008f10] text-white font-extrabold text-sm rounded-full shadow-xl shadow-[#00AA13]/20 active:scale-95 transition-all duration-200 flex items-center justify-center gap-3 uppercase tracking-widest">
                    <span>MASUK</span>
                    <span class="material-symbols-outlined text-xl">east</span>
                </button>

                <div class="flex items-center gap-4">
                    <div class="h-[1px] bg-slate-100 flex-1"></div>
                    <span class="text-[10px] font-black text-slate-300 uppercase tracking-widest">Atau</span>
                    <div class="h-[1px] bg-slate-100 flex-1"></div>
                </div>

                <button type="button" class="w-full h-14 bg-white border border-slate-100 text-slate-900 font-bold text-sm rounded-full hover:bg-slate-50 transition-all active:scale-95 flex items-center justify-center gap-4 shadow-sm">
                    <svg class="w-5 h-5" viewBox="0 0 24 24">
                        <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
                        <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                        <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.26.81-.58z" fill="#FBBC05"/>
                        <path d="M12 5.38c1.62 0 3.06.56 4.21 1.66l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
                    </svg>
                    <span>Google Account</span>
                </button>

                <footer class="text-center pt-4">
                    <a href="{{ route('register') }}" class="text-sm font-bold text-slate-500 hover:text-[#00AA13] transition-all">
                        Belum punya akun? <span class="text-[#00AA13]">Daftar di sini</span>
                    </a>
                </footer>
            </div>
        </form>

        <!-- Abstract Decoration -->
        <div class="absolute -bottom-10 -right-10 w-40 h-40 bg-[#00AA13]/5 rounded-full blur-3xl"></div>
    </main>
</x-guest-layout>
