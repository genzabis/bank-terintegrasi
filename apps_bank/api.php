<?php
require_once __DIR__ . '/functions.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit;

$action = $_GET['action'] ?? $_POST['action'] ?? '';
$body   = json_decode(file_get_contents('php://input'), true);
if (!is_array($body)) $body = [];
$input  = array_merge($_GET, $_POST, $body);

function reply(array $d, int $c = 200): void {
    http_response_code($c);
    echo json_encode($d, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

// Catat semua incoming request (kecuali ping yg terlalu sering)
if ($action !== 'ping' && $action !== '') {
    $sumber = $input['sumber'] ?? 'EXTERNAL';
    audit_log('IN', $sumber, $action, $_SERVER['REQUEST_METHOD'].' /api.php?action='.$action, true, '', $input);
}

switch ($action) {
    case 'ping':
        reply(['success'=>true,'sistem'=>SISTEM_NAMA,'kode'=>SISTEM_KODE,'time'=>date('c'),'port'=>SISTEM_PORT]);
        break;

    case 'rekening':
        reply(['success'=>true,'data'=>get_rekening()]);
        break;

    case 'cek_rekening':
        $no = $input['no_rek'] ?? '';
        $r  = $no ? find_rekening($no) : null;
        if (!$r) reply(['success'=>false,'message'=>'Rekening tidak ditemukan'], 404);
        reply(['success'=>true,'data'=>$r]);
        break;

    case 'debit':
        $no  = $input['no_rek']     ?? '';
        $amt = (float)($input['jumlah'] ?? 0);
        $ket = $input['keterangan'] ?? 'Pembayaran';
        $src = $input['sumber']     ?? 'EXTERNAL';
        if (!$no || $amt <= 0) reply(['success'=>false,'message'=>'Parameter tidak lengkap'], 400);
        $res = update_saldo($no, $amt, 'DEBIT', $ket, $src);
        reply($res, $res['success'] ? 200 : 400);
        break;

    case 'kredit':
        $no  = $input['no_rek']     ?? '';
        $amt = (float)($input['jumlah'] ?? 0);
        $ket = $input['keterangan'] ?? 'Setoran';
        $src = $input['sumber']     ?? 'EXTERNAL';
        if (!$no || $amt <= 0) reply(['success'=>false,'message'=>'Parameter tidak lengkap'], 400);
        $res = update_saldo($no, $amt, 'KREDIT', $ket, $src);
        reply($res, $res['success'] ? 200 : 400);
        break;

    case 'transfer':
        $dari = $input['dari'] ?? '';
        $ke   = $input['ke']   ?? '';
        $amt  = (float)($input['jumlah'] ?? 0);
        $ket  = $input['keterangan'] ?? 'Transfer';
        if (!$dari || !$ke || $amt <= 0) reply(['success'=>false,'message'=>'Parameter tidak lengkap'], 400);
        if ($dari === $ke) reply(['success'=>false,'message'=>'Rekening asal & tujuan sama'], 400);
        if (!find_rekening($ke)) reply(['success'=>false,'message'=>'Rekening tujuan tidak valid'], 400);

        $r1 = update_saldo($dari, $amt, 'DEBIT',  "Transfer ke $ke - $ket", 'INTERNAL');
        if (!$r1['success']) reply($r1, 400);
        update_saldo($ke, $amt, 'KREDIT', "Transfer dari $dari - $ket", 'INTERNAL');
        reply(['success'=>true,'message'=>'Transfer sukses','saldo_dari'=>$r1['saldo']]);
        break;

    case 'mutasi':
        $no = $input['no_rek'] ?? null;
        reply(['success'=>true,'data'=>get_mutasi($no)]);
        break;

    case 'audit':
        reply(['success'=>true,'data'=>get_audit((int)($input['limit'] ?? 50))]);
        break;

    default:
        reply([
            'success'=>false,
            'message'=>'Unknown action',
            'available'=>['ping','rekening','cek_rekening','debit','kredit','transfer','mutasi','audit'],
        ], 400);
}
