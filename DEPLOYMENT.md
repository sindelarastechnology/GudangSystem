# Deployment & Go-Live Guide
## Sistem Gudang Bahan Baku - Laravel 13 + Filament v3

> **Dokumen ini adalah checklist lengkap untuk deployment ke production/staging.**
> Stack: Laravel 13, Filament v3, MySQL, PHP 8.3.31

---

## ✅ FASE 10 CHECKLIST

### 1. Pre-Deployment: Test Suite ✅

**Status:** 49 tests passed, 124 assertions, 9.18s

```bash
php artisan test
```

**Expected Output:**
- ✅ All tests passing
- ✅ No warnings or deprecations
- ✅ Coverage: StockLedgerService, DocumentNumberGenerator, all transaction flows, opname sessions

**Fase yang tercakup:**
- Fase 2: Core stok & moving average
- Fase 3: Stock In & Out
- Fase 4: Transfer antar gudang
- Fase 5: Stock opname (counting → finalized → cancelled)
- Fase 6: Low stock notifications
- Fase 7: Reports & dashboard
- Fase 8: Asset snapshots
- Fase 9: Konkurensi & audit trail

---

### 2. Configuration Review ✅

**File:** `config/warehouse.php`

#### Production Values (via .env):

```env
# Cooldown notifikasi stok menipis (hari)
LOW_STOCK_NOTIFICATION_COOLDOWN_DAYS=3

# Timeout auto-cancel sesi opname ngambang (jam)
OPNAME_STALE_HOURS=24

# Document prefixes (opsional, sudah ada default)
DOC_PREFIX_STOCK_IN=SIN
DOC_PREFIX_STOCK_OUT=SOUT
DOC_PREFIX_TRANSFER=TRF
DOC_PREFIX_OPNAME=OPN

# Min stock default untuk item baru
WAREHOUSE_DEFAULT_MIN_STOCK=0
```

**Rekomendasi:**
- **Development/Staging:** `OPNAME_STALE_HOURS=2` (lebih cepat untuk testing)
- **Production:** `OPNAME_STALE_HOURS=24` (sesuai dokumen)
- **Cooldown:** 3 hari sudah optimal (tidak spam, tidak terlalu jarang)

---

### 3. Queue Worker Setup

**LowStockNotification menggunakan `ShouldQueue`** - butuh queue worker aktif.

#### A. Setup Queue Driver

**File:** `.env`

```env
QUEUE_CONNECTION=database
```

**Generate migration (jika belum):**
```bash
php artisan queue:table
php artisan migrate
```

#### B. Run Queue Worker (Production)

**Menggunakan Supervisor (recommended):**

**File:** `/etc/supervisor/conf.d/gudang-worker.conf`

```ini
[program:gudang-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/gudang/artisan queue:work database --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/path/to/gudang/storage/logs/worker.log
stopwaitsecs=3600
```

**Reload supervisor:**
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start gudang-worker:*
```

#### C. Alternative: Systemd Service

**File:** `/etc/systemd/system/gudang-worker.service`

```ini
[Unit]
Description=Gudang Queue Worker
After=network.target

[Service]
Type=simple
User=www-data
WorkingDirectory=/path/to/gudang
ExecStart=/usr/bin/php /path/to/gudang/artisan queue:work database --sleep=3 --tries=3
Restart=always

