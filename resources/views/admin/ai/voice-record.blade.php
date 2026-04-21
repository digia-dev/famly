<x-app-layout>
    @section('title', 'Catat Transaksi')

    <!-- Sticky Header with Back Button -->
    <div class="fixed top-0 left-0 right-0 bg-white/80 backdrop-blur-md z-[80] border-b border-slate-100 flex items-center h-14 px-4">
        <button onclick="window.history.back()" class="w-9 h-9 flex items-center justify-center text-slate-800 active:scale-95 transition-all">
            <span class="material-symbols-outlined text-[24px]">arrow_back</span>
        </button>
        <h1 class="flex-1 text-center text-[13px] font-black text-slate-900 tracking-tight pr-9">Catat Transaksi</h1>
    </div>

    <div class="bg-[#F6F7F8] min-h-screen flex flex-col items-center pt-14 px-4 overflow-x-hidden" 
         x-data="voiceRecorder()">
        
        <!-- Animated Voice Pulse Background -->
        <div class="fixed inset-0 pointer-events-none opacity-10 overflow-hidden">
            <div class="absolute inset-0 bg-primary/5"></div>
            <div x-show="isRecording" 
                 class="absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2 w-[300px] h-[300px] border-2 border-primary rounded-full animate-ping opacity-10"></div>
        </div>

        <div class="z-10 w-full max-w-md flex flex-col items-center">
            <!-- AI Living Orb Area (Interactive Replacement for White Circle) -->
            <div x-show="!resultData" 
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 scale-90"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-75"
                 class="relative w-48 h-48 flex items-center justify-center">
                <!-- Outer Ambient Glow -->
                <div class="absolute inset-0 rounded-full blur-[40px] opacity-20 transition-all duration-700"
                     :class="isProcessing ? 'bg-amber-400' : (isRecording ? 'bg-primary' : 'bg-emerald-400')"></div>
                
                <!-- The Morphing Orb -->
                <div class="relative w-32 h-32 flex items-center justify-center transition-all duration-500"
                     :class="isRecording ? 'scale-110' : 'scale-100'">
                    
                    <!-- Base Orb Layer -->
                    <div class="absolute inset-0 rounded-full shadow-2xl shadow-slate-200/50 transition-all duration-700 overflow-hidden border border-white/50"
                         :class="isProcessing ? 'bg-gradient-to-br from-amber-400 to-orange-500' : (isRecording ? 'bg-gradient-to-br from-blue-500 via-primary to-emerald-500' : 'bg-white')">
                        
                        <!-- Inner Gloss/Refraction -->
                        <div class="absolute inset-0 bg-gradient-to-tr from-white/20 to-transparent opacity-50"></div>
                    </div>

                    <!-- Voice Pips (Only when recording) -->
                    <div x-show="isRecording" class="flex items-center gap-1.5 h-12 z-10">
                        @foreach(range(1, 6) as $i)
                            <div class="w-1.5 bg-white rounded-full animate-wave shadow-[0_0_10px_rgba(255,255,255,0.5)]" 
                                 style="height: {{ rand(30, 90) }}%; animation-delay: {{ $i * 0.15 }}s"></div>
                        @endforeach
                    </div>

                    <!-- AI Character / Icon -->
                    <div x-show="!isRecording" class="z-10 flex flex-col items-center transition-all duration-500"
                         :class="isProcessing ? 'animate-bounce' : ''">
                        <span class="material-symbols-outlined text-[32px] transition-colors duration-500"
                              :class="isProcessing ? 'text-white' : 'text-primary'">
                            <span x-text="isProcessing ? 'smart_toy' : 'mic_none'"></span>
                        </span>
                    </div>
                </div>

                <!-- Circular Status Ring -->
                <svg class="absolute inset-0 w-full h-full -rotate-90 pointer-events-none opacity-20">
                    <circle cx="96" cy="96" r="88" stroke="currentColor" stroke-width="2" fill="none" class="text-slate-200" />
                    <circle cx="96" cy="96" r="88" stroke="currentColor" stroke-width="2" fill="none" 
                            class="transition-all duration-1000"
                            :class="isProcessing ? 'text-amber-500' : (isRecording ? 'text-primary' : 'text-emerald-500')"
                            stroke-dasharray="552" :stroke-dashoffset="isRecording ? '0' : '552'" />
                </svg>
            </div>

            <!-- Status Label Area -->
            <div x-show="!resultData" class="text-center mt-2">
                <div class="flex items-center justify-center gap-2">
                    <div x-show="isRecording" class="flex gap-1">
                        <div class="w-1 h-1 bg-primary rounded-full animate-ping"></div>
                        <div class="w-1 h-1 bg-primary rounded-full animate-ping [animation-delay:0.2s]"></div>
                        <div class="w-1 h-1 bg-primary rounded-full animate-ping [animation-delay:0.4s]"></div>
                    </div>
                    <p class="text-[11px] font-black tracking-widest transition-colors duration-500 uppercase" 
                       :class="isProcessing ? 'text-amber-600' : (isRecording ? 'text-primary' : 'text-emerald-600')"
                       x-text="isProcessing ? 'SEDANG BERPIKIR' : (isRecording ? 'MENDENGARKAN' : 'SIAP')"></p>
                </div>
                <p class="text-[10px] font-bold text-slate-400 mt-1 px-8 leading-tight" x-text="status"></p>
            </div>

            <!-- Live Transcript Bubble (Compact) -->
            <div x-show="(isRecording || transcript) && !resultData" 
                 class="w-full bg-white/70 backdrop-blur-md p-4 rounded-[28px] shadow-sm border border-slate-100 min-h-[60px] transition-all mt-4">
                <p class="text-[12px] font-medium text-slate-700 italic leading-snug" 
                   x-text="(transcript + interimTranscript).trim() ? '“' + (transcript + interimTranscript).trim() + '”' : 'Mulai bicara...'"></p>
            </div>
            </div>

            <!-- result area (Editable & Compact) -->
            <template x-if="resultData">
                <div class="w-full bg-white p-5 rounded-[28px] border border-slate-100 shadow-xl shadow-slate-200/50 space-y-3 mb-24 animate-in fade-in slide-in-from-bottom-4 duration-500"
                     x-data="{ activeTab: 'pos' }">
                    
                    <!-- Simplified Transcript Reference -->
                    <div class="bg-slate-50/50 rounded-2xl p-3 border border-slate-100/30">
                        <p class="text-[11px] font-medium text-slate-400 italic leading-snug" x-text="'“' + transcript.trim() + '”'"></p>
                    </div>

                    <div class="flex items-start gap-3 pb-3 border-b border-dashed border-slate-100">
                        <div class="w-9 h-9 rounded-xl bg-emerald-50 flex items-center justify-center text-primary shrink-0 mt-1">
                            <span class="material-symbols-outlined text-lg">edit_note</span>
                        </div>
                        <div class="flex-1 space-y-2">
                            <!-- Keterangan (Items/Barang) -->
                            <div class="flex flex-col">
                                <span class="text-[8px] font-bold text-slate-400 tracking-tight">Keterangan Barang/Jasa</span>
                                <input type="text" x-model="resultData.description" 
                                       class="w-full bg-transparent border-none p-0 text-[14px] font-bold text-slate-800 focus:ring-0 placeholder:text-slate-300" 
                                       placeholder="Misal: Beli Baju">
                            </div>

                            <!-- Merchant (Toko) - Optional -->
                            <div class="flex flex-col border-t border-slate-50 pt-1" x-show="resultData.merchant">
                                <span class="text-[8px] font-bold text-slate-400 tracking-tight">Nama Toko / Merchant</span>
                                <input type="text" x-model="resultData.merchant" 
                                       class="w-full bg-transparent border-none p-0 text-[12px] font-bold text-slate-500 focus:ring-0 placeholder:text-slate-300" 
                                       placeholder="Nama Toko">
                            </div>
                            
                            <!-- Total -->
                            <div class="flex items-center gap-1.5 pt-1 border-t border-slate-50">
                                <span class="text-[10px] font-bold text-primary">Rp</span>
                                <input type="number" x-model="resultData.total" 
                                       class="w-full bg-transparent border-none p-0 text-[16px] font-black text-slate-900 focus:ring-0">
                            </div>
                        </div>
                    </div>

                    <!-- Enhanced Selection Area -->
                    <div class="space-y-3">
                        <!-- Segmented Control -->
                        <div class="flex p-1 bg-slate-100 rounded-xl">
                            <button @click="activeTab = 'pos'" 
                                    class="flex-1 py-1.5 text-[10px] font-bold tracking-tight rounded-lg transition-all"
                                    :class="activeTab === 'pos' ? 'bg-white text-primary shadow-sm' : 'text-slate-400'">
                                Pos Anggaran
                            </button>
                            <button @click="activeTab = 'wallet'" 
                                    class="flex-1 py-1.5 text-[10px] font-bold tracking-tight rounded-lg transition-all"
                                    :class="activeTab === 'wallet' ? 'bg-white text-primary shadow-sm' : 'text-slate-400'">
                                Dompet / Rekening
                            </button>
                        </div>

                        <!-- Scrollable Selection Grid -->
                        <div class="space-y-2 max-h-[160px] overflow-y-auto no-scrollbar pr-1">
                            <!-- Pos List -->
                            <template x-if="activeTab === 'pos'">
                                <div class="grid grid-cols-1 gap-2">
                                    @foreach($posItems as $pos)
                                        <button @click="selectedWallet = '{{ $pos->nama }}'" 
                                                class="flex items-center justify-between p-3 rounded-xl border transition-all text-left group"
                                                :class="selectedWallet === '{{ $pos->nama }}' ? 'bg-emerald-50 border-primary' : 'bg-white border-slate-100'">
                                            <div class="flex items-center gap-2.5">
                                                <div class="w-7 h-7 rounded-lg flex items-center justify-center transition-colors"
                                                     :class="selectedWallet === '{{ $pos->nama }}' ? 'bg-primary text-white' : 'bg-slate-50 text-slate-400'">
                                                    <span class="material-symbols-outlined text-[16px]">account_balance_wallet</span>
                                                </div>
                                                <div>
                                                    <p class="text-[11px] font-bold text-slate-700 leading-none">{{ $pos->nama }}</p>
                                                    <p class="text-[9px] font-medium text-slate-400 mt-1">Saldo: Rp {{ number_format($pos->balance, 0, ',', '.') }}</p>
                                                </div>
                                            </div>
                                            <div x-show="selectedWallet === '{{ $pos->nama }}'" class="text-primary">
                                                <span class="material-symbols-outlined text-[18px]">check_circle</span>
                                            </div>
                                        </button>
                                    @endforeach
                                </div>
                            </template>

                            <!-- Wallet List -->
                            <template x-if="activeTab === 'wallet'">
                                <div class="grid grid-cols-1 gap-2">
                                    @foreach($wallets as $wallet)
                                        <button @click="selectedWallet = '{{ $wallet->nama }}'" 
                                                class="flex items-center justify-between p-3 rounded-xl border transition-all text-left group"
                                                :class="selectedWallet === '{{ $wallet->nama }}' ? 'bg-emerald-50 border-primary' : 'bg-white border-slate-100'">
                                            <div class="flex items-center gap-2.5">
                                                <div class="w-7 h-7 rounded-lg flex items-center justify-center transition-colors"
                                                     :class="selectedWallet === '{{ $wallet->nama }}' ? 'bg-primary text-white' : 'bg-slate-50 text-slate-400'">
                                                    <span class="material-symbols-outlined text-[16px]">payments</span>
                                                </div>
                                                <div>
                                                    <p class="text-[11px] font-bold text-slate-700 leading-none">{{ $wallet->nama }}</p>
                                                    <p class="text-[9px] font-medium text-slate-400 mt-1">Saldo: Rp {{ number_format($wallet->balance, 0, ',', '.') }}</p>
                                                </div>
                                            </div>
                                            <div x-show="selectedWallet === '{{ $wallet->nama }}'" class="text-primary">
                                                <span class="material-symbols-outlined text-[18px]">check_circle</span>
                                            </div>
                                        </button>
                                    @endforeach
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- AI Smart Tip (Ultra Compact) -->
                    <div class="p-2.5 bg-amber-50 rounded-xl border border-amber-100/30 flex gap-2">
                        <span class="material-symbols-outlined text-amber-600 text-xs">auto_awesome</span>
                        <p class="text-[9px] font-medium text-amber-900/70" x-text="resultData.smart_tip"></p>
                    </div>

                    <div class="pt-2">
                        <button @click="saveTransaction" 
                                class="w-full bg-primary text-white py-3.5 rounded-xl font-bold text-[13px] shadow-lg shadow-primary/20 active:scale-95 transition-all flex items-center justify-center gap-2">
                            <template x-if="isProcessing">
                                <div class="w-3.5 h-3.5 border-2 border-white border-t-transparent animate-spin rounded-full"></div>
                            </template>
                            <span x-text="isProcessing ? 'Menyimpan...' : 'Simpan Transaksi'"></span>
                        </button>
                        <button @click="resultData = null; transcript = ''; status = 'Mulai ulang'" 
                                class="w-full text-[10px] font-bold text-slate-400 py-3 mt-1 tracking-tight">HAPUS & REKAM ULANG</button>
                    </div>
                </div>
            </template>

        <!-- Primary Mic Button (Only visible when no result) -->
        <div x-show="!resultData" class="fixed bottom-12 left-0 right-0 z-[70] flex flex-col items-center gap-3 px-4">
            <p x-show="!isRecording" class="text-[10px] font-bold text-slate-400 uppercase tracking-widest animate-bounce">Tahan / Ketuk untuk bicara</p>
            <button @click="toggleRecording" 
                    class="w-16 h-16 rounded-full flex items-center justify-center shadow-2xl transition-all active:scale-90"
                    :class="isRecording ? 'bg-red-500 scale-110 shadow-red-500/20' : 'bg-primary shadow-primary/20'">
                <span class="material-symbols-outlined text-white text-2xl" x-text="isRecording ? 'stop' : 'mic'"></span>
            </button>
        </div>
        </div>
    </div>

    <style>
        @keyframes wave {
            0%, 100% { transform: scaleY(1); }
            50% { transform: scaleY(1.5); }
        }
        .animate-wave {
            animation: wave 1s ease-in-out infinite;
        }
    </style>

    @push('scripts')
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('voiceRecorder', () => ({
                isRecording: false,
                transcript: '',
                interimTranscript: '',
                status: 'Ketuk tombol mic untuk mulai',
                isProcessing: false,
                resultData: null,
                recognition: null,
                selectedWallet: '',

                init() {
                    const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
                    
                    // Recover preferences from cookies
                    const savedWallet = this.getCookie('famly_last_wallet');
                    const savedTab = this.getCookie('famly_last_tab');
                    if (savedTab) this.activeTab = savedTab;
                    if (savedWallet) this.selectedWallet = savedWallet;

                    if (SpeechRecognition) {
                        this.recognition = new SpeechRecognition();
                        this.recognition.continuous = true;
                        this.recognition.interimResults = true;
                        this.recognition.lang = 'id-ID';

                        this.recognition.onstart = () => {
                            this.isRecording = true;
                            this.interimTranscript = '';
                            this.status = 'Sedang mendengarkan...';
                        };

                        this.recognition.onresult = (event) => {
                            this.interimTranscript = '';
                            for (let i = event.resultIndex; i < event.results.length; ++i) {
                                if (event.results[i].isFinal) {
                                    this.transcript += event.results[i][0].transcript + ' ';
                                } else {
                                    this.interimTranscript += event.results[i][0].transcript;
                                }
                            }
                        };

                        this.recognition.onerror = (event) => {
                            console.error('Speech error:', event.error);
                            if (event.error === 'no-speech') {
                                this.status = 'Suara tidak terdeteksi.';
                            } else {
                                this.status = 'Error: ' + event.error;
                            }
                            this.isRecording = false;
                        };

                        this.recognition.onend = () => {
                            this.isRecording = false;
                            if (this.transcript.trim().length > 0) {
                                this.processTranscript();
                            } else {
                                this.status = 'Selesai. Suara tidak terdeteksi.';
                            }
                        };
                    } else {
                        this.status = 'Browser tidak mendukung rekaman suara.';
                    }

                    // Watch for tab & wallet changes to save in cookies
                    this.$watch('activeTab', value => this.setCookie('famly_last_tab', value, 30));
                    this.$watch('selectedWallet', value => this.setCookie('famly_last_wallet', value, 30));
                },

                setCookie(name, value, days) {
                    let expires = "";
                    if (days) {
                        let date = new Date();
                        date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
                        expires = "; expires=" + date.toUTCString();
                    }
                    document.cookie = name + "=" + (value || "") + expires + "; path=/";
                },

                getCookie(name) {
                    let nameEQ = name + "=";
                    let ca = document.cookie.split(';');
                    for (let i = 0; i < ca.length; i++) {
                        let c = ca[i];
                        while (c.charAt(0) == ' ') c = c.substring(1, c.length);
                        if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length, c.length);
                    }
                    return null;
                },

                toggleRecording() {
                    if (this.isRecording) {
                        this.recognition.stop();
                    } else {
                        this.transcript = '';
                        this.resultData = null;
                        this.recognition.start();
                    }
                },

                async processTranscript() {
                    this.isProcessing = true;
                    this.status = 'AI sedang membedah kata...';

                    try {
                        const response = await fetch('{{ route('ai.process-voice') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({ text: this.transcript })
                        });

                        const result = await response.json();
                        if (result.success) {
                            this.resultData = result.data;
                            this.selectedWallet = result.data.suggested_wallet;
                            this.status = 'Hasil siap ditinjau!';
                        } else {
                            throw new Error(result.message);
                        }
                    } catch (error) {
                        this.status = 'Gagal: ' + error.message;
                    } finally {
                        this.isProcessing = false;
                    }
                },

                async saveTransaction() {
                    if (this.isProcessing) return;
                    this.isProcessing = true;
                    this.status = 'Menyimpan...';

                    try {
                        const response = await fetch('{{ route('ai.save-transaction') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                total: this.resultData.total,
                                merchant: this.resultData.merchant,
                                description: this.resultData.description,
                                wallet_name: this.selectedWallet,
                                items: this.resultData.items
                            })
                        });

                        const result = await response.json();
                        if (result.success) {
                            window.location.href = '{{ route('admin.dashboard') }}?status=voice_success';
                        } else {
                            // Extract validation errors if possible
                            let errMsg = result.message || 'Terjadi kesalahan validasi.';
                            if (result.errors) {
                                errMsg = Object.values(result.errors).flat().join('\n');
                            }
                            throw new Error(errMsg);
                        }
                    } catch (error) {
                        alert('Gagal menyimpan:\n' + error.message);
                    } finally {
                        this.isProcessing = false;
                    }
                }
            }));
        });
    </script>
    @endpush
</x-app-layout>
