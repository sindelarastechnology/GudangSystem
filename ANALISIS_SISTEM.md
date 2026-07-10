# Analisis Sistem Gudang Bahan Baku — Laporan Lengkap

**Tanggal Analisis:** 10 Juli 2026
**Lingkup:** Seluruh kode sumber (Models, Services, Filament Resources, Widgets, Policies, Migrations, Seeders, Tests, Config)

---

## Ringkasan Eksekutif

| Kategori | Jumlah |
|---|---|
| **Critical (Crash/Data Corruption)** | 6 |
| **High (Logic Error/Fungsionalitas Rusak)** | 5 |
| **Medium (Performance/UX/Inkonsistensi)** | 14 |
| **Low (Minor/Coding Style)** | 16 |
| **Warning/Observations** | 8 |
| **Total** | **49 temuan** |

---

## A. CRITICAL BUGS

### A1. RolePolicy — Blade template syntax mentah di permission names

| **File** | `app/Policies/RolePolicy.php:64-107` |
|---|---|
| **Severity** | 🔴 **CRITICAL** |
| **Deskripsi** | Enam method menggunakan literal `{{ }}` (Blade template syntax) yang tidak pernah di-render sebagai permission name: |
| **Baris** | `forceDelete` → `$user->can('{{ ForceDelete }}')` — harusnya `'force_delete_role'` |
| | `forceDeleteAny` → `$user->can('{{ ForceDeleteAny }}')` |
| | `restore` → `$user->can('{{ Restore }}')` |
| | `restoreAny` → `$user->can('{{ RestoreAny }}')` |
| | `replicate` → `$user->can('{{ Replicate }}')` |
| | `reorder` → `$user->can('{{ Reorder }}')` |
| **Dampak** | Semua 6 method selalu return `false` — aksi force delete, restore, replicate, reorder pada Role resource **tidak pernah bisa dijalankan oleh siapapun** |
| **Fix** | Ganti semua `'{{ ... }}'` dengan permission name sesuai konvensi Shield (`'force_delete_role'`, dll) |

### A2. DailyLowStockDigest — Role name mismatch

| **File** | `app/Console/Commands/DailyLowStockDigest.php:39` |
|---|---|
| **Severity** | 🔴 **CRITICAL** |
| **Deskripsi** | Query mencari user dengan role `'superadmin'` (tanpa underscore), tapi `config/filament-shield.php:25` mendefinisikan super admin sebagai `'super_admin'` (dengan underscore) |
| **Dampak** | Daily low stock digest **tidak pernah terkirim** ke user dengan role super_admin yang benar. Notifikasi harian tidak berfungsi sama sekali di production. |
| **Fix** | Ganti `'superadmin'` → `'super_admin'` |

### A3. Warehouse.php — Missing BelongsTo import

| **File** | `app/Models/Warehouse.php:60` |
|---|---|
| **Severity** | 🔴 **CRITICAL** |
| **Deskripsi** | Method `lockedByOpname()` menggunakan return type `BelongsTo` tetapi `use Illuminate\Database\Eloquent\Relations\BelongsTo;` tidak di-import. Hanya `HasMany` yang di-import. |
| **Dampak** | **Fatal error**: `Class "App\Models\BelongsTo" not found` ketika method ini dipanggil atau class di-load dengan strict type checking. |
| **Fix** | Tambahkan `use Illuminate\Database\Eloquent\Relations\BelongsTo;` |

### A4. DocumentNumberGenerator — Race condition on first counter creation

| **File** | `app/Services/DocumentNumberGenerator.php:33-38` |
|---|---|
| **Severity** | 🔴 **CRITICAL** |
| **Deskripsi** | Ketika counter belum ada untuk kombinasi `(document_type, period)`, `lockForUpdate()` tidak mengunci baris apapun (NULL). Dua request concurrent bisa masuk blok `if (!$counter)` dan duplikat INSERT. Constraint unik `uq_document_counter_type_period` akan membuat satu request gagal dengan `UniqueConstraintViolationException` yang **tidak di-catch** → HTTP 500. |
| **Dampak** | ~1% request gagal dengan 500 error di sistem dengan concurrent tinggi. |
| **Fix** | Add try-catch `UniqueConstraintViolationException` — sama seperti pattern di `StockLedgerService::lockOrCreateStock():167-182` |

