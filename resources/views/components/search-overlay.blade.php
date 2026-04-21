<!-- HIGH-FIDELITY SEARCH OVERLAY (Gojek Super App Style) -->
<div class="fixed inset-0 z-[100] bg-white hidden flex-col transition-all duration-300 transform translate-y-full opacity-0" id="search-overlay">
    
    <!-- Top Search Header (Clean & Sticky) -->
    <div class="bg-white border-b border-slate-100 px-4 py-3 flex items-center gap-3">
        <button class="w-10 h-10 flex items-center justify-center text-slate-400 active:scale-90 transition-transform" onclick="closeSearchOverlay()">
            <span class="material-symbols-outlined">arrow_back</span>
        </button>
        
        <div class="flex-1 relative">
            <input type="text" 
                   id="main-search-input"
                   class="w-full bg-slate-50 border-none rounded-xl py-2.5 pl-4 pr-10 text-[14px] font-medium placeholder:text-slate-400 focus:ring-1 focus:ring-primary/20 transition-all font-jakarta" 
                   placeholder="Cari transaksi, anggota, atau fitur..."
                   autocomplete="off">
            <button id="clear-search-btn" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-300 hidden" onclick="clearSearchInput()">
                <span class="material-symbols-outlined text-[20px]">cancel</span>
            </button>
        </div>
    </div>

    <!-- Scrollable Experience -->
    <div class="flex-1 overflow-y-auto no-scrollbar pb-20">
        
        <!-- Search Results (Hidden by default) -->
        <div id="search-results-container" class="hidden p-4">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-[12px] font-bold text-slate-400 uppercase tracking-wider">Hasil Pencarian</h3>
                <span id="results-count" class="text-[10px] bg-slate-50 text-slate-500 px-2 py-0.5 rounded-full font-bold">0 hasil</span>
            </div>
            <div class="space-y-2" id="results-list">
                <!-- Dynamic Results -->
            </div>
        </div>

        <!-- Default Suggestions Area -->
        <div id="suggestions-area" class="p-5 space-y-8">
            
            <!-- 1. Recent Searches -->
            <div id="recent-searches-section">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-[14px] font-bold text-slate-900 tracking-tight">Pencarian Terkini</h3>
                    <button class="text-[11px] font-bold text-primary" onclick="clearSearchHistory()">Hapus Semua</button>
                </div>
                <div class="flex flex-wrap gap-2" id="history-container">
                    <!-- History Chips -->
                </div>
            </div>

            <!-- 2. AI Wizard Suggestions (Natural Language) -->
            <div>
                <h3 class="text-[14px] font-bold text-slate-900 tracking-tight mb-3">Tanya Si Famly (AI)</h3>
                <div class="space-y-3">
                    @php
                        $aiPrompts = [
                            [
                                'q' => 'Berapa sisa saldo bulanan?',
                                'sub' => 'Analisis sisa anggaran otomatis',
                                'icon' => 'query_stats',
                                'bg' => 'bg-emerald-50',
                                'border' => 'border-emerald-100/50',
                                'text' => 'text-emerald-900',
                                'sub_text' => 'text-emerald-600',
                                'icon_bg' => 'bg-primary'
                            ],
                            [
                                'q' => 'Siapa yang paling hemat minggu ini?',
                                'sub' => 'Cek ranking pengeluaran keluarga',
                                'icon' => 'emoji_events',
                                'bg' => 'bg-blue-50',
                                'border' => 'border-blue-100/50',
                                'text' => 'text-blue-900',
                                'sub_text' => 'text-blue-600',
                                'icon_bg' => 'bg-blue-500'
                            ],
                            [
                                'q' => 'Apakah saya boros bulan ini?',
                                'sub' => 'Analisa tren pengeluaran harian',
                                'icon' => 'trending_up',
                                'bg' => 'bg-amber-50',
                                'border' => 'border-amber-100/50',
                                'text' => 'text-amber-900',
                                'sub_text' => 'text-amber-600',
                                'icon_bg' => 'bg-amber-500'
                            ],
                            [
                                'q' => 'Berikan tips menabung Rp 1 Juta',
                                'sub' => 'Strategi alokasi dana mandiri',
                                'icon' => 'savings',
                                'bg' => 'bg-rose-50',
                                'border' => 'border-rose-100/50',
                                'text' => 'text-rose-900',
                                'sub_text' => 'text-rose-600',
                                'icon_bg' => 'bg-rose-500'
                            ],
                            [
                                'q' => 'Berapa total pemasukan bulan ini?',
                                'sub' => 'Ringkasan dana masuk keluarga',
                                'icon' => 'account_balance_wallet',
                                'bg' => 'bg-indigo-50',
                                'border' => 'border-indigo-100/50',
                                'text' => 'text-indigo-900',
                                'sub_text' => 'text-indigo-600',
                                'icon_bg' => 'bg-indigo-500'
                            ]
                        ];
                        // Pick 2 random prompts
                        $randomPrompts = collect($aiPrompts)->random(min(2, count($aiPrompts)));
                    @endphp

                    @foreach($randomPrompts as $prompt)
                        <button onclick="window.location.href='{{ route('ai.assistant') }}?q={{ urlencode($prompt['q']) }}'" 
                                class="w-full flex items-center gap-3 p-3 {{ $prompt['bg'] }} rounded-2xl border {{ $prompt['border'] }} text-left active:scale-[0.98] transition-transform group">
                            <div class="w-8 h-8 rounded-xl {{ $prompt['icon_bg'] }} text-white flex items-center justify-center shadow-sm group-hover:rotate-12 transition-transform">
                                <span class="material-symbols-outlined text-[18px]">{{ $prompt['icon'] }}</span>
                            </div>
                            <div>
                                <p class="text-[12px] font-bold {{ $prompt['text'] }}">"{{ $prompt['q'] }}"</p>
                                <p class="text-[10px] {{ $prompt['sub_text'] }} font-medium tracking-tight">{{ $prompt['sub'] }}</p>
                            </div>
                        </button>
                    @endforeach
                </div>
            </div>

            <!-- 3. Quick Feature Shortcuts -->
            <div>
                <h3 class="text-[14px] font-bold text-slate-900 tracking-tight mb-3">Aksi Cepat</h3>
                <div class="grid grid-cols-2 gap-3" id="popular-container">
                    <!-- Dynamic Shortcuts (Management categories/Wallets) -->
                </div>
            </div>

        </div>
    </div>
