# Tech Architect: Famly Orchestration System

## 1. High-Level Stack
- **Core**: Laravel 10 (PHP 8.2+).
- **Frontend**: Blade, Alpine.js (Logic), Tailwind CSS (FamlyUI).
- **Realtime Service**: Laravel Reverb / Pusher untuk Group Chat dan Notifikasi.
- **Database**: MySQL 8.0.

## 2. Advanced Orchestration (GroupScope)
Sistem menggunakan **Global Query Scopes** (GroupScope) pada model `Tabungan`, `KategoriNamaTabungan`, dan `PlannedTransaction`.
- **Logic**: 
    - Jika `current_group_id` terdeteksi di session/user, query otomatis menambahkan `where group_id = current_group_id`.
    - Jika `current_group_id` NULL, query beralih ke `where user_id = auth_id AND group_id IS NULL` (Konteks Pribadi).
- **Security**: Menjamin isolasi data total antar unit keluarga/komunitas.

## 3. Agenda & Ritual Lifecycle
- **Entitas**: `PlannedTransaction` (Agenda).
- **Activity Types**: 
    - `Task`: Satu kali kejadian.
    - `Reminder`: Pengingat terjadwal.
    - `Ritual`: Kegiatan berulang yang disinkronkan ke seluruh anggota grup.
- **Workflow**: Create -> Pending -> Manual/AI Check-in -> Completed. Jika terlewat, status berubah menjadi `Missed`.

## 4. AI Engine (@Fams)
- **Model**: **Gemini 1.5 Flash** (via Google AI SDK).
- **Capabilities**:
    - **Vision**: Analisis struk belanja (Vision parsing).
    - **Voice**: NLP untuk input transaksi suara.
    - **Predictive**: Burn rate projection & anomaly detection di Grup.

## 5. Monetization Control
- **Personal Trial**: Limit 3x penggunaan AI per hari.
- **Subscriber**: Unlimited AI + Full Reporting access.
- **Group Activation**: Biaya iuran 1x (Lifetime) Rp 249.000 untuk mengaktifkan fitur kolaborasi grup.

## 6. Realtime & PWA
- **Broadcast**: Laravel Events + Reverb untuk update chat pesan `@Fams`.
- **PWA**: VITE PWA Plugin untuk native mobile feel.
