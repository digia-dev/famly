# Database Schema: Famly Orchestration

## 1. Core Tables

### `users`
- `id`, `name`, `email`, `password`
- `subscription_status`, `subscription_until`, `ai_usage_count`
- `current_group_id`: (Integer) ID grup yang sedang aktif dibuka.

### `groups` (Refactored from `families`)
- `id`, `name`, `type` (Corporate, Family, Community)
- `invite_code`, `admin_id`
- `is_paid`: (Boolean) Penanda apakah iuran aktivasi Rp 249.000 sudah terverifikasi.
- `created_at`, `updated_at`

### `group_members` (Pivot Table)
- `group_id`, `user_id`
- `role` (admin, member)
- `status` (Pending, Active, Blocked)

### `group_requests` (New joining flow)
- `id`, `user_id`, `group_id`
- `status` (pending, approved, rejected)
- `message` (Text)

### `kategori_nama_tabungans` (Wallets & Categories)
- `id`, `nama`, `icon`, `wallet_type`, `color`
- `is_group`: (Boolean) True jika milik grup.
- `group_id`: (Nullable) ID grup jika `is_group` true.
- `user_id`: (Nullable) ID user jika `is_group` false.

### `tabungans` (Transactions)
- `id`, `nominal`, `keterangan`
- `nama`: (FK to kategori_nama_tabungans)
- `user_id`: (FK to users) Pencatat transaksi.
- `group_id`: (Nullable) Scoped context.
- `created_at`, `updated_at`

### `planned_transactions` (Agenda & Rituals)
- `id`, `nama`, `nominal`, `keterangan`, `activity_type` (task, reminder, ritual)
- `jatuh_tempo`, `is_group`, `group_id`, `user_id`
- `status` (pending, completed, missed)

### `chat_messages`
- `id`, `group_id`, `user_id`
- `message`: (Text)
- `type`: (Text: text, ai_insight, system)
- `created_at`

## 2. Relationships Table
| From | To | Type |
| :--- | :--- | :--- |
| `users` | `groups` | Many-to-Many via `group_members` |
| `users` | `group_requests` | One-to-Many |
| `groups` | `kategori_nama_tabungans` | One-to-Many |
| `users` | `kategori_nama_tabungans` | One-to-Many (Personal Wallets) |
| `kategori_nama_tabungans` | `tabungans` | One-to-Many |
| `groups` | `planned_transactions` | One-to-Many |
| `groups` | `chat_messages` | One-to-Many |
