# Sistem Gudang Bahan Baku

Aplikasi manajemen gudang bahan baku untuk konveksi — multi-gudang, moving average cost, stock opname, notifikasi stok menipis.

**Stack:** Laravel 13, Filament v3, MySQL/SQLite, PHP 8.3, TailwindCSS 4

---

## Persyaratan Sistem

- PHP ^8.3
- Composer
- Node.js & npm
- MySQL (atau SQLite untuk development)
- Ekstensi PHP: `BCMath`, `Ctype`, `JSON`, `Mbstring`, `OpenSSL`, `PDO`, `Tokenizer`, `XML`, `pdo_mysql` (jika pakai MySQL)

---

## Cara Clone & Setup

### 1. Clone repositori

```bash
git clone https://github.com/<username>/<repo>.git
cd <repo>
```

### 2. Setup otomatis (recommended)

```bash
composer setup
```

Perintah ini menjalankan:
1. `composer install` — install dependency PHP
2. Copy `.env.example` ke `.env`
3. `php artisan key:generate` — generate APP_KEY
4. `php artisan migrate --force` — jalankan migrasi database
5. `npm install` — install dependency frontend
6. `npm run build` — build asset Vite

### 3. Setup manual (alternatif)

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
npm install
npm run build
```

### 4. Konfigurasi Database (jika pakai MySQL)

Edit `.env` ubah koneksi database:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=gudang
DB_USERNAME=root
DB_PASSWORD=
```

Buat database `gudang` terlebih dahulu, lalu jalankan migrasi:

```bash
php artisan migrate
```

> **Default:** `.env.example` memakai SQLite — langsung jalan tanpa setup database.

### 5. Seed data master (kategori, satuan, gudang)

```bash
php artisan db:seed
```

### 6. Buat user admin

```bash
php artisan shield:super-admin
```

Ikuti prompt untuk membuat user Super Admin.

---

## Menjalankan Aplikasi (Development)

### Cara termudah (semua service jalan paralel):

```bash
composer dev
```

Ini menjalankan 4 service sekaligus:
- `php artisan serve` — server web di `http://localhost:8000`
- `php artisan queue:listen` — queue worker
- `php artisan pail` — log viewer
- `npm run dev` — Vite dev server (HMR)

### Akses aplikasi:

| URL | Keterangan |
|-----|------------|
| `http://localhost:8000/admin` | Panel admin Filament |
| `http://localhost:8000` | Redirect ke /admin |

---

## Testing

```bash
composer test
```

Atau langsung:

```bash
php artisan test
```

---

## Struktur Proyek

| Path | Keterangan |
|------|------------|
| `app/Filament/` | Resource & widget Filament |
| `app/Models/` | Eloquent models (19 model) |
| `app/Services/` | Logic bisnis (StockLedgerService, DocumentNumberGenerator) |
| `config/warehouse.php` | Konfigurasi khusus gudang |
| `database/migrations/` | 24 file migrasi |
| `database/seeders/` | Seeder data master |
| `routes/web.php` | Route Filament (otomatis) |

---

## Script yang Tersedia

| Perintah | Fungsi |
|----------|--------|
| `composer setup` | Setup awal proyek (1x jalan) |
| `composer dev` | Jalankan dev server + queue + Vite |
| `composer test` | Jalankan test suite |
| `npm run build` | Build asset frontend |
| `php artisan shield:super-admin` | Buat user Super Admin |

---

## Deployment

Lihat [DEPLOYMENT.md](./DEPLOYMENT.md) untuk panduan lengkap deployment ke production.
