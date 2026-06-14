<?php
require_once __DIR__ . '/config.php';

function read_json(string $path): array {
    if (!file_exists($path)) return [];
    $d = json_decode(file_get_contents($path), true);
    return is_array($d) ? $d : [];
}
function write_json(string $path, array $data): bool {
    if (!is_dir(dirname($path))) @mkdir(dirname($path), 0777, true);
    return (bool) file_put_contents($path, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}
function format_rupiah($n): string { return 'Rp ' . number_format((float)$n, 0, ',', '.'); }
function relative_time(string $ts): string {
    $diff = time() - strtotime($ts);
    if ($diff < 60)    return $diff.'d lalu';
    if ($diff < 3600)  return floor($diff/60).'m lalu';
    if ($diff < 86400) return floor($diff/3600).'j lalu';
    return floor($diff/86400).'h lalu';
}
function inisial(string $nama): string {
    $parts = preg_split('/\s+/', trim($nama));
    $a = mb_substr($parts[0] ?? '', 0, 1);
    $b = mb_substr($parts[1] ?? '', 0, 1);
    return strtoupper($a . $b ?: $a);
}

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

// Audit
function audit_log(string $arah, string $peer, string $action, string $endpoint, bool $ok, string $msg = '', array $payload = []): void {
    $list = read_json(FILE_AUDIT);
    $list[] = ['id'=>'AUD'.date('YmdHis').rand(100,999),'arah'=>$arah,'peer'=>$peer,'action'=>$action,
        'endpoint'=>$endpoint,'ok'=>$ok,'message'=>$msg,'payload'=>$payload,'tanggal'=>date('Y-m-d H:i:s')];
    if (count($list)>500) $list=array_slice($list,-500);
    write_json(FILE_AUDIT, $list);
}
function get_audit(int $limit=100): array { return array_slice(array_reverse(read_json(FILE_AUDIT)), 0, $limit); }

// Domain
function get_siswa(): array { return read_json(FILE_SISWA); }
function find_siswa(string $id): ?array {
    foreach (get_siswa() as $s) if ($s['id']===$id) return $s;
    return null;
}
function add_siswa(array $s): array {
    $list = get_siswa();
    $s['id']     = $s['id']     ?? 'S'.str_pad((string)(count($list)+1),4,'0',STR_PAD_LEFT);
    $s['dibuat'] = date('Y-m-d H:i:s');
    $list[] = $s;
    write_json(FILE_SISWA, $list);
    return $s;
}

function get_spp(): array { return read_json(FILE_SPP); }
function tambah_tagihan_spp(string $siswa_id, float $jumlah, string $bulan): array {
    $list = get_spp();
    $data = [
        'id'=>'SPP'.date('YmdHis').rand(10,99),
        'siswa_id'=>$siswa_id,'bulan'=>$bulan,'jumlah'=>$jumlah,
        'status'=>'BELUM','dibuat'=>date('Y-m-d H:i:s'),
    ];
    $list[] = $data;
    write_json(FILE_SPP, $list);
    return $data;
}
function set_spp_status(string $id, string $status, ?string $no_rek = null): void {
    $list = get_spp();
    foreach ($list as &$s) if ($s['id']===$id) {
        $s['status']=$status; $s['dibayar_via']=$no_rek; $s['waktu_bayar']=date('Y-m-d H:i:s'); break;
    }
    unset($s);
    write_json(FILE_SPP, $list);
}

function get_produk_siswa(): array { return read_json(FILE_PRODUK_SISWA); }
function add_produk_siswa(array $p): array {
    $list = get_produk_siswa();
    $p['id']     = $p['id']     ?? 'PS'.date('YmdHis').rand(10,99);
    $p['dibuat'] = date('Y-m-d H:i:s');
    $list[] = $p;
    write_json(FILE_PRODUK_SISWA, $list);
    return $p;
}
function set_produk_siswa_field(string $id, string $field, $value): void {
    $list = get_produk_siswa();
    foreach ($list as &$p) if ($p['id']===$id) { $p[$field]=$value; break; }
    unset($p);
    write_json(FILE_PRODUK_SISWA, $list);
}

// ===== Prestasi (fitur tambahan) =====
function get_prestasi(?string $siswa_id = null): array {
    $list = read_json(FILE_PRESTASI);
    if ($siswa_id) $list = array_values(array_filter($list, fn($p)=>$p['siswa_id']===$siswa_id));
    usort($list, fn($a,$b)=>strcmp($b['tanggal']??'', $a['tanggal']??''));
    return $list;
}
function add_prestasi(string $siswa_id, string $judul, string $tingkat, string $tanggal): array {
    $list = read_json(FILE_PRESTASI);
    $data = [
        'id'=>'PRS'.date('YmdHis').rand(10,99),
        'siswa_id'=>$siswa_id,'judul'=>$judul,'tingkat'=>$tingkat,'tanggal'=>$tanggal,
        'dibuat'=>date('Y-m-d H:i:s'),
    ];
    $list[] = $data;
    write_json(FILE_PRESTASI, $list);
    return $data;
}

// Diskon kode
function generate_kode_diskon(string $siswa_id, int $persen): string {
    return 'EDU-'.substr(strtoupper(md5($siswa_id.time().rand())),0,6).'-'.$persen;
}

// Integrasi
function bayar_via_bank(string $no_rek, float $jumlah, string $keterangan): array {
    $r = http_post_json(BANK_URL.'/api.php?action=debit', [
        'no_rek'=>$no_rek,'jumlah'=>$jumlah,'keterangan'=>$keterangan,'sumber'=>SISTEM_NAMA,
    ]);
    audit_log('OUT','AppsBank','debit',BANK_URL.'/api.php?action=debit',
        !empty($r['success']),
        ($r['success']??false)?"Debit $no_rek ".format_rupiah($jumlah):($r['message']??''),
        ['no_rek'=>$no_rek,'jumlah'=>$jumlah]);
    return $r;
}
function upload_produk_ke_ecommerce(array $p): array {
    $payload = [
        'nama'=>$p['nama'],'harga'=>$p['harga'],'stok'=>$p['stok']??1,
        'kategori'=>'siswa','deskripsi'=>'Karya siswa: '.($p['siswa_nama']??'-'),
        'sumber'=>SISTEM_NAMA,'asal_id'=>$p['id'],
    ];
    $r = http_post_json(ECOMMERCE_URL.'/api.php?action=add_produk', $payload);
    audit_log('OUT','AppsEcommerce','add_produk',ECOMMERCE_URL.'/api.php?action=add_produk',
        !empty($r['success']), $r['success']??false ? 'Produk terdaftar id='.($r['data']['id']??'-') : ($r['message']??''),
        $payload);
    return $r;
}
function daftar_diskon_travel(string $kode, int $persen, string $siswa_id, string $siswa_nama): array {
    $payload = ['kode'=>$kode,'persen'=>$persen,'sumber'=>SISTEM_NAMA,'untuk'=>$siswa_nama,'siswa_id'=>$siswa_id];
    $r = http_post_json(TRAVEL_URL.'/api.php?action=tambah_voucher', $payload);
    audit_log('OUT','AppsTravel','tambah_voucher',TRAVEL_URL.'/api.php?action=tambah_voucher',
        !empty($r['success']), 'Voucher '.$kode.' '.$persen.'%', $payload);
    return $r;
}
function ping_peers(): array {
    $peers = ['AppsBank'=>BANK_URL,'AppsEcommerce'=>ECOMMERCE_URL,'AppsTravel'=>TRAVEL_URL];
    $out = [];
    foreach ($peers as $name=>$url) {
        $start=microtime(true);
        $r = http_get($url.'/api.php?action=ping', 2);
        $latency = round((microtime(true)-$start)*1000);
        $out[$name] = ['url'=>$url,'up'=>!empty($r['success']),'latency'=>$latency];
    }
    return $out;
}
