# 🧺 LaundryKu - Sistem Manajemen Laundry

<div align="center">

![LaundryKu Banner](https://img.shields.io/badge/LaundryKu-Laundry%20Management%20System-1e3a5f?style=for-the-badge&logo=data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIyNCIgaGVpZ2h0PSIyNCIgdmlld0JveD0iMCAwIDI0IDI0IiBmaWxsPSJub25lIiBzdHJva2U9IndoaXRlIiBzdHJva2Utd2lkdGg9IjIiIHN0cm9rZS1saW5lY2FwPSJyb3VuZCIgc3Ryb2tlLWxpbmVqb2luPSJyb3VuZCI+PHBhdGggZD0iTTMgM2gxOHYxOEgzeiIvPjwvc3ZnPg==)

[![Laravel](https://img.shields.io/badge/Laravel-11-FF2D20?style=flat-square&logo=laravel&logoColor=white)](https://laravel.com)
[![Next.js](https://img.shields.io/badge/Next.js-14-000000?style=flat-square&logo=next.js&logoColor=white)](https://nextjs.org)
[![PostgreSQL](https://img.shields.io/badge/PostgreSQL-Supabase-4169E1?style=flat-square&logo=postgresql&logoColor=white)](https://supabase.com)
[![License](https://img.shields.io/badge/License-MIT-green?style=flat-square)](LICENSE)

**Sistem Point of Sale (POS) dan Manajemen Laundry Modern dengan Fitur Lengkap**

[Demo](#demo) • [Fitur](#-fitur) • [Instalasi](#-instalasi) • [Dokumentasi](#-dokumentasi-api) • [Kontribusi](#-kontribusi)

</div>

---

## 📖 Tentang Proyek

**LaundryKu** adalah sistem manajemen laundry berbasis web yang dirancang untuk memudahkan pengelolaan operasional bisnis laundry. Dengan antarmuka yang modern dan intuitif, LaundryKu membantu pemilik usaha dan kasir dalam mengelola order, pelanggan, layanan, dan laporan keuangan.

### 🎯 Target Pengguna
- **Owner/Pemilik**: Mengelola layanan, pelanggan, pengguna, melihat laporan, dan pengaturan toko
- **Kasir**: Membuat order baru, mengelola status order, dan menerima pembayaran

---

## ✨ Fitur

### 👤 Autentikasi & Otorisasi
- Login dengan role-based access (Owner/Kasir)
- Session management dengan Laravel Sanctum
- Protected routes berdasarkan role

### 📋 Manajemen Order
- Buat order baru dengan pencarian pelanggan
- Pilih layanan (kiloan/satuan) dengan kalkulasi otomatis
- Update status order (Diterima → Dicuci → Dikeringkan → Disetrika → Selesai → Diambil)
- Filter dan pencarian order
- Cetak struk/nota

### 👥 Manajemen Pelanggan
- Tambah, edit, dan hapus pelanggan
- Pencarian pelanggan
- Riwayat transaksi pelanggan

### 🧺 Manajemen Layanan
- CRUD layanan laundry
- Jenis layanan: Kiloan & Satuan
- Estimasi waktu pengerjaan
- Aktif/Nonaktif layanan

### 📊 Dashboard & Laporan
- **Dashboard Owner**: Pendapatan harian/bulanan/tahunan, grafik, top layanan, top pelanggan
- **Dashboard Kasir**: Order hari ini, order pending, order belum bayar/diambil
- **Laporan Keuangan**: Harian, bulanan, tahunan dengan export

### ⚙️ Pengaturan
- Informasi toko (nama, alamat, telepon, email)
- Template struk
- Manajemen pengguna

---

## 🛠️ Tech Stack

### Backend
| Teknologi | Versi | Deskripsi |
|-----------|-------|-----------|
| PHP | 8.1+ | Bahasa pemrograman |
| Laravel | 11.x | Framework PHP |
| Laravel Sanctum | - | API Authentication |
| PostgreSQL | 15+ | Database (Supabase) |

### Frontend
| Teknologi | Versi | Deskripsi |
|-----------|-------|-----------|
| Next.js | 14.x | React Framework |
| React | 18.x | UI Library |
| Recharts | - | Charts & Graphs |
| React Hot Toast | - | Notifications |

---

## 📦 Instalasi

### Prasyarat
- PHP >= 8.1
- Composer
- Node.js >= 18
- npm atau yarn
- PostgreSQL (atau akun Supabase)

### 1️⃣ Clone Repository
```bash
git clone https://github.com/username/laundryku.git
cd laundryku
```

### 2️⃣ Setup Backend (Laravel)
```bash
cd backend-laravel

# Install dependencies
composer install

# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Configure database di .env
# DB_CONNECTION=pgsql
# DB_HOST=your-supabase-host
# DB_PORT=5432
# DB_DATABASE=postgres
# DB_USERNAME=postgres
# DB_PASSWORD=your-password

# Jalankan migration
php artisan migrate

# Jalankan seeder (opsional, untuk data awal)
php artisan db:seed

# Jalankan server
php artisan serve --port=8000
```

### 3️⃣ Setup Frontend (Next.js)
```bash
cd frontend

# Install dependencies
npm install

# Copy environment file
cp .env.example .env.local

# Configure API URL di .env.local
# NEXT_PUBLIC_API_URL=http://localhost:8000

# Jalankan development server
npm run dev
```

### 4️⃣ Akses Aplikasi
- **Frontend**: http://localhost:3000
- **Backend API**: http://localhost:8000

### 👤 Default Users
| Role | Username | Password |
|------|----------|----------|
| Owner | owner | password123 |
| Kasir | kasir | password123 |

---

## 📁 Struktur Proyek

```
laundryku/
├── backend-laravel/          # Laravel Backend
│   ├── app/
│   │   ├── Http/Controllers/ # API Controllers
│   │   └── Models/           # Eloquent Models
│   ├── database/
│   │   └── migrations/       # Database Migrations
│   ├── routes/
│   │   └── api.php           # API Routes
│   └── .env.example
│
├── frontend/                  # Next.js Frontend
│   ├── src/
│   │   ├── app/              # App Router Pages
│   │   │   ├── kasir/        # Kasir Pages
│   │   │   ├── owner/        # Owner Pages
│   │   │   └── login/        # Auth Pages
│   │   ├── components/       # Reusable Components
│   │   ├── hooks/            # Custom Hooks
│   │   └── lib/              # Utilities & API Client
│   └── .env.example
│
└── README.md
```

---

## 📚 Dokumentasi API

### Authentication
| Method | Endpoint | Deskripsi |
|--------|----------|-----------|
| POST | `/api/auth/login` | Login user |
| POST | `/api/auth/logout` | Logout user |
| GET | `/api/auth/me` | Get current user |

### Orders
| Method | Endpoint | Deskripsi |
|--------|----------|-----------|
| GET | `/api/orders` | List semua order |
| POST | `/api/orders` | Buat order baru |
| GET | `/api/orders/{id}` | Detail order |
| PUT | `/api/orders/{id}/status` | Update status |
| PUT | `/api/orders/{id}/payment` | Update pembayaran |

### Customers
| Method | Endpoint | Deskripsi |
|--------|----------|-----------|
| GET | `/api/customers` | List pelanggan |
| POST | `/api/customers` | Tambah pelanggan |
| PUT | `/api/customers/{id}` | Update pelanggan |
| DELETE | `/api/customers/{id}` | Hapus pelanggan |

### Services
| Method | Endpoint | Deskripsi |
|--------|----------|-----------|
| GET | `/api/services` | List layanan |
| POST | `/api/services` | Tambah layanan |
| PUT | `/api/services/{id}` | Update layanan |
| DELETE | `/api/services/{id}` | Hapus layanan |

### Dashboard & Reports
| Method | Endpoint | Deskripsi |
|--------|----------|-----------|
| GET | `/api/dashboard/owner` | Dashboard owner |
| GET | `/api/dashboard/kasir` | Dashboard kasir |
| GET | `/api/reports/daily` | Laporan harian |
| GET | `/api/reports/monthly` | Laporan bulanan |
| GET | `/api/reports/yearly` | Laporan tahunan |

---

## 🖼️ Screenshots

<details>
<summary>📊 Dashboard Owner</summary>

Dashboard dengan statistik pendapatan, grafik, dan top data.
</details>

<details>
<summary>📋 Daftar Order</summary>

Tabel order dengan filter status dan pencarian.
</details>

<details>
<summary>➕ Buat Order Baru</summary>

Form pembuatan order dengan pencarian pelanggan dan pilihan layanan.
</details>

---

## 🔧 Environment Variables

### Backend (.env)
```env
APP_NAME=LaundryKu
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=pgsql
DB_HOST=your-supabase-host
DB_PORT=5432
DB_DATABASE=postgres
DB_USERNAME=postgres
DB_PASSWORD=your-password

SANCTUM_STATEFUL_DOMAINS=localhost:3000
SESSION_DOMAIN=localhost
```

### Frontend (.env.local)
```env
NEXT_PUBLIC_API_URL=http://localhost:8000
```

---

## 🤝 Kontribusi

Kontribusi sangat diterima! Silakan ikuti langkah berikut:

1. Fork repository ini
2. Buat branch fitur (`git checkout -b feature/AmazingFeature`)
3. Commit perubahan (`git commit -m 'Add some AmazingFeature'`)
4. Push ke branch (`git push origin feature/AmazingFeature`)
5. Buka Pull Request

---

## 📄 Lisensi

Didistribusikan di bawah Lisensi MIT. Lihat `LICENSE` untuk informasi lebih lanjut.

---

## 📞 Kontak

**Developer**: Your Name  
**Email**: your.email@example.com  
**Project Link**: [https://github.com/username/laundryku](https://github.com/username/laundryku)

---

<div align="center">

**⭐ Jangan lupa beri bintang jika proyek ini membantu! ⭐**

Made with ❤️ in Indonesia

</div>
