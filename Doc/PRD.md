# PRD: Famly Orchestration Platform

## 1. Visi Produk
Transformasi Famly dari aplikasi keuangan keluarga menjadi platform orkestrasi keuangan kolaboratif untuk berbagai entitas (Keluarga, Korporat Kecil, Komunitas, Grup Arisan, dll.).

## 2. Target Pengguna
- **Keluarga**: Pengelolaan keuangan rumah tangga bersama.
- **Komunitas/Arisan**: Transparansi dana iuran dan kas.
- **Korporat/Bisnis Kecil**: Shared wallet untuk operasional tim.
- **Individu**: Pengelolaan dana pribadi yang terintegrasi dengan grup sosial.

## 3. Fitur Utama

### A. Dynamic Group Management
- **Multi-Group Membership**: Pengguna dapat bergabung ke lebih dari satu grup.
- **Invite System**: Undangan via tautan unik. Admin grup wajib menyetujui (Accept) calon anggota baru.
- **Role System**: Admin (kontrol penuh), Member (catat transaksi, lihat laporan). Grup dapat memiliki lebih dari satu Admin.
- **Admin-Only Features**: Hanya Admin yang diperbolehkan membuat Agenda Grup (Tasks, Rituals, Reminders) dan menggunakan tag AI `@Fams`.

### B. Adaptive Dashboard & Management
- **Personal First**: Dashboard utama menampilkan list Dompet Pribadi untuk privasi.
- **Group Shortcuts**: Akses cepat ke grup-grup aktif di bagian dashboard.
- **Toggled Management**: Filter di header untuk berpindah antara pengelolaan aset "Pribadi" dan "Grup".

### C. Collaborative Chat & AI Financial Advisor
- **Group Chat**: Ruang diskusi per grup untuk koordinasi keuangan.
- **AI Tagging (@Fams)**: Admin dapat memanggil AI di dalam chat untuk menganalisis kesehatan kas grup. (Dibatasi hanya untuk peran Admin).
- **Transaction Notifications**: Ikon notifikasi di pojok kanan atas chat yang menampilkan log transaksi terbaru khusus untuk dompet grup tersebut.

## 4. Alur Kerja (Workflow)
1. **Pendaftaran**: User mendaftar -> Mendapatkan dompet pribadi default.
2. **Setup Grup**: User membuat grup -> Mendapatkan link invite.
3. **Kolaborasi**: Anggota masuk -> Mencatat transaksi di kategori grup -> Notifikasi muncul di chat.
4. **Analisis**: User memanggil `@Fams` di chat -> AI memberikan laporan instan.

## 5. Monetisasi (Premium Strategy)
- **Group Creation (Rp 249.000)**: Biaya aktivasi sekali bayar (One-time fee) untuk setiap grup baru yang dibuat.
- **Trial Profile**: Maksimal 1 bulan history, 3x AI/hari (untuk akun personal).
- **Subscriber Premium (Rp 29.900/bln)**: Unlimited History (Personal), fitur ekspor laporan, dan akses prioritas bantuan @Fams.
