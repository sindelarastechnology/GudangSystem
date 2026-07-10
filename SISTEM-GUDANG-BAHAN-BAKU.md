# Perancangan Sistem Gudang Bahan Baku - Konveksi (Revisi 2.2)

**Stack:** Laravel 13, Filament v3, MySQL, PHP 8.3.31
**Versi Dokumen:** 2.2 — Revisi dari 2.1: (1) status `cancelled` terpisah dari `finalized` pada stock opname supaya sesi batal tidak tercampur dengan sesi yang benar-benar diproses, (2) urutan lock konsisten pada transfer antar gudang untuk mencegah deadlock, (3) re-validasi `warehouses.is_locked` di dalam DB Transaction (bukan cuma sebelum masuk transaksi) untuk menutup celah check-then-act, (4) unique constraint pada `unit_conversions` supaya tidak ada aturan konversi ganda untuk kombinasi item+satuan yang sama, (5) klarifikasi pembuatan baris `material_stocks` (lazy-create), (6) klarifikasi satuan input pada stock opname.
**Fokus:** Stok akurat + Nilai aset gudang jelas. Sistem ini **murni gudang bahan baku** — bukan sistem akuntansi (jurnal, buku besar, neraca tidak termasuk) dan **bukan sistem produksi** (BOM, alur kerja produksi, perhitungan HPP produk jadi tidak termasuk; gudang bahan baku hanya mencatat barang keluar UNTUK produksi, tanpa masuk ke prosesnya).

---

## 1. Tujuan & Filosofi Sistem

Sistem ini dibangun untuk menjawab kebutuhan inti:

1. **Stok harus akurat** — setiap pergerakan barang (masuk/keluar/transfer/koreksi) tercatat jelas, tidak ada stok "siluman".
2. **Nilai aset gudang harus jelas** — kapan saja ditanya "berapa nilai bahan baku di gudang X sekarang?", sistem punya jawaban pasti.
3. **Tidak perlu akuntansi** — tidak ada jurnal debit/kredit, buku besar, atau neraca. HPP tetap dihitung otomatis dan konsisten.
4. **Sederhana dan cepat dipakai** — semua transaksi bersifat **satu langkah (one-shot)**. Tidak ada status draft/menunggu approval. User input, simpan, sistem langsung memproses dan meng-update stok + nilai aset. Ini berlaku untuk barang masuk, barang keluar, transfer antar gudang, maupun stock opname.
5. **Siap multi-gudang** — bukan sekadar disiapkan strukturnya, tapi memang dipakai aktif: stok dan harga rata-rata dihitung **per gudang**, karena barang yang sama bisa punya harga rata-rata berbeda di gudang yang berbeda (misal beda sumber pembelian/transfer).

### Keputusan Desain Kunci: Metode Costing

**Metode: Moving Average Cost (Weighted Average Perpetual), dihitung per item PER GUDANG.**

Kenapa per gudang: kalau avg_cost digabung lintas gudang, nilai aset per gudang jadi tidak akurat (padahal itu salah satu tujuan utama sistem ini). Jadi setiap kombinasi (item, gudang) punya avg_cost sendiri.

Rumus:

```
Saat Barang Masuk (di Gudang A):
  qty_baru      = qty_lama_gudangA + qty_masuk
  nilai_baru    = (qty_lama_gudangA x avg_cost_lama_gudangA) + (qty_masuk x harga_beli_masuk)
  avg_cost_baru_gudangA = nilai_baru / qty_baru

Saat Barang Keluar (dari Gudang A):
  HPP_keluar    = qty_keluar x avg_cost_gudangA_saat_ini
  qty_baru_gudangA = qty_lama_gudangA - qty_keluar
  avg_cost TIDAK berubah saat keluar

Saat Transfer Gudang A -> Gudang B:
  - Di Gudang A: dianggap "keluar", qty berkurang, avg_cost A tidak berubah
  - Di Gudang B: dianggap "masuk", dengan harga_beli_masuk = avg_cost_gudangA saat transfer
    (harga dibawa apa adanya, tidak markup, karena bukan pembelian baru)
  - Ini otomatis mempengaruhi avg_cost_gudangB mengikuti rumus barang masuk di atas

Nilai Aset per Gudang = SUM( qty_saat_ini x avg_cost_saat_ini ) untuk semua item DI GUDANG itu
Nilai Aset Total Perusahaan = SUM seluruh gudang
```

---

## 2. Ruang Lingkup Sistem

**Termasuk:**

- Multi-gudang bahan baku (stok & nilai aset terpisah per gudang)
- Master data bahan baku, kategori, satuan, konversi satuan, supplier
- Transaksi barang masuk (pembelian, retur produksi, penyesuaian tambah) — **langsung diproses, sekali input**
- Transaksi barang keluar (untuk produksi, retur ke supplier, penyesuaian kurang) — **langsung diproses, sekali input**
- Transfer stok antar gudang — **langsung diproses, sekali input**
- Kartu stok / ledger per item per gudang (riwayat mutasi lengkap)
- Stock opname per gudang (stok fisik vs sistem) — **langsung diproses, sekali input, otomatis menyesuaikan stok**
- Notifikasi stok menipis (in-app, real-time)
- Laporan nilai aset, mutasi, dan stok per gudang maupun gabungan
- Snapshot nilai aset bulanan (untuk laporan historis)

**Tidak termasuk:**

- Jurnal akuntansi, buku besar, neraca, laporan laba rugi
- Hutang piutang ke supplier
- Perhitungan HPP produk jadi / BOM produksi (di luar gudang bahan baku)
- Barcode/QR code scanning
- Integrasi API pihak ketiga (WhatsApp, dsb)
- Reservasi/booking stok

---

## 3. Struktur Database (ERD)

### 3.1 Master Data

**`material_categories`**
| Kolom | Tipe | Keterangan |
|---|---|---|
| id | bigint PK | |
| name | varchar(100) | contoh: Kain, Benang, Kancing |
| code | varchar(20) unique | |
| created_at, updated_at | timestamp | |

**`units`**
| Kolom | Tipe | Keterangan |
|---|---|---|
| id | bigint PK | |
| name | varchar(50) | contoh: Meter |
| symbol | varchar(10) | contoh: m |
| created_at, updated_at | timestamp | |

**`unit_conversions`** — opsional, untuk kasus beli per Roll tapi keluar per Meter
| Kolom | Tipe | Keterangan |
|---|---|---|
| id | bigint PK | |
| raw_material_id | bigint FK | |
| from_unit_id | bigint FK | |
| to_unit_id | bigint FK | |
| conversion_factor | decimal(15,4) | contoh: 1 roll = 25 meter |
| | | **UNIQUE(raw_material_id, from_unit_id, to_unit_id)** — mencegah dua aturan konversi berbeda terdaftar untuk kombinasi item+satuan yang sama (tanpa ini, sistem bisa diam-diam memakai aturan yang salah kalau ada duplikat) |