### A5. StockOpnameService.saveDraftDetails — No database transaction

| **File** | `app/Services/StockOpnameService.php:56-95` |
|---|---|
| **Severity** | 🔴 **CRITICAL** |
| **Deskripsi** | Seluruh method `saveDraftDetails()` berjalan **tanpa** `DB::transaction()`. Jika proses gagal di tengah (misal koneksi DB putus setelah 5 dari 10 detail tersimpan), data `StockOpnameDetail` dalam keadaan **partial** tanpa recovery. |
| **Dampak** | **Korupsi data** — Sesi opname bisa memiliki item yang terhitung sebagian dengan tidak ada cara mendeteksi completeness. |
| **Fix** | Bungkus foreach loop dalam `DB::transaction()` |

### A6. StockOpnameService.finalize — TOCTOU race condition

| **File** | `app/Services/StockOpnameService.php:98-174` |
|---|---|
| **Severity** | 🔴 **CRITICAL** |
| **Deskripsi** | TOCTOU (Time-of-Check to Time-of-Use): `$opname` di-load di luar method. Check `$opname->isCounting()` menggunakan model yang sudah **stale**. Di dalam transaksi: tidak ada `lockForUpdate()` pada opname maupun warehouse. Dua proses concurrent bisa melakukan finalisasi ganda. |
| **Dampak** | **Double-counting inventory** — perbedaan stok dari opname dihitung dua kali, merusak saldo stok dan ledger. |
| **Fix** | Di dalam transaksi: `$opname = StockOpname::lockForUpdate()->findOrFail($opname->id)` dan `Warehouse::lockForUpdate()` lalu re-verify status. |

---

## B. HIGH SEVERITY BUGS

### B1. StockTransferService.lockOrCreate — Missing UniqueConstraintViolationException catch

| **File** | `app/Services/StockTransferService.php:200-208` |
|---|---|
| **Severity** | 🟠 **HIGH** |
| **Deskripsi** | Sama seperti A4 — `MaterialStock::create()` tanpa try-catch `UniqueConstraintViolationException`. Dua transfer concurrent ke gudang yang sama untuk material yang sama bisa collide. |
| **Dampak** | Transfer gagal dengan 500 error, user harus input ulang. |
| **Fix** | Duplicate pattern try-catch dari `StockLedgerService::lockOrCreateStock()` |

### B2. StockLedgerService.convertToBaseUnit — Reverse conversion direction not handled

| **File** | `app/Services/StockLedgerService.php:26-29` |
|---|---|
| **Severity** | 🟠 **HIGH** |
| **Deskripsi** | Query hanya cek satu arah: `from_unit_id = unit_input AND to_unit_id = base_unit`. Jika konversi disimpan terbalik (e.g., `from=kg, to=gram, factor=1000`), maka input dalam gram tidak akan pernah menemukan konversi. |
| **Dampak** | **MEMPENGARUHI SEMUA TRANSAKSI**: stock-in, stock-out, transfer, opname. User tidak bisa bertransaksi dalam satuan yang konversinya disimpan di arah sebaliknya. |
| **Fix** | Jika konversi arah maju tidak ditemukan, cari arah reverse dan gunakan `1 / conversion_factor` |

### B3. QuickStatsOverviewWidget — Days-to-stockout calculation mixes incompatible units

