<?php
require_once __DIR__ . '/_auth.php';
logout_user();
set_flash_msg('Anda telah berhasil logout', 'success');
header('Location: ' . SISTEM_URL . '/login.php');
exit;
