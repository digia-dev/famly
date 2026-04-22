<x-app-layout>
    @section('title', 'Memverifikasi Pembayaran')

    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif !important; background-color: #FFFFFF; }
        .spinner {
            width: 48px;
            height: 48px;
            border: 5px solid #F0FAF1;
            border-bottom-color: #00AA13;
            border-radius: 50%;
            display: inline-block;
            box-sizing: border-box;
            animation: rotation 1s linear infinite;
        }
        @keyframes rotation {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>

    <div class="min-h-screen flex flex-col items-center justify-center text-center px-10 pb-40" 
         x-data="{ countdown: 3 }" 
         x-init="
            const interval = setInterval(() => {
                countdown--;
                if (countdown === 0) {
                    clearInterval(interval);
                    document.getElementById('finalize-form').submit();
                }
            }, 1000);
         ">
        
        <div class="mb-10">
            <span class="spinner"></span>
        </div>

        <h1 class="text-xl font-black text-slate-800 tracking-tight mb-3">Memverifikasi Pembayaran</h1>
        <p class="text-[13px] font-bold text-slate-400 leading-relaxed max-w-[240px]">
            Sedang mengonfirmasi transaksi Anda melalui <span class="text-slate-900">{{ $method }}</span>...
        </p>

        <!-- Hidden form to finalize -->
        <form id="finalize-form" action="{{ route('checkout.finalize') }}" method="POST" class="hidden">
            @csrf
        </form>

        <div class="mt-12 flex flex-col items-center">
            <div class="flex items-center gap-2 justify-center mb-6">
                <span class="w-1.5 h-1.5 rounded-full bg-[#00AA13] animate-bounce" style="animation-delay: 0.1s"></span>
                <span class="w-1.5 h-1.5 rounded-full bg-[#00AA13] animate-bounce" style="animation-delay: 0.2s"></span>
                <span class="w-1.5 h-1.5 rounded-full bg-[#00AA13] animate-bounce" style="animation-delay: 0.3s"></span>
            </div>
            
            <div class="flex items-center gap-6 opacity-30 grayscale hover:grayscale-0 transition-all duration-500">
                <p class="text-[9px] font-black tracking-widest text-slate-400">SECURED BY</p>
                <div class="flex gap-4">
                    <span class="text-[12px] font-black text-slate-800">MIDTRANS</span>
                    <span class="text-[12px] font-black text-slate-800">XENDIT</span>
                    <span class="text-[12px] font-black text-slate-800">MAYAR</span>
                </div>
            </div>
            
            <p class="text-[10px] font-black text-[#00AA13] uppercase tracking-widest mt-8">Security Shield Active</p>
        </div>
    </div>
</x-app-layout>
