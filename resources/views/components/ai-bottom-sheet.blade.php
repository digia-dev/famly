<div x-data="{ 
        openAI: false, 
        scrolledDown: false, 
        lastScrollTop: 0 
     }" 
     @scroll.window="
        let st = window.pageYOffset || document.documentElement.scrollTop;
        scrolledDown = (st > lastScrollTop && st > 50) || !document.getElementById('bottom-nav');
        lastScrollTop = Math.max(0, st);
     "
     x-init="scrolledDown = !document.getElementById('bottom-nav')"
     class="relative">
    <!-- Compact Centered AI Button -->
    <div class="fixed left-0 right-0 z-[60] flex justify-center px-4 pointer-events-none transition-all duration-300"
         :class="scrolledDown ? 'bottom-8' : 'bottom-24'">
        <button @click="openAI = true" 
                class="pointer-events-auto bg-white/95 border border-slate-100 py-1.5 px-3 rounded-full shadow-lg shadow-emerald-900/10 flex items-center gap-2 group active:scale-90 transition-all">
            <div class="w-6 h-6 rounded-full bg-emerald-700 flex items-center justify-center text-white shrink-0">
                <span class="material-symbols-outlined text-[12px]">auto_awesome</span>
            </div>
            <span class="text-[9px] font-bold text-slate-600 tracking-tight whitespace-nowrap">Catat via AI</span>
            <div class="w-1 h-1 rounded-full bg-red-500 animate-pulse"></div>
        </button>
    </div>

    <!-- AI Bottom Sheet Slider -->
    <div x-show="openAI" 
         class="fixed inset-0 z-[100] flex items-end justify-center" 
         x-cloak>
        
        <!-- Backdrop Backdrop (Explicit Fade) -->
        <div x-show="openAI"
             x-transition.opacity.duration.300ms
             class="absolute inset-0 bg-slate-950/40"
             @click="openAI = false"></div>

        <div x-show="openAI"
             x-transition:enter="transition ease-out duration-500 transform"
             x-transition:enter-start="translate-y-full"
             x-transition:enter-end="translate-y-0"
             x-transition:leave="transition ease-in duration-300 transform"
             x-transition:leave-start="translate-y-0"
             x-transition:leave-end="translate-y-full"
             x-data="{ 
                startY: 0, 
                currentY: 0, 
                isDragging: false,
                get translateY() { return this.isDragging ? Math.max(0, this.currentY - this.startY) : 0 }
             }"
             @touchstart="startY = $event.touches[0].clientY; isDragging = true"
             @touchmove="currentY = $event.touches[0].clientY; if (currentY - startY > 10) $event.preventDefault();"
             @touchend="if (currentY - startY > 120) { openAI = false; } isDragging = false; startY = 0; currentY = 0"
             class="bg-white rounded-t-[32px] shadow-2xl flex flex-col w-full relative z-10"
             :style="'transform: translateY(' + translateY + 'px); transition: ' + (isDragging ? 'none' : 'transform 0.5s cubic-bezier(0.16, 1, 0.3, 1)')"
             @click.stop>
            
            <!-- Handle -->
            <div class="w-12 h-1.5 bg-slate-100 rounded-full mx-auto mt-3 mb-6"></div>

            <div class="px-4 pb-6">
                <!-- Compact Header -->
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="text-[14px] font-black text-slate-900 tracking-tight">Mau catat apa hari ini?</h3>
                        <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">Pilih metode input</p>
                    </div>
                    <div class="w-7 h-7 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center">
                        <span class="material-symbols-outlined text-[18px]">auto_awesome</span>
                    </div>
                </div>

                <!-- Familiar & Accessible Grid Layout (Reduced rounding & space) -->
                <div class="grid grid-cols-1 gap-2">
                    <!-- Action: Foto Struk (Primary) -->
                    <button class="relative flex items-center gap-3 p-3 rounded-2xl bg-emerald-700 text-white shadow-md active:scale-[0.98] transition-all text-left"
                            onclick="window.location.href='{{ route('ai.scan-struk') }}'">
                        <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center text-white shrink-0">
                            <span class="material-symbols-outlined text-[24px]">photo_camera</span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h4 class="text-[13px] font-extrabold truncate tracking-tight">Foto Struk Langsung</h4>
                            <p class="text-[9px] text-white/70 font-medium truncate">Otomatis baca nominal & barang melalui AI</p>
                        </div>
                        <span class="absolute top-2 right-2 bg-amber-400 text-emerald-900 text-[8px] font-black px-2 py-0.5 rounded-full uppercase tracking-tighter">Paling Cepat</span>
                    </button>

                    <!-- Action: Agenda & Pengingat (New Interactive Task Input) -->
                    <button class="relative flex items-center gap-3 p-3 rounded-2xl bg-amber-50 border border-amber-100 text-amber-700 active:scale-[0.98] transition-all text-left"
                            onclick="window.location.href='{{ route('agenda.create') }}'">
                        <div class="w-10 h-10 rounded-xl bg-amber-100 flex items-center justify-center text-amber-600 shrink-0">
                            <span class="material-symbols-outlined text-[24px]">calendar_today</span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h4 class="text-[13px] font-extrabold truncate tracking-tight">Agenda & Pengingat</h4>
                            <p class="text-[9px] text-amber-600/70 font-medium truncate">Catat tugas rutin atau ritual keluarga</p>
                        </div>
                        <div class="flex items-center gap-1">
                            <div class="w-1.5 h-1.5 rounded-full bg-amber-400 animate-pulse"></div>
                            <span class="text-[8px] font-black uppercase tracking-widest text-amber-400">Baru</span>
                        </div>
                    </button>

                    <div class="grid grid-cols-2 gap-2">
                        <!-- Action: Suara -->
                        <button class="flex flex-col items-center justify-center p-3.5 rounded-2xl bg-slate-50 border border-slate-100 flex-1 active:scale-[0.98] transition-all gap-1.5 text-center"
                                onclick="window.location.href='{{ route('ai.voice') }}'">
                            <div class="w-9 h-9 rounded-full bg-white shadow-sm flex items-center justify-center text-emerald-600">
                                <span class="material-symbols-outlined text-[22px]">mic</span>
                            </div>
                            <h4 class="text-[12px] font-bold text-slate-800">Pakai Suara</h4>
                            <div class="flex gap-0.5 h-2 items-center">
                                <div class="w-1 h-1.5 bg-emerald-400 rounded-full animate-bounce"></div>
                                <div class="w-1 h-2.5 bg-emerald-600 rounded-full animate-bounce" style="animation-delay: 0.1s"></div>
                                <div class="w-1 h-1.5 bg-emerald-400 rounded-full animate-bounce" style="animation-delay: 0.2s"></div>
                            </div>
                        </button>

                        <!-- Action: Ketik Manual -->
                        <button class="flex flex-col items-center justify-center p-3.5 rounded-2xl bg-slate-50 border border-slate-100 flex-1 active:scale-[0.98] transition-all gap-1.5 text-center"
                                onclick="window.location.href='{{ route('transaction.create') }}'">
                            <div class="w-9 h-9 rounded-full bg-white shadow-sm flex items-center justify-center text-slate-400">
                                <span class="material-symbols-outlined text-[22px]">keyboard</span>
                            </div>
                            <h4 class="text-[12px] font-bold text-slate-800">Ketik Manual</h4>
                            <p class="text-[8px] font-bold text-slate-400 uppercase tracking-tighter text-balance">Input Transaksi Biasa</p>
                        </button>
                    </div>
                </div>

                <!-- Compact Footer -->
                <div class="mt-3.5 p-2.5 bg-slate-50 rounded-xl border border-dotted border-slate-200 flex items-center gap-2.5">
                    <span class="material-symbols-outlined text-slate-300 text-[14px]">bolt</span>
                    <p class="text-[9px] text-slate-400 font-medium">Sering mencatat pakai AI bantu Famly jadi lebih pintar!</p>
                </div>
            </div>
        </div>
    </div>
</div>
