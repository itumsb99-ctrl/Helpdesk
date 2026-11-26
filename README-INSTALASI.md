# IT Helpdesk Rumah Sakit - Laravel 12

Paket ini adalah source code aplikasi (overlay) untuk proyek Laravel 12 baru.
Target: PHP 8.2+, MySQL 8+, Bootstrap 5 via CDN.

## 1. Buat proyek Laravel baru
```bash
cd C:\xampp\htdocs
composer create-project laravel/laravel helpdesk-rs "^12.0"
cd helpdesk-rs
```

## 2. Salin isi folder `source` dari ZIP ini
Salin seluruh isi folder `source` ke root proyek Laravel `helpdesk-rs`, izinkan overwrite file jika diminta.
Paket ini tidak menyertakan folder `vendor` Laravel.

## 3. Buat database
Buat database MySQL bernama `helpdesk_rs`, lalu sesuaikan `.env`:
```env
APP_NAME="IT Helpdesk RS"
APP_URL=http://localhost
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=helpdesk_rs
DB_USERNAME=root
DB_PASSWORD=
```

## 4. Jalankan instalasi
```bash
php artisan key:generate
php artisan migrate --seed
php artisan serve
```
Buka http://127.0.0.1:8000

## Akun demo
- Admin: admin@helpdesk.local / password
- Teknisi: teknisi@helpdesk.local / password
- Pegawai: pegawai@helpdesk.local / password

Segera ganti password demo setelah login.

## 5. SLA otomatis
Untuk development, jalankan scheduler setiap menit:
```bash
php artisan schedule:work
```
Pada server Windows produksi, buat Task Scheduler yang menjalankan:
`php artisan schedule:run` setiap menit, dari direktori proyek.

## Catatan
- Ini adalah fondasi aplikasi Helpdesk yang dapat dijalankan dan dikembangkan.
- Pengaturan jam SLA memakai menit kalender; hari libur/jam kerja belum dikecualikan.
- Notifikasi aplikasi tersimpan di database. Email/WhatsApp belum dikonfigurasi.
- Untuk akses LAN, set `APP_URL=http://IP-SERVER`, jalankan server web Apache dengan document root ke folder `public`, lalu buka port firewall yang diperlukan.