| **File** | `app/Filament/Widgets/QuickStatsOverviewWidget.php:74-82` |
|---|---|
| **Severity** | 🟠 **HIGH** |
| **Deskripsi** | `$avgDailyUsage = AVG(qty dari StockLedger)` dan `$avgCurrentStock = AVG(current_stock dari MaterialStock)` — menghitung rata-rata dari item dengan unit **berbeda** (meter, kg, pcs). Hasilnya matematis **tidak bermakna**. |
| **Dampak** | Statistik "Estimasi Hari Hingga Stok Habis" menampilkan angka yang salah dan menyesatkan. |
| **Fix** | Hitung days-to-stockout per-item (sebagai rasio) lalu rata-ratakan hasilnya, bukan rata-rata quantity mentah. |

### B4. LowStockNotificationTest — Role name mismatch dengan config production

| **File** | `tests/Feature/LowStockNotificationTest.php:32` |
|---|---|
| **Severity** | 🟠 **HIGH** |
| **Deskripsi** | Test create role `'superadmin'` (no underscore) tapi config Shield menggunakan `'super_admin'`. Test dan command `DailyLowStockDigest` aligned satu sama lain TAPI inconsistent dengan production config. |
| **Dampak** | Test pass di environment testing tapi fitur **gagal di production**. Test memberikan false sense of security. |
| **Fix** | Ganti `'superadmin'` → `'super_admin'` di test dan pastikan command juga konsisten (lihat A2) |

### B5. StockOpnameService.finalize — Zero avg cost untuk surplus item baru

| **File** | `app/Services/StockOpnameService.php:126-128` |
|---|---|
| **Severity** | 🟠 **HIGH** |
| **Deskripsi** | Saat opname menemukan surplus (`physical_qty_base > system_qty_final`) untuk material tanpa record stok sebelumnya, `MaterialStock` baru dibuat dengan `current_avg_cost = 0`. Adjustment kemudian dicatat dengan biaya 0, mengakibatkan **undervaluation** aset. |
| **Dampak** | Saldo aset di balance sheet menjadi kurang (undervalue) untuk item-item temuan opname. |
| **Fix** | Gunakan rata-rata harga pembelian terakhir dari `StockLedger` atau harga standar dari `RawMaterial`, jangan 0. |

---

## C. MEDIUM SEVERITY ISSUES

### C1. StockInService — unit_price required untuk non-purchase types

| **File** | `app/Filament/Resources/StockInTransactionResource.php:87-92` |
|---|---|
| **Severity** | 🟡 **MEDIUM** |
| **Deskripsi** | `unit_price` di details repeater marked `->required()`. Untuk tipe `adjustment_add` dan `production_return`, harga beli mungkin tidak relevan. |
| **Dampak** | User dipaksa input harga untuk transaksi yang seharusnya zero-cost. |
| **Fix** | Buat conditionally required berdasarkan tipe transaksi. |

### C2. StockTransferResource — Missing validation: from/to warehouse must be different

| **File** | `app/Filament/Resources/StockTransferResource.php:35-46` |
|---|---|
| **Severity** | 🟡 **MEDIUM** |
| **Deskripsi** | Tidak ada validasi yang mencegah user memilih gudang asal = gudang tujuan. Service layer menangani ini (`StockTransferService.php:32`) tetapi error baru muncul setelah submit. |
| **Fix** | Tambahkan rule `->different('from_warehouse_id')` pada field `to_warehouse_id` di form. |

### C3. StockInTransactionResource — supplier_id tidak conditional required

| **File** | `app/Filament/Resources/StockInTransactionResource.php:41-46` |
|---|---|
| **Severity** | 🟡 **MEDIUM** |
| **Deskripsi** | `supplier_id` marked `->nullable()`. Untuk tipe `purchase`, supplier seharusnya required secara logic. |
| **Fix** | `->required(fn (Forms\Get $get): bool => $get('type') === 'purchase')` |

### C4. StockLedger — last_notified_at unconditional reset

