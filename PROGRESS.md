# Progress Bank Sampah Digital Desa

## Overview
Proyek sistem informasi manajemen Bank Sampah berbasis web untuk desa-desa di Indonesia.

---

## Fase-fase Pengembangan

### [x] Fase 1: Setup Project Structure ✅
**Tanggal:** September 2025

- [x] Buat struktur folder project
- [x] Setup public/index.php sebagai entry point
- [x] Konfigurasi database connection (PDO)
- [x] Konfigurasi aplikasi (app.php)
- [x] Setup .env.example
- [x] Setup .gitignore
- [x] Buat README.md

**Deliverables:**
- Struktur folder `public/`, `app/`, `config/`, `app/views/`
- Routing sederhana (query string & clean URL)
- CSS & JS assets

---

### [x] Fase 2: Database Schema ✅
**Tanggal:** September 2025

- [x] Buat file migration SQL
- [x] Buat file seed data sample
- [x] Jalankan migration ke database lokal
- [x] Insert sample data

**Skema Database - 16 Tabel:**

| No | Tabel | Deskripsi |
|----|-------|-----------|
| 1 | `desa` | Master data desa/kelurahan |
| 2 | `bank_sampah` | Data unit bank sampah |
| 3 | `users` | Akun pengguna sistem |
| 4 | `nasabah` | Data anggota/nasabah bank sampah |
| 5 | `kategori_sampah` | Kategori dan jenis sampah |
| 6 | `harga_sampah` | Harga beli/jual per kategori |
| 7 | `pengepul` | Data pengepul/pedagang |
| 8 | `transaksi_beli` | Transaksi pembelian sampah |
| 9 | `detail_beli` | Detail item transaksi beli |
| 10 | `transaksi_jual` | Transaksi penjualan sampah |
| 11 | `detail_jual` | Detail item transaksi jual |
| 12 | `stok_sampah` | Stok per kategori per bank |
| 13 | `mutasi_saldo` | Riwayat perubahan saldo |
| 14 | `pengajuan_pencairan` | Permintaan pencairan saldo |
| 15 | `notifikasi` | Sistem notifikasi |
| 16 | `audit_log` | Log aktivitas audit |

**Indeks Tambahan:**
- `transaksi_beli(bank_sampah_id, tanggal)`
- `transaksi_jual(bank_sampah_id, tanggal)`
- `pengajuan_pencairan(status, nasabah_id)`
- `mutasi_saldo(nasabah_id, created_at)`

**Sample Data:**
- 1 desa, 1 bank sampah
- 3 users (admin, pengelola, kades)
- 3 nasabah, 10 kategori sampah
- 10 harga sampah aktif
- 3 pengepul, 4 transaksi beli, 2 transaksi jual
- 3 pengajuan pencairan, sample notifikasi & audit log

**File:**
- `database/migrations/001_initial_schema.sql`
- `database/seeds/001_sample_data.sql`

---

### [x] Fase 3: Authentication & Authorization ✅
**Tanggal:** September 2025

**File yang Dibuat:**

| File | Deskripsi |
|------|-----------|
| `app/models/User.php` | Model untuk interaksi tabel users |
| `app/helpers/auth.php` | Helper functions untuk auth & proteksi |
| `app/controllers/AuthController.php` | Controller login/logout |
| `app/views/auth/login.php` | Form login |
| `app/controllers/AdminController.php` | Controller admin (dengan proteksi) |
| `app/controllers/PengelolaController.php` | Controller pengelola (dengan proteksi) |
| `app/controllers/KadesController.php` | Controller kepala desa (dengan proteksi) |
| `app/controllers/NasabahController.php` | Controller nasabah (dengan proteksi) |
| `app/views/admin/dashboard.php` | Dashboard placeholder admin |
| `app/views/pengelola/dashboard.php` | Dashboard placeholder pengelola |
| `app/views/kades/dashboard.php` | Dashboard placeholder kades |
| `app/views/nasabah/dashboard.php` | Dashboard placeholder nasabah |
| `public/index.php` | Routing dengan proteksi session |

**Fitur:**
- [x] Login dengan no_hp + password
- [x] Session management dengan session_regenerate_id()
- [x] Role-based access control (ADMIN, PENGELOLA, KADES, NASABAH)
- [x] Middleware proteksi route (requireLogin, requireRole)
- [x] Redirect otomatis sesuai role
- [x] Flash message untuk notifikasi
- [x] Password hashing dengan bcrypt

