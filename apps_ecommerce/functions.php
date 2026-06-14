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

// ===== Audit =====
function audit_log(string $arah, string $peer, string $action, string $endpoint, bool $ok, string $msg = '', array $payload = []): void {
    $list = read_json(FILE_AUDIT);
    $list[] = [
        'id'=>'AUD'.date('YmdHis').rand(100,999), 'arah'=>$arah, 'peer'=>$peer,
        'action'=>$action, 'endpoint'=>$endpoint, 'ok'=>$ok, 'message'=>$msg,
        'payload'=>$payload, 'tanggal'=>date('Y-m-d H:i:s'),
    ];
    if (count($list) > 500) $list = array_slice($list, -500);
    write_json(FILE_AUDIT, $list);
}
function get_audit(int $limit = 100): array {
    $list = array_reverse(read_json(FILE_AUDIT));
    return array_slice($list, 0, $limit);
}

// ===== Domain - PRODUK =====
function get_produk(): array { return read_json(FILE_PRODUK); }
function find_produk(string $id): ?array {
    foreach (get_produk() as $p) if ($p['id'] === $id) return $p;
    return null;
}
function add_produk(array $p): array {
    $list = get_produk();
    $p['id']     = $p['id']     ?? 'P' . date('YmdHis') . rand(10,99);
    $p['stok']   = (int)($p['stok'] ?? 1);
    $p['harga']  = (float)($p['harga'] ?? 0);
    $p['kategori']= $p['kategori'] ?? 'umum';
    $p['sumber'] = $p['sumber']  ?? 'INTERNAL';
    $p['rating'] = $p['rating']  ?? 4.5;
    $p['terjual']= $p['terjual'] ?? 0;
    $p['dibuat'] = date('Y-m-d H:i:s');
    $list[] = $p;
    write_json(FILE_PRODUK, $list);
    return ['success'=>true,'data'=>$p];
}
function kurangi_stok(string $id, int $qty): void {
    $list = get_produk();
    foreach ($list as &$p) if ($p['id'] === $id) {
        $p['stok']    = max(0, (int)$p['stok'] - $qty);
        $p['terjual']= (int)($p['terjual'] ?? 0) + $qty;
        break;
    }
    unset($p);
    write_json(FILE_PRODUK, $list);
}

// ===== Keranjang =====
function get_keranjang(string $user='guest'): array { return read_json(FILE_KERANJANG)[$user] ?? []; }
function add_keranjang(string $user, string $produk_id, int $qty=1): void {
    $all = read_json(FILE_KERANJANG);
    $cart = $all[$user] ?? [];
    $found = false;
    foreach ($cart as &$it) if ($it['produk_id']===$produk_id) { $it['qty']+=$qty; $found=true; break; }
    unset($it);
    if (!$found) $cart[] = ['produk_id'=>$produk_id,'qty'=>$qty];
    $all[$user] = $cart;
    write_json(FILE_KERANJANG, $all);
}
function clear_keranjang(string $user): void {
    $all = read_json(FILE_KERANJANG); unset($all[$user]); write_json(FILE_KERANJANG, $all);
}
function hapus_item_keranjang(string $user, string $produk_id): void {
    $all = read_json(FILE_KERANJANG);
    $cart = $all[$user] ?? [];
    $cart = array_values(array_filter($cart, fn($it)=>$it['produk_id']!==$produk_id));
    $all[$user] = $cart;
    write_json(FILE_KERANJANG, $all);
}

// ===== Wishlist (fitur tambahan) =====
function get_wishlist(string $user='guest'): array { return read_json(FILE_WISHLIST)[$user] ?? []; }
function toggle_wishlist(string $user, string $produk_id): void {
    $all = read_json(FILE_WISHLIST);
    $list = $all[$user] ?? [];
    if (in_array($produk_id, $list)) $list = array_values(array_filter($list, fn($x)=>$x!==$produk_id));
    else $list[] = $produk_id;
    $all[$user] = $list;
    write_json(FILE_WISHLIST, $all);
}

// ===== Review (fitur tambahan) =====
function get_review(?string $produk_id = null): array {
    $list = read_json(FILE_REVIEW);
    if ($produk_id) $list = array_values(array_filter($list, fn($r)=>$r['produk_id']===$produk_id));
    usort($list, fn($a,$b)=>strcmp($b['tanggal']??'', $a['tanggal']??''));
    return $list;
}
function add_review(string $produk_id, string $user, int $star, string $komentar): void {
    $list = read_json(FILE_REVIEW);
    $list[] = [
        'id'=>'REV'.date('YmdHis').rand(10,99),
        'produk_id'=>$produk_id, 'user'=>$user, 'star'=>max(1,min(5,$star)),
        'komentar'=>$komentar, 'tanggal'=>date('Y-m-d H:i:s'),
    ];
    write_json(FILE_REVIEW, $list);
}