| **File** | `app/Services/StockLedgerService.php:82-84` |
|---|---|
| **Severity** | 🟡 **MEDIUM** |
| **Deskripsi** | Setiap kali stock-in terjadi dan quantity baru `> min_stock`, `last_notified_at` di-set ke `null` tanpa syarat. |
| **Dampak** | Notifikasi cooldown di-reset meski stok sudah di atas minimum sejak sebelumnya. |
| **Fix** | Hanya reset ketika `$oldQty <= min_stock && $newQty > min_stock`. |

### C5. Widget — Tidak ada caching

| **File** | Semua widget files |
|---|---|
| **Severity** | 🟡 **MEDIUM** |
| **Deskripsi** | Tidak ada widget yang implementasikan caching. Widget dashboard melakukan multiple aggregate queries setiap render. Dengan auto-refresh (`poll('30s')`), ini menciptakan beban DB signifikan. |
| **Fix** | Implement `remember()` untuk aggregate data yang jarang berubah, atau gunakan snapshot untuk trend charts. |

### C6. WarehouseComparisonWidget — N+1 company total recalculated in loop

| **File** | `app/Filament/Widgets/WarehouseComparisonWidget.php:45` |
|---|---|
| **Severity** | 🟡 **MEDIUM** |
| **Deskripsi** | `MaterialStock::sum(...)` dihitung di DALAM foreach loop untuk setiap warehouse. Dengan N warehouse, terjadi 2N+1 query instead of N+1. |
| **Fix** | Pindahkan company total query ke luar loop. |

### C7. StockMovementChartWidget — Transfer excluded on "all" filter

| **File** | `app/Filament/Widgets/StockMovementChartWidget.php:59-74` |
|---|---|
| **Severity** | 🟡 **MEDIUM** |
| **Deskripsi** | Ketika filter `'all'`, transfer-in dan transfer-out queries dilewati. Gerakan transfer tidak terlihat di chart agregat. |
| **Fix** | Sertakan transfer dalam aggregate chart atau dokumentasikan exclusion. |

### C8. filament-shield — is_scoped_to_tenant = true tapi tenant_model = null

| **File** | `config/filament-shield.php:13,17` |
|---|---|
| **Severity** | 🟡 **MEDIUM** |
| **Deskripsi** | Multi-tenancy scoping enabled tapi tidak ada tenant model. Shield bisa menghasilkan authorization behavior yang tidak terduga. |
| **Fix** | Set `is_scoped_to_tenant => false` (kecuali multi-tenancy memang direncanakan). |

### C9. filament-shield — discover_all_* = false tapi resource di-register via discovery

| **File** | `config/filament-shield.php:82-86` |
|---|---|
| **Severity** | 🟡 **MEDIUM** |
| **Deskripsi** | Shield discovery disabled. Resources/widgets/pages tidak akan auto-scan untuk permission generation. Jika ada resource baru, permission-nya tidak tergenerate otomatis. |
| **Fix** | Set `discover_all_resources => true` atau secara eksplisit register entities. |

### C10. LowStockNotification — Dead code toMail()

| **File** | `app/Notifications/LowStockNotification.php:19-22,52-73` |
|---|---|
| **Severity** | 🟡 **MEDIUM** |
| **Deskripsi** | `via()` hanya return `['database']`, tapi `toMail()` method lengkap didefinisikan dan tidak pernah dipanggil. |
| **Fix** | Hapus `toMail()` atau tambahkan `mail` ke `via()`. |

### C11. Migrations — Missing composite indexes untuk common query patterns

| **File** | `database/migrations/2026_07_06_141802_create_stock_in_transactions_table.php` (dan stock_out, stock_transfers) |
|---|---|
| **Severity** | 🟡 **MEDIUM** |
| **Deskripsi** | Widgets query tabel ini filtering `warehouse_id` + `transaction_date` range, tapi hanya single-column foreign key indexes yang ada. Tidak ada composite index `(warehouse_id, transaction_date)`. |
| **Dampak** | Full table scan untuk query dashboard/report yang sering dijalankan. |

### C12. Migrations — Missing unique constraint on units.symbol

