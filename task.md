# Famly Redesign Master Task List: Private Wealth Edition

Fokus utama daftar ini adalah implementasi visual dan fungsionalitas UI yang presisi sesuai dengan pedoman **Private Wealth Aesthetic** (Emerald & Gold) dan folder desain yang tersedia.

---

## ✅ Phase 1: Foundational Design System (The "Digital Heirloom" Core)
- [x] T1.01 Konfigurasi Design Tokens (Emerald `#006d36`, Gold `#735c00`) di Tailwind CSS.
- [x] T1.02 Implementasi **The No-Line Rule**: Pastikan tidak ada border 1px solid untuk memisahkan konten.
- [x] T1.03 Setup Typography: Gunakan Helvetica/Arial dengan tracking editorial.
- [x] T1.04 Setup PWA Baseline: `manifest.json`, `sw.js`, dan Ikon.
- [x] T1.05 Global Layout: Implementasi `bg-surface` (#f9f9fe) dan `bg-surface-low` (#f3f3f8).
- [x] T1.06 Komponen Navigasi: Redesain Header & Bottom Bar sesuai Figma.
- [x] T1.07 Implementasi "Weighted Transitions" menggunakan cubic-bezier (0.4, 0, 0.2, 1).

## ✅ Phase 2: Onboarding & Authentication Redesign
- [x] T2.01 Redesain `selamat_datang`: Implementasi visual Soft Minimalism & Hero Image.
- [x] T2.02 Redesain `masuk_ke_akun`: Form login tanpa border box, minimalist bottom-line.
- [x] T2.03 Redesain `daftar_akun_baru`: Step-by-step onboarding yang elegan.
- [x] T2.04 Redesain `lupa_sandi`: Halaman pemulihan akun yang bersih.
- [x] T2.05 Redesain `ubah_kata_sandi`: Interface keamanan dengan feedback visual.

## ✅ Phase 3: Core Dashboard & Awareness
- [x] T3.01 Redesain `dashboard_utama`: Layout bento berdasar desain Figma (Total Balance, Achievement).
- [x] T3.02 Implementasi "The Family Pulse": Horizontal scrolling avatar dengan ring status.
- [x] T3.03 Redesain `notifikasi` & Agenda: List aktivitas keluarga dengan tonal layering.

## ✅ Phase 4: Wallet & Transaction Management (The Ledger)
- [x] T4.01 Redesain `detail_dompet`: Tampilan detail dompet sesuai desain Figma (Dana Darurat layout).
- [x] T4.02 Dashboard Integration: List dompet/kantong yang dapat diklik ke detail.
- [x] T4.03 Redesain `tambah_kantong`: Modal/Screen pembuatan pos keuangan baru.
- [x] T4.04 Redesain `manajemen_dompet`: List kartu dompet (Primary/Secondary bank feel).
- [ ] T4.05 Transaction CRUD: Integrasi backend untuk input pemasukan/pengeluaran di tiap dompet.

## 🟢 Phase 5: AI Butler & Financial Growth
- [ ] T5.01 Integrasi Groq API: Setup kueri untuk analisis transaksi keluarga.
- [ ] T5.02 UI Chat Assistant: Antarmuka chat "Butler" yang elegan dan responsif.
- [ ] T5.03 Smart Predictions: Widget prediksi pengeluaran bulan depan.
- [ ] T5.04 Financial Rituals: Pengingat pembayaran rutin dengan tone bahasa yang sopan.

## 🟢 Phase 6: Family Sharing & Social Proof
- [ ] T6.01 Role Management: Filter tampilan antara "Dins" (Head) dan "Viewer" (Member).
- [ ] T6.02 Photo Memory: Lampiran foto/nota pada setiap transaksi dengan galeri light-box.
- [ ] T6.03 Activity Stream: Timeline real-time siapa yang menginput apa.

## 🟢 Phase 7: Polish & Launch Readiness
- [ ] T7.01 Audit "No-Line Rule": Pastikan konsistensi tonal layering di seluruh halaman.
- [ ] T7.02 PWA Prompt: Implementasi tombol "Install App" yang tidak mengganggu.
- [ ] T7.03 Optimization: Pengaturan caching dan optimasi asset gambar.