**`suppliers`**
| Kolom | Tipe | Keterangan |
|---|---|---|
| id | bigint PK | |
| name | varchar(150) | |
| phone | varchar(30) nullable | |
| address | text nullable | |
| is_active | boolean default true | untuk sembunyikan dari pilihan form transaksi baru |
| created_at, updated_at | timestamp | |
| **deleted_at** | **timestamp nullable** | **soft delete — lihat catatan di bawah tabel `raw_materials`** |

**`warehouses`** — sekarang aktif dipakai, bukan sekadar disiapkan. Ditambah kolom untuk mekanisme "freeze" saat stock opname berlangsung (lihat §3.6 & §4.4).
| Kolom | Tipe | Keterangan |
|---|---|---|
| id | bigint PK | |
| name | varchar(100) | contoh: Gudang Pusat, Gudang Cabang Blitar |
| code | varchar(20) unique | |
| location | varchar(200) nullable | |
| is_active | boolean default true | untuk sembunyikan dari pilihan form transaksi baru |
| **is_locked** | **boolean default false** | **true selama ada sesi stock opname aktif di gudang ini** |
| **locked_by_opname_id** | **bigint FK nullable → stock_opnames.id** | **menunjuk sesi opname yang sedang mengunci gudang ini** |
| **locked_at** | **timestamp nullable** | **kapan lock mulai — dipakai juga untuk auto-unlock kalau sesi ngambang terlalu lama** |
| created_at, updated_at | timestamp | |
| **deleted_at** | **timestamp nullable** | **soft delete — lihat catatan di bawah tabel `raw_materials`** |

**`raw_materials`** — Master item saja (definisi bahan baku). **Stok & nilai TIDAK disimpan di sini lagi** karena satu item bisa ada di banyak gudang dengan qty/harga berbeda-beda.
| Kolom | Tipe | Keterangan |
|---|---|---|
| id | bigint PK | |
| code | varchar(30) unique | SKU internal |
| name | varchar(150) | |
| material_category_id | bigint FK | |
| unit_id | bigint FK | satuan dasar untuk kartu stok — **semua qty di `stock_ledgers` dan `material_stocks` selalu dalam satuan ini** |
| image | varchar(255) nullable | foto bahan |
| is_active | boolean default true | untuk sembunyikan dari pilihan form transaksi baru |
| created_at, updated_at | timestamp | |
| **deleted_at** | **timestamp nullable** | **soft delete, lihat catatan di bawah** |

> **Catatan soft delete (berlaku untuk `raw_materials`, `warehouses`, `suppliers`):** Ketiga master data ini memakai Eloquent `SoftDeletes` trait, bukan hard delete. Alasannya: begitu sebuah item/gudang/supplier sudah dipakai di satu baris `stock_ledgers` saja, ia tidak boleh hilang total dari database karena akan merusak riwayat (foreign key menunjuk ke data yang tidak ada). Aturan mainnya:
>
> - `is_active = false` → item disembunyikan dari dropdown/pilihan di form transaksi baru, tapi **masih tampil apa adanya** di laporan & kartu stok historis. Dipakai untuk kasus "sudah tidak dipakai lagi tapi riwayatnya masih relevan".
> - `deleted_at` terisi (soft delete) → dipakai untuk kasus "dihapus karena salah input / duplikat", item hilang dari semua listing default (Filament otomatis exclude via scope), tapi baris di `stock_ledgers`, `stock_in_details`, dll tetap utuh karena FK tidak benar-benar terputus.
> - **Hard delete (`forceDelete()`) diblokir total di level Model** — di-override supaya selalu melempar exception, sehingga tidak ada cara (baik dari Filament Resource maupun Tinker/query manual) untuk benar-benar menghapus baris yang sudah pernah direferensikan. Kalau memang perlu bersih-bersih data yang belum pernah dipakai sama sekali, lakukan lewat migration/seeder khusus, bukan lewat aplikasi.
> - Filament Resource menambahkan `TrashedFilter` di tabel supaya Admin bisa lihat & restore data yang ter-soft-delete kalau ternyata salah hapus.

**`material_stocks`** — **Tabel kunci untuk multi-gudang.** Satu baris = kombinasi 1 item + 1 gudang. Ini tabel cache/turunan (hasil kalkulasi dari ledger), dipakai supaya listing & dashboard cepat.
| Kolom | Tipe | Keterangan |
|---|---|---|
| id | bigint PK | |
| raw_material_id | bigint FK | |
| warehouse_id | bigint FK | |
| min_stock | decimal(15,4) default 0 | ambang batas minimum, **per gudang** (gudang A bisa beda kebutuhan minimumnya dari gudang B) |
| current_stock | decimal(15,4) default 0 | cache |
| current_avg_cost | decimal(15,4) default 0 | cache, khusus kombinasi item+gudang ini |
| current_asset_value | decimal(18,2) default 0 | cache, = current_stock x current_avg_cost |
| **last_notified_at** | **timestamp nullable** | **kapan terakhir kali notifikasi stok menipis dikirim untuk kombinasi item+gudang ini — mencegah spam notifikasi harian (lihat §3.9)** |
| updated_at | timestamp | |
| | | UNIQUE(raw_material_id, warehouse_id) |

> **Catatan penting:** Semua perubahan pada `material_stocks` WAJIB lewat satu service class (misal `StockLedgerService`), tidak boleh diedit langsung dari Filament Resource. Ini menjaga konsistensi antara cache dan ledger.

> **Kapan baris `material_stocks` dibuat:** **Lazy-create**, bukan digenerate massal di awal. Baris (item, gudang) dibuat otomatis (`firstOrCreate` di dalam lock yang sama) pada saat pertama kali kombinasi tsb tersentuh transaksi (barang masuk pertama kali ke gudang itu, atau transfer masuk pertama kali). Kalau digenerate massal untuk semua kombinasi item x gudang sejak awal, akan banyak baris dengan `current_stock = 0` yang tidak pernah relevan (item yang memang tidak pernah disimpan di gudang tsb). Konsekuensinya: form Stock Opname harus menampilkan **union** dari (a) semua `material_stocks` yang sudah ada untuk gudang tsb, dan (b) tetap mengizinkan penambahan item yang belum punya baris `material_stocks` di gudang itu dengan `system_qty` dianggap 0 (baris baru dibuat saat finalisasi kalau ada selisih).

### 3.2 Transaksi Barang Masuk (One-Shot, Tanpa Approval)

**`stock_in_transactions`** (Header)
| Kolom | Tipe | Keterangan |
|---|---|---|
| id | bigint PK | |
| transaction_number | varchar(30) unique | auto generate via `DocumentNumberGenerator`, contoh: SIN-2026070001 — lihat §3.9 untuk mekanisme anti-bentrok |
| transaction_date | date | |
| warehouse_id | bigint FK | gudang tujuan barang masuk |
| supplier_id | bigint FK nullable | null jika bukan dari pembelian |
| type | enum | `purchase`, `production_return`, `adjustment_add` |
| reference_number | varchar(50) nullable | no. surat jalan / nota supplier |
| attachment | varchar(255) nullable | upload foto nota/surat jalan |
| notes | text nullable | |
| created_by | bigint FK users | |
| created_at, updated_at | timestamp | |