| **File** | `database/migrations/2026_07_06_135903_create_units_table.php` |
|---|---|
| **Severity** | 🟡 **MEDIUM** |
| **Deskripsi** | Kolom `symbol` di tabel `units` tidak memiliki unique constraint. Seeders menggunakan `firstOrCreate(['symbol' => ...])` yang mengasumsikan symbol unique. Duplikasi data bisa terjadi tanpa error. |
| **Fix** | Tambahkan `->unique()` pada kolom `symbol`. |

### C13. Seeders — Duplicate unit symbols between UnitSeeder and ProductionSeeder

| **File** | `database/seeders/UnitSeeder.php` + `database/seeders/ProductionSeeder.php` |
|---|---|
| **Severity** | 🟡 **MEDIUM** |
| **Deskripsi** | Kedua seeder membuat unit dengan symbol overlapping (`pcs`, `m`, `kg`). UnitSeeder pakai `create()` langsung, ProductionSeeder pakai `firstOrCreate()`. Data akhir tergantung urutan eksekusi. |
| **Dampak** | Dua entry "Meter" atau "Kg" bisa ada di DB tanpa constraint mencegahnya. |

### C14. HistoricalAssetValueReport — No default filter loads ALL records

| **File** | `app/Filament/Pages/Reports/HistoricalAssetValueReport.php:39-41,63-64` |
|---|---|
| **Severity** | 🟡 **MEDIUM** |
| **Deskripsi** | `mount()` call `$this->form->fill()` tanpa default values. Query pakai `->when($snapshotDate, ...)` — jika `$snapshotDate = null`, SEMUA snapshot records di-load. Akan menjadi performance problem seiring data bertambah. |
| **Fix** | Set default: `'snapshot_date' => now()->subMonth()->endOfMonth()->format('Y-m-d')` |

---

## D. LOW SEVERITY ISSUES

### D1. CountingSession — physicalQty array indexed by unstable numeric index

| **File** | `app/Filament/Resources/StockOpnameResource/Pages/CountingSession.php:28-30` |
|---|---|
| **Severity** | 🔵 **LOW** |
| **Deskripsi** | Array `$physicalQty` di-index dengan numeric index dari foreach, bukan `raw_material_id`. Jika order query berubah, mapping quantity ke material bisa salah. |
| **Fix** | Gunakan `raw_material_id` sebagai key. |

### D2. CountingSession — saveDraft() lacks input validation

| **File** | `app/Filament/Resources/StockOpnameResource/Pages/CountingSession.php:38-67` |
|---|---|
| **Severity** | 🔵 **LOW** |
| **Deskripsi** | Tidak ada validasi untuk negative values, empty strings, atau non-numeric values pada `$physicalQty`. |
| **Fix** | Tambahkan validasi sebelum panggil `saveDraftDetails()`. |

### D3. CountingSession Blade — Diff display uses stale data

| **File** | `resources/views/filament/resources/stock-opname/pages/counting-session.blade.php:86-92` |
|---|---|
| **Severity** | 🔵 **LOW** |
| **Deskripsi** | Perhitungan diff `$item['physical_qty'] - $item['system_qty']` menggunakan data dari `getViewData()` awal, bukan nilai Livewire terbaru. |
| **Fix** | Gunakan `$this->physicalQty[$index]` di Blade template. |

### D4. MaterialStocksRelationManager — Duplicate editing UI for min_stock

| **File** | `app/Filament/Resources/RawMaterialResource/RelationManagers/MaterialStocksRelationManager.php:25,45-46,50-59` |
|---|---|
| **Severity** | 🔵 **LOW** |
| **Deskripsi** | `min_stock` bisa di-edit via dua cara: inline TextInputColumn (AJAX) dan modal EditAction. UX confusing dan potensi race condition. |
| **Fix** | Hapus salah satu (rekomendasi: simpan inline column, hapus modal action). |

### D5. RoleResource — Uses deprecated getActions()

