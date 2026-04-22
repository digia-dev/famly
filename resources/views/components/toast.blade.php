@props(['type' => 'info', 'message' => ''])

<div x-data="{ 
        show: true, 
        init() { 
            setTimeout(() => this.show = false, 5000) 
        } 
    }" 
    x-show="show" 
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0 translate-y-4"
    x-transition:enter-end="opacity-100 translate-y-0"
    x-transition:leave="transition ease-in duration-300"
    x-transition:leave-start="opacity-100 translate-y-0"
    x-transition:leave-end="opacity-0 translate-y-4"
    class="fixed bottom-24 left-4 right-4 z-[200] flex justify-center pointer-events-none">
    
    <div class="pointer-events-auto bg-white/95 backdrop-blur-xl border {{ $type === 'success' ? 'border-emerald-100 shadow-emerald-500/10' : ($type === 'error' ? 'border-rose-100 shadow-rose-500/10' : 'border-slate-100 shadow-slate-500/10') }} rounded-[24px] px-4 py-3 shadow-2xl flex items-center gap-3 animate-bounce-subtle">
        <div class="w-10 h-10 rounded-2xl flex items-center justify-center 
            {{ $type === 'success' ? 'bg-emerald-500 text-white' : ($type === 'error' ? 'bg-rose-500 text-white' : 'bg-primary text-white') }}">
            <span class="material-symbols-outlined text-[20px]">
                {{ $type === 'success' ? 'check_circle' : ($type === 'error' ? 'error' : 'info') }}
            </span>
        </div>
        <div class="pr-2">
            <p class="text-[12px] font-black text-slate-800 tracking-tight leading-tight">{{ $message }}</p>
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-tighter mt-0.5">Baru Saja</p>
        </div>
        <button @click="show = false" class="text-slate-300 hover:text-slate-500 transition-colors">
            <span class="material-symbols-outlined text-sm">close</span>
        </button>
    </div>
</div>

<style>
    @keyframes bounce-subtle {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-3px); }
    }
    .animate-bounce-subtle {
        animation: bounce-subtle 3s infinite ease-in-out;
    }
</style>
