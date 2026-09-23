# Placeholder - Views per Role

Folder ini digunakan untuk menyimpan view/template berdasarkan role pengguna:

- `nasabah/`   - View untuk role Nasabah (pelaku sampah)
- `pengelola/` - View untuk role Pengelola (petugas bank sampah)
- `admin/`     - View untuk role Administrator (manajemen sistem)
- `kades/`     - View untuk role Kepala Desa (pelaporan)

Struktur contoh:
```
views/
├── layouts/
│   └── main.php
├── partials/
│   └── header.php
│   └── footer.php
├── nasabah/
│   ├── dashboard.php
│   ├── setor.php
│   ├── tarik.php
│   └── profil.php
├── pengelola/
│   ├── dashboard.php
│   ├── verifikasi.php
│   ├── laporan.php
│   └──...
├── admin/
│   ├── dashboard.php
│   ├── pengguna.php
│   ├── master_data.php
│   └──...
└── kades/
    ├── dashboard.php
    └── laporan.php
```