> Tidak ada kolom `status`. Begitu tombol simpan ditekan, transaksi langsung final dan stok langsung ter-update. Kalau ada salah input, dibuatkan transaksi koreksi baru (adjustment), bukan mengedit transaksi lama — supaya jejak riwayat tetap utuh untuk audit.

**`stock_in_details`**
| Kolom | Tipe | Keterangan |
|---|---|---|
| id | bigint PK | |
| stock_in_transaction_id | bigint FK | |
| raw_material_id | bigint FK | |
| **unit_id** | **bigint FK → units** | **satuan yang dipakai supplier di nota (misal Roll) — bisa beda dari satuan dasar item** |
| qty | decimal(15,4) | qty **dalam `unit_id` di atas**, apa adanya sesuai nota (misal 4 Roll) |
| **qty_base** | **decimal(15,4)** | **qty setelah dikonversi ke satuan dasar (`raw_materials.unit_id`) memakai `unit_conversions` — inilah yang dipakai untuk update `stock_ledgers` & `material_stocks`** |
| unit_price | decimal(15,4) | harga beli **per `unit_id` di atas** (misal harga per Roll), bukan per satuan dasar |
| subtotal | decimal(18,2) | qty x unit_price |
| notes | varchar(255) nullable | |

> **Mekanisme konversi:** Saat input, user pilih `unit_id` bebas dari daftar satuan yang punya aturan konversi terdaftar untuk item tsb di `unit_conversions` (atau satuan dasar itu sendiri, faktor = 1). Sistem cari `conversion_factor` dari `unit_id` terpilih → `raw_materials.unit_id`, lalu hitung `qty_base = qty x conversion_factor`. Kalau kombinasi item+unit tidak ditemukan di `unit_conversions` dan bukan satuan dasar, form menolak simpan (validasi wajib, bukan asumsi 1:1). `qty_base` inilah — bukan `qty` mentah — yang dipakai di semua perhitungan `StockLedgerService` (avg_cost, running_qty_balance, dst).

### 3.3 Transaksi Barang Keluar (One-Shot, Tanpa Approval)

**`stock_out_transactions`** (Header)
| Kolom | Tipe | Keterangan |
|---|---|---|
| id | bigint PK | |
| transaction_number | varchar(30) unique | contoh: SOUT-2026070001 — generate via `DocumentNumberGenerator`, lihat §3.9 |
| transaction_date | date | |
| warehouse_id | bigint FK | gudang asal barang keluar |
| type | enum | `production_usage`, `supplier_return`, `adjustment_reduce`, `damaged_lost` |
| destination | varchar(150) nullable | contoh: nama line produksi / no. SPK |
| notes | text nullable | |
| created_by | bigint FK users | |
| created_at, updated_at | timestamp | |

**`stock_out_details`**
| Kolom | Tipe | Keterangan |
|---|---|---|
| id | bigint PK | |
| stock_out_transaction_id | bigint FK | |
| raw_material_id | bigint FK | |
| **unit_id** | **bigint FK → units** | **satuan yang dipakai saat input barang keluar (misal untuk produksi biasanya diminta per Meter, tapi bisa saja per Roll)** |
| qty | decimal(15,4) | qty **dalam `unit_id` di atas** |
| **qty_base** | **decimal(15,4)** | **qty setelah dikonversi ke satuan dasar — dipakai untuk validasi stok & update ledger, sama mekanismenya dengan `stock_in_details`** |
| cost_at_issue | decimal(15,4) | otomatis diambil dari `material_stocks.current_avg_cost` (item+gudang ini), **per satuan dasar** |
| subtotal_hpp | decimal(18,2) | qty_base x cost_at_issue = HPP pemakaian |
| notes | varchar(255) nullable | |

> Validasi: `qty_base` tidak boleh melebihi `current_stock` (yang juga dalam satuan dasar) pada kombinasi item+gudang tersebut. Ditolak di level aplikasi sebelum transaksi disimpan.

### 3.4 Transfer Antar Gudang (One-Shot, Tanpa Approval)

**`stock_transfers`** (Header) — kebutuhan baru karena multi-gudang aktif dipakai
| Kolom | Tipe | Keterangan |
|---|---|---|
| id | bigint PK | |
| transfer_number | varchar(30) unique | contoh: TRF-2026070001 — generate via `DocumentNumberGenerator`, lihat §3.9 |
| transfer_date | date | |
| from_warehouse_id | bigint FK | |
| to_warehouse_id | bigint FK | harus berbeda dari from_warehouse_id |
| notes | text nullable | |
| created_by | bigint FK users | |
| created_at, updated_at | timestamp | |

**`stock_transfer_details`**
| Kolom | Tipe | Keterangan |
|---|---|---|
| id | bigint PK | |
| stock_transfer_id | bigint FK | |
| raw_material_id | bigint FK | |
| **unit_id** | **bigint FK → units** | **satuan yang dipakai staff saat input transfer** |
| qty | decimal(15,4) | qty **dalam `unit_id` di atas** |
| **qty_base** | **decimal(15,4)** | **qty setelah dikonversi ke satuan dasar — dipakai untuk mutasi stok kedua gudang** |
| cost_at_transfer | decimal(15,4) | diambil dari avg_cost gudang asal saat itu (per satuan dasar), dibawa apa adanya ke gudang tujuan |

> Efek: mengurangi stok di gudang asal (avg_cost gudang asal tidak berubah) dan menambah stok di gudang tujuan (avg_cost gudang tujuan dihitung ulang dengan rumus moving average seperti barang masuk biasa). Semua perhitungan qty di sini memakai `qty_base`, satuan dasar.

### 3.5 Kartu Stok (Ledger) — Jantung Sistem

**`stock_ledgers`** — satu baris untuk setiap pergerakan stok apapun, per item PER GUDANG. Ini **source of truth**. **Semua `qty` di sini selalu dalam satuan dasar (`raw_materials.unit_id`)** — konversi dari satuan input (Roll, dus, dll di tabel detail) sudah dilakukan sebelum baris ini ditulis, jadi ledger tidak perlu tahu satuan asli transaksi.
| Kolom | Tipe | Keterangan |
|---|---|---|
| id | bigint PK | |
| raw_material_id | bigint FK | |
| warehouse_id | bigint FK | |
| transaction_date | date | |
| direction | enum | `in`, `out` |
| source_type | varchar(50) | `stock_in`, `stock_out`, `transfer_in`, `transfer_out`, `opname_adjustment` |
| source_id | bigint | id dari tabel sumber (polymorphic reference) |
| qty | decimal(15,4) | selalu positif, arah ditentukan `direction` |
| unit_cost | decimal(15,4) | harga per satuan saat transaksi ini |
| running_qty_balance | decimal(15,4) | saldo qty item+gudang ini SETELAH transaksi |
| running_avg_cost | decimal(15,4) | avg cost item+gudang ini SETELAH transaksi |
| running_asset_value | decimal(18,2) | running_qty_balance x running_avg_cost |
| notes | varchar(255) nullable | |
| created_at | timestamp | |

