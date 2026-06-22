<?php
// =============================================
// Konfigurasi Sistem Pendidikan (C)
// =============================================
define('SISTEM_NAMA', 'AppsPendidikan');
define('SISTEM_KODE', 'C');
define('SISTEM_PORT', 8002);
define('SISTEM_URL',  'http://10.10.4.167:8002');

// URL sistem lain (full-mesh)

define('BANK_URL',      'http://10.10.4.127:8000');
define('ECOMMERCE_URL', 'http://10.10.7.192:8001');
define('TRAVEL_URL',    'http://10.10.4.127:8003');

// Path data
define('DATA_DIR',           __DIR__ . '/data');
define('FILE_SISWA',         DATA_DIR . '/siswa.json');
define('FILE_SPP',           DATA_DIR . '/spp.json');
define('FILE_PRODUK_SISWA',  DATA_DIR . '/produk_siswa.json');
define('FILE_PRESTASI',      DATA_DIR . '/prestasi.json');
define('FILE_AUDIT',         DATA_DIR . '/audit.json');
define('FILE_USERS',         DATA_DIR . '/users.json');

// Rekening sekolah (penerima pembayaran SPP)
define('REKENING_SEKOLAH', '1004');

// Theme color (Pendidikan = emerald)
define('THEME_COLOR', '#10d489');
