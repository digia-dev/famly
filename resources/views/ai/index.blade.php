<x-app-layout>
    @section('title', 'Tanya Si Famly')

    <!-- Design System Override -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif !important; background-color: #F8FAFC; }
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
        .native-gradient { background: linear-gradient(135deg, #00aa13 0%, #008910 100%); }
    </style>

    <div class="flex flex-col h-screen overflow-hidden bg-slate-50/50 relative" 
          x-data="{ 
            messages: [],
            userInput: '',
            loading: false,
            isRecording: false,
            recognition: null,
            userName: '{{ Auth::user()->name }}',
            userPhoto: '{{ Auth::user()->profile_photo_url ?? 'https://ui-avatars.com/api/?name='.urlencode(Auth::user()->name).'&background=00aa13&color=fff' }}',
            
            formatRupiah(number) {
                if (!number) return 'Rp 0';
                return 'Rp ' + Number(number).toLocaleString('id-ID');
            },
            
            init() {
                if ('webkitSpeechRecognition' in window) {
                    this.recognition = new webkitSpeechRecognition();
                    this.recognition.continuous = false;
                    this.recognition.lang = 'id-ID';
                    this.recognition.onresult = (event) => {
                        this.userInput = event.results[0][0].transcript;
                        this.stopRecording();
                        this.sendMessage();
                    };
                    this.recognition.onerror = () => { this.stopRecording(); };
                }

                // Auto-generate if query param 'q' exists
                const urlParams = new URLSearchParams(window.location.search);
                const q = urlParams.get('q');
                if (q) {
                    this.userInput = q;
                    this.$nextTick(() => {
                        this.sendMessage();
                        // Clean up URL without refresh
                        window.history.replaceState({}, document.title, window.location.pathname);
                    });
                }
            },
            
            toggleRecording() {
                if (this.isRecording) { this.stopRecording(); } 
                else if (this.recognition) { this.isRecording = true; this.recognition.start(); }
            },
            stopRecording() { this.isRecording = false; if (this.recognition) this.recognition.stop(); },
            
            async sendMessage() {
                if (!this.userInput.trim()) return;
                const text = this.userInput;
                this.messages.push({ 
                    role: 'user', 
                    sender: this.userName,
                    content: text, 
                    time: new Date().toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'}) 
                });
                this.userInput = '';
                this.loading = true;
                this.$nextTick(() => { this.scrollToBottom(); });

                try {
                    const response = await fetch('{{ route('ai.process') }}', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                        body: JSON.stringify({ message: text })
                    });
                    const data = await response.json();
                    if (data.success) {
                        let content = data.response;
                        const cardPatterns = [
                            { type: 'financial_summary', tag: 'DATA_CARD' },
                            { type: 'receipt', tag: 'RECEIPT_CARD' }
                        ];

                        let blocks = [];
                        let tempContent = content;

                        cardPatterns.forEach(pattern => {
                            const regex = new RegExp(`\\[${pattern.tag}\\]([\\s\\S]*?)\\[\\/?${pattern.tag}\\]`, 'g');
                            let match;
                            while ((match = regex.exec(content)) !== null) {
                                try {
                                    let jsonStr = match[1].replace(/```json|```/g, '').trim();
                                    const cardData = JSON.parse(jsonStr);
                                    blocks.push({ type: pattern.type, data: cardData });
                                    tempContent = tempContent.replace(match[0], '');
                                } catch (e) { console.error('JSON Parse Error', e); }
                            }
                        });

                        const finalMsg = tempContent.trim();
                        if (finalMsg) {
                            this.messages.push({ role: 'ai', sender: 'Si Famly', content: finalMsg, time: new Date().toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'}) });
                        }

                        blocks.forEach(block => {
                            const msgObj = {
                                role: 'ai',
                                sender: 'Si Famly',
                                type: block.type,
                                status: 'pending',
                                loading: false,
                                time: new Date().toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})
                            };

                            if (block.type === 'financial_summary') {
                                Object.assign(msgObj, { title: block.data.title, items: block.data.items, footer: block.data.footer });
                            } else {
                                Object.assign(msgObj, { 
                                    merchant: block.data.merchant, 
                                    items: Array.isArray(block.data.items) ? block.data.items : [], 
                                    total: block.data.total, 
                                    suggested_wallet: block.data.suggested_wallet, 
                                    smart_tip: block.data.smart_tip 
                                });
                            }
                            this.messages.push(msgObj);
                        });

                        if (!finalMsg && blocks.length === 0) {
                            this.messages.push({ role: 'ai', sender: 'Si Famly', content: content, time: new Date().toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'}) });
                        }
                    }
                } catch (e) {} finally { this.loading = false; this.$nextTick(() => { this.scrollToBottom(); }); }
            },
            
            triggerUpload(isCamera = false) {
                if (isCamera) this.$refs.fileInput.setAttribute('capture', 'environment');
                else this.$refs.fileInput.removeAttribute('capture');
                this.$refs.fileInput.click();
            },
            
            handleFileUpload(event) {
                const file = event.target.files[0];
                if (!file) return;
                const reader = new FileReader();
                reader.onload = async (e) => {
                    const base64 = e.target.result;
                    this.messages.push({ role: 'user', sender: this.userName, type: 'image', img: base64, time: new Date().toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'}) });
                    this.loading = true;
                    try {
                        const response = await fetch('{{ route('ai.scan-receipt') }}', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                            body: JSON.stringify({ image: base64 })
                        });
                        const result = await response.json();
                        if (result.success && result.data) {
                            this.messages.push({ 
                                role: 'ai', 
                                sender: 'Si Famly',
                                type: 'receipt', 
                                status: 'pending', 
                                loading: false,
                                merchant: result.data.merchant, 
                                suggested_wallet: result.data.suggested_wallet, 
                                smart_tip: result.data.smart_tip, 
                                items: Array.isArray(result.data.items) ? result.data.items : [], 
                                total: result.data.total, 
                                time: new Date().toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'}) 
                            });
                        }
                    } catch (err) {} finally { this.loading = false; this.$nextTick(() => { this.scrollToBottom(); }); }
                };
                reader.readAsDataURL(file);
            },

            async saveTransaction(msg) {
                if (msg.loading) return;
                msg.loading = true;
                const calculatedTotal = msg.items.reduce((sum, item) => sum + parseInt(item.price || 0), 0);
                
                try {
                    const response = await fetch('{{ route('ai.save-transaction') }}', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                        body: JSON.stringify({
                            merchant: msg.merchant,
                            total: calculatedTotal,
                            items: msg.items,
                            suggested_wallet: msg.suggested_wallet
                        })
                    });
                    const result = await response.json();
                    if (result.success) {
                        msg.status = 'saved';
                        msg.selected_wallet = msg.suggested_wallet;
                    } else {
                        throw new Error(result.message || 'Gagal menyimpan');
                    }
                } catch (e) {
                    alert('Gagal: ' + e.message + '. Silakan refresh halaman jika sesi habis.');
                } finally {
                    msg.loading = false;
                }
            },

            scrollToBottom() {
                const el = document.getElementById('chat-scroll');
                if (el) el.scrollTo({ top: el.scrollHeight, behavior: 'smooth' });
            }
         }">
        
        <!-- NATIVE HEADER (Dashboard Theme) -->
        <header class="fixed top-0 left-0 right-0 sm:left-64 z-[100] bg-white shadow-sm border-b border-slate-100 h-16 flex items-center px-4">
            <div class="flex items-center gap-3 w-full max-w-screen-xl mx-auto">
                <a href="javascript:history.back()" class="w-10 h-10 flex items-center justify-center text-slate-400 bg-slate-50 rounded-xl active:scale-95 transition-all">
                    <span class="material-symbols-outlined text-[20px]">arrow_back</span>
                </a>
                <div class="flex-1">
                    <h1 class="text-[14px] font-extrabold text-slate-800 leading-none">Si Famly</h1>
                    <p class="text-[9px] font-bold text-[#00AA13] mt-1 uppercase tracking-tight">Asisten Keuangan Cerdas</p>
                </div>
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-full bg-emerald-50 border border-emerald-100 flex items-center justify-center text-[#00AA13]">
                        <span class="material-symbols-outlined text-[16px]">verified_user</span>
                    </div>
                </div>
            </div>
        </header>

        <!-- CHAT AREA (Scroll Container) -->
        <main id="chat-scroll" class="flex-1 pt-20 pb-28 px-4 overflow-y-auto no-scrollbar space-y-6">
            <div class="max-w-2xl mx-auto space-y-6 pb-4">
                
                <!-- Welcome Card (Native Style) -->
                <div class="flex justify-start">
                    <div class="max-w-[85%] bg-white p-5 rounded-3xl rounded-tl-none shadow-[0_4px_20px_rgb(0,0,0,0.03)] border border-slate-100/50">
                        <div class="flex items-center gap-3 mb-3 pb-3 border-b border-slate-50">
                            <div class="w-10 h-10 rounded-2xl bg-emerald-500 flex items-center justify-center text-white shadow-lg shadow-emerald-500/20">
                                <span class="material-symbols-outlined text-[20px]">smart_toy</span>
                            </div>
                            <div>
                                <h4 class="text-[12px] font-extrabold text-slate-800">Si Famly</h4>
                                <p class="text-[9px] font-bold text-[#00AA13]">Online</p>
                            </div>
                        </div>
                        <p class="text-[13px] font-medium text-slate-600 leading-relaxed">Halo Keluarga Hebat! Mari diskusikan kondisi keuangan hari ini. Ingin catat pengeluaran atau cari tips hemat? 👋</p>
                    </div>
                </div>

                <template x-for="(msg, index) in messages" :key="index">
                    <div class="flex w-full animate-in fade-in slide-in-from-bottom-2 duration-300" :class="msg.role === 'user' ? 'justify-end' : 'justify-start'">
                        <div class="max-w-[85%] flex flex-col" :class="msg.role === 'user' ? 'items-end' : 'items-start'">
                            
                            <div class="p-4 rounded-[28px] shadow-sm relative transition-all"
                                 :class="msg.role === 'user' ? 'native-gradient text-white rounded-tr-sm shadow-emerald-500/20' : 'bg-white text-slate-800 rounded-tl-sm border border-slate-100/50 shadow-[0_4px_20px_rgb(0,0,0,0.03)]'">
                                
                                <template x-if="msg.type === 'image'"><img :src="msg.img" class="rounded-2xl overflow-hidden shadow-sm mb-2 max-h-56 w-full object-cover"></template>
                                
                                <template x-if="msg.type === 'receipt'">
                                    <div class="min-w-[260px] space-y-4">
                                        <div class="flex justify-between items-center bg-slate-50/80 -mx-4 -mt-4 p-4 rounded-t-[24px] border-b border-slate-100 mb-2">
                                            <div class="flex items-center gap-2">
                                                <div class="w-8 h-8 rounded-full bg-white flex items-center justify-center border border-slate-100">
                                                    <span class="material-symbols-outlined text-[#00AA13] text-[18px]">receipt_long</span>
                                                </div>
                                                <h4 contenteditable="true" @blur="msg.merchant = $event.target.innerText" class="text-[12px] font-extrabold text-slate-800 uppercase focus:outline-none" x-text="msg.merchant"></h4>
                                            </div>
                                        </div>
                                        <div class="px-1 space-y-2">
                                            <template x-for="item in msg.items">
                                                <div class="flex justify-between text-[11px] font-semibold text-slate-500 gap-4">
                                                    <span contenteditable="true" @blur="item.name = $event.target.innerText" class="flex-1 focus:outline-none focus:text-slate-900 pr-2" x-text="item.name"></span>
                                                    <span contenteditable="true" @blur="item.price = $event.target.innerText.replace(/[^0-9]/g, '')" class="focus:outline-none focus:text-slate-900 whitespace-nowrap" x-text="formatRupiah(item.price)"></span>
                                                </div>
                                            </template>
                                        </div>
                                        <div class="pt-3 border-t border-slate-100 flex justify-between items-center">
                                            <span class="text-[10px] font-extrabold text-slate-300 tracking-wider">TOTAL</span>
                                            <span class="text-[18px] font-extrabold text-slate-900" x-text="formatRupiah(msg.items.reduce((sum, item) => sum + parseInt(item.price || 0), 0))"></span>
                                        </div>
                                        
                                        <!-- Interactive Selector (Improved UX) -->
                                        <div class="px-3 py-3 bg-emerald-50/50 border border-emerald-100/50 rounded-2xl flex justify-between items-center -mx-1">
                                            <div class="flex flex-col">
                                                <span class="text-[8px] font-extrabold text-slate-400 uppercase tracking-widest">ALOKASI DOMPET</span>
                                                <span class="text-[12px] font-extrabold text-[#00AA13]" x-text="msg.suggested_wallet || 'Pilih...'"></span>
                                            </div>
                                            <button @click="msg.status = (msg.status === 'selecting' ? 'pending' : 'selecting')" class="w-8 h-8 rounded-lg bg-white border border-emerald-100 text-[#00AA13] flex items-center justify-center active:scale-90 transition-all">
                                                <span class="material-symbols-outlined text-[16px]">edit</span>
                                            </button>
                                        </div>

                                        <div x-show="msg.status === 'selecting'" class="mt-2 space-y-4 pt-3 border-t border-slate-100 max-h-56 overflow-y-auto no-scrollbar">
                                            @php $groupedWallets = $wallets->groupBy('wallet_type'); @endphp
                                            @foreach(['wallet' => 'DOMPET (SUMBER DANA)', 'pos' => 'POS ANGGARAN', 'savings' => 'TABUNGAN'] as $type => $label)
                                                @if(isset($groupedWallets[$type]))
                                                    <div class="space-y-2">
                                                        <span class="text-[8px] font-extrabold text-slate-400 uppercase tracking-widest bg-slate-100 px-2 py-0.5 rounded-full">{{ $label }}</span>
                                                        <div class="flex flex-wrap gap-1.5">
                                                            @foreach($groupedWallets[$type] as $w)
                                                                <button @click="msg.suggested_wallet = '{{ $w->nama }}'; msg.status = 'pending'" 
                                                                        class="text-[10px] font-bold px-3 py-2 bg-white border border-slate-100 rounded-xl hover:border-[#00AA13] hover:text-[#00AA13] shadow-sm transition-all active:scale-95">
                                                                    {{ $w->nama }}
                                                                </button>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                @endif
                                            @endforeach
                                        </div>

                                        <div class="flex gap-2" x-show="msg.status === 'pending'">
                                            <button @click="msg.status = 'cancelled'" class="flex-1 py-3.5 rounded-2xl border border-slate-200 text-slate-400 text-[11px] font-extrabold uppercase">Batal</button>
                                            <button @click="saveTransaction(msg)" :disabled="msg.loading" class="flex-[2] py-3.5 rounded-2xl bg-[#00AA13] text-white text-[11px] font-extrabold shadow-lg shadow-emerald-500/20 uppercase flex items-center justify-center gap-2">
                                                <span x-show="msg.loading" class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"></span>
                                                <span x-text="msg.loading ? 'Menyimpan...' : 'Simpan Transaksi'"></span>
                                            </button>
                                        </div>
                                    </div>
                                </template>

                                <template x-if="msg.status === 'saved'">
                                    <div class="mt-4 p-4 bg-emerald-50 rounded-[20px] border border-emerald-100 animate-in zoom-in-95 duration-500">
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 rounded-full bg-white flex items-center justify-center text-[#00AA13]">
                                                <span class="material-symbols-outlined text-[18px]">check_circle</span>
                                            </div>
                                            <div>
                                                <span class="text-[11px] font-extrabold text-slate-800 block">Tersimpan Berhasil!</span>
                                                <p class="text-[9px] font-semibold text-slate-500 mt-0.5" x-text="msg.selected_wallet"></p>
                                            </div>
                                        </div>
                                        <p class="text-[10px] font-bold text-center mt-3 pt-3 border-t border-emerald-100 italic text-emerald-600" x-text="'💡 ' + msg.smart_tip"></p>
                                    </div>
                                </template>

                                <template x-if="msg.type === 'financial_summary'">
                                    <div class="min-w-[240px] space-y-4">
                                        <h4 class="text-[12px] font-extrabold uppercase text-slate-800 tracking-tight" x-text="msg.title"></h4>
                                        <div class="space-y-2">
                                            <template x-for="item in msg.items"><div class="flex justify-between text-[11px] font-bold"><span class="text-slate-400" x-text="item.label"></span><span class="text-slate-700" x-text="formatRupiah(item.value)"></span></div></template>
                                        </div>
                                        <div class="pt-3 border-t flex justify-between items-center"><span class="text-[9px] font-extrabold text-slate-300">ESTIMASI TOTAL</span><span class="text-[18px] font-extrabold text-[#00AA13]" x-text="msg.footer"></span></div>
                                    </div>
                                </template>

                                <template x-if="!msg.type"><p class="text-[14px] font-medium leading-relaxed whitespace-pre-line" x-html="msg.role === 'ai' ? msg.content.replace(/\*\*(.*?)\*\*/g, '<b>$1</b>') : msg.content"></p></template>
                            </div>
                            <span class="text-[9px] font-extrabold text-slate-300 mt-1.5 px-2 uppercase tracking-tighter" x-text="msg.time"></span>
                        </div>
                    </div>
                </template>

                <!-- Typing Indicator (Native) -->
                <div x-show="loading" class="flex justify-start animate-in fade-in slide-in-from-bottom-2">
                    <div class="p-4 bg-white rounded-3xl rounded-tl-sm border border-slate-100 shadow-sm flex items-center gap-2 scale-90 origin-left">
                        <span class="w-2 h-2 bg-[#00AA13] rounded-full animate-bounce [animation-delay:-0.3s]"></span>
                        <span class="w-2 h-2 bg-[#00AA13] rounded-full animate-bounce [animation-delay:-0.15s]"></span>
                        <span class="w-2 h-2 bg-[#00AA13] rounded-full animate-bounce"></span>
                    </div>
                </div>
            </div>
        </main>

        <!-- NATIVE INPUT BAR (Dashboard Style) -->
        <div class="fixed bottom-0 left-0 right-0 sm:left-64 z-[110] bg-white border-t border-slate-100 p-4 pb-safe shadow-[0_-10px_40px_rgb(0,0,0,0.06)]">
            <div class="max-w-2xl mx-auto flex items-center gap-3">
                <div class="flex-1 bg-slate-50 rounded-[28px] border border-slate-100 flex items-center px-4 py-1.5 transition-all focus-within:bg-white focus-within:ring-2 focus-within:ring-emerald-500/10 focus-within:border-emerald-500/20">
                    <input 
                        x-model="userInput"
                        @keydown.enter.prevent="sendMessage()"
                        type="text"
                        class="flex-1 border-none focus:ring-0 text-[14px] bg-transparent py-2.5 placeholder:text-slate-400 font-semibold text-slate-700"
                        placeholder="Ketik pengeluaran / tanya tips..."
                        :disabled="loading"
                    >
                    <div class="flex items-center gap-1 border-l border-slate-200 ml-2 pl-2">
                        <button @click="triggerUpload(false)" class="w-9 h-9 flex items-center justify-center text-slate-400 hover:text-[#00AA13] transition-colors"><span class="material-symbols-outlined text-[20px]">attach_file</span></button>
                        <button @click="triggerUpload(true)" class="w-9 h-9 flex items-center justify-center text-slate-400 hover:text-[#00AA13] transition-colors"><span class="material-symbols-outlined text-[20px]">photo_camera</span></button>
                        <input type="file" x-ref="fileInput" class="hidden" @change="handleFileUpload($event)" accept="image/*">
                    </div>
                </div>

                <button @click="userInput.trim() ? sendMessage() : toggleRecording()" 
                        class="w-12 h-12 rounded-2xl flex items-center justify-center text-white shadow-xl shadow-emerald-500/20 transition-all active:scale-90 flex-shrink-0"
                        :class="(userInput.trim() || loading) ? 'native-gradient' : (isRecording ? 'bg-rose-500 shadow-rose-500/20 animate-pulse' : 'native-gradient')">
                    <span class="material-symbols-outlined text-[20px]" x-text="(userInput.trim() || loading) ? 'send' : (isRecording ? 'stop' : 'mic')"></span>
                </button>
            </div>
        </div>
    </div>
</x-app-layout>
