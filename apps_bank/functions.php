<?php
require_once __DIR__ . '/config.php';

// =============================================
// Helper umum: baca/tulis file JSON
// =============================================
function read_json(string $path): array {
    if (!file_exists($path)) return [];
    $raw = file_get_contents($path);
    $data = json_decode($raw, true);
    return is_array($data) ? $data : [];
}
function write_json(string $path, array $data): bool {
    if (!is_dir(dirname($path))) @mkdir(dirname($path), 0777, true);
    return (bool) file_put_contents($path, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), LOCK_EX);
}
function format_rupiah($n): string { return 'Rp ' . number_format((float)$n, 0, ',', '.'); }
function relative_time(string $ts): string {
    $diff = time() - strtotime($ts);
    if ($diff < 60)    return $diff.'d lalu';
    if ($diff < 3600)  return floor($diff/60).'m lalu';
    if ($diff < 86400) return floor($diff/3600).'j lalu';
    return floor($diff/86400).'h lalu';
}

// =============================================
// HTTP client (cURL)
// =============================================
function http_get(string $url, int $timeout = 5): array {
    $ch = curl_init($url);
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER=>true, CURLOPT_TIMEOUT=>$timeout, CURLOPT_HTTPHEADER=>['Accept: application/json']]);
    $resp = curl_exec($ch); $err = curl_error($ch); curl_close($ch);
    if ($resp === false) return ['success'=>false,'message'=>'HTTP error: '.$err];
    $d = json_decode($resp, true);
    return is_array($d) ? $d : ['success'=>false,'message'=>'Invalid JSON','raw'=>$resp];
}
function http_post_json(string $url, array $payload, int $timeout = 5): array {
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER=>true, CURLOPT_POST=>true, CURLOPT_TIMEOUT=>$timeout,
        CURLOPT_POSTFIELDS=>json_encode($payload),
        CURLOPT_HTTPHEADER=>['Content-Type: application/json','Accept: application/json']
    ]);
    $resp = curl_exec($ch); $err = curl_error($ch); curl_close($ch);
    if ($resp === false) return ['success'=>false,'message'=>'HTTP error: '.$err];
    $d = json_decode($resp, true);
    return is_array($d) ? $d : ['success'=>false,'message'=>'Invalid JSON','raw'=>$resp];
}

// =============================================
// Audit log integrasi (siapa panggil siapa)
// =============================================
function audit_log(string $arah, string $peer, string $action, string $endpoint, bool $ok, string $msg = '', array $payload = []): void {
    $list = read_json(FILE_AUDIT);
    $list[] = [
        'id'        => 'AUD' . date('YmdHis') . rand(100,999),
        'arah'      => $arah,        // IN | OUT
        'peer'      => $peer,        // AppsBank/AppsEcommerce/...
        'action'    => $action,      // debit, ping, ...
        'endpoint'  => $endpoint,    // URL atau method
        'ok'        => $ok,
        'message'   => $msg,
        'payload'   => $payload,
        'tanggal'   => date('Y-m-d H:i:s'),
    ];
    if (count($list) > 500) $list = array_slice($list, -500);
    write_json(FILE_AUDIT, $list);
}
function get_audit(int $limit = 100): array {
    $list = read_json(FILE_AUDIT);
    $list = array_reverse($list);
    return array_slice($list, 0, $limit);
}

// =============================================
// Domain - BANK
// =============================================
function get_rekening(?string $username = null): array { 
    $list = read_json(FILE_REKENING); 
    if ($username) $list = array_values(array_filter($list, fn($r) => ($r['username'] ?? '') === $username));
    return $list;
}
function find_rekening(string $no_rek): ?array {
    foreach (read_json(FILE_REKENING) as $r) if ($r['no_rek'] === $no_rek) return $r;
    return null;
}

