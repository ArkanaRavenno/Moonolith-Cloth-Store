# 🌙 Moonolith Cloth Store

> **Modern E-Commerce Fashion & Apparel Web Platform**  
> Solusi platform e-commerce busana modern berbasis full-stack **Laravel 12 (REST API)** dan **Vue 3 (Vite + Tailwind CSS)**, dilengkapi integrasi Payment Gateway **Midtrans**, Live Chat pelanggan, Flash Sale interaktif, dan Dashboard Manajemen Admin komprehensif.

---

## 📌 Tentang Project (Project Overview)

**Moonolith Cloth Store** adalah platform toko online pakaian dan busana kontemporer yang dirancang untuk memberikan pengalaman berbelanja yang intuitif, cepat, dan aman bagi pelanggan, serta kontrol operasional penuh bagi pemilik bisnis melalui dashboard analitik real-time.

Aplikasi ini mengadopsi arsitektur terpisah (*decoupled architecture*):
- **Backend**: API RESTful berbasis Laravel 12 yang aman, modular, dan teroptimasi.
- **Frontend**: Single Page Application (SPA) berbasis Vue 3 dengan Vite dan Tailwind CSS untuk performa rendering tinggi dan antarmuka responsif modern.

---

## ✨ Fitur Utama (Key Features)

### 🛍️ Sisi Pelanggan (Customer / User)
- **Katalog & Navigasi Produk:**
  - Filter kategori fleksibel (*Men, Women, Kids, Most Buy*).
  - Pencarian produk dan penelusuran katalog dinamis.
- **Detail Produk & Varian Multi-Dimensi:**
  - Pemilihan varian ukuran (*size*), warna (*color*), dan ketersediaan stok real-time.
- **Event Flash Sale:**
  - Banner dan item Flash Sale dengan harga khusus dan penanda waktu diskon.
- **Keranjang Belanja (*Cart*) & Wishlist (*Favorites*):**
  - Manajemen keranjang belanja dengan validasi stok otomatis sebelum checkout.
  - Fitur simpan produk favorit (Wishlist).
- **Checkout & Integrasi Pembayaran:**
  - Kalkulasi ongkos kirim (*Shipping Rates*).
  - Integrasi **Midtrans Snap Payment Gateway** (QRIS, GoPay, Virtual Account Bank, Kartu Kredit).
  - Notifikasi otomatis status transaksi via Webhook.
- **Riwayat Pesanan & Invoice PDF:**
  - Pelacakan status pemesanan (*Pending, Processing, Shipped, Delivered, Canceled*).
  - Unduh invoice resmi format PDF (**Barryvdh DomPDF**).
- **Ulasan & Rating Produk:**
  - Pelanggan dapat memberikan bintang rating dan ulasan setelah transaksi selesai.
- **Live Chat Pelanggan:**
  - Fitur ruang obrolan langsung (*Chat Room*) dengan admin toko untuk bantuan dan konsultasi produk.
- **Manajemen Profil:**
  - Pembaruan informasi akun, alamat, dan nomor kontak.

---

### 📊 Sisi Manajemen & Toko (Admin / Super Admin)
- **Dashboard Analitik & Metrik Bisnis:**
  - Ringkasan total pendapatan, jumlah pesanan, total produk, dan grafik transaksi.
- **Manajemen Produk & Varian (Inventory Management):**
  - Tambah, edit, hapus produk dan kategori.
  - Manajemen stok dan *restock* berbasis varian ukuran dan warna.
  - Multi-upload gambar produk.
- **Pengaturan Flash Sale:**
  - Penjadwalan kampanye flash sale, alokasi stok promo, dan batas potongan harga.
- **Manajemen Pesanan (*Order Processing*):**
  - Pemantauan pesanan masuk, verifikasi status, dan pembaruan resi pengiriman.
- **Pusat Chat Admin (*Chat Center*):**
  - Antarmuka inbox terpadu untuk merespons chat dari seluruh pelanggan.
- **Laporan Keuangan & Arus Kas (*Cashflow*):**
  - Analisis *cashflow* dan ekspor laporan transaksi/penjualan ke format PDF.
- **Manajemen Pengguna & Hak Akses (RBAC):**
  - Pengelolaan akun administrator dengan otorisasi berbasis peran (*Super Admin* & *Admin*).

---

## 🛠️ Tech Stack & Teknologi