> Dengan `running_qty_balance` dan `running_avg_cost` tersimpan per baris, Anda bisa cek stok & nilai aset item tertentu di gudang tertentu per tanggal manapun di masa lalu, tanpa hitung ulang dari nol. Ini juga alat audit utama.

### 3.6 Stock Opname (Dua Fase, Tetap Final/Tanpa Approval)

> **Keputusan desain untuk race condition jendela waktu opname:** Menghitung fisik di gudang riil bisa makan waktu berjam-jam. Kalau selama itu ada transaksi masuk/keluar lain berjalan di gudang yang sama, `system_qty` yang dibandingkan di akhir jadi basi. Solusi yang dipakai adalah **kombinasi dua mekanisme**, bukan cuma salah satu:
>
> 1. **Warehouse freeze selama sesi opname berlangsung.** Begitu sesi opname dibuat, `warehouses.is_locked` untuk gudang tsb di-set `true` (+ `locked_by_opname_id`, `locked_at`). Selama `is_locked = true`, `StockInResource`, `StockOutResource`, dan `StockTransferResource` **menolak transaksi apapun** yang menyentuh gudang tsb (baik sebagai gudang tujuan maupun asal/tujuan transfer), dengan pesan jelas "Gudang sedang opname, transaksi ditunda". Ini mencegah stok riil berubah sama sekali selama petugas menghitung fisik.
> 2. **`system_qty` diambil ulang saat detik terakhir sebelum simpan** (bukan cuma snapshot di awal), sebagai pengaman kedua kalau ada celah (misal ada job/proses lain yang tetap menulis ke ledger gudang tsb meski harusnya diblokir). Nilai awal saat sesi dibuat tetap disimpan sebagai referensi tampilan, tapi nilai yang benar-benar dipakai untuk hitung selisih adalah hasil re-fetch di dalam DB Transaction final, dengan `lockForUpdate()` pada baris `material_stocks` terkait.
>
> Dengan freeze aktif, celah untuk poin 2 seharusnya sudah tertutup di level aplikasi; re-fetch di langkah final tetap dipertahankan sebagai defense-in-depth murah (tidak nambah kompleksitas berarti) dan berguna sebagai jaring pengaman kalau lock gagal ter-set karena bug.

**`stock_opnames`** (Header sesi opname per gudang)
| Kolom | Tipe | Keterangan |
|---|---|---|
| id | bigint PK | |
| opname_number | varchar(30) unique | generate via `DocumentNumberGenerator`, lihat §3.9 |
| opname_date | date | |
| warehouse_id | bigint FK | opname selalu untuk 1 gudang tertentu |
| **status** | **enum: `counting`, `finalized`, `cancelled`** | **`counting` = sesi dibuat, gudang ter-lock, petugas sedang input fisik. `finalized` = sudah disimpan final DENGAN ledger adjustment ditulis, lock dilepas. `cancelled` = sesi dibatalkan TANPA ledger apapun ditulis (misal salah pilih gudang, atau sesi ditinggal terlalu lama), lock tetap dilepas. Ini BUKAN tabel approval — tidak ada status "pending review" atau `approved_by`, cuma menandai fase teknis dari satu proses opname yang sama.** |
| **started_at** | **timestamp** | **kapan sesi dibuat / gudang mulai di-lock** |
| **finalized_at** | **timestamp nullable** | **kapan sesi disimpan final (status=finalized) / gudang dibuka kembali** |
| **cancelled_at** | **timestamp nullable** | **kapan sesi dibatalkan (status=cancelled) / gudang dibuka kembali — mutually exclusive dengan `finalized_at`** |
| notes | text nullable | |
| created_by | bigint FK users | |
| created_at, updated_at | timestamp | |

> Alurnya sekarang genuinely dua langkah (lihat §4.4): (a) buat sesi → gudang otomatis ter-lock → sistem tampilkan `system_qty` referensi awal; (b) petugas isi `physical_qty` per item (bisa berjam-jam) → klik "Finalisasi" → sistem re-fetch `system_qty` terkini dengan lock, hitung selisih final, tulis ledger, buka lock gudang. Begitu status `finalized` ATAU `cancelled`, sesi ini immutable sama seperti transaksi lain — kalau salah hitung, buat sesi baru. `finalized` dan `cancelled` adalah dua akhir yang berbeda secara semantik: jangan pernah memakai `finalized` untuk merepresentasikan sesi yang dibatalkan tanpa ledger, karena laporan/audit trail perlu bisa membedakan "opname yang benar-benar mengubah stok" dari "opname yang batal total".

**`stock_opname_details`**
| Kolom | Tipe | Keterangan |
|---|---|---|
| id | bigint PK | |
| stock_opname_id | bigint FK | |
| raw_material_id | bigint FK | |
| system_qty | decimal(15,4) | **hasil re-fetch saat finalisasi** (bukan saat sesi dibuat), **selalu dalam satuan dasar** — inilah angka yang dipakai untuk hitung selisih |
| **physical_qty_unit_id** | **bigint FK → units** | **satuan yang dipakai petugas saat menghitung fisik (misal Roll, karena begitulah bahan disimpan/dihitung secara fisik di gudang) — TIDAK harus satuan dasar** |
| **physical_qty** | **decimal(15,4)** | **hasil hitung fisik petugas, dalam `physical_qty_unit_id` di atas, apa adanya** |
| **physical_qty_base** | **decimal(15,4)** | **`physical_qty` dikonversi ke satuan dasar via `unit_conversions` (mekanisme sama seperti §3.2) — inilah yang dibandingkan dengan `system_qty`** |
| difference_qty | decimal(15,4) | physical_qty_base - system_qty |
| avg_cost_at_opname | decimal(15,4) | untuk hitung nilai selisih, diambil saat finalisasi |
| difference_value | decimal(18,2) | difference_qty x avg_cost_at_opname |
| notes | varchar(255) nullable | |

> **Kenapa ditambahkan:** petugas gudang biasanya menghitung fisik dalam satuan yang natural di lapangan (misal Roll kain), bukan selalu satuan dasar (Meter). Memaksa input selalu dalam satuan dasar berisiko human error saat konversi manual dilakukan di kepala petugas. Field ini konsisten dengan pola `unit_id` + `qty` + `qty_base` yang sudah dipakai di `stock_in_details`, `stock_out_details`, dan `stock_transfer_details`.