function update_saldo(string $no_rek, float $delta, string $tipe, string $keterangan, string $sumber = 'INTERNAL'): array {
    $rek = get_rekening();
    $found = false; $newSaldo = 0;
    foreach ($rek as &$r) {
        if ($r['no_rek'] === $no_rek) {
            $found = true;
            if ($tipe === 'DEBIT' && $r['saldo'] < $delta) {
                return ['success'=>false,'message'=>'Saldo tidak cukup','saldo'=>$r['saldo']];
            }
            $r['saldo'] = $tipe === 'DEBIT' ? $r['saldo'] - $delta : $r['saldo'] + $delta;
            $newSaldo = $r['saldo'];
            break;
        }
    }
    unset($r);
    if (!$found) return ['success'=>false,'message'=>'Rekening tidak ditemukan'];

    write_json(FILE_REKENING, $rek);
    $mut = read_json(FILE_MUTASI);
    $mut[] = [
        'id'         => 'MUT' . date('YmdHis') . rand(100,999),
        'no_rek'     => $no_rek,
        'tipe'       => $tipe,
        'jumlah'     => (float)$delta,
        'keterangan' => $keterangan,
        'sumber'     => $sumber,
        'tanggal'    => date('Y-m-d H:i:s'),
    ];
    write_json(FILE_MUTASI, $mut);

    return ['success'=>true,'message'=>'OK','saldo'=>$newSaldo];
}

function get_mutasi(?string $no_rek = null): array {
    $mut = read_json(FILE_MUTASI);
    if ($no_rek) $mut = array_values(array_filter($mut, fn($m) => $m['no_rek'] === $no_rek));
    usort($mut, fn($a,$b) => strcmp($b['tanggal'], $a['tanggal']));
    return $mut;
}

// Mutasi 7 hari terakhir per hari (untuk chart)
function mutasi_per_hari(int $hari = 7, ?array $my_reks = null): array {
    $mut = read_json(FILE_MUTASI);
    if ($my_reks !== null) {
        $mut = array_filter($mut, fn($m) => in_array($m['no_rek'], $my_reks));
    }
    $out = [];
    for ($i = $hari-1; $i >= 0; $i--) {
        $d = date('Y-m-d', strtotime("-$i day"));
        $out[$d] = ['debit'=>0,'kredit'=>0];
    }
    foreach ($mut as $m) {
        $d = substr($m['tanggal'], 0, 10);
        if (isset($out[$d])) {
            $k = $m['tipe'] === 'DEBIT' ? 'debit' : 'kredit';
            $out[$d][$k] += (float)$m['jumlah'];
        }
    }
    return $out;
}

// =============================================
// Kartu Debit (fitur tambahan)
// =============================================
function get_kartu(?string $username = null): array { 
    $list = read_json(FILE_KARTU); 
    if ($username) {
        $rek = array_column(get_rekening($username), 'no_rek');
        $list = array_values(array_filter($list, fn($k) => in_array($k['no_rek'], $rek)));
    }
    return $list;
}
function add_kartu(string $no_rek, string $tipe, float $limit_harian): array {
    $list = get_kartu();
    $no_kartu = '4321 ' . str_pad((string)rand(0,9999),4,'0',STR_PAD_LEFT) . ' ' . str_pad((string)rand(0,9999),4,'0',STR_PAD_LEFT) . ' ' . substr($no_rek, -4);
    $list[] = [
        'id'           => 'KRT' . date('YmdHis') . rand(10,99),
        'no_kartu'     => $no_kartu,
        'no_rek'       => $no_rek,
        'tipe'         => $tipe,
        'limit_harian' => $limit_harian,
        'aktif'        => true,
        'cvv'          => str_pad((string)rand(0,999),3,'0',STR_PAD_LEFT),
        'expired'      => date('m/y', strtotime('+3 year')),
        'dibuat'       => date('Y-m-d H:i:s'),
    ];
    write_json(FILE_KARTU, $list);
    return ['success'=>true,'data'=>end($list)];
}
function toggle_kartu(string $id): void {
    $list = get_kartu();
    foreach ($list as &$k) if ($k['id'] === $id) { $k['aktif'] = !$k['aktif']; break; }
    unset($k);
    write_json(FILE_KARTU, $list);
}

// =============================================
// Cek peer health
// =============================================
function ping_peers(): array {
    $peers = ['AppsEcommerce'=>ECOMMERCE_URL, 'AppsPendidikan'=>PENDIDIKAN_URL, 'AppsTravel'=>TRAVEL_URL];
    $out = [];
    foreach ($peers as $name => $url) {
        $start = microtime(true);
        $r = http_get($url . '/api.php?action=ping', 2);
        $latency = round((microtime(true) - $start) * 1000);
        $out[$name] = ['url'=>$url, 'up'=>!empty($r['success']), 'latency'=>$latency];
    }
    return $out;
}
