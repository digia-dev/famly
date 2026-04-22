@php
$user = Auth::user();
@endphp

@unless(request()->routeIs(['ai.assistant', 'help']))
<!-- DESKTOP SIDEBAR -->
<aside class="hidden sm:flex flex-col w-64 bg-slate-50/50 backdrop-blur-xl fixed inset-y-0 left-0 z-50 overflow-y-auto shadow-[4px_0_24px_rgba(0,0,0,0.02)]">
    <div class="h-16 flex items-center px-6">
        <a href="{{ route('admin.dashboard') }}" class="text-2xl font-black tracking-tighter text-primary no-underline font-['Helvetica']">Famly</a>
    </div>

    
    <nav class="flex-1 p-4 space-y-2">
        <p class="px-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-4">Navigasi Utama</p>
        
        <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 px-4 py-3 rounded-2xl transition-all {{ request()->routeIs('admin.dashboard') ? 'bg-primary/10 text-primary font-bold shadow-sm' : 'text-slate-600 hover:bg-slate-50' }}">
            <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' {{ request()->routeIs('admin.dashboard') ? 1 : 0 }};">home</span>
            <span class="text-sm">Beranda</span>
        </a>

        <a href="{{ route('management.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-2xl transition-all {{ request()->routeIs('management.*') ? 'bg-primary/10 text-primary font-bold shadow-sm' : 'text-slate-600 hover:bg-slate-50' }}">
            <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' {{ request()->routeIs('management.*') ? 1 : 0 }};">account_balance_wallet</span>
            <span class="text-sm">Dompet</span>
        </a>

        <a href="{{ route('agenda.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-2xl transition-all {{ request()->routeIs('agenda.*') ? 'bg-primary/10 text-primary font-bold shadow-sm' : 'text-slate-600 hover:bg-slate-50' }}">
            <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' {{ request()->routeIs('agenda.*') ? 1 : 0 }};">event_available</span>
            <span class="text-sm">Agenda</span>
        </a>

        <div class="pt-6">
            <p class="px-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-4">Wawasan AI</p>
            <a href="{{ route('ai.assistant') }}" class="flex items-center gap-3 px-4 py-3 rounded-2xl transition-all {{ request()->routeIs('ai.assistant') ? 'bg-primary/10 text-primary font-bold shadow-sm' : 'text-slate-600 hover:bg-slate-50' }}">
                <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' {{ request()->routeIs('ai.assistant') ? 1 : 0 }};">forum</span>
                <span class="text-sm">Chat AI</span>
            </a>
            <a href="{{ route('reports.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-2xl transition-all {{ request()->routeIs('reports.*') ? 'bg-primary/10 text-primary font-bold shadow-sm' : 'text-slate-600 hover:bg-slate-50' }}">
                <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' {{ request()->routeIs('reports.*') ? 1 : 0 }};">bar_chart_4_bars</span>
                <span class="text-sm">Laporan</span>
            </a>
        </div>
    </nav>

    <div class="p-4">
        <div class="flex items-center gap-3 px-3 py-4 bg-white rounded-3xl shadow-sm border-none">
            <div class="w-10 h-10 rounded-full bg-slate-100 flex items-center justify-center overflow-hidden">
                <img class="w-full h-full object-cover" src="{{ Auth::user()->profile_photo_url ?? 'https://ui-avatars.com/api/?name='.Auth::user()->name.'&background=006d36&color=fff' }}" alt="{{ Auth::user()->name }}">
            </div>
            <div class="flex-1 overflow-hidden">
                <p class="text-[12px] font-black text-slate-900 truncate tracking-tight">{{ Auth::user()->name }}</p>
                <div class="flex items-center gap-1">
                    <div class="w-1 h-1 rounded-full bg-primary animate-pulse"></div>
                    <p class="text-[9px] text-primary uppercase font-black tracking-tighter">
                        {{ strtoupper(Auth::user()->subscription_status ?? 'Trial') }}
                    </p>
                </div>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="material-symbols-outlined text-slate-300 hover:text-red-500 transition-colors text-[20px]">logout</button>
            </form>
        </div>
    </div>
</aside>
@endunless

<!-- TOP BAR (Unified for Internal Pages) -->
<nav class="fixed top-0 left-0 right-0 {{ request()->is('admin/dashboard*') || request()->routeIs(['ai.assistant', 'help', 'reports.index']) ? 'hidden' : 'sm:left-64' }} z-[60] bg-white/80 backdrop-blur-xl h-16 flex items-center transition-all duration-300 shadow-[0_4px_24px_rgba(0,0,0,0.02)]">
    <div class="w-full px-6 flex items-center justify-between">
        <div class="flex items-center gap-4">
            @if(request()->routeIs(['notification.index', 'agenda.*', 'management.*', 'profile.edit', 'transaction.*', 'eksplor.index']))
                <a href="javascript:history.back()" class="w-10 h-10 -ml-3 rounded-2xl bg-slate-50 text-primary flex items-center justify-center active:scale-90 transition-all">
                    <span class="material-symbols-outlined text-[20px] font-black">arrow_back</span>
                </a>
            @endif
            <span class="text-lg font-black tracking-tighter text-slate-900">@yield('title', 'Dashboard')</span>
        </div>
        <div class="flex items-center gap-5">
            <button class="material-symbols-outlined text-slate-400 text-[24px] hover:text-primary transition-colors" onclick="openSearchOverlay()">search</button>
            <div class="relative">
                <a href="{{ route('notification.index') }}" class="material-symbols-outlined text-slate-400 text-[24px] hover:text-primary transition-colors block">notifications</a>
                @php $unreadCount = Auth::user() ? Auth::user()->notifications()->where('is_read', false)->count() : 0; @endphp
                @if($unreadCount > 0)
                    <span class="absolute -top-1 -right-1 min-w-[18px] h-[18px] px-1 bg-red-500 rounded-full border-2 border-white text-[10px] text-white font-black flex items-center justify-center">
                        {{ $unreadCount > 9 ? '9+' : $unreadCount }}
                    </span>
                @endif
            </div>
        </div>
    </div>
