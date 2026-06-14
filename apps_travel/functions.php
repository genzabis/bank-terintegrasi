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

// Domain - Tiket
function get_tiket(): array { return read_json(FILE_TIKET); }
function find_tiket(string $id): ?array {
    foreach (get_tiket() as $t) if ($t['id']===$id) return $t;
    return null;
}
function add_tiket(array $t): array {
    $list = get_tiket();
    $t['id']     = $t['id'] ?? 'T'.date('YmdHis').rand(10,99);
    $t['dibuat'] = date('Y-m-d H:i:s');
    $list[] = $t;
    write_json(FILE_TIKET, $list);
    return $t;
}
function kurangi_kuota_tiket(string $id, int $qty): void {
    $list = get_tiket();
    foreach ($list as &$t) if ($t['id']===$id) { $t['kuota']=max(0,(int)$t['kuota']-$qty); break; }
    unset($t);
    write_json(FILE_TIKET, $list);
}

// Hotel
function get_hotel(): array { return read_json(FILE_HOTEL); }
function find_hotel(string $id): ?array {
    foreach (get_hotel() as $h) if ($h['id']===$id) return $h;
    return null;
}

// Paket wisata (fitur tambahan)
function get_paket(): array { return read_json(FILE_PAKET); }
function find_paket(string $id): ?array {
    foreach (get_paket() as $p) if ($p['id']===$id) return $p;
    return null;
}

// Pesanan
function get_pesanan(?string $jenis = null): array {
    $p = read_json(FILE_PESANAN);
    if ($jenis) $p = array_values(array_filter($p, fn($x)=>($x['jenis']??'')===$jenis));
    usort($p, fn($a,$b)=>strcmp($b['tanggal']??'', $a['tanggal']??''));
    return $p;
}
function simpan_pesanan(array $p): array {
    $list = get_pesanan();
    $p['id']      = $p['id']      ?? 'TPS'.date('YmdHis').rand(10,99);
    $p['tanggal'] = $p['tanggal'] ?? date('Y-m-d H:i:s');
    $list[] = $p;
    write_json(FILE_PESANAN, $list);
    return $p;
}

// Voucher
function get_voucher(): array { return read_json(FILE_VOUCHER); }
function find_voucher(string $kode): ?array {
    foreach (get_voucher() as $v) if (strcasecmp($v['kode'],$kode)===0) return $v;
    return null;
}
function add_voucher(array $v): array {
    $list = get_voucher();
    $v['kode']    = strtoupper(trim($v['kode']));
    $v['persen']  = (int)$v['persen'];
    $v['dipakai'] = 0;
    $v['dibuat']  = date('Y-m-d H:i:s');
    foreach ($list as $exist) if (strcasecmp($exist['kode'],$v['kode'])===0)
        return ['success'=>false,'message'=>'Kode voucher sudah ada'];
    $list[] = $v;
    write_json(FILE_VOUCHER, $list);
    return ['success'=>true,'data'=>$v];
}
function tandai_voucher_dipakai(string $kode): void {
    $list = get_voucher();
    foreach ($list as &$v) if (strcasecmp($v['kode'],$kode)===0) {
        $v['dipakai'] = (int)($v['dipakai']??0) + 1;
        $v['terakhir_pakai'] = date('Y-m-d H:i:s');
        break;
    }
    unset($v);
    write_json(FILE_VOUCHER, $list);
}