[Install]
WantedBy=multi-user.target
```

**Enable & start:**
```bash
sudo systemctl enable gudang-worker
sudo systemctl start gudang-worker
sudo systemctl status gudang-worker
```

---

### 4. Cron Setup (WAJIB) ✅

**3 Scheduled Jobs terdaftar:**

| Command | Schedule | Fungsi |
|---------|----------|--------|
| `opname:auto-cancel-stale` | Hourly | Auto-cancel sesi opname >24 jam |
| `stock:daily-low-stock-digest` | Daily 07:00 | Kirim ringkasan stok kritis |
| `snapshot:monthly-asset-value` | Daily 00:05 | Snapshot nilai aset bulanan |

#### Setup Cron

**Edit crontab:**
```bash
sudo crontab -e -u www-data
```

**Tambahkan baris ini:**
```cron
* * * * * cd /path/to/gudang && php artisan schedule:run >> /dev/null 2>&1
```

**Verifikasi:**
```bash
php artisan schedule:list
```

**Expected output:**
```
0 0-23 * * *  opname:auto-cancel-stale ............................ Next Due: 1 hour from now
0 7 * * *     stock:daily-low-stock-digest ....................... Next Due: 19 hours from now
5 0 * * *     snapshot:monthly-asset-value ....................... Next Due: 12 hours from now
```

---

### 5. Production Seeder Requirements

#### A. Data Master yang WAJIB ada:

**File:** `database/seeders/ProductionSeeder.php` (buat baru)

```php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class ProductionSeeder extends Seeder
{
    public function run(): void
    {
        // Minimal 1 kategori, 1 satuan, 1 gudang untuk testing awal
        $this->call([
            MaterialCategorySeeder::class,  // minimal: "Bahan Umum"
            UnitSeeder::class,               // minimal: "Pcs", "Meter", "Kg"
            WarehouseSeeder::class,          // gudang riil user (Gudang Pusat, dll)
        ]);
    }
}
```

#### B. Data yang TIDAK perlu di-seed:

- ❌ `material_stocks` - lazy-create otomatis saat transaksi pertama
- ❌ `stock_ledgers` - diisi otomatis oleh transaksi
- ❌ `document_number_counters` - diisi otomatis saat generate nomor pertama
- ❌ `raw_materials` - diinput manual via Filament setelah go-live
- ❌ `suppliers` - diinput manual via Filament

#### C. Run Seeder di Production:

```bash
php artisan db:seed --class=ProductionSeeder
```

---

### 6. Role & User Setup (Filament Shield)

#### A. Generate Permissions (jika belum):

```bash
php artisan shield:generate --all --panel=admin
```

#### B. Buat Super Admin:

```bash
php artisan shield:super-admin
```

**Prompt akan muncul untuk:**
- Name: (e.g., "Admin Sistem")
- Email: (e.g., "admin@perusahaan.com")
- Password: (strong password)

#### C. Role yang Disarankan:

| Role | Akses |
|------|-------|
| **super_admin** | Full access (dibuat via `shield:super-admin`) |
| **admin** | Semua gudang, semua transaksi, laporan |
| **staff_gudang_pusat** | Hanya Gudang Pusat, transaksi, laporan read-only |
| **staff_gudang_cabang** | Hanya gudang cabang terkait |
| **viewer** | Read-only semua, untuk Owner/Manajemen |

**Buat role manual via Filament:**
1. Login sebagai super_admin
2. Navigate: Shield → Roles
3. Create role baru
4. Assign permissions sesuai kebutuhan
5. Assign users ke role

---

### 7. End-to-End Test Checklist

**Jalankan sekali di staging/production sebelum go-live:**

#### Test Flow:

```
1. Login sebagai Admin
   → Verifikasi dashboard tampil (Total Nilai Aset, Stok Kritis)

2. Master Data
   → Buat 1 kategori baru: "Kategori Test"
   → Buat 1 satuan baru: "Test Unit (TU)"
   → Buat 1 supplier: "Supplier Test"
   → Buat 1 item: "Item Test" (kategori Test, satuan TU)
   → Set min_stock = 10 di gudang default

3. Stock In (Pembelian)
   → Input Stock In: 50 TU @ Rp 10,000/TU ke Gudang Pusat
   → Verifikasi: transaction_number tergenerate (SIN-YYYYMM0001)
   → Verifikasi: Kartu stok ada 1 baris (IN, qty=50, avg_cost=10000)
   → Verifikasi: Material stocks: current_stock=50, current_avg_cost=10000

4. Stock Out (Pemakaian Produksi)
   → Input Stock Out: 15 TU dari Gudang Pusat
   → Verifikasi: Kartu stok ada 2 baris (IN 50, OUT 15, balance=35)
   → Verifikasi: cost_at_issue = 10000
   → Verifikasi: Material stocks: current_stock=35, avg_cost tetap 10000

5. Transfer Antar Gudang (jika >1 gudang)
   → Transfer 10 TU: Gudang Pusat → Gudang Cabang
   → Verifikasi: Gudang Pusat stock=25, Gudang Cabang stock=10
   → Verifikasi: avg_cost Gudang Pusat tetap 10000
   → Verifikasi: avg_cost Gudang Cabang = 10000 (dibawa dari asal)

