# Design Implementation: FamlyUI (Multi-Group)

## 1. Design Philosophy
- **High-Density**: Penggunaan ruang maksimal tanpa kesan sesak (High information density).
- **Gojek-Style**: Elemen membulat (`rounded-2xl` - `rounded-[35px]`), penggunaan warna hijau primer yang kuat.
- **No-Line Rule (Premium Architecture)**: 
    - Dilarang menggunakan border default `border-slate-200`.
    - Gunakan **Tonal Layering** (perbedaan tipis warna background) atau **Soft Shadows** untuk pemisahan elemen.
    - Border hanya diperbolehkan jika menggunakan warna tersamar (`border-slate-100/50`).
- **Glassmorphism**: Layer transparan (blur-xl) untuk navigasi floating dan modal.

## 2. Layout Patterns
### A. The Floating Island
- Navigasi utama (Bottom Nav) tidak menempel ke pinggir layar.
- Menggunakan `rounded-[32px]` dengan padding samping, memberikan kesan melayang (Floating).

### B. High-Density Widgets
- Penggunaan `aspect-square` untuk grid dompet.
- Angka nominal menggunakan `font-black` (900) dengan ukuran yang dikompaksi.

## 3. UI Tokens
- **Primary**: `#00AA13` (Famly Green).
- **Secondary**: `#F7F9FA` (Soft Background).
- **Typography**: 
    - Font: **Plus Jakarta Sans**.
    - Hierarchy: Angka (900), Judul (800), Body (600), Caption (500).
- **Radius**:
    - Small: `16px` (Buttons).
    - Medium: `24px` (Cards).
    - Large: `32px` - `40px` (Headers & Navigation Islands).

## 4. Interaction Principles
- **Kinetic Scrolling**: Transisi halus saat scroll antar section.
- **Context Feedback**: Perubahan warna aksen tipis saat berpindah dari konteks Pribadi ke Grup.