// Business logic
function proses_pesan_tiket(string $tiket_id, int $qty, string $pemesan, ?string $no_rek, ?string $kode_voucher, float $diskonManual, string $sumber): array {
    $t = find_tiket($tiket_id);
    if (!$t) return ['success'=>false,'message'=>'Tiket tidak ditemukan'];
    if ((int)$t['kuota'] < $qty) return ['success'=>false,'message'=>'Kuota tiket tidak cukup'];
    $hargaTotal = (float)$t['harga'] * $qty;
    $diskon = 0; $voucherTerpakai = null;
    if ($kode_voucher) {
        $v = find_voucher($kode_voucher);
        if (!$v) return ['success'=>false,'message'=>'Voucher tidak valid'];
        $diskon = (float)$v['persen'];
        $voucherTerpakai = $v['kode'];
    } elseif ($diskonManual > 0) $diskon = $diskonManual;
    $totalBayar = $hargaTotal - ($hargaTotal * $diskon / 100);
    if ($no_rek) {
        $bay = bayar_via_bank($no_rek, $totalBayar, "Tiket {$t['nama']} x{$qty}");
        if (empty($bay['success'])) return ['success'=>false,'message'=>'Gagal bayar: '.($bay['message']??''),'detail'=>$bay];
    }
    kurangi_kuota_tiket($tiket_id, $qty);
    if ($voucherTerpakai) tandai_voucher_dipakai($voucherTerpakai);
    $kode = 'TKT-'.strtoupper(substr(md5($tiket_id.microtime()),0,6));
    $psn = simpan_pesanan([
        'jenis'=>'TIKET','tiket_id'=>$tiket_id,'nama'=>$t['nama'],'qty'=>$qty,
        'harga_satuan'=>$t['harga'],'subtotal'=>$hargaTotal,'diskon_persen'=>$diskon,
        'total'=>$totalBayar,'pemesan'=>$pemesan,'no_rek'=>$no_rek,
        'voucher'=>$voucherTerpakai,'sumber'=>$sumber,'kode'=>$kode,
        'status'=>$no_rek?'LUNAS':'TERBOOKING',
    ]);
    return ['success'=>true,'message'=>'Pemesanan tiket sukses','data'=>$psn];
}
function proses_pesan_hotel(string $hotel_id, int $malam, string $pemesan, ?string $no_rek): array {
    $h = find_hotel($hotel_id);
    if (!$h) return ['success'=>false,'message'=>'Hotel tidak ditemukan'];
    $total = (float)$h['harga_per_malam'] * $malam;
    if ($no_rek) {
        $bay = bayar_via_bank($no_rek, $total, "Hotel {$h['nama']} x{$malam} malam");
        if (empty($bay['success'])) return ['success'=>false,'message'=>'Gagal bayar: '.($bay['message']??'')];
    }
    $kode = 'HTL-'.strtoupper(substr(md5($hotel_id.microtime()),0,6));
    $psn = simpan_pesanan([
        'jenis'=>'HOTEL','hotel_id'=>$hotel_id,'nama'=>$h['nama'],'malam'=>$malam,
        'total'=>$total,'pemesan'=>$pemesan,'no_rek'=>$no_rek,'kode'=>$kode,
        'status'=>$no_rek?'LUNAS':'TERBOOKING',
    ]);
    return ['success'=>true,'message'=>'Booking hotel sukses','data'=>$psn];
}
function proses_pesan_paket(string $paket_id, string $pemesan, string $no_rek): array {
    $p = find_paket($paket_id);
    if (!$p) return ['success'=>false,'message'=>'Paket tidak ditemukan'];
    $total = (float)$p['harga'];
    $bay = bayar_via_bank($no_rek, $total, "Paket Wisata {$p['nama']}");
    if (empty($bay['success'])) return ['success'=>false,'message'=>'Gagal bayar: '.($bay['message']??'')];
    $kode = 'PKT-'.strtoupper(substr(md5($paket_id.microtime()),0,6));
    $psn = simpan_pesanan([
        'jenis'=>'PAKET','paket_id'=>$paket_id,'nama'=>$p['nama'],
        'total'=>$total,'pemesan'=>$pemesan,'no_rek'=>$no_rek,
        'kode'=>$kode,'status'=>'LUNAS','include'=>$p['include']??[],
    ]);
    return ['success'=>true,'message'=>'Booking paket sukses','data'=>$psn];
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
function fetch_produk_ecommerce(): array {
    $r = http_get(ECOMMERCE_URL.'/api.php?action=produk', 3);
    return $r['data'] ?? [];
}
function fetch_rekening_bank(): array {
    $r = http_get(BANK_URL.'/api.php?action=rekening', 3);
    return $r['data'] ?? [];
}
function ping_peers(): array {
    $peers = ['AppsBank'=>BANK_URL,'AppsEcommerce'=>ECOMMERCE_URL,'AppsPendidikan'=>PENDIDIKAN_URL];
    $out = [];
    foreach ($peers as $name=>$url) {
        $start=microtime(true);
        $r = http_get($url.'/api.php?action=ping', 2);
        $latency = round((microtime(true)-$start)*1000);
        $out[$name] = ['url'=>$url,'up'=>!empty($r['success']),'latency'=>$latency];
    }
    return $out;
}