**Login Test (No HP + Password):**
| Role | No HP | Password |
|------|-------|----------|
| Admin | 081234567801 | password123 |
| Pengelola | 081234567802 | password123 |
| Kades | 081234567803 | password123 |
| Nasabah | 081234567811 | password123 |
| Nasabah | 081234567812 | password123 |
| Nasabah | 081234567813 | password123 |

---

### [x] Fase 4: Modul Admin ✅
**Tanggal:** September 2025

Todo:
- [x] Dashboard admin (fitur)
- [x] Kelola data desa
- [x] Kelola data bank sampah
- [x] Kelola data pengguna
- [x] Kelola data hidung
- [x] Pengaturan sistem (placeholder)

**File yang Dibuat:**

| File | Deskripsi |
|------|-----------|
| `app/models/Desa.php` | Model CRUD master data desa |
| `app/models/BankSampah.php` | Model CRUD bank sampah |
| `app/controllers/AdminController.php` | Controller dengan semua fitur CRUD |
| `app/views/admin/dashboard.php` | Dashboard dengan statistik |
| `app/views/admin/users/index.php` | List pengguna |
| `app/views/admin/users/form.php` | Form tambah/edit pengguna |
| `app/views/admin/desa/index.php` | List desa |
| `app/views/admin/desa/form.php` | Form tambah/edit desa |
| `app/views/admin/bank_sampah/index.php` | List bank sampah |
| `app/views/admin/bank_sampah/form.php` | Form tambah/edit bank sampah |
| `app/views/admin/nasabah/index.php` | List hidung |
| `app/views/admin/nasabah/form.php` | Form tambah/edit hidung |
| `app/views/admin/pengaturan/index.php` | Pengaturan placeholder |

---

### [x] Fase 5: Modul Pengelola ✅
**Tanggal:** September 2025

Todo:
- [x] Dashboard pengelola (fitur)
- [x] Kelola kategori & harga sampah
- [x] Transaksi beli (setoran sampah)
- [x] Transaksi jual (ke pengepul)
- [x] Kelola pengepul
- [x] Laporan operasional

**File yang Dibuat:**

| File | Deskripsi |
|------|-----------|
| `app/models/KategoriSampah.php` | Model CRUD kategori sampah |
| `app/models/HargaSampah.php` | Model harga sampah |
| `app/models/Pengepul.php` | Model CRUD pengepul |
| `app/models/TransaksiBeli.php` | Model transaksi beli (dengan mutasi saldo) |
| `app/models/TransaksiJual.php` | Model transaksi jual |
| `app/models/StokSampah.php` | Model stok sampah |
| `app/controllers/PengelolaController.php` | Controller dengan semua fitur |
| `app/views/pengelola/dashboard.php` | Dashboard dengan statistik |
| `app/views/pengelola/transaksi_beli/*` | List & form transaksi beli |
| `app/views/pengelola/transaksi_jual/*` | List & form transaksi jual |
| `app/views/pengelola/kategori/*` | CRUD kategori |
| `app/views/pengelola/harga/*` | CRUD harga sampah |
| `app/views/pengelola/pengepul/*` | CRUD pengepul |
| `app/views/pengelola/nasabah/index.php` | List data hidung |
| `app/views/pengelola/laporan/index.php` | Laporan operasional |

---

### [x] Fase 6: Modul Nasabah ✅
**Tanggal:** September 2025

Todo:
- [x] Dashboard nasabah (fitur)
- [x] Riwayat setor
- [x] Pengajuan pencairan
- [x] Edit profil (placeholder)

**File yang Dibuat:**

| File | Deskripsi |
|------|-----------|
| `app/models/Nasabah.php` | Model untuk interaksi tabel hidung |
| `app/models/MutasiSaldo.php` | Model untuk riwayat mutasi saldo |
| `app/models/PengajuanPencairan.php` | Model untuk pengajuan pencairan saldo |
| `app/controllers/NasabahController.php` | Controller dengan dashboard, riwayat, cairkan |
| `app/views/nasabah/dashboard.php` | Dashboard dengan saldo dan ringkasan |
| `app/views/nasabah/riwayat.php` | Halaman riwayat transaksi dengan pagination |
| `app/views/nasabah/cairkan.php` | Form pengajuan pencairan |
| `public/index.php` | Update routing untuk /nasabah/riwayat & /nasabah/cairkan |