</div>

@push('scripts')
<script>
    let searchTimeout = null;

    function openSearchOverlay() {
        const overlay = document.getElementById('search-overlay');
        overlay.classList.remove('hidden');
        overlay.classList.add('flex');
        
        // Animasi slide up
        setTimeout(() => {
            overlay.classList.remove('translate-y-full', 'opacity-0');
            overlay.classList.add('translate-y-0', 'opacity-100');
            document.getElementById('main-search-input').focus();
        }, 10);
        
        loadSuggestions();
    }

    function closeSearchOverlay() {
        const overlay = document.getElementById('search-overlay');
        overlay.classList.add('translate-y-full', 'opacity-0');
        overlay.classList.remove('translate-y-0', 'opacity-100');
        
        setTimeout(() => {
            overlay.classList.add('hidden');
            overlay.classList.remove('flex');
        }, 300);
    }

    function loadSuggestions() {
        fetch('{{ route('search.suggestions') }}')
            .then(res => res.json())
            .then(data => {
                renderHistory(data.history);
                renderPopular(data.popular);
            });
    }

    function renderHistory(history) {
        const container = document.getElementById('history-container');
        if (!history || history.length === 0) {
            document.getElementById('recent-searches-section').classList.add('hidden');
            return;
        }
        document.getElementById('recent-searches-section').classList.remove('hidden');
        container.innerHTML = history.map(h => `
            <button class="px-4 py-1.5 bg-slate-50 text-slate-600 border border-slate-100 rounded-full text-[11px] font-bold hover:bg-primary/5 hover:text-primary transition-all active:scale-95" 
                    onclick="setSearchQuery('${h.query}')">
                ${h.query}
            </button>
        `).join('');
    }

    function renderPopular(popular) {
        const container = document.getElementById('popular-container');
        if (!popular || popular.length === 0) return;
        
        container.innerHTML = popular.map(p => `
            <div class="flex items-center gap-3 p-3 bg-white border border-slate-100 rounded-2xl hover:bg-slate-50 transition-all cursor-pointer group active:scale-95" onclick="setSearchQuery('${p.nama}')">
                <div class="w-8 h-8 rounded-xl bg-slate-50 border border-slate-100 flex items-center justify-center text-slate-500 group-hover:bg-primary/10 group-hover:text-primary transition-all">
                    <span class="material-symbols-outlined text-[18px]" style="font-variation-settings: 'FILL' 1;">${p.icon || 'account_balance_wallet'}</span>
                </div>
                <span class="text-[12px] font-bold text-slate-700 truncate">${p.nama}</span>
            </div>
        `).join('');
    }

    function clearSearchInput() {
        const input = document.getElementById('main-search-input');
        input.value = '';
        performSearch('');
        input.focus();
    }

    function setSearchQuery(query) {
        const input = document.getElementById('main-search-input');
        input.value = query;
        performSearch(query);
        input.focus();
    }

    function performSearch(q) {
        const resultsContainer = document.getElementById('search-results-container');
        const countBadge = document.getElementById('results-count');
        const suggestionsArea = document.getElementById('suggestions-area');
        const list = document.getElementById('results-list');
        const clearBtn = document.getElementById('clear-search-btn');

        if (!q || q.length === 0) {
            clearBtn.classList.add('hidden');
            resultsContainer.classList.add('hidden');
            suggestionsArea.classList.remove('hidden');
            return;
        }

        clearBtn.classList.remove('hidden');
        resultsContainer.classList.remove('hidden');
        suggestionsArea.classList.add('hidden');
        
        if (q.length < 2) return;

        list.innerHTML = `<div class="p-20 flex flex-col items-center gap-4 text-slate-300">
            <div class="animate-spin rounded-full h-8 w-8 border-2 border-primary border-t-transparent"></div>
            <p class="text-[11px] font-bold">Mencari data...</p>
        </div>`;

        fetch(`{{ url('/search') }}?q=${encodeURIComponent(q)}`)
            .then(res => res.json())
            .then(data => {
                countBadge.innerText = `${data.results.length} hasil`;
                if (!data.results || data.results.length === 0) {
                    list.innerHTML = `<div class="py-20 text-center text-slate-300">
                        <span class="material-symbols-outlined text-5xl mb-2">search_off</span>
                        <p class="text-[12px] font-bold">Tidak ada hasil yang cocok</p>
                    </div>`;
                    return;
                }

                list.innerHTML = data.results.map(r => {
                    if (r.type === 'ai_card') {
                        return `
                            <div class="bg-emerald-50 rounded-3xl p-5 border border-emerald-100 shadow-sm mb-4 active:scale-[0.98] transition-transform cursor-pointer" onclick="window.location.href='${r.url}'">
                                <div class="flex items-center gap-3 mb-4">
                                    <div class="w-10 h-10 rounded-2xl bg-primary text-white flex items-center justify-center">
                                        <span class="material-symbols-outlined text-[20px]">${r.icon}</span>
                                    </div>
                                    <div>
                                        <h4 class="text-[13px] font-bold text-emerald-900">${r.title}</h4>
                                        <p class="text-[11px] text-emerald-600 font-medium">${r.subtitle}</p>
                                    </div>
                                </div>
                                <div class="mb-4">
                                    <h2 class="text-3xl font-extrabold text-emerald-900 tracking-tighter">${r.ai_value}</h2>
                                    <p class="text-[12px] text-emerald-700/70 font-medium mt-1 leading-relaxed">${r.ai_detail}</p>
                                </div>
                                <div class="flex items-center justify-between pt-4 border-t border-emerald-100">
                                    <span class="text-[11px] font-bold text-primary">Lihat Analisis Detail</span>
                                    <span class="material-symbols-outlined text-primary text-sm">chevron_right</span>
                                </div>
                            </div>
                        `;
                    }

                    return `
                        <a href="${r.url}" class="flex items-center gap-4 p-4 bg-white border border-slate-100 rounded-2xl hover:border-primary/20 hover:bg-primary/[0.02] transition-all group active:scale-[0.98] block no-underline mb-2">
                            <div class="w-11 h-11 rounded-2xl bg-slate-50 flex items-center justify-center text-slate-400 group-hover:bg-primary group-hover:text-white transition-all shadow-sm shadow-slate-200/50">
                                <span class="material-symbols-outlined text-2xl" style="font-variation-settings: 'FILL' 1;">${r.icon}</span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <h4 class="text-[13px] font-bold text-slate-900 tracking-tight truncate">${r.title}</h4>
                                <p class="text-[11px] font-semibold text-slate-400 tracking-tight mt-0.5">${r.subtitle}</p>
                            </div>
                            <span class="material-symbols-outlined text-slate-200 group-hover:text-primary transition-colors">chevron_right</span>
                        </a>
                    `;
                }).join('');
            });
    }

    document.getElementById('main-search-input').addEventListener('input', function(e) {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            performSearch(e.target.value);
        }, 300);
    });

    // Logging & Misc code kept as per original logic...
</script>
<style>
    .no-scrollbar::-webkit-scrollbar { display: none; }
    .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
</style>
@endpush