### 3.7 Snapshot Nilai Aset (Laporan Historis)

**`asset_value_snapshots`** — diisi otomatis via scheduled job tiap akhir bulan
| Kolom | Tipe | Keterangan |
|---|---|---|
| id | bigint PK | |
| snapshot_date | date | tanggal akhir bulan |
| warehouse_id | bigint FK | |
| raw_material_id | bigint FK | |
| qty | decimal(15,4) | |
| avg_cost | decimal(15,4) | |
| asset_value | decimal(18,2) | |
| created_at | timestamp | |

> Kenapa perlu: `current_avg_cost` terus berubah seiring waktu. Snapshot membekukan angka historis per gudang supaya laporan bulan lalu tidak ikut berubah karena ada transaksi baru di bulan berikutnya.

### 3.8 Notifikasi Stok Menipis (dengan Cooldown Anti-Spam)

Tidak perlu tabel baru — memanfaatkan tabel `notifications` bawaan Laravel (`database` notification channel) yang otomatis dibaca Filament sebagai bell-icon notification di panel admin. Ditambah kolom `material_stocks.last_notified_at` (lihat §3.1) supaya item yang stoknya tetap di bawah minimum selama berhari-hari tidak memicu notifikasi berulang tiap hari.

**Mekanisme:**

- **Trigger real-time:** setiap kali ada transaksi yang mengurangi stok (barang keluar atau transfer keluar), sistem cek: apakah `material_stocks.current_stock` (setelah update) `<= material_stocks.min_stock`?
  - Jika ya **dan** (`last_notified_at` masih null **atau** sudah lebih dari 3 hari sejak `last_notified_at`), kirim notifikasi Laravel (`Notification::send()`) ke user dengan role tertentu (Admin Gudang / Owner), lalu update `last_notified_at = now()`.
  - Jika stok masih di bawah minimum tapi belum lewat 3 hari sejak notifikasi terakhir, **tidak** kirim notifikasi baru (mencegah spam kalau ada banyak transaksi keluar kecil-kecil di gudang yang sama pada hari yang sama).
- **Trigger terjadwal (cadangan):** scheduled job harian jam 07:00 mengecek ulang semua `material_stocks` yang `current_stock <= min_stock`, dengan filter yang sama: hanya kirim kalau `last_notified_at` null atau sudah ≥ 3 hari. Job ini juga yang meng-update `last_notified_at` untuk baris yang dikirimi notifikasi.
- **Reset otomatis:** begitu ada transaksi masuk/transfer masuk yang membuat `current_stock > min_stock` lagi, `last_notified_at` di-reset ke `null` lewat `StockLedgerService`, supaya kalau nanti stok menipis lagi di masa depan, notifikasi langsung terkirim dari awal (tidak ketahan cooldown lama yang sudah tidak relevan).
- Opsional: notifikasi juga dikirim via email (pakai `Illuminate\Notifications\Notification` dengan channel `mail`, memakai SMTP internal perusahaan — ini BUKAN API pihak ketiga, murni fitur bawaan Laravel), tunduk pada cooldown yang sama.
- Jeda 3 hari ini adalah nilai default yang disarankan, sebaiknya dibuat configurable lewat `config/warehouse.php` (`low_stock_notification_cooldown_days`), bukan hardcode, supaya bisa disesuaikan tanpa ubah kode.

### 3.9 Generator Nomor Transaksi (Anti-Bentrok)

> **Keputusan desain untuk race condition nomor transaksi:** Kalau 2 staff input barang masuk di detik yang sama dan nomor di-generate dengan `COUNT+1` biasa (misal `SELECT COUNT(*) FROM stock_in_transactions WHERE ...` lalu +1), ada celah antara SELECT dan INSERT di mana dua request bisa membaca angka yang sama sebelum salah satunya sempat commit — hasilnya dua transaksi dengan `transaction_number` yang identik, gagal karena constraint unique, atau lebih parah kalau constraint unique tidak ketat, nomor bentrok tanpa ketahuan.

Solusi yang dipakai: **tabel counter terpisah + `lockForUpdate()`**, konsisten dengan pola yang sudah dipakai di sistem akuntansi Anda sebelumnya (auto-numbering `{PREFIX}-{YYYY}-{XXXX}` dengan `DB::transaction()` + `lockForUpdate()`).

**`document_number_counters`** (tabel baru)
| Kolom | Tipe | Keterangan |
|---|---|---|
| id | bigint PK | |
| document_type | varchar(30) | `stock_in`, `stock_out`, `stock_transfer`, `stock_opname` |
| period | varchar(6) | format `YYYYMM`, contoh `202607` — counter reset tiap bulan |
| last_number | int unsigned default 0 | angka urut terakhir yang sudah dipakai untuk `document_type` + `period` ini |
| updated_at | timestamp | |
| | | UNIQUE(document_type, period) |

**Alur generate nomor (di dalam `DocumentNumberGenerator` service, dipanggil di awal `DB::transaction()` yang sama dengan proses simpan transaksi):**

```
1. $period = tanggal transaksi format YYYYMM (bukan tanggal server, supaya konsisten kalau input mundur)
2. DB::transaction(function () {
     $counter = DocumentNumberCounter::where('document_type', $type)
                    ->where('period', $period)
                    ->lockForUpdate()          // <-- kunci baris counter ini
                    ->first();
     if (!$counter) {
         $counter = DocumentNumberCounter::create([... , 'last_number' => 0]);
         // dilindungi unique constraint + retry kalau ada race saat create baris pertama
     }
     $counter->increment('last_number');       // atomic, masih dalam lock yang sama
     $nextNumber = $counter->last_number;
   });
3. $transaction_number = "{PREFIX}-{$period}{str_pad($nextNumber, 4, '0', STR_PAD_LEFT)}";
   // contoh: SIN-2026070001, SOUT-2026070002, TRF-2026070001, OPN-2026070001
```

- `lockForUpdate()` memastikan staff kedua yang input di detik yang sama harus menunggu transaksi staff pertama selesai (row lock di level database), sehingga tidak mungkin dua proses membaca `last_number` yang sama sebelum keduanya commit.
- Counter terpisah per `document_type` supaya nomor Stock In, Stock Out, Transfer, dan Opname independen satu sama lain (masing-masing mulai dari 0001 tiap bulan).
- Alternatif yang lebih sederhana (menempelkan `id` auto-increment transaksi ke nomor, misal `SIN-{id}`) sengaja **tidak** dipakai karena format nomor jadi tidak mengikuti pola bulanan yang biasa dipakai di dokumen internal (nota, laporan) dan `id` bisa melompat jauh kalau ada rollback transaksi gagal — tabel counter di atas lebih terkendali dan sudah terbukti dipakai di proyek akuntansi Anda sebelumnya.

---

## 4. Alur Bisnis (Business Flow)

### 4.1 Barang Masuk — Satu Langkah

