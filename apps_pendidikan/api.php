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
    case 'siswa':        reply(['success'=>true,'data'=>get_siswa()]); break;
    case 'cek_siswa':
        $s = find_siswa($input['id'] ?? '');
        if (!$s) reply(['success'=>false,'message'=>'Siswa tidak ditemukan'], 404);
        reply(['success'=>true,'data'=>$s]);
        break;
    case 'produk_siswa': reply(['success'=>true,'data'=>get_produk_siswa()]); break;
    case 'spp':          reply(['success'=>true,'data'=>get_spp()]); break;
    case 'prestasi':     reply(['success'=>true,'data'=>get_prestasi($input['siswa_id'] ?? null)]); break;
    case 'audit':        reply(['success'=>true,'data'=>get_audit((int)($input['limit']??50))]); break;
    default:
        reply(['success'=>false,'message'=>'Unknown action',
            'available'=>['ping','siswa','cek_siswa','produk_siswa','spp','prestasi','audit']], 400);
}
