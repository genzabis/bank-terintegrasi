<?php
// =============================================
// Konfigurasi Sistem Bank (A)
// =============================================
define('SISTEM_NAMA', 'AppsBank');
define('SISTEM_KODE', 'A');
define('SISTEM_PORT', 8000);
define('SISTEM_URL',  'http://192.168.18.176:8000');

// URL sistem lain (full-mesh)
define('ECOMMERCE_URL',  '192.168.18.176:8001');
define('PENDIDIKAN_URL', 'http://10.10.4.167:8002');
define('TRAVEL_URL',     'http://10.10.4.127:8003');

// Path data lokal
define('DATA_DIR',       __DIR__ . '/data');
define('FILE_REKENING',  DATA_DIR . '/rekening.json');
define('FILE_MUTASI',    DATA_DIR . '/mutasi.json');
define('FILE_KARTU',     DATA_DIR . '/kartu.json');
define('FILE_AUDIT',     DATA_DIR . '/audit.json');
define('FILE_USERS',     DATA_DIR . '/users.json');

// Theme color (Bank = cyan-blue)
define('THEME_COLOR', '#4f8cff');