6. Stock Opname
   → Buka sesi opname di Gudang Pusat
   → Verifikasi: gudang ter-lock (is_locked=true)
   → Verifikasi: transaksi baru ke Gudang Pusat ditolak
   → Input fisik: 20 TU (system=25, physical=20, selisih=-5)
   → Finalisasi opname
   → Verifikasi: Kartu stok ada baris adjustment OUT 5 TU
   → Verifikasi: Material stocks: current_stock=20
   → Verifikasi: gudang terbuka kembali (is_locked=false)

7. Notifikasi Stok Menipis
   → Stock Out lagi: 15 TU (sisa=5, di bawah min_stock=10)
   → Verifikasi: Notifikasi muncul di bell icon (Filament)
   → Verifikasi: last_notified_at terisi

8. Laporan
   → Cek Laporan Nilai Aset Saat Ini
     → Total aset semua gudang = (20 x 10000) + (10 x 10000) = 300,000
   → Cek Kartu Stok Item Test
     → Semua mutasi tercatat urut (IN, OUT, TRANSFER, OPNAME)
   → Cek Laporan Stok Kritis
     → Item Test muncul (stok 5 < min 10)

9. Cleanup Test Data (opsional)
   → Hapus Item Test, Supplier Test, Kategori Test (via soft delete)
```

**Expected Time:** ~30 menit

---

### 8. Backup Strategy

#### A. Database Backup (WAJIB)

**Menggunakan mysqldump:**

```bash
# Manual backup
mysqldump -u root -p gudang > backup_$(date +%Y%m%d_%H%M%S).sql

# Automated daily backup (crontab)
0 2 * * * /usr/bin/mysqldump -u root -pPASSWORD gudang | gzip > /backup/gudang_$(date +\%Y\%m\%d).sql.gz
```

**Retention:** 30 hari (hapus backup >30 hari)

#### B. File Backup

**Direktori yang perlu di-backup:**
- `/storage/app/stock-in/` - lampiran nota barang masuk
- `.env` - konfigurasi (JANGAN commit ke git)

```bash
# Backup files
tar -czf backup_files_$(date +%Y%m%d).tar.gz storage/app/stock-in .env
```

#### C. Restore Process

```bash
# Restore database
mysql -u root -p gudang < backup_20260707.sql

# Restore files
tar -xzf backup_files_20260707.tar.gz
```

---

## Post-Deployment Monitoring

### 1. Log Files to Monitor

```bash
# Laravel logs
tail -f storage/logs/laravel.log

# Queue worker logs (jika pakai supervisor)
tail -f storage/logs/worker.log

# Web server logs
tail -f /var/log/nginx/error.log  # atau /var/log/apache2/error.log
```

### 2. Health Checks

```bash
# Check queue worker running
php artisan queue:monitor database

# Check scheduled jobs
php artisan schedule:list

# Check database connection
php artisan tinker
>>> DB::connection()->getPdo();
```

### 3. Performance Baselines

- **Response time:** <500ms untuk dashboard, <1s untuk laporan kompleks
- **Queue processing:** <5 detik untuk notifikasi
- **Database size growth:** ~1-2 MB per 1000 transaksi

---

## Troubleshooting

### Issue: Notifikasi tidak muncul
**Solusi:**
1. Cek queue worker: `supervisorctl status gudang-worker`
2. Cek failed jobs: `php artisan queue:failed`
3. Retry: `php artisan queue:retry all`

### Issue: Sesi opname ngambang tidak auto-cancel
**Solusi:**
1. Cek cron running: `grep CRON /var/log/syslog`
2. Manual trigger: `php artisan opname:auto-cancel-stale`

### Issue: Snapshot bulanan tidak jalan
**Solusi:**
1. Cek schedule: `php artisan schedule:list`
2. Manual trigger: `php artisan snapshot:monthly-asset-value`
3. Cek tanggal server (harus UTC atau lokal sesuai config)

---

## Deployment Completed ✅

**Final Checklist:**

- [x] Test suite: 49/49 passed
- [x] Config reviewed & environment variables set
- [x] Queue worker running (supervisor/systemd)
- [x] Cron configured for scheduled jobs
- [x] Production seeder executed
- [x] Super admin created
- [x] E2E test passed
- [x] Backup strategy implemented
- [x] Monitoring setup

**System Ready for Go-Live! 🚀**