### Backend
- **Framework:** Laravel 12.x (PHP 8.2+)
- **Authentication:** Laravel Sanctum (Token-based API Authentication)
- **Payment Gateway:** Midtrans PHP SDK (`midtrans/midtrans-php`)
- **PDF Generator:** Barryvdh Laravel DomPDF
- **Database:** MySQL / SQLite
- **Architecture:** Controller, Form Request, Service Layer, Resource API

### Frontend
- **Framework:** Vue.js 3 (Composition API)
- **Build Tool:** Vite 7.x
- **Styling:** Tailwind CSS 4.x
- **State & Routing:** Vue Router 4.x
- **HTTP Client:** Axios (dengan token interceptor)
- **Icons:** Lucide Vue Next
- **Charts & UI Feedback:** Chart.js, SweetAlert2

---

## 📁 Struktur Direktori (Repository Structure)

```text
Moonolith-Cloth-Store/
├── backend/                  # REST API Server (Laravel 12)
│   ├── app/
│   │   ├── Http/Controllers/Api/
│   │   │   ├── Admin/        # Controller operasional admin
│   │   │   ├── Auth/         # Registrasi, login, autentikasi
│   │   │   ├── Public/       # Endpoint publik (katalog, produk)
│   │   │   └── User/         # Endpoint pelanggan (cart, checkout, order, chat)
│   │   └── Models/           # Eloquent Models & relasi
│   ├── database/
│   │   ├── migrations/       # Skema database
│   │   └── seeders/          # Dummy data & initial data
│   ├── routes/
│   │   └── api.php           # Deklarasi seluruh route REST API
│   └── composer.json
│
├── frontend/                 # Client SPA (Vue 3 + Vite + Tailwind CSS)
│   ├── src/
│   │   ├── assets/           # Gambar, styling global
│   │   ├── components/       # Komponen UI reusable (Navbar, Footer, Card, Modal)
│   │   ├── router/           # Routing halaman & Navigation Guards
│   │   ├── views/
│   │   │   ├── admin/        # Tampilan dashboard & manajemen admin
│   │   │   └── user/         # Tampilan toko & portal belanja user
│   │   ├── App.vue
│   │   └── main.js
│   ├── package.json
│   └── vite.config.js
│
└── README.md                 # Dokumentasi utama proyek
```

---

## 🚀 Panduan Instalasi & Menjalankan Lokal

### Prasyarat Sistem
- **PHP** >= 8.2
- **Composer** >= 2.x
- **Node.js** >= 20.x & **npm**
- **Web Server / Database:** Laragon / XAMPP / MySQL

---

### 1. Setup Backend (Laravel)

```bash
# Masuk ke direktori backend
cd backend

# Install dependensi PHP
composer install

# Salin file konfigurasi environment
cp .env.example .env

# Generate Application Key
php artisan key:generate

# Konfigurasikan database dan Midtrans pada file .env:
# DB_DATABASE=moonolith_db
# DB_USERNAME=root
# DB_PASSWORD=
# MIDTRANS_SERVER_KEY=your_server_key
# MIDTRANS_CLIENT_KEY=your_client_key
# MIDTRANS_IS_PRODUCTION=false

# Jalankan migrasi dan seeder
php artisan migrate --seed

# Hubungkan symbolic link untuk penyimpanan berkas gambar
php artisan storage:link

# Jalankan server development Laravel
php artisan serve
```
> Server backend default berjalan di: `http://127.0.0.1:8000`

---

### 2. Setup Frontend (Vue 3 + Vite)

```bash
# Buka terminal baru dan masuk ke direktori frontend
cd frontend

# Install dependensi Node.js
npm install

# Jalankan server development Vite
npm run dev
```
> Aplikasi frontend default berjalan di: `http://localhost:5173`

---

## 🔗 Integrasi Midtrans Webhook

Untuk pengujian webhook notifikasi transaksi lokal:
1. Gunakan alat tunnelling seperti **Ngrok** untuk meneruskan port backend (`8000`).
2. Masukkan URL webhook ke Dashboard Midtrans Sandbox:
   ```text
   https://<your-ngrok-subdomain>.ngrok-free.app/api/midtrans/webhook
   ```

---

## 👤 Lisensi & Pengembang

Proyek ini dikembangkan oleh **Arkana Ravenno** sebagai bagian dari tugas akhir implementasi platform e-commerce fashion modern berdaya saing tinggi.

- **GitHub:** [@ArkanaRavenno](https://github.com/ArkanaRavenno)
- **Email:** `ramandaivan12@gmail.com`