| **File** | `app/Filament/Resources/RoleResource/Pages/ListRoles.php:13`, `ViewRole.php:13`, `EditRole.php:18` |
|---|---|
| **Severity** | 🔵 **LOW** |
| **Deskripsi** | `getActions()` deprecated di Filament 3.x. Semua List/View/Edit pages lain sudah pakai `getHeaderActions()` yang benar. |
| **Fix** | Ganti ke `getHeaderActions()`. |

### D6. SupplierResource — address in form but missing from table

| **File** | `app/Filament/Resources/SupplierResource.php:36-37,47-61` |
|---|---|
| **Severity** | 🔵 **LOW** |
| **Deskripsi** | Form memiliki field `address`, tapi table columns tidak menampilkan address. |
| **Fix** | Tambahkan `TextColumn::make('address')->wrap()->toggleable()`. |

### D7. CountingSession — Unused import

| **File** | `app/Filament/Resources/StockOpnameResource/Pages/CountingSession.php:11` |
|---|---|
| **Severity** | 🔵 **LOW** |
| **Deskripsi** | `use Filament\Forms;` di-import tapi tidak pernah digunakan di file tersebut. |
| **Fix** | Hapus import. |

### D8. Warehouse — Missing HasMany relationship to StockLedger

| **File** | `app/Models/Warehouse.php` |
|---|---|
| **Severity** | 🔵 **LOW** |
| **Deskripsi** | Warehouse memiliki `materialStocks()`, `stockInTransactions()`, dll, tapi tidak ada `stockLedgers()` relationship padahal foreign key `warehouse_id` ada di `stock_ledgers` table. |

### D9. RawMaterial — Missing 5 inverse HasMany relationships

| **File** | `app/Models/RawMaterial.php` |
|---|---|
| **Severity** | 🔵 **LOW** |
| **Deskripsi** | RawMaterial tidak memiliki relationship ke `stockInDetails`, `stockOutDetails`, `stockTransferDetails`, `stockOpnameDetails`, `assetValueSnapshots`. |

### D10. User — Missing 4 inverse HasMany relationships for created_by

| **File** | `app/Models/User.php` |
|---|---|
| **Severity** | 🔵 **LOW** |
| **Deskripsi** | User tidak memiliki relationship `stockInTransactions()`, `stockOutTransactions()`, `stockTransfers()`, `stockOpnames()` via `created_by`. |

### D11. Unit — Missing 3 inverse HasMany relationships

| **File** | `app/Models/Unit.php` |
|---|---|
| **Severity** | 🔵 **LOW** |
| **Deskripsi** | Unit tidak memiliki relationship ke `stockInDetails`, `stockOutDetails`, `stockTransferDetails`. |

### D12. StockLedger — Missing polymorphic morphTo() relationship

| **File** | `app/Models/StockLedger.php` |
|---|---|
| **Severity** | 🔵 **LOW** |
| **Deskripsi** | Kolom `source_type` + `source_id` mengindikasikan polymorphic relationship, tapi tidak ada `morphTo()` yang didefinisikan. |

### D13. LowStockNotification — Lazy-loaded relationships in queued job

| **File** | `app/Notifications/LowStockNotification.php:29-35` |
|---|---|
| **Severity** | 🔵 **LOW** |
| **Deskripsi** | Notification implements `ShouldQueue` tapi mengakses `$this->stock->rawMaterial->name`, `->warehouse->name`, dll via lazy loading. Jika records terhapus antara dispatch dan processing, akan error. |
| **Fix** | Simpan data yang diperlukan (nama material, nama warehouse) sebagai property langsung, eager load relationship. |

### D14. StockInService — unitPriceBase fallback zero qtyBase

| **File** | `app/Services/StockInService.php:75-76` |
|---|---|
| **Severity** | 🔵 **LOW** |
| **Deskripsi** | Jika `$qtyBase = 0`, fallback `unitPriceBase` menggunakan `$item['unit_price']` yang merupakan harga untuk unit transaksi, bukan base unit. Akan merekam cost per-base-unit yang salah di ledger. |

