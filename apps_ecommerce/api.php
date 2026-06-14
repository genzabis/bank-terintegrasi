<?php
require_once __DIR__ . '/functions.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit;

$action = $_GET['action'] ?? $_POST['action'] ?? '';
$body = json_decode(file_get_contents('php://input'), true);
if (!is_array($body)) $body = [];
$input = array_merge($_GET, $_POST, $body);

function reply(array $d, int $c = 200): void {
    http_response_code($c);
    echo json_encode($d, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

if ($action !== 'ping' && $action !== '') {
    audit_log('IN', $input['sumber'] ?? 'EXTERNAL', $action,
        $_SERVER['REQUEST_METHOD'].' /api.php?action='.$action, true, '', $input);
}

switch ($action) {
    case 'ping':
        reply(['success'=>true,'sistem'=>SISTEM_NAMA,'kode'=>SISTEM_KODE,'time'=>date('c'),'port'=>SISTEM_PORT]);
        break;

    case 'produk':
        reply(['success'=>true,'data'=>get_produk()]);
        break;

    case 'add_produk':
        $nama = trim($input['nama'] ?? '');
        $hrg = (float)($input['harga'] ?? 0);
        if (!$nama || $hrg <= 0) reply(['success'=>false,'message'=>'Nama / harga tidak valid'], 400);
        $r = add_produk([
            'nama'=>$nama,'harga'=>$hrg,'stok'=>(int)($input['stok']??1),
            'kategori'=>$input['kategori']??'siswa',
            'deskripsi'=>$input['deskripsi']??'',
            'sumber'=>$input['sumber']??'AppsPendidikan',
            'asal_id'=>$input['asal_id']??null,
        ]);
        reply($r);
        break;

    case 'review':
        $pid = $input['produk_id'] ?? '';
        reply(['success'=>true,'data'=>get_review($pid ?: null)]);
        break;

    case 'pesanan':
        reply(['success'=>true,'data'=>get_pesanan()]);
        break;

    case 'checkout':
        $r = proses_checkout(
            $input['user']    ?? 'guest',
            $input['no_rek']  ?? '',
            $input['items']   ?? [],
            $input['voucher'] ?? '',
            (float)($input['diskon_pct'] ?? 0)
        );
        reply($r, !empty($r['success']) ? 200 : 400);
        break;

    case 'audit':
        reply(['success'=>true,'data'=>get_audit((int)($input['limit'] ?? 50))]);
        break;

    default:
        reply(['success'=>false,'message'=>'Unknown action',
            'available'=>['ping','produk','add_produk','review','pesanan','checkout','audit']], 400);
}
