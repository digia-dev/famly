# Dokumen Desain Teknis & Arsitektur (Technical Design & Architecture) - Proyek Famly


## 1. Ringkasan Sistem
Aplikasi "Famly" dirancang sebagai platform manajemen keuangan kolaboratif berbasis cloud yang mengintegrasikan teknologi *Artificial Intelligence* (AI) untuk otomatisasi pencatatan. Sistem ini menggunakan arsitektur *Client-Server* dengan pendekatan *Backend-as-a-Service* (BaaS) untuk memastikan sinkronisasi data *real-time* di seluruh perangkat anggota keluarga.

---

## 2. Arsitektur Teknologi (Tech Stack)

| Komponen | Teknologi | Alasan Pemilihan |
| :--- | :--- | :--- |
| **Framework** | **Laravel 11 (Blade)** | Kerangka kerja PHP modern yang solid dengan ekosistem yang luas dan efisiensi pengembangan tinggi. |
| **Styling** | **Tailwind CSS v4** | *Utility-first CSS* untuk desain yang sangat kustom, modern, dan konsisten (Private Wealth Aesthetic). |
| **Frontend Logic** | **Alpine.js** | Minimalis dan reaktif untuk interaksi UI ringan tanpa beban *bundle* besar. |
| **Backend & Database** | **MySQL** | Reliable, mendukung skema relasional yang kompleks untuk data keuangan keluarga. |
| **PWA & Offline** | **Service Workers** | Menjamin ketersediaan aplikasi secara offline dan pengalaman instalasi native pada perangkat mobile. |
| **Font & Icons** | **Google Fonts & Material Symbols** | Memberikan estetika premium dan ikonografi yang jernih. |

---

## 3. Arsitektur Sistem (High-Level Architecture)

Sistem dibangun di atas infrastruktur mikro yang terintegrasi melalui API Gateway:

1.  **Presentation Layer:** Aplikasi web berbasis Blade yang dioptimalkan untuk performa PWA, menggunakan Alpine.js untuk reaktivitas UI.
2.  **Logic & Service Layer:** Menggunakan Laravel Controller dan Service Layer untuk memproses logika bisnis, klasifikasi kategori keuangan, dan integrasi API AI.
3.  **Data Layer:** MySQL sebagai penyimpanan utama dengan manajemen akses data berbasis *Family ID* untuk menjamin privasi antar grup keluarga secara ketat.

---

## 4. Skema Database (Data Schema)

### 4.1. Tabel Utama

| Tabel | Deskripsi | Kolom Kunci |
| :--- | :--- | :--- |
| `families` | Menyimpan entitas grup keluarga. | `id` (PK), `family_name`, `created_at` |
| `users` | Profil pengguna individu. | `id` (PK), `email`, `full_name`, `family_id` (FK) |
| `family_roles` | Definisi peran (Admin, Editor, Viewer). | `id`, `user_id`, `family_id`, `role_type` |
| `wallets` | Dompet/Kategori alokasi dana. | `id` (PK), `family_id` (FK), `name`, `balance`, `limit` |
| `transactions` | Catatan pengeluaran/pemasukan. | `id` (PK), `wallet_id` (FK), `user_id` (FK), `amount`, `category`, `status`, `metadata_ai` |
| `tasks` | Agenda tagihan dan penugasan. | `id` (PK), `family_id` (FK), `assigned_to` (FK), `due_date`, `is_completed` |

---

## 5. Perancangan Integrasi AI (Multimodal Input Pipeline)

Untuk mendukung alur bisnis 2.2, sistem akan menjalankan *pipeline* sebagai berikut:

1.  **Input Capture:** *Client* mengirimkan file (audio/gambar) atau teks ke *Edge Function*.
2.  **Processing:**
    *   **Gambar:** Dikonversi via OCR menjadi teks mentah.
    *   **Audio:** Dikonversi via Speech-to-Text (STT).
3.  **Parsing (LLM):** Model AI melakukan ekstraksi entitas (Nominal, Merchant, Tanggal, Kategori) dan mengembalikan format JSON.
4.  **Verification:** Data dikirim kembali ke aplikasi untuk konfirmasi pengguna sebelum melakukan *commit* ke database.

---

## 6. Mekanisme Keamanan dan Akses Data

### 6.1. Row Level Security (RLS)
Sistem menerapkan kebijakan akses data yang ketat di level database:
- `SELECT`: Pengguna hanya bisa melihat data jika `family_id` mereka sama dengan `family_id` pada baris data.
- `INSERT/UPDATE`: Dibatasi berdasarkan peran (Role) yang disimpan di tabel `family_roles`.

### 6.2. Hierarki Izin (Permissions)

| Fitur | Admin | Editor | Viewer |
| :--- | :--- | :--- | :--- |
| Create/Delete Group | Ya | Tidak | Tidak |
| Add/Invite Member | Ya | Tidak | Tidak |
| Add Transaction | Ya | Ya | Tidak |
| Edit Budget Limit | Ya | Ya | Tidak |
| View Analytics | Ya | Ya | Ya |

---

## 7. Sinkronisasi Data & Mode Offline

Aplikasi menerapkan pola **Offline-First**:
1.  Setiap perubahan data dilakukan pada *Local Storage* terlebih dahulu.
2.  *Service Worker* di latar belakang akan mendeteksi status koneksi.
3.  Saat *Online*, sistem akan melakukan sinkronisasi menggunakan *timestamp* terbaru untuk menghindari konflik data (*Last Write Wins* atau *Merge* berdasarkan ID Transaksi).

---

## 8. Infrastruktur Analitik dan Wawasan (AI Insights)

Proses pembuatan laporan (Alur 2.5) dilakukan secara asinkron:
1.  **Data Aggregation:** Sistem melakukan agregasi transaksi bulanan per kategori.
2.  **Contextual Analysis:** Data agregat dikirim ke AI Engine bersama dengan data historis bulan sebelumnya.
3.  **Insight Generation:** AI menghasilkan naratif saran keuangan (misal: identifikasi pemborosan pada kategori tertentu).
4.  **Formatting:** Hasil dikonversi menjadi komponen UI visual atau dokumen PDF/Excel menggunakan *Library Reporting*.

---

## 9. Matriks Status Teknis Transaksi

| Kode Status | Logika Sistem | Trigger |
| :--- | :--- | :--- |
| `PENDING` | Data tersimpan di *staging area*, belum mengurangi saldo dompet. | AI Processing selesai. |
| `VERIFIED` | Saldo dompet dikurangi (`current_balance - amount`). | Konfirmasi User. |
| `RECURRING` | Sistem menjadwalkan *cron job* untuk pembuatan entitas transaksi baru. | Input Tagihan Rutin. |
| `CANCELLED` | Data diabaikan dalam kalkulasi namun tetap ada untuk audit trail. | Hapus/Batal oleh Admin. |

---

**Catatan Akhir:**  
Dokumen ini menjadi acuan teknis bagi tim pengembang (Frontend, Backend, AI Engineer) untuk memulai fase pengembangan (Sprints). Seluruh perubahan pada arsitektur ini harus melalui persetujuan Senior System Analyst.