```
User pilih gudang tujuan, supplier/tipe, tanggal, upload nota
  -> Input detail per item (unit_id, qty dalam unit_id tsb, harga beli per unit_id tsb)
  -> Klik Simpan
  -> Sistem, dalam 1 DB Transaction:
       1. Generate transaction_number via DocumentNumberGenerator (lockForUpdate() pada counter, §3.9)
       2. Untuk tiap detail: cari conversion_factor (unit_id item -> raw_materials.unit_id),
          tolak simpan jika tidak ada aturan konversi terdaftar; hitung qty_base = qty x conversion_factor
       3. Insert stock_in_transactions + stock_in_details (qty asli + qty_base tersimpan keduanya)
       4. Untuk tiap detail:
          - Lock baris material_stocks (item + gudang tsb)
          - Hitung avg_cost baru (rumus moving average, pakai qty_base)
          - Insert 1 baris stock_ledgers (direction=in, qty = qty_base)
          - Update material_stocks (current_stock, current_avg_cost, current_asset_value)
          - Jika current_stock (baru) > min_stock, reset last_notified_at = null
  -> SELESAI. Tidak ada status menunggu, langsung final dan tampil di kartu stok (dalam satuan dasar).
```

### 4.2 Barang Keluar — Satu Langkah

```
User pilih gudang asal, tipe/tujuan, tanggal
  -> Sistem cek warehouses.is_locked untuk gudang asal (pre-check di level form, UX cepat) -> jika true, tolak ("Gudang sedang opname")
  -> Input detail per item (unit_id, qty dalam unit_id tsb; harga TIDAK diinput manual)
  -> Klik Simpan
  -> Sistem, dalam 1 DB Transaction:
       1. Lock baris warehouses (gudang asal) dengan lockForUpdate(), lalu RE-CEK is_locked
          (pre-check di awal tadi cuma UX, bukan jaminan; sesi opname bisa saja mulai tepat di antara
          pre-check dan masuk transaksi ini -> re-cek di sini menutup celah tsb) -> jika true, rollback & tolak
       2. Generate transaction_number via DocumentNumberGenerator (§3.9)
       3. Untuk tiap detail: hitung qty_base dari unit_id -> raw_materials.unit_id (tolak jika konversi tidak ada)
       4. Lock baris material_stocks (item + gudang tsb)
       5. Validasi qty_base <= current_stock, tolak jika tidak cukup
       6. Ambil current_avg_cost -> jadi cost_at_issue
       7. Insert stock_out_transactions + stock_out_details (qty asli + qty_base)
       8. Insert 1 baris stock_ledgers (direction=out, qty = qty_base)
       9. Update material_stocks (current_stock, current_asset_value; avg_cost TIDAK berubah)
      10. Cek current_stock <= min_stock DAN (last_notified_at null atau >= 3 hari) -> jika ya, kirim
          notifikasi stok menipis dan update last_notified_at = now()
  -> SELESAI.
```

### 4.3 Transfer Antar Gudang — Satu Langkah

```
User pilih gudang asal & gudang tujuan (harus beda), tanggal
  -> Sistem cek warehouses.is_locked untuk gudang asal MAUPUN tujuan (pre-check UX) -> jika salah satu true, tolak
  -> Input detail per item (unit_id, qty dalam unit_id tsb)
  -> Klik Simpan
  -> Sistem, dalam 1 DB Transaction:
       1. Lock baris warehouses (gudang asal & tujuan) dengan lockForUpdate() DALAM URUTAN warehouse_id ASCENDING
          (bukan urutan asal-lalu-tujuan) -> lalu RE-CEK is_locked untuk keduanya, tolak & rollback jika salah
          satu true. Urutan ascending ini WAJIB konsisten di semua alur yang mengunci >1 gudang, supaya transfer
          A->B yang berbarengan dengan transfer B->A tidak saling menunggu selamanya (deadlock) — kalau urutan
          lock ikut arah transfer, dua transaksi berlawanan bisa saling mengunci baris yang ditunggu satu sama lain.
       2. Generate transfer_number via DocumentNumberGenerator (§3.9)
       3. Untuk tiap detail: hitung qty_base dari unit_id -> raw_materials.unit_id (tolak jika konversi tidak ada)
       4. Lock material_stocks (item + gudang asal) & (item + gudang tujuan), JUGA dalam urutan warehouse_id
          ascending yang sama, dengan alasan yang sama seperti langkah 1
       5. Validasi qty_base <= stok gudang asal
       6. Ambil avg_cost gudang asal -> jadi cost_at_transfer
       7. Insert stock_transfers + stock_transfer_details (qty asli + qty_base)
       8. Insert 2 baris stock_ledgers (qty = qty_base): 1 baris (direction=out, source_type=transfer_out) di
          gudang asal, 1 baris (direction=in, source_type=transfer_in) di gudang tujuan
       9. Update material_stocks kedua gudang (avg_cost gudang tujuan dihitung ulang, avg_cost gudang asal tetap)
      10. Cek juga trigger notifikasi (dengan cooldown last_notified_at, §3.8) jika stok gudang asal jadi
          <= min_stock
  -> SELESAI.
```

### 4.4 Stock Opname — Dua Fase (Buka Sesi → Lock Gudang → Finalisasi)

