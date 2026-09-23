# Bank Sampah Digital Desa

Sistem informasi manajemen Bank Sampah berbasis web untuk desa-desa di Indonesia.

## Requirements

- **PHP**: 7.3 atau lebih baru (tested on PHP 7.3.29)
- **MySQL**: 5.7 atau lebih baru (disarankan MySQL 8.0)
- **Web Server**: Apache/Nginx (atau gunakan built-in PHP server untuk development)
- **Ekstensi PHP**: pdo, pdo_mysql, mbstring, json

## Cara Menjalankan Secara Lokal

### 1. Clone/Download Project

```bash
# Clone repository (jika menggunakan git)
git clone <repository-url> bank-sampah-desa

# Atau download dan extract file
cd bank-sampah-desa
```

### 2. Setup Environment

Copy file `.env.example` menjadi `.env`:

```bash
# Linux/Mac
cp .env.example .env

# Windows (Command Prompt)
copy .env.example .env

# Windows (PowerShell)
Copy-Item .env.example .env
```

Edit file `.env` sesuai konfigurasi database Anda:

```env
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost/bank-sampah-desa/public

DB_HOST=localhost
DB_PORT=3306
DB_NAME=bank_sampah_desa
DB_USER=root
DB_PASSWORD=your_password_here
```

### 3. Buat Database

Login ke MySQL dan buat database baru:

```sql
CREATE DATABASE bank_sampah_desa CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 4. Konfigurasi Web Server

#### Option A: PHP Built-in Server (untuk development)

```bash
cd public
php -S localhost:8000
```

Buka browser: http://localhost:8000

#### Option B: Apache Virtual Host

Tambahkan virtual host di `httpd-vhosts.conf`:

```apache
<VirtualHost *:80>
    DocumentRoot "C:/xampp/htdocs/bank-sampah-desa/public"
    ServerName bank-sampah.test
    <Directory "C:/xampp/htdocs/bank-sampah-desa/public">
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

Tambahkan hosts di `C:\Windows\System32\drivers\etc\hosts`:

```
127.0.0.1 bank-sampah.test
```

#### Option C: Nginx

```nginx
server {
    listen 80;
    server_name bank-sampah.test;
    root C:/xampp/htdocs/bank-sampah-desa/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass 127.0.0.1:9000;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

### 5. Struktur URL

Setelah setup, Anda dapat mengakses:

| URL | Keterangan |
|-----|------------|
| `/` atau `?page=home` | Halaman beranda |
| `/dashboard` | Dashboard utama |
| `/nasabah` | Modul Nasabah |
| `/pengelola` | Modul Pengelola |
| `/admin` | Modul Administrator |
| `/kades` | Modul Kepala Desa |

Dengan parameter:
```
/nasabah/edit/1    -> NasabahController->edit(1)
/pengelola/verifikasi -> PengelolaController->verifikasi()
```

## Struktur Project

```
bank-sampah-desa/
├── public/                 # Entry point (document root)
│   ├── index.php          # Router utama
│   └── assets/            # Static assets
│       ├── css/           # Stylesheet
│       └── js/            # JavaScript
├── app/
│   ├── controllers/       # Controller (logika bisnis)
│   ├── models/            # Model (interaksi database)
│   └── views/             # View (tampilan)
│       ├── layouts/       # Template utama
│       ├── partials/       # Komponen reusable
│       ├── nasabah/       # View role Nasabah
│       ├── pengelola/     # View role Pengelola
│       ├── admin/         # View role Admin
│       └── kades/         # View role Kepala Desa
├── config/
│   ├── app.php           # Konfigurasi aplikasi
│   └── database.php      # Konfigurasi database
├── .env.example          # Template environment
├── .gitignore           # File yang diabaikan git
└── README.md            # Dokumentasi
```

## Role Pengguna

| Role | Keterangan | Akses |
|------|------------|-------|
| Nasabah | Pelaku sampah | Setor sampah, tarik saldo, lihat riwayat |
| Pengelola | Petugas bank sampah | Verifikasi setoran, kelola data sampah |
| Admin | Administrator sistem | Kelola pengguna, pengaturan sistem |
| Kades | Kepala Desa | Monitoring, laporan keuangan |

## Database Schema (Coming Soon)

Detail schema database akan ditambahkan pada fase development selanjutnya.

## Troubleshooting

### Error: "Connection refused" atau "Unknown database"
- Pastikan MySQL service sudah running
- Cek konfigurasi DB di file `.env`
- Pastikan nama database sudah dibuat

### Error: "Permission denied"
- Pastikan folder storage/cache writable (Linux/Mac: `chmod 755`)

### Blank page
- Set `APP_DEBUG=true` di `.env` untuk melihat error
- Cek PHP error log

## Lisensi

MIT License - Bebas digunakan untuk keperluan apapun.

## Kontribusi

Silakan buat issue atau pull request untuk pengembangan sistem ini.
