<?php
// =============================================
// Konfigurasi Sistem Ecommerce (B)
// =============================================
define('SISTEM_NAMA', 'AppsEcommerce');
define('SISTEM_KODE', 'B');
define('SISTEM_PORT', 8001);
define('SISTEM_URL',  'http://localhost:8001');

// URL sistem lain
define('BANK_URL',       'http://localhost:8000');
define('PENDIDIKAN_URL', 'http://localhost:8002');
define('TRAVEL_URL',     'http://localhost:8003');

// Path data
define('DATA_DIR',        __DIR__ . '/data');
define('FILE_PRODUK',     DATA_DIR . '/produk.json');
define('FILE_KERANJANG',  DATA_DIR . '/keranjang.json');
define('FILE_PESANAN',    DATA_DIR . '/pesanan.json');
define('FILE_REVIEW',     DATA_DIR . '/review.json');
define('FILE_WISHLIST',   DATA_DIR . '/wishlist.json');
define('FILE_AUDIT',      DATA_DIR . '/audit.json');
define('FILE_USERS',      DATA_DIR . '/users.json');

// Rekening penampung penjualan ecommerce
define('REKENING_TOKO', '1003');

// Theme color (Ecommerce = amber)
define('THEME_COLOR', '#ff8c42');