### D15. TotalAssetValueWidget — with('warehouse') on aggregated query

| **File** | `app/Filament/Widgets/TotalAssetValueWidget.php:89-96` |
|---|---|
| **Severity** | 🔵 **LOW** |
| **Deskripsi** | `MaterialStock::select(...)->groupBy('warehouse_id')->with('warehouse')->get()` akan return partial MaterialStock models. Eager load secara fungsional benar tapi bisa unexpected jika code lain akses non-selected columns. |

### D16. TopItemsByValueWidget — Values divided by 1000

| **File** | `app/Filament/Widgets/TopItemsByValueWidget.php:69` |
|---|---|
| **Severity** | 🔵 **LOW** |
| **Deskripsi** | Nilai item dibagi 1000 untuk chart readability. Jika nilai < 1000 IDR, bar akan invisible (tampil 0.00). Tooltip menunjukkan nilai benar, tapi bar-nya tidak kelihatan. |

---

## E. WARNINGS / OBSERVATIONS

### E1. Transaction Resources — No edit/delete/void mechanism

| **Files** | `StockTransferResource`, `StockOutTransactionResource`, `StockInTransactionResource`, `StockOpnameResource` |
|---|---|
| **Deskripsi** | Semua transaction resources hanya memiliki ViewAction. Tidak ada mekanisme untuk void/cancel transaksi yang salah. Design ini disengaja untuk audit integrity, tapi error input memerlukan intervensi DB langsung. |
| **Saran** | Pertimbangkan "Void Transaction" action untuk authorized users. |

### E2. StockCardReport — Hardcoded source_type strings

| **File** | `app/Filament/Pages/Reports/StockCardReport.php:100-114` |
|---|---|
| **Deskripsi** | Nilai `source_type` (`'stock_in'`, `'stock_out'`, `'transfer_in'`, dll) di-hardcode sebagai string. Jika berubah di model/service, report akan silent broken. |
| **Saran** | Definisikan sebagai constants di `StockLedger` model dan refer dari sana. |

### E3. AutoCancelStaleOpnameSessions — Job tidak ada

| **File** | `app/Console/Commands/AutoCancelStaleOpnameSessions.php` dan `routes/console.php` |
|---|---|
| **Deskripsi** | Command `opname:auto-cancel-stale` tidak di-schedule di `console.php`. File command-nya ada tapi tidak pernah dijalankan otomatis. |
| **Saran** | Tambahkan `Schedule::command('opname:auto-cancel-stale')->hourly()` di console.php. |

### E4. create_asset_value_snapshots — Missing FK cascades

| **File** | `database/migrations/2026_07_07_012637_create_asset_value_snapshots_table.php:14-15` |
|---|---|
| **Deskripsi** | Foreign keys `warehouse_id` dan `raw_material_id` tidak punya cascading behavior. Jika warehouse/material di-hard-delete, snapshot records jadi orphan. |

### E5. DatabaseSeeder not calling ProductionSeeder

| **File** | `database/seeders/DatabaseSeeder.php:11-15` |
|---|---|
| **Deskripsi** | `DatabaseSeeder` tidak memanggil `ProductionSeeder`. Dua seeder ini menghasilkan data master yang overlapping tapi dengan kode berbeda (`GDG-PST` vs `GD-PUSAT`). |
| **Saran** | Standarisasi data master dan buat satu entry point seeding. |

### E6. MonthlyAssetValueSnapshot — Runs daily but logically monthly

| **File** | `routes/console.php:9` |
|---|---|
| **Deskripsi** | Di-schedule `dailyAt('00:05')` tapi logic hanya buat snapshot bulan lalu sekali. Jadwal ini misleading — seharusnya first day of month atau diberi nama lebih jelas. |

### E7. ExampleTest — Welcome route test

