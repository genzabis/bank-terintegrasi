<?php
// =============================================
// Konfigurasi Sistem Travel (D)
// =============================================
define('SISTEM_NAMA', 'AppsTravel');
define('SISTEM_KODE', 'D');
define('SISTEM_PORT', 8003);
define('SISTEM_URL',  'http://10.10.4.127:8003');

// URL sistem lain (full-mesh)
define('BANK_URL',       'http://10.10.4.127:8000');
define('ECOMMERCE_URL',  'http://10.10.7.192:8001');
define('PENDIDIKAN_URL', 'http://10.10.4.167:8002');

// Path data
define('DATA_DIR',     __DIR__ . '/data');
define('FILE_TIKET',   DATA_DIR . '/tiket.json');
define('FILE_HOTEL',   DATA_DIR . '/hotel.json');
define('FILE_PESANAN', DATA_DIR . '/pesanan.json');
define('FILE_VOUCHER', DATA_DIR . '/voucher.json');
define('FILE_PAKET',   DATA_DIR . '/paket.json');
define('FILE_AUDIT',   DATA_DIR . '/audit.json');
define('FILE_USERS',   DATA_DIR . '/users.json');

// Rekening Travel
define('REKENING_TRAVEL', '1005');

// Theme color (Travel = magenta)
define('THEME_COLOR', '#d946ef');
