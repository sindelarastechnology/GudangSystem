<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Low Stock Notification Cooldown (Days)
    |--------------------------------------------------------------------------
    |
    | Jeda minimum (dalam hari) antara pengiriman notifikasi stok menipis
    | untuk kombinasi item+gudang yang sama. Mencegah spam notifikasi
    | jika ada banyak transaksi keluar kecil dalam waktu berdekatan.
    |
    */
    'low_stock_notification_cooldown_days' => env('LOW_STOCK_NOTIFICATION_COOLDOWN_DAYS', 3),

    /*
    |--------------------------------------------------------------------------
    | Stock Opname Stale Timeout (Hours)
    |--------------------------------------------------------------------------
    |
    | Batas waktu maksimum sesi stock opname dalam status 'counting'.
    | Jika melebihi batas ini, sesi akan otomatis di-cancel oleh scheduled job
    | dan gudang akan di-unlock.
    |
    */
    'opname_stale_hours' => env('OPNAME_STALE_HOURS', 24),

    /*
    |--------------------------------------------------------------------------
    | Document Number Prefixes
    |--------------------------------------------------------------------------
    |
    | Prefix untuk setiap tipe dokumen transaksi.
    |
    */
    'document_prefixes' => [
        'stock_in' => env('DOC_PREFIX_STOCK_IN', 'SIN'),
        'stock_out' => env('DOC_PREFIX_STOCK_OUT', 'SOUT'),
        'stock_transfer' => env('DOC_PREFIX_TRANSFER', 'TRF'),
        'stock_opname' => env('DOC_PREFIX_OPNAME', 'OPN'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Values
    |--------------------------------------------------------------------------
    |
    | Nilai default untuk konfigurasi numerik di sistem.
    |
    */
    'default_min_stock' => env('WAREHOUSE_DEFAULT_MIN_STOCK', 0),
    'default_currency' => 'IDR',
];