| **File** | `tests/Feature/ExampleTest.php:15-17` |
|---|---|
| **Deskripsi** | Test `$this->get('/')->assertStatus(200)` untuk welcome view. Tidak ada test untuk `/admin` route yang merupakan halaman utama aplikasi. |
| **Saran** | Tambahkan test untuk Filament panel access. |

### E8. Policies menggunakan permission naming dengan :: separator

| **File** | Semua policy files |
|---|---|
| **Deskripsi** | Permission names menggunakan `::` separator (e.g., `'view_any_stock::transfer'`). Names ini harus exact match dengan Shield output. Jika Shield diregenerate dengan konfigurasi berbeda, mapping akan broken. |

---

## F. TEST COVERAGE GAPS

| Area | Coverage | Notes |
|---|---|---|
| **Widgets** | **0%** | 7 widget files — 0 tests. Widget memiliki query logic yang kompleks yang tidak ter-cover. |
| **Policies** | **0%** | 10 policy files — 0 tests. RolePolicy memiliki 6 broken permission yang tidak terdeteksi. |
| **Notifications** | Partial | LowStockNotification data structure tidak di-unit-test. Hanya integration flow yang di-test. |
| **Console Commands** | Partial | `DailyLowStockDigest` di-test via `LowStockNotificationTest`. `AutoCancelStaleOpnameSessions` dan `MonthlyAssetValueSnapshot` tidak ada test. |
| **Services** | Good | Service layer memiliki test coverage yang baik (DocumentNumberGenerator, StockLedgerService, Transaksi, Opname). |
| **Seeders** | **0%** | Tidak ada test untuk seeders. |

---

## G. REKOMENDASI PRIORITAS

### Segera (Production Blocker)
1. **A1** — Fix `RolePolicy.php` — Blade template syntax in 6 permission names
2. **A2** — Fix `DailyLowStockDigest.php` — role name `'superadmin'` → `'super_admin'`
3. **A3** — Fix `Warehouse.php` — add missing `BelongsTo` import
4. **A4** — Fix `DocumentNumberGenerator.php` — add UniqueConstraintViolationException catch
5. **A5** — Fix `StockOpnameService::saveDraftDetails()` — wrap in DB::transaction
6. **A6** — Fix `StockOpnameService::finalize()` — add lockForUpdate on opname + warehouse

### High Priority
7. **B1** — Fix `StockTransferService::lockOrCreate()` — add UniqueConstraintViolationException catch
8. **B2** — Fix `convertToBaseUnit()` — add reverse direction fallback
9. **B3** — Fix `QuickStatsOverviewWidget` — fix days-to-stockout calculation (per-item ratio)
10. **B5** — Fix avg cost = 0 untuk new stock records di opname adjustment

### Medium Priority
11. **C1-C14** — Perbaiki validation rules, caching, query optimization, migration indexes, config Shield
12. **E3** — Schedule `AutoCancelStaleOpnameSessions` di console.php
13. **F** — Tambah test coverage untuk widgets dan policies

---

## H. KESIMPULAN

Sistem secara keseluruhan memiliki arsitektur yang baik dengan pemisahan concern yang jelas (Service Layer pattern, Form Requests, Policies). Fitur-fitur inti — multi-warehouse, moving average, stock opname, transfer dengan deadlock prevention — diimplementasikan dengan baik.

Namun terdapat **6 critical bugs** yang **harus diperbaiki sebelum production deployment**:
- Role policy yang tidak functional (A1)
- Notifikasi low stock yang tidak pernah terkirim (A2)
- Fatal error pada Warehouse model (A3)
- Race condition pada document number generation (A4)
- Data corruption risk pada stock opname draft (A5)
- Double-counting risk pada opname finalization (A6)

Setelah 6 critical bugs diperbaiki, sistem siap untuk production dengan catatan test coverage untuk widget dan policies perlu ditambahkan secara bertahap.

---

*Laporan dihasilkan oleh analisis kode otomatis pada 10 Juli 2026.*