```
FASE 1 — Buka Sesi:
User pilih gudang, klik "Mulai Opname"
  -> Sistem, dalam 1 DB Transaction singkat:
       1. Validasi warehouses.is_locked masih false (tidak ada sesi opname lain aktif di gudang ini)
       2. Insert stock_opnames (status=counting, started_at=now())
       3. Update warehouses: is_locked=true, locked_by_opname_id=<id sesi ini>, locked_at=now()
  -> Sistem tampilkan semua item aktif di gudang tsb beserta system_qty SAAT INI sebagai referensi
     (angka ini hanya tampilan awal, BUKAN yang dipakai untuk hitung selisih final)
  -> Selama status=counting, StockInResource/StockOutResource/StockTransferResource menolak semua
     transaksi yang menyentuh gudang ini

FASE 2 — Hitung Fisik (bisa berjam-jam, gudang tetap ter-lock):
Petugas isi physical_qty (hasil hitung fisik) per item, bisa disimpan sebagai draft berkali-kali
  -> stock_opname_details bisa di-update selama status masih counting (belum ada ledger yang dibuat,
     jadi ini bukan pelanggaran prinsip "no edit" — sesi belum final)

FASE 3 — Finalisasi:
Petugas klik "Finalisasi Opname"
  -> Sistem, dalam 1 DB Transaction:
       1. Untuk tiap item: lock baris material_stocks (item + gudang ini), RE-FETCH current_stock
          sebagai system_qty final (bukan angka referensi awal Fase 1)
       2. Hitung difference_qty & difference_value tiap item memakai system_qty hasil re-fetch ini
       3. Untuk tiap item yang ada selisih:
          - Insert stock_ledgers (source_type=opname_adjustment, direction sesuai tambah/kurang)
          - Update material_stocks (current_stock, current_asset_value)
       4. Cek notifikasi stok menipis (dengan cooldown last_notified_at) untuk item yang hasil opname-nya
          di bawah min_stock
       5. Update stock_opnames: status=finalized, finalized_at=now()
       6. Update warehouses: is_locked=false, locked_by_opname_id=null, locked_at=null
  -> SELESAI. Sesi opname (status=finalized) immutable, tidak bisa diedit — kalau salah, buat sesi baru.
     Gudang otomatis terbuka kembali untuk transaksi normal.

FASE ALTERNATIF — Batalkan Sesi (dari status=counting, kapan saja sebelum finalisasi):
User klik "Batalkan Opname" (misal salah pilih gudang, atau sesi sudah tidak relevan)
  -> Sistem, dalam 1 DB Transaction singkat:
       1. Validasi status sesi masih `counting` (tidak bisa membatalkan sesi yang sudah `finalized`)
       2. Update stock_opnames: status=cancelled, cancelled_at=now()
       3. Update warehouses: is_locked=false, locked_by_opname_id=null, locked_at=null
  -> TIDAK ADA ledger yang ditulis, TIDAK ADA perubahan pada material_stocks. Ini murni membuka lock gudang
     kembali. Sengaja dibuat status terpisah (`cancelled`, bukan dipaksa jadi `finalized`) supaya laporan &
     audit trail bisa membedakan sesi yang benar-benar memproses stok dari sesi yang batal total.

MEKANISME PENGAMAN — Auto-Cancel Sesi Ngambang (Scheduled Job, WAJIB diimplementasikan, bukan opsional):
Scheduled job jalan tiap jam
  -> Scan stock_opnames WHERE status='counting' AND started_at < now() - 24 jam
  -> Untuk tiap sesi yang ketemu: jalankan proses yang sama persis dengan "Batalkan Sesi" di atas
     (status=cancelled, cancelled_at=now() [ditandai oleh sistem, bukan user], buka lock gudang)
  -> Kirim notifikasi ke Admin Gudang/Owner bahwa sesi opname X di gudang Y auto-cancelled karena
     ditinggal petugas, supaya bisa ditindaklanjuti manual (buat sesi opname baru)
```

> **Catatan operasional:** karena gudang ter-lock total selama Fase 2, sebaiknya opname dijadwalkan di luar jam sibuk transaksi. Durasi 24 jam di atas adalah default yang disarankan — sebaiknya configurable lewat `config/warehouse.php` (`opname_stale_hours`), bukan hardcode, konsisten dengan pola konfigurasi cooldown notifikasi di §3.8.

### 4.5 Notifikasi Stok Menipis

```
Trigger real-time: setiap transaksi keluar/transfer keluar/opname yang menurunkan stok
  -> Jika current_stock (baru) <= min_stock -> kirim Notification (database channel, tampil di Filament)

Trigger terjadwal (cadangan): scheduled job harian jam 07:00
  -> Scan semua material_stocks WHERE current_stock <= min_stock
  -> Kirim ringkasan notifikasi (daftar semua item kritis) ke Admin Gudang/Owner
```

### 4.6 Snapshot Bulanan (Scheduled Job)

```
Laravel Scheduler jalan tiap tanggal 1 jam 00:05
  -> Ambil semua kombinasi material_stocks aktif
  -> Insert ke asset_value_snapshots dengan data per akhir bulan sebelumnya
```

---

## 5. Konkurensi & Integritas Data (Penting!)

Karena input dilakukan banyak staff dan lintas gudang, ini wajib diterapkan:

1. **`DB::transaction()`** di setiap proses (masuk/keluar/transfer/opname) — atomic, rollback total jika ada kegagalan di tengah proses.
2. **Row locking (`lockForUpdate()`)** pada baris `material_stocks` yang sedang diproses (kombinasi item+gudang spesifik), supaya 2 transaksi bersamaan pada item & gudang yang sama tidak saling menimpa.
3. **Validasi qty keluar/transfer dilakukan setelah lock diambil**, bukan sebelumnya, supaya memakai data stok terkini.
4. **Tidak ada UPDATE langsung** ke `material_stocks` dari luar service class (`StockLedgerService`). Semua perubahan stok WAJIB lewat service ini — Filament Resource hanya memanggil service, tidak menulis tabel stok secara langsung.
5. **Tidak ada edit/hapus transaksi yang sudah tersimpan.** Karena semua transaksi bersifat final (one-shot), koreksi kesalahan dilakukan lewat transaksi baru (adjustment atau opname), bukan mengubah data lama. Ini penting untuk menjaga jejak audit tetap utuh.

---

## 6. Struktur Filament Resource

| Resource                                                                            | Fungsi                              | Catatan                                                                                                                                    |
| ----------------------------------------------------------------------------------- | ----------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------ |
| `RawMaterialResource`                                                               | CRUD master bahan baku              | Relation Manager: tampilkan stok di semua gudang (dari `material_stocks`) + kartu stok (dari `stock_ledgers`)                              |
| `MaterialStockResource` (opsional, atau cukup tab di RawMaterialResource)           | Lihat & atur `min_stock` per gudang | Read-only untuk qty/avg_cost, editable untuk `min_stock` saja                                                                              |
| `StockInResource`                                                                   | Input barang masuk                  | Repeater untuk detail item, FileUpload untuk nota, langsung simpan tanpa status                                                            |
| `StockOutResource`                                                                  | Input barang keluar                 | Repeater untuk detail item, field harga disabled (auto dari sistem), langsung simpan                                                       |
| `StockTransferResource`                                                             | Input transfer antar gudang         | Pilih gudang asal & tujuan, repeater item, langsung simpan                                                                                 |
| `StockOpnameResource`                                                               | Sesi stock opname                   | Halaman custom: generate daftar item + system_qty, input physical_qty (+ pilihan satuan), tombol "Finalisasi" dan "Batalkan Sesi" terpisah |
| `SupplierResource`, `MaterialCategoryResource`, `UnitResource`, `WarehouseResource` | Master data pendukung               | Standard CRUD                                                                                                                              |
| Dashboard Widgets                                                                   | Ringkasan                           | Total Nilai Aset (per gudang & gabungan), Daftar Stok Kritis, Grafik Masuk vs Keluar per bulan                                             |
| Filament Notifications (bawaan)                                                     | Notifikasi stok menipis             | Bell icon di panel admin, memakai database notification channel                                                                            |

**Saran teknis Filament:**

- `Forms\Components\Repeater` untuk input detail multi-item di Stock In/Out/Transfer.
- Field `unit_price`/`cost_at_issue` di Stock Out dan Transfer **read-only**, diisi otomatis dari backend.
- `Tables\Columns\TextColumn::make('current_asset_value')->money('IDR')` untuk format rupiah.
- Filter gudang (`SelectFilter`) di hampir semua tabel resource supaya user bisa fokus ke 1 gudang.
- Pertimbangkan **Filament Shield** untuk role: Admin Gudang (semua gudang), Staff Gudang (gudang tertentu saja), Owner/Viewer (read-only semua gudang).

