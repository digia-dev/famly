<x-guest-layout>
    <main class="w-full max-w-md bg-white rounded-sm shadow-xl overflow-hidden flex flex-col mx-auto">
        <!-- Header Section -->
        <div class="h-48 relative overflow-hidden bg-[#e4e2e4]">
            <div class="absolute inset-0 bg-gradient-to-br from-[#006D36] to-[#50C878] opacity-10"></div>
            <img class="w-full h-full object-cover mix-blend-overlay" src="https://lh3.googleusercontent.com/aida-public/AB6AXuBavacTWx9Ok5hSdb1p-rKHb2gaUEo6VQjEOlsWIMyBd7NZd7qvrvPgw_LBvTdMeF8m17YxErNM7GdINAvAwhAiWgMSANsMIJQPdzNJ6VICI7TTBLuwLBDm5vG74OS4gk-9l2CjaFDINf1hqqgqVt74onWtrX7B9MgBcOwPD_Kc1Q9tgJjH-x5IADQ9K5_pqDGKSZxQa8px8kDwUc0Ygau3mrNRZ4AVYsGSK04bAm9YZdkbPsNFLmcOufsT44lLmCjkFFdccDHDbcc" />
            <div class="absolute bottom-6 left-6">
                <div class="text-[#006D36] font-black tracking-tighter text-3xl mb-1">Famly</div>
                <div class="h-[2px] w-12 bg-[#735c00]"></div>
            </div>
        </div>

        <!-- Form Canvas -->
        <div class="p-8">
            <header class="mb-8">
                <h1 class="text-2xl font-bold text-[#1b1b1d] tracking-tight leading-none">Lupa Kata Sandi?</h1>
                <p class="text-[#3e4a3f] text-[10px] mt-2 font-medium uppercase tracking-widest">Pemulihan Akses Keamanan</p>
            </header>

            <div class="mb-6 text-xs text-[#6e7a6e] font-medium leading-relaxed">
                Jangan khawatir. Masukkan alamat email Anda dan kami akan mengirimkan tautan pemulihan kata sandi yang memungkinkan Anda membuat yang baru.
            </div>

            <x-auth-session-status class="mb-4" :status="session('status')" />

            <form method="POST" action="{{ route('password.email') }}" class="space-y-6">
                @csrf

                <!-- Input Module: Email -->
                <div class="relative flex flex-col bg-[#f0edef] p-3 rounded-sm group transition-all duration-200 border-l-2 border-transparent focus-within:border-[#006d36]">
                    <label class="text-[10px] font-bold uppercase tracking-wider text-[#3e4a3f] mb-1">Email</label>
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-sm text-[#6e7a6e]">alternate_email</span>
                        <input name="email" value="{{ old('email') }}" required autofocus
                               class="bg-transparent border-none p-0 w-full text-sm font-medium focus:ring-0 placeholder:text-[#bdcabc] text-[#1b1b1d]" 
                               placeholder="nama@email.com" type="email" />
                    </div>
                </div>
                <x-input-error :messages="$errors->get('email')" class="mt-1" />

                <!-- Primary Action -->
                <div class="pt-2">
                    <button type="submit" class="w-full h-12 bg-gradient-to-r from-[#006d36] to-[#50c878] text-white font-bold text-sm rounded-sm active:scale-[0.98] transition-all duration-100 flex items-center justify-center gap-2 shadow-sm shadow-[#006d36]/10">
                        <span>KIRIM TAUTAN RESET</span>
                        <span class="material-symbols-outlined text-lg">mail</span>
                    </button>
                </div>
            </form>

            <!-- Navigation Link -->
            <footer class="mt-8 flex flex-col items-center gap-6">
                <a class="text-xs font-bold text-[#3e4a3f] hover:text-[#006d36] transition-colors flex items-center gap-2" href="{{ route('login') }}">
                    <span class="material-symbols-outlined text-sm">chevron_left</span> Kembali ke <span class="text-[#006d36]">Masuk</span>
                </a>
                
                <!-- System Metadata -->
                <div class="flex items-center gap-4 opacity-40">
                    <div class="w-8 h-[1px] bg-[#bdcabc]"></div>
                    <span class="text-[9px] font-bold tracking-widest uppercase text-[#3e4a3f]">Security Protocols</span>
                    <div class="w-8 h-[1px] bg-[#bdcabc]"></div>
                </div>
            </footer>
        </div>
    </main>

    {{-- Background Decoration --}}
    <div class="fixed inset-0 -z-10 overflow-hidden pointer-events-none opacity-20">
        <div class="absolute top-0 right-0 w-[500px] h-[500px] bg-[#50c878] blur-[120px] rounded-full -translate-y-1/2 translate-x-1/2"></div>
        <div class="absolute bottom-0 left-0 w-[300px] h-[300px] bg-[#fed65b] blur-[100px] rounded-full translate-y-1/2 -translate-x-1/2"></div>
    </div>
</x-guest-layout>