</nav>

@unless(request()->routeIs(['ai.assistant', 'ai.voice']))
<!-- SMART MOBILE BOTTOM NAV (Gojek / Grab / Super App Familiar Style) -->
<div 
    id="bottom-nav"
    class="sm:hidden fixed bottom-6 left-6 right-6 z-[60] bg-white/90 backdrop-blur-xl rounded-[28px] shadow-[0_8px_32px_rgba(0,0,0,0.08)] transition-transform duration-300 ease-in-out pb-safe border-none"
>
    <div class="px-2 py-3 flex justify-around items-center">
        <!-- Beranda -->
        <a href="{{ route('admin.dashboard') }}" class="flex flex-col items-center justify-center gap-1 w-14 transition-all duration-200 {{ request()->routeIs('admin.dashboard') ? 'text-primary' : 'text-slate-400' }}">
            <span class="material-symbols-outlined text-[26px]" style="font-variation-settings: 'FILL' {{ request()->routeIs('admin.dashboard') ? 1 : 0 }};">home</span>
            <span class="text-[9px] font-bold tracking-tight">Beranda</span>
        </a>

        <!-- Dompet -->
        <a href="{{ route('management.index') }}" class="flex flex-col items-center justify-center gap-1 w-14 transition-all duration-200 {{ request()->routeIs('management.*') ? 'text-primary' : 'text-slate-400' }}">
            <span class="material-symbols-outlined text-[26px]" style="font-variation-settings: 'FILL' {{ request()->routeIs('management.*') ? 1 : 0 }};">account_balance_wallet</span>
            <span class="text-[9px] font-bold tracking-tight">Dompet</span>
        </a>

        <!-- Chat (Centered Action) -->
        <a href="{{ route('ai.assistant') }}" class="flex flex-col items-center justify-center gap-1 w-14 transition-all duration-200 {{ request()->routeIs(['ai.assistant', 'ai.voice']) ? 'text-primary' : 'text-slate-400' }}">
            <div class="relative">
                <span class="material-symbols-outlined text-[26px]" style="font-variation-settings: 'FILL' {{ request()->routeIs(['ai.assistant', 'ai.voice']) ? 1 : 0 }};">forum</span>
                @if($unreadCount > 0)
                <span class="absolute -top-1 -right-1 w-2.5 h-2.5 bg-red-500 rounded-full border border-white"></span>
                @endif
            </div>
            <span class="text-[9px] font-bold tracking-tight">Chat</span>
        </a>

        <!-- Agenda -->
        <a href="{{ route('agenda.index') }}" class="flex flex-col items-center justify-center gap-1 w-14 transition-all duration-200 {{ request()->routeIs('agenda.*') ? 'text-primary' : 'text-slate-400' }}">
            <span class="material-symbols-outlined text-[26px]" style="font-variation-settings: 'FILL' {{ request()->routeIs('agenda.*') ? 1 : 0 }};">event_available</span>
            <span class="text-[9px] font-bold tracking-tight">Agenda</span>
        </a>

        <!-- Report / Laporan -->
        <a href="{{ route('reports.index') }}" class="flex flex-col items-center justify-center gap-1 w-14 transition-all duration-200 {{ request()->routeIs('reports.*') ? 'text-primary' : 'text-slate-400' }}">
            <span class="material-symbols-outlined text-[26px]" style="font-variation-settings: 'FILL' {{ request()->routeIs('reports.*') ? 1 : 0 }};">bar_chart_4_bars</span>
            <span class="text-[9px] font-bold tracking-tight">Laporan</span>
        </a>

    </div>
</div>
@endunless

<script>
    let lastScrollTop = 0;
    const bottomNav = document.getElementById('bottom-nav');

    window.addEventListener('scroll', function() {
        if (!bottomNav) return;
        let scrollTop = window.pageYOffset || document.documentElement.scrollTop;
        if (scrollTop > lastScrollTop && scrollTop > 50) {
            bottomNav.style.transform = 'translateY(130%)'; // Hidden
        } else {
            bottomNav.style.transform = 'translateY(0)'; // Shown
        }
        lastScrollTop = Math.max(0, scrollTop);
    }, { passive: true });

    async function switchContext(id) {
        try {
            const response = await fetch(id ? `/groups/switch/${id}` : '/groups/switch/0', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
            });
            const data = await response.json();
            if (data.success) {
                window.location.reload();
            }
        } catch (e) {
            console.error('Failed to switch context');
        }
    }
</script>