---

## 7. Laporan (Reports)

| Laporan                                           | Isi                                                                                                  | Filter Gudang                  |
| ------------------------------------------------- | ---------------------------------------------------------------------------------------------------- | ------------------------------ |
| **Nilai Aset Saat Ini**                           | Per item per gudang: qty, avg_cost, asset_value + subtotal per gudang + total keseluruhan perusahaan | Bisa pilih 1 gudang atau semua |
| **Nilai Aset Historis**                           | Nilai aset per tanggal tertentu di masa lalu, per gudang                                             | Ya                             |
| **Kartu Stok / Mutasi per Item**                  | Riwayat in/out/transfer dengan saldo berjalan                                                        | Per item, per gudang           |
| **Rekap Barang Masuk (periode)**                  | Total qty & nilai masuk per periode, per gudang/supplier/kategori                                    | Ya                             |
| **Rekap Barang Keluar / HPP Pemakaian (periode)** | Total qty & nilai HPP keluar per periode, per gudang/tujuan                                          | Ya                             |
| **Rekap Transfer Antar Gudang**                   | Daftar transfer, dari-ke gudang, qty & nilai yang berpindah                                          | Ya                             |
| **Laporan Stock Opname & Selisih**                | Selisih qty & nilai rupiah per sesi opname, per gudang                                               | Ya                             |
| **Stok Kritis**                                   | Item dengan `current_stock <= min_stock`, per gudang                                                 | Ya                             |
| **Perbandingan Antar Gudang**                     | Nilai aset & stok item yang sama di gudang berbeda, side-by-side                                     | Semua gudang                   |

---

## 8. Ringkasan Perubahan dari Versi Sebelumnya

**Perubahan v2.1 -> v2.2:**

| Perubahan        | Detail                                                                                                                                                                |
| ---------------- | --------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| ✅ Ditambahkan   | Status `cancelled` terpisah dari `finalized` pada `stock_opnames` + alur "Batalkan Sesi" resmi (§4.4)                                                                 |
| ✅ Ditambahkan   | Scheduled job auto-cancel sesi opname ngambang >24 jam, dijadikan requirement wajib bukan sekadar saran                                                               |
| ✅ Ditambahkan   | Unique constraint pada `unit_conversions` (raw_material_id, from_unit_id, to_unit_id)                                                                                 |
| ✅ Ditambahkan   | `physical_qty_unit_id` + `physical_qty_base` pada `stock_opname_details`, konsisten dengan pola unit_id/qty_base di transaksi lain                                    |
| 🔧 Diperbaiki    | Urutan lock pada transfer antar gudang (§4.3) dibuat konsisten (warehouse_id ascending) untuk mencegah deadlock antar transfer berlawanan arah                        |
| 🔧 Diperbaiki    | Re-validasi `warehouses.is_locked` di dalam DB Transaction (bukan cuma pre-check) pada alur barang keluar & transfer, menutup celah check-then-act                    |
| 📝 Diklarifikasi | Baris `material_stocks` dibuat lazy (saat kombinasi item+gudang pertama kali tersentuh transaksi), bukan pre-generated massal                                         |
| 📝 Diklarifikasi | Scope ditegaskan ulang di header: bukan akuntansi DAN bukan sistem produksi — gudang bahan baku hanya mencatat barang keluar untuk produksi, tanpa masuk ke prosesnya |

**Perubahan v2.0 -> v2.1:**

| Perubahan      | Detail                                                                                                      |
| -------------- | ----------------------------------------------------------------------------------------------------------- |
| ❌ Dihapus     | Barcode/QR code scanning                                                                                    |
| ❌ Dihapus     | Integrasi API pihak ketiga (WhatsApp, dsb)                                                                  |
| ❌ Dihapus     | Reservasi/booking stok                                                                                      |
| ❌ Dihapus     | Alur draft → approval di semua transaksi (in/out/transfer/opname) — sekarang semua one-shot                 |
| ✅ Ditambahkan | Notifikasi stok menipis (in-app Filament, real-time + cadangan job harian)                                  |
| ✅ Ditambahkan | Multi-gudang aktif: `material_stocks` per item per gudang, `stock_transfers` untuk perpindahan antar gudang |
| ✅ Diubah      | `raw_materials` sekarang murni master item (definisi), stok & harga rata-rata pindah ke `material_stocks`   |
| ✅ Diubah      | `stock_ledgers` dan `asset_value_snapshots` menambahkan `warehouse_id`                                      |

---

## 9. Diagram Alur Data (Ringkas)

```
[Supplier] --> Stock In (pilih gudang) --> Ledger (IN, gudang X) --> Update material_stocks (item, gudang X)

[Produksi] <-- Stock Out (dari gudang X) <-- ambil avg_cost dari material_stocks (item, gudang X)
                     |
                     v
              Ledger (OUT, gudang X) --> Update material_stocks --> Cek min_stock --> Notifikasi jika menipis

Gudang X --> Stock Transfer --> Gudang Y
   Ledger (transfer_out, gudang X)         Ledger (transfer_in, gudang Y)
   avg_cost gudang X tetap                 avg_cost gudang Y dihitung ulang

Stock Opname (per gudang) --> input fisik --> Simpan --> Ledger (opname_adjustment) --> Update material_stocks

Scheduled Job (akhir bulan) --> Baca semua material_stocks --> Simpan ke asset_value_snapshots (per gudang)
Scheduled Job (harian) --> Scan material_stocks <= min_stock --> Kirim notifikasi ringkasan
```

---

## 10. Roadmap Implementasi yang Disarankan

1. **Fase 1 — Master Data:** categories, units, unit_conversions, suppliers, warehouses, raw_materials
2. **Fase 2 — Core Stok:** tabel `material_stocks`, `StockLedgerService` (inti moving average per gudang)
3. **Fase 3 — Transaksi Utama:** Stock In, Stock Out (semua one-shot, langsung proses)
4. **Fase 4 — Multi-Gudang:** Stock Transfer antar gudang
5. **Fase 5 — Stock Opname:** dua fase (counting -> finalized), termasuk alur batalkan sesi (status=cancelled) dan scheduled job auto-cancel sesi ngambang (>24 jam)
6. **Fase 6 — Notifikasi:** trigger real-time + scheduled job harian untuk stok menipis
7. **Fase 7 — Laporan & Dashboard:** semua laporan di atas + widget dashboard
8. **Fase 8 — Snapshot Bulanan:** scheduled job untuk `asset_value_snapshots`

---

_Dokumen ini adalah blueprint perancangan. Detail migration file, model, dan service class Laravel bisa disusun menyusul berdasarkan struktur ini._