**Fitur:**
- [x] Dashboard: tampilkan saldo tabungan, saldo tersedia, riwayat 5 transaksi terakhir
- [x] Riwayat: tabel lengkap mutasi saldo dengan pagination
- [x] Pencairan: form dengan validasi (minimal Rp 10.000, tidak melebihi saldo)
- [x] Proteksi: semua data diambil dari session (bukan input form)
- [x] Prepared statement PDO untuk semua query
- [x] Format Rupiah Indonesia (Rp X.XXX.XXX)
- [x] Konstanta MINIMAL_PENCAIRAN untuk mudah diubah

---

### [x] Fase 7: Modul Kepala Desa ✅
**Tanggal:** September 2025

Todo:
- [x] Dashboard kades (fitur)
- [x] Monitoring kegiatan
- [x] Laporan keuangan
- [x] Export laporan (placeholder PDF/Excel)

**File yang Dibuat:**

| File | Deskripsi |
|------|-----------|
| `app/controllers/KadesController.php` | Controller dengan dashboard, monitoring, laporan |
| `app/views/kades/dashboard.php` | Dashboard dengan rekapitulasi per bank |
| `app/views/kades/monitoring.php` | Monitoring aktivitas terbaru |
| `app/views/kades/laporan.php` | Laporan keuangan dengan filter |

---

### [x] Fase 8: Fitur Tambahan ✅
**Tanggal:** September 2025

Todo:
- [x] Sistem notifikasi
- [x] Audit trail
- [x] Dashboard analytics

**File yang Dibuat:**

| File | Deskripsi |
|------|-----------|
| `app/models/Notifikasi.php` | Model untuk sistem notifikasi |
| `app/models/AuditLog.php` | Model + helper untuk audit trail |
| `app/models/Analytics.php` | Model untuk statistik & analytics |
| `app/views/admin/audit_log.php` | View audit log untuk admin |
| `app/controllers/AdminController.php` | Update dengan method auditLog |

**Fitur:**
- [x] Notifikasi: create, get, markRead, countUnread
- [x] Audit Log: INSERT, UPDATE, DELETE per record
- [x] Analytics: overview, chart data 7 hari, top kategori
- [x] Helper functions: audit_log(), notifikasi()

---

## ✅ SEMUA FASE SELESAI

---

## Tech Stack
- **PHP:** 7.3+ (Compatible dengan PHP 7.3.29 - tanpa union types)
- **Database:** MySQL 5.7+ (InnoDB, utf8mb4)
- **Frontend:** HTML5, CSS3, Vanilla JS
- **Server:** Apache/Nginx

---

## Cara Setup Lokal

```bash
# 1. Clone repository
git clone <url> bank-sampah-desa
cd bank-sampah-desa

# 2. Copy environment file
cp .env.example .env

# 3. Edit .env sesuai konfigurasi Anda

# 4. Buat database
mysql -u root -e "CREATE DATABASE bank_sampah_desa CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# 5. Jalankan migration
mysql -u root bank_sampah_desa < database/migrations/001_initial_schema.sql

# 6. Insert sample data
mysql -u root bank_sampah_desa < database/seeds/001_sample_data.sql

# 7. Jalankan server
cd public
php -S localhost:8000
```

## Login Test
| Role | No HP | Password |
|------|-------|----------|
| Admin | 081234567801 | password123 |
| Pengelola | 081234567802 | password123 |
| Kades | 081234567803 | password123 |

---

## Catatan
- Project menggunakan pure PHP tanpa framework
- Routing menggunakan native PHP
- Pattern MVC sederhana
- Password di-hash dengan bcrypt
- Auth menggunakan no_hp (bukan email)
"Target versi PHP: 7.3.29 — JANGAN pakai fitur PHP 8.x seperti match expression, nullsafe operator (?->), named arguments, str_contains(), str_starts_with(), str_ends_with(), enum, readonly property, atau union type. Gunakan syntax yang kompatibel PHP 7.3: switch/if-else biasa, isset()/strpos() untuk cek string, dan array biasa."