// ===== Pesanan =====
function get_pesanan(): array {
    $p = read_json(FILE_PESANAN);
    usort($p, fn($a,$b)=>strcmp($b['tanggal']??'', $a['tanggal']??''));
    return $p;
}
function simpan_pesanan(array $p): array {
    $list = get_pesanan();
    $p['id']      = $p['id']      ?? 'PSN'.date('YmdHis').rand(10,99);
    $p['tanggal'] = $p['tanggal'] ?? date('Y-m-d H:i:s');
    $list[] = $p;
    write_json(FILE_PESANAN, $list);
    return $p;
}

// ===== Business logic =====
function proses_checkout(string $user, string $no_rek, array $items, string $kode_voucher = '', float $diskon_pct = 0): array {
    if (!$no_rek || !$items) return ['success'=>false,'message'=>'Parameter tidak lengkap'];
    $total = 0; $detail = [];
    foreach ($items as $it) {
        $p = find_produk($it['produk_id'] ?? '');
        if (!$p) return ['success'=>false,'message'=>'Produk tidak ditemukan: '.($it['produk_id']??'')];
        $qty = max(1, (int)($it['qty'] ?? 1));
        $sub = $qty * (float)$p['harga'];
        $total += $sub;
        $detail[] = ['produk_id'=>$p['id'],'nama'=>$p['nama'],'qty'=>$qty,'harga'=>$p['harga'],'sub'=>$sub];
    }
    $diskon = 0;
    if ($diskon_pct > 0) $diskon = $total * $diskon_pct / 100;
    $final = $total - $diskon;

    $bay = bayar_via_bank($no_rek, $final, 'Pembayaran ecommerce');
    if (empty($bay['success'])) return ['success'=>false,'message'=>'Gagal bayar: '.($bay['message']??''),'detail'=>$bay];
    foreach ($detail as $d) kurangi_stok($d['produk_id'], $d['qty']);
    $psn = simpan_pesanan([
        'user'=>$user,'no_rek'=>$no_rek,'items'=>$detail,
        'subtotal'=>$total,'diskon'=>$diskon,'voucher'=>$kode_voucher,
        'total'=>$final,'status'=>'LUNAS','metode'=>'BANK',
    ]);
    clear_keranjang($user);
    return ['success'=>true,'message'=>'Checkout sukses','pesanan'=>$psn,'saldo_baru'=>$bay['saldo']??null];
}

// ===== Integrasi peer =====
function fetch_tiket_travel(): array {
    $r = http_get(TRAVEL_URL . '/api.php?action=tiket', 3);
    audit_log('OUT', 'AppsTravel', 'tiket', TRAVEL_URL.'/api.php?action=tiket', !empty($r['success']));
    return $r['data'] ?? [];
}
function fetch_rekening_bank(): array {
    $r = http_get(BANK_URL . '/api.php?action=rekening', 3);
    return $r['data'] ?? [];
}
function bayar_via_bank(string $no_rek, float $jumlah, string $keterangan): array {
    $r = http_post_json(BANK_URL . '/api.php?action=debit', [
        'no_rek'=>$no_rek,'jumlah'=>$jumlah,'keterangan'=>$keterangan,'sumber'=>SISTEM_NAMA,
    ]);
    audit_log('OUT', 'AppsBank', 'debit', BANK_URL.'/api.php?action=debit',
        !empty($r['success']),
        ($r['success']??false) ? "Debit $no_rek " . format_rupiah($jumlah) . " sukses" : ($r['message']??''),
        ['no_rek'=>$no_rek,'jumlah'=>$jumlah]);
    return $r;
}
function pesan_tiket_travel(array $payload): array {
    $r = http_post_json(TRAVEL_URL . '/api.php?action=pesan_tiket', $payload + ['sumber'=>SISTEM_NAMA]);
    audit_log('OUT','AppsTravel','pesan_tiket',TRAVEL_URL.'/api.php?action=pesan_tiket',
        !empty($r['success']), $r['message']??'', $payload);
    return $r;
}
function ping_peers(): array {
    $peers = ['AppsBank'=>BANK_URL, 'AppsPendidikan'=>PENDIDIKAN_URL, 'AppsTravel'=>TRAVEL_URL];
    $out = [];
    foreach ($peers as $name => $url) {
        $start = microtime(true);
        $r = http_get($url . '/api.php?action=ping', 2);
        $latency = round((microtime(true) - $start) * 1000);
        $out[$name] = ['url'=>$url, 'up'=>!empty($r['success']), 'latency'=>$latency];
    }
    return $out;
}
