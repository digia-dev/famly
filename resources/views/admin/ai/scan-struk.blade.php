<x-app-layout>
    <div class="fixed inset-0 bg-[#0F1113] z-[100] flex flex-col items-center justify-between font-jakarta text-white overflow-hidden" 
         x-data="receiptScanner({{ json_encode($wallets) }}, {{ json_encode($posItems) }}, {{ json_encode($savings) }})"
         x-init="init()">
        
        <!-- Premium Toast -->
        <template x-if="notification.show">
            <div class="fixed top-6 left-4 right-4 z-[300] animate-slide-down">
                <div class="bg-white rounded-[24px] overflow-hidden shadow-2xl border border-slate-100 flex items-center p-3 gap-3">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0"
                         :class="{ 'bg-emerald-500 text-white': notification.type === 'success', 'bg-amber-500 text-white': notification.type === 'warning', 'bg-rose-500 text-white': notification.type === 'error' }">
                        <span class="material-symbols-outlined text-[20px]" x-text="notification.type === 'success' ? 'check_circle' : (notification.type === 'warning' ? 'warning' : 'block')"></span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-[11px] font-bold text-slate-800 leading-none" x-text="notification.title"></p>
                        <p class="text-[10px] font-medium text-slate-400 truncate mt-0.5" x-text="notification.message"></p>
                    </div>
                    <button @click="notification.show = false" class="text-slate-300 px-1"><span class="material-symbols-outlined text-[18px]">close</span></button>
                </div>
            </div>
        </template>

        <!-- Viewfinder -->
        <div class="relative flex-1 w-full flex items-center justify-center py-4">
            <div class="relative w-full max-w-sm aspect-[4/5] rounded-[32px] overflow-hidden bg-black shadow-2xl flex items-center justify-center border border-white/5">
                <video x-ref="video" autoplay playsinline muted @loadedmetadata="cameraReady = true"
                       class="w-full h-full object-cover brightness-105 opacity-60"></video>
                <div class="absolute inset-0 z-20 flex items-center justify-center pointer-events-none">
                    <div class="w-[85%] h-[75%] border-2 border-emerald-500/20 rounded-[28px] relative">
                        <div class="absolute top-0 left-0 w-8 h-8 border-t-4 border-l-4 border-emerald-500 rounded-tl-2xl"></div>
                        <div class="absolute top-0 right-0 w-8 h-8 border-t-4 border-r-4 border-emerald-500 rounded-tr-2xl"></div>
                        <div class="absolute bottom-0 left-0 w-8 h-8 border-b-4 border-l-4 border-emerald-500 rounded-bl-2xl"></div>
                        <div class="absolute bottom-0 right-0 w-8 h-8 border-b-4 border-r-4 border-emerald-500 rounded-br-2xl"></div>
                        <div x-show="processing" class="absolute top-0 left-0 right-0 h-40 bg-gradient-to-b from-emerald-500/20 to-transparent animate-scan z-30" x-cloak></div>
                    </div>
                </div>

                <div x-show="processing" x-cloak class="absolute inset-0 z-40 bg-black/80 backdrop-blur-md flex flex-col items-center justify-center text-center px-8">
                    <div class="w-12 h-12 rounded-full border-2 border-white/10 border-t-emerald-500 animate-spin mb-4"></div>
                    <h3 class="text-[11px] font-bold tracking-widest text-emerald-500" x-text="statusText"></h3>
                    <p class="text-[9px] text-white/30 mt-2 tracking-widest font-bold" x-text="statusDetail"></p>
                </div>
            </div>
            <canvas x-ref="canvas" class="hidden"></canvas>

            <!-- Floating Top Controls -->
            <div class="absolute top-8 left-6 right-6 flex items-center justify-between z-50">
                <button @click="stopCamera(); window.history.back()" class="w-10 h-10 rounded-xl bg-white/10 backdrop-blur-md flex items-center justify-center">
                    <span class="material-symbols-outlined text-white/70">close</span>
                </button>
                <button @click="toggleFlash()" :class="flashOn ? 'bg-amber-500' : 'bg-white/10'" class="w-10 h-10 rounded-xl backdrop-blur-md flex items-center justify-center">
                    <span class="material-symbols-outlined text-[20px]">bolt</span>
                </button>
            </div>
        </div>

        <template x-if="result">
            <div class="fixed inset-0 z-[200] bg-black/60 backdrop-blur-sm flex items-end justify-center transition-all duration-500"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-300"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 x-data="{ 
                    startY: 0, 
                    currentY: 0, 
                    isDragging: false,
                    get translateY() { return this.isDragging ? Math.max(0, this.currentY - this.startY) : 0 }
                 }"
                 @click="result = null; startCamera()"
                 @touchstart="startY = $event.touches[0].clientY; isDragging = true"
                 @touchmove="currentY = $event.touches[0].clientY; if (currentY - startY > 10) $event.preventDefault();"
                 @touchend="if (currentY - startY > 120) { result = null; startCamera(); } isDragging = false; startY = 0; currentY = 0">
                
                <div class="bg-[#F8FAFC] text-slate-900 w-full max-w-lg rounded-t-[32px] shadow-2xl flex flex-col max-h-[85vh] overflow-hidden relative"
                     x-show="result"
                     x-transition:enter="transition ease-out duration-500 transform"
                     x-transition:enter-start="translate-y-full"
                     x-transition:enter-end="translate-y-0"
                     x-transition:leave="transition ease-in duration-300 transform"
                     x-transition:leave-start="translate-y-0"
                     x-transition:leave-end="translate-y-full"
                     :style="'transform: translateY(' + translateY + 'px); transition: ' + (isDragging ? 'none' : 'transform 0.5s cubic-bezier(0.16, 1, 0.3, 1)')"
                     @click.stop>
                    <div class="w-10 h-1 bg-slate-200 rounded-full mx-auto my-3 shrink-0"></div>
                    
                    <div class="px-5 shrink-0 mb-4">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-[20px] font-bold tracking-tight text-slate-900 leading-none">Catat Transaksi</h3>
                            <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-500 flex items-center justify-center">
                                 <span class="material-symbols-outlined text-[20px]" style="font-variation-settings: 'FILL' 1">auto_awesome</span>
                            </div>
                        </div>

                        <!-- Context Toggle (Personal vs Groups) -->
                        <div class="flex items-center gap-1.5 overflow-x-auto no-scrollbar py-1">
                            <button @click="switchContext('personal')" 
                                    :class="selectedContext === 'personal' ? 'bg-primary text-white shadow-md' : 'bg-white text-slate-400 border border-slate-100'"
                                    class="px-4 py-2 rounded-full text-[10px] font-black uppercase tracking-widest transition-all whitespace-nowrap active:scale-95">
                                Pribadi
                            </button>
                            @foreach(Auth::user()->groups as $group)
                            <button @click="switchContext('{{ $group->id }}')" 
                                    :class="selectedContext == '{{ $group->id }}' ? 'bg-amber-500 text-white shadow-md' : 'bg-white text-slate-400 border border-slate-100'"
                                    class="px-4 py-2 rounded-full text-[10px] font-black uppercase tracking-widest transition-all whitespace-nowrap active:scale-95">
                                {{ $group->name }}
                            </button>
                            @endforeach
                        </div>
                    </div>

                    <div class="flex-1 overflow-y-auto no-scrollbar px-5 space-y-3 pb-6">
                        <!-- Inputs -->
                        <div class="space-y-2">
                             <div class="bg-white rounded-2xl p-4 shadow-sm border border-slate-100/50">
                                <span class="text-[9px] font-bold text-slate-400 block mb-1">Total (IDR)</span>
                                <div class="flex items-baseline gap-1">
                                    <span class="text-xs font-bold text-slate-300">Rp</span>
                                    <input type="number" x-model="result.total" class="w-full bg-transparent border-none p-0 text-3xl font-bold text-slate-900 focus:ring-0">
                                </div>
                            </div>
                            <div class="bg-white rounded-2xl p-4 shadow-sm border border-slate-100/50 flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-slate-50 flex items-center justify-center text-slate-400 shrink-0">
                                    <span class="material-symbols-outlined text-[20px]">edit_note</span>
                                </div>
                                <div class="flex-1">
                                    <span class="text-[9px] font-bold text-slate-400 block mb-0.5">Keterangan</span>
                                    <input type="text" x-model="result.merchant" class="w-full bg-transparent border-none p-0 text-[13px] font-bold text-slate-700 focus:ring-0">
                                </div>
                            </div>
                        </div>

                        <!-- Allocation System (Prioritize POS) -->
                        <div class="bg-white rounded-[24px] p-4 shadow-sm border border-slate-100/50 space-y-4">
                            <div class="flex items-center justify-between">
                                <h5 class="text-[10px] font-bold text-slate-400 leading-none" 
                                    x-text="walletType === 'pos' ? 'Alokasi ke Pos Anggaran' : 'Pilih Dompet'"></h5>
                                
                                <button @click="toggleWalletType()" 
                                        class="px-4 py-1.5 rounded-xl border border-slate-200 text-[10px] font-bold active:scale-95 transition-all text-slate-600 bg-white shadow-sm">
                                    <span x-text="walletType === 'pos' ? 'Pilih Dompet' : 'Kembali ke Pos'"></span>
                                </button>
                            </div>

                            <div class="flex gap-2 overflow-x-auto no-scrollbar pb-1">
                                <template x-for="item in currentItems()" :key="item.id">
                                    <label class="cursor-pointer shrink-0">
                                        <input type="radio" :value="item.id" x-model="result.walletId" class="peer sr-only">
                                        <div class="w-28 p-4 rounded-2xl bg-slate-50 border border-slate-100 peer-checked:bg-emerald-600 peer-checked:border-emerald-600 peer-checked:text-white transition-all active:scale-95 shadow-sm">
                                            <div class="flex items-center justify-between mb-3 text-slate-400 peer-checked:text-white/50">
                                                <span class="material-symbols-outlined text-[16px]" x-text="walletType === 'pos' ? 'shopping_bag' : 'wallet'"></span>
                                                <span class="material-symbols-outlined text-[14px] opacity-0 peer-checked:opacity-100 text-white">check_circle</span>
                                            </div>
                                            <h6 class="text-[11px] font-bold leading-tight truncate mb-0.5" x-text="item.nama"></h6>
                                            <p class="text-[9px] font-medium opacity-60" x-text="'Rp' + Number(item.balance || 0).toLocaleString('id-ID')"></p>
                                        </div>
                                    </label>
                                </template>
                            </div>
                        </div>

                        <!-- Insight Advice -->
                        <div class="p-4 bg-emerald-50/50 rounded-2xl flex items-start gap-4 border border-emerald-100/30">
                            <span class="material-symbols-outlined text-emerald-500 text-[20px] shrink-0">auto_awesome</span>
                            <p class="text-[11px] font-bold text-emerald-800 leading-snug italic" x-text="result.smart_tip"></p>
                        </div>
                        
                        <!-- Fixed Bottom Action -->
                        <div class="flex gap-2 pt-2">
                            <button @click="result = null; startCamera()" class="w-12 h-12 rounded-xl border border-slate-200 text-slate-400 flex items-center justify-center shrink-0 active:scale-90 shadow-sm"><span class="material-symbols-outlined">restart_alt</span></button>
                            <button @click="saveTransaction()" class="flex-1 bg-emerald-600 text-white rounded-xl text-[13px] font-bold flex items-center justify-center gap-3 active:scale-95 shadow-lg shadow-emerald-500/20">
                                <span x-show="!saving">Simpan Transaksi</span>
                                <span x-show="saving" class="w-4 h-4 border-2 border-white/20 border-t-white rounded-full animate-spin"></span>
                                <span x-show="!saving" class="material-symbols-outlined text-[18px]">verified</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </template>

        <!-- Capture Controls -->
        <div class="w-full px-12 pb-10 flex items-center justify-center relative z-50">
            <div class="relative flex items-center justify-center">
                <div class="absolute w-32 h-32 rounded-full bg-emerald-500/10 blur-3xl animate-pulse"></div>
                <button @click="captureImage()" :disabled="processing || !cameraReady"
                        class="w-16 h-16 rounded-full bg-white flex items-center justify-center shadow-xl relative z-10 active:scale-90 transition-all border-[4px] border-emerald-500/5 disabled:opacity-50">
                    <span class="material-symbols-outlined text-slate-900 text-2xl">photo_camera</span>
                </button>
            </div>
            
            <label class="absolute right-12 w-10 h-10 rounded-xl bg-white/10 flex items-center justify-center border border-white/10 cursor-pointer active:scale-90 transition-all opacity-40 hover:opacity-100">
                <input type="file" accept="image/*" class="hidden" @change="handleFileUpload">
                <span class="material-symbols-outlined text-white text-lg">image</span>
            </label>
        </div>
    </div>

    <style>
        [x-cloak] { display: none !important; }
        @keyframes scan { 0% { transform: translateY(0); opacity: 0; } 50% { opacity: 1; } 100% { transform: translateY(120%); opacity: 0; } }
        .animate-scan { animation: scan 3s infinite linear; }
        .animate-slide-up { animation: slide-up 0.4s cubic-bezier(0.16, 1, 0.3, 1); }
        @keyframes slide-up { from { transform: translateY(100%); } to { transform: translateY(0); } }
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
        /* Reset all to normal case */
        .uppercase { text-transform: none !important; }
        .tracking-widest { letter-spacing: normal !important; }
    </style>

    <script>
    function receiptScanner(wallets, posItems, savings) {
        return {
            wallets, posItems, savings,
            stream: null, flashOn: false, processing: false, saving: false, result: null, cameraReady: false, walletType: 'pos',
            selectedContext: '{{ Auth::user()->current_group_id ?: "personal" }}',
            statusText: '', statusDetail: '', notification: { show: false, type: 'success', title: '', message: '' },
            init() { setTimeout(() => this.startCamera(), 300); },
            
            currentItems() {
                let items = [];
                if (this.walletType === 'pos') items = this.posItems;
                else items = this.wallets;

                // Filter by selected context
                if (this.selectedContext === 'personal') {
                    return items.filter(i => !i.group_id);
                } else {
                    return items.filter(i => i.group_id == this.selectedContext);
                }
            },

            toggleWalletType() {
                this.walletType = (this.walletType === 'pos') ? 'wallet' : 'pos';
                const targetList = this.currentItems();
                if (targetList.length) { 
                    this.result.walletId = targetList[0].id;
                } else {
                    this.result.walletId = null;
                }
            },

            switchContext(newContext) {
                this.selectedContext = newContext;
                const targetList = this.currentItems();
                if (targetList.length) {
                    this.result.walletId = targetList[0].id;
                } else {
                    this.result.walletId = null;
                }
            },

            notify(type, title, message) {
                this.notification = { show: true, type, title, message };
                setTimeout(() => { this.notification.show = false; }, 4000);
            },

            async startCamera() {
                this.cameraReady = false;
                try {
                    const constraints = { video: { facingMode: 'environment', width: { ideal: 1280 }, height: { ideal: 720 } }, audio: false };
                    this.stream = await navigator.mediaDevices.getUserMedia(constraints);
                    this.$refs.video.srcObject = this.stream;
                } catch (err) { this.notify('error', 'Akses Gagal', 'Gagal memuat kamera.'); this.cameraReady = true; }
            },

            stopCamera() { if (this.stream) { this.stream.getTracks().forEach(track => track.stop()); this.stream = null; } },

            toggleFlash() {
                if (this.stream) {
                    const track = this.stream.getVideoTracks()[0];
                    if (track.getCapabilities().torch) {
                        this.flashOn = !this.flashOn;
                        track.applyConstraints({ advanced: [{ torch: this.flashOn }] });
                    }
                }
            },

            async captureImage() {
                if (this.processing) return;
                this.processing = true; this.statusText = 'Memindai Struk...';
                this.statusDetail = 'AI Vision sedang mengidentifikasi data.';
                const video = this.$refs.video; const canvas = this.$refs.canvas;
                canvas.width = video.videoWidth || 640; canvas.height = video.videoHeight || 480;
                canvas.getContext('2d').drawImage(video, 0, 0);
                const base64Image = canvas.toDataURL('image/jpeg', 0.8);
                this.stopCamera(); this.processWithAI(base64Image);
            },

            async handleFileUpload(event) {
                const file = event.target.files[0]; if (!file) return;
                this.processing = true; this.statusText = 'Memproses File...';
                const reader = new FileReader();
                reader.onload = (e) => { this.stopCamera(); this.processWithAI(e.target.result); };
                reader.readAsDataURL(file);
            },

            async processWithAI(image) {
                this.statusText = 'Menganalisis';
                this.statusDetail = 'Mencocokkan data dengan kategori pos Anda.';
                try {
                    const response = await fetch('{{ route("ai.scan-receipt") }}', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                        body: JSON.stringify({ image: image })
                    });
                    const data = await response.json();
                    if (data.success) {
                        this.result = data.data;
                        this.walletType = 'pos';
                        if (this.posItems.length) { this.result.walletId = this.posItems[0].id;
                        } else if (this.wallets.length) { this.walletType = 'wallet'; this.result.walletId = this.wallets[0].id; }
                    } else { throw new Error(data.message); }
                } catch (err) {
                    this.notify('error', 'Gagal', err.message); this.startCamera();
                } finally { this.processing = false; }
            },

            async saveTransaction() {
                if (this.saving) return; this.saving = true;
                try {
                    const response = await fetch('{{ route("ai.save-transaction") }}', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                        body: JSON.stringify({
                            ...this.result,
                            group_id: this.selectedContext
                        })
                    });
                    const data = await response.json();
                    if (data.success) { window.location.href = '{{ route("admin.dashboard") }}?success_ai=1'; }
                    else { throw new Error(data.message); }
                } catch (err) { this.notify('error', 'Gagal', err.message); this.saving = false; }
            }
        };
    }
    </script>
</x-app-layout>
