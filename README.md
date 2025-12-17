### ELGROW---Habit-Tracker

### Deskripsi Website

ELGROW adalah aplikasi web yang dirancang untuk membantu pengguna membangun kebiasaan positif dan mencapai tujuan secara terstruktur. Melalui fitur registrasi dan login, pengguna dapat mengakses dashboard utama untuk mengelola habit, menetapkan goal, melihat analisis perkembangan, serta meninjau riwayat aktivitas. ELGROW menyediakan tampilan yang sederhana dan interaktif untuk memantau progres secara konsisten, sehingga pengguna dapat meningkatkan produktivitas dan kedisiplinan dalam kehidupan sehari-hari.

---

## Tech Stack yang Digunakan

- **Frontend** : HTML, CSS, Tailwind CSS, JavaScript
- **Backend** : PHP
- **Database** : MySQL
- **Version Control** : Git & GitHub

---

## Cara Menjalankan Website

### Masukan ke dalam file di xammp(htdocs) /laragon(www)
### Setelah masukan filenya aktifkan apache & MYSQL
### setelah itu buka
```bash
composer install
npm install
cp .env.example .env
```
### lalu sesuaikan pada .env
```bash
DB_DATABASE=goutside
DB_USERNAME=root
DB_PASSWORD=
```
### generate KEY
```bash
php artisan key:generate
```
### migrasi database
```bash
php artisan migrate
```
### jalankan server
```bash
php artisan serve
npm run dev
###jalankan di http://127.0.0.1:8000
```

## Kontributor
Fandy Fernanda Yapari NIM 227
Miuvanida Klandistin NIM 208
Zikry Azizy Aljava NIM 228
Zhidan Frizky Pradana NIM 218

### WEB MASIH AKAN DIKEMBANGKAN LEBIH LANJUT
