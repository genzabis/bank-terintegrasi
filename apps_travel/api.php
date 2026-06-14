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
    case 'tiket':   reply(['success'=>true,'data'=>get_tiket()]); break;
    case 'hotel':   reply(['success'=>true,'data'=>get_hotel()]); break;
    case 'paket':   reply(['success'=>true,'data'=>get_paket()]); break;
    case 'voucher': reply(['success'=>true,'data'=>get_voucher()]); break;

    case 'tambah_voucher':
        $r = add_voucher([
            'kode'=>$input['kode']??'','persen'=>$input['persen']??0,
            'untuk'=>$input['untuk']??null,'siswa_id'=>$input['siswa_id']??null,
            'sumber'=>$input['sumber']??'EXTERNAL',
        ]);
        reply($r, !empty($r['success']) ? 200 : 400);
        break;

    case 'apply_diskon':
        $kode = trim($input['kode'] ?? '');
        $v = $kode ? find_voucher($kode) : null;
        if (!$v) reply(['success'=>false,'message'=>'Voucher tidak ditemukan'], 404);
        reply(['success'=>true,'data'=>$v]);
        break;

    case 'pesan_tiket':
        $r = proses_pesan_tiket(
            $input['tiket_id']??'',
            max(1,(int)($input['qty']??1)),
            $input['pemesan']??'Tamu',
            $input['no_rek']??null,
            $input['kode_voucher']??null,
            (float)($input['diskon']??0),
            $input['sumber']??'INTERNAL'
        );
        reply($r, !empty($r['success']) ? 200 : 400);
        break;

    case 'pesan_hotel':
        $r = proses_pesan_hotel(
            $input['hotel_id']??'',
            max(1,(int)($input['malam']??1)),
            $input['pemesan']??'Tamu',
            $input['no_rek']??null
        );
        reply($r, !empty($r['success']) ? 200 : 400);
        break;

    case 'pesan_paket':
        $r = proses_pesan_paket(
            $input['paket_id']??'',
            $input['pemesan']??'Tamu',
            $input['no_rek']??''
        );
        reply($r, !empty($r['success']) ? 200 : 400);
        break;

    case 'pesanan': reply(['success'=>true,'data'=>get_pesanan()]); break;
    case 'audit':   reply(['success'=>true,'data'=>get_audit((int)($input['limit']??50))]); break;

    default:
        reply(['success'=>false,'message'=>'Unknown action',
            'available'=>['ping','tiket','hotel','paket','voucher','tambah_voucher','apply_diskon','pesan_tiket','pesan_hotel','pesan_paket','pesanan','audit']], 400);
}
