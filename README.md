# Dokumentasi AppsDistribusi: Sistem Terdistribusi Multi-Aplikasi

Proyek ini adalah implementasi tugas Komputasi Paralel dan Terdistribusi yang melibatkan 4 aplikasi PHP independen yang saling terhubung satu sama lain menggunakan REST API dengan payload data berformat JSON dalam topologi full-mesh, tanpa adanya server pusat atau single point of failure (SPOF).

## Daftar Isi
1. Deskripsi Proyek
2. Sistem yang Terlibat
3. Akun Demo untuk Pengujian
4. Arsitektur dan Alur Komunikasi
5. Persyaratan Sistem
6. Langkah-Langkah Instalasi
7. Cara Menjalankan Sistem
8. Skenario Uji Coba dan Demo (Langkah demi Langkah)
9. Integrasi Data dan REST API Endpoints
10. Struktur Direktori dan Data Penyimpanan
11. Catatan Teknis dan Penanganan Deadlock

---

## 1. Deskripsi Proyek
AppsDistribusi adalah proyek integrasi sistem terdistribusi peer-to-peer. Proyek ini terdiri dari 4 aplikasi web berbasis PHP native yang saling berkomunikasi secara dua arah menggunakan REST API via pustaka cURL. Database dari setiap aplikasi menggunakan file JSON lokal yang disimpan dalam folder masing-masing aplikasi, sehingga sistem ini sangat ringan dan tidak memerlukan instalasi server basis data RDBMS terpisah seperti MySQL.

---

## 2. Sistem yang Terlibat

| Sistem | Aplikasi | Port | Tema Visual | Fungsi Utama |
|---|---|---|---|---|
| A | AppsBank | 8000 | Cyan-Blue | Manajemen rekening, mutasi saldo, transaksi debit/kredit/transfer, dan penerbitan kartu debit. |
| B | AppsEcommerce | 8001 | Amber | Marketplace, keranjang belanja, checkout barang, wishlist, ulasan produk, dan bundling produk. |
| C | AppsPendidikan | 8002 | Emerald | Data siswa, status SPP, pencatatan prestasi, upload produk karya siswa, dan voucher diskon travel. |
| D | AppsTravel | 8003 | Magenta | Penjualan tiket perjalanan, pemesanan hotel, paket wisata, dan pengelolaan voucher diskon. |

Setiap sistem dapat diakses secara lokal menggunakan port yang telah ditentukan di atas.

---

## 3. Akun Demo untuk Pengujian
Setiap aplikasi menggunakan kredensial yang sama untuk login admin dan user demo:

* **Administrator**
  * Username: `admin`
  * Password: `admin123`
* **Demo User**
  * Username: `user`
  * Password: `user123`

---

## 4. Arsitektur dan Alur Komunikasi
Komunikasi data dilakukan secara langsung antar aplikasi (peer-to-peer) tanpa adanya perantara/broker data (No Hub/Broker).

### Bagan Arsitektur (Full-Mesh)
```
                    AppsDistribusi - Full-Mesh REST API
                    ====================================

       ┌─────────────────────┐         ┌─────────────────────┐
       │   AppsBank (A)      │<────────┤  AppsEcommerce (B)  │
       │   localhost:8000    │  debit  │  localhost:8001     │
       │                     │  json   │                     │
       │ - rekening.json     │         │ - produk.json       │
       │ - mutasi.json       │         │ - keranjang.json    │
       └──┬──────────────┬───┘         │ - pesanan.json      │
          │              │             └──┬──────────────┬───┘
   debit  │              │ debit          │              │
   json   │              │ json           │ pesan_tiket  │
          │              │                │ bundle json  │
          ▼              ▼                ▼              ▼
       ┌──┴──────────────┴───┐         ┌──────────────────┴──┐
       │ AppsPendidikan (C)  │────────>│   AppsTravel (D)    │
       │ localhost:8002      │ voucher │   localhost:8003    │
       │                     │ json    │                     │
       │ - siswa.json        │         │ - tiket.json        │
       │ - spp.json          │         │ - hotel.json        │
       │ - produk_siswa.json │────────>│ - voucher.json      │
       └─────────────────────┘ produk  │ - pesanan.json      │
                               json     └─────────────────────┘
                                        (Pendidikan->Ecommerce
                                         kirim produk siswa)
```

Setiap anak panah menggambarkan panggilan REST API (HTTP + JSON via cURL).

---

## 5. Persyaratan Sistem
Sebelum menjalankan aplikasi, pastikan komputer Anda memenuhi syarat berikut:
1. **PHP versi 8.0 atau yang lebih baru** terpasang pada komputer Anda.
2. **Pustaka cURL PHP** dalam keadaan aktif (diperlukan untuk melakukan integrasi antar aplikasi).
3. **Sistem Operasi**: Windows (disarankan agar dapat menggunakan file otomatisasi `.bat`) atau Linux/macOS.

### Cara Mengaktifkan Ekstensi cURL di PHP:
1. Buka file konfigurasi `php.ini` Anda (jika menggunakan XAMPP di Windows, biasanya terletak di `C:\xampp\php\php.ini`).
2. Cari baris berikut: `;extension=curl`
3. Hapus tanda titik koma (`;`) di awal baris tersebut sehingga menjadi: `extension=curl`
4. Simpan file `php.ini` dan restart web server PHP Anda.

---

## 6. Langkah-Langkah Instalasi
1. Pindahkan atau ekstrak folder proyek `appsdistribusi` ke dalam folder root server Anda (misalnya di `d:\xampp\htdocs\appsdistribusi`).
2. Pastikan file php.ini Anda sudah memiliki ekstensi cURL aktif seperti yang dijelaskan pada bagian Persyaratan Sistem.
3. Hak Akses Tulis: Karena database menggunakan penyimpanan file JSON lokal, pastikan folder `data` pada setiap sistem memiliki hak akses membaca dan menulis (read/write). Folder tersebut adalah:
   - `apps_bank/data/`
   - `apps_ecommerce/data/`
   - `apps_pendidikan/data/`
   - `apps_travel/data/`
4. Tidak diperlukan setup basis data SQL seperti MySQL atau MariaDB karena semua data telah disimpan di dalam file JSON bawaan.

---

## 7. Cara Menjalankan Sistem

### Metode A: Menjalankan Otomatis (Khusus Windows)
1. Buka File Explorer dan masuk ke direktori `d:\xampp\htdocs\appsdistribusi`.
2. Klik dua kali file `start_all.bat`.
3. File batch tersebut akan:
   - Menetapkan variabel lingkungan `PHP_CLI_SERVER_WORKERS=4` untuk mengaktifkan proses paralel multi-worker bawaan PHP server agar tidak terjadi deadlock.
   - Membuka 4 terminal Command Prompt (CMD) secara otomatis untuk melayani port 8000, 8001, 8002, dan 8003.
   - Menunggu 3 detik sampai semua server siap menerima request.
   - Membuka browser bawaan ke 4 alamat URL aplikasi terdistribusi.

### Metode B: Menjalankan Manual (Windows, Linux, macOS)
Buka 4 terminal atau tab command line terpisah, atur environment variable `PHP_CLI_SERVER_WORKERS=4`, kemudian jalankan perintah berikut:

.\start_all.bat


* **Terminal 1: AppsBank (Port 8000)**
  ```bash
  # Windows CMD
  set PHP_CLI_SERVER_WORKERS=4
  cd apps_bank && php -S localhost:8000

  # Linux / macOS
  export PHP_CLI_SERVER_WORKERS=4
  cd apps_bank && php -S localhost:8000
  ```
* **Terminal 2: AppsEcommerce (Port 8001)**
  ```bash
  # Windows CMD
  set PHP_CLI_SERVER_WORKERS=4
  cd apps_ecommerce && php -S localhost:8001

  # Linux / macOS
  export PHP_CLI_SERVER_WORKERS=4
  cd apps_ecommerce && php -S localhost:8001
  ```
* **Terminal 3: AppsPendidikan (Port 8002)**
  ```bash
  # Windows CMD
  set PHP_CLI_SERVER_WORKERS=4
  cd apps_pendidikan && php -S localhost:8002

  # Linux / macOS
  export PHP_CLI_SERVER_WORKERS=4
  cd apps_pendidikan && php -S localhost:8002
  ```
* **Terminal 4: AppsTravel (Port 8003)**
  ```bash
  # Windows CMD
  set PHP_CLI_SERVER_WORKERS=4
  cd apps_travel && php -S localhost:8003

  # Linux / macOS
  export PHP_CLI_SERVER_WORKERS=4
  cd apps_travel && php -S localhost:8003
  ```

### Cara Mematikan Layanan Server:
* **Windows (Otomatis)**: Klik dua kali file `stop_all.bat` di dalam folder utama proyek, yang akan mematikan semua proses server `php.exe`.
* **Manual**: Tekan tombol `Ctrl + C` pada masing-masing terminal yang sedang berjalan.

---

## 8. Skenario Uji Coba dan Demo (Langkah demi Langkah)

### Skenario 1: Pembayaran Belanja AppsEcommerce via Saldo AppsBank
1. Buka browser dan akses halaman AppsEcommerce di `http://localhost:8001`.
2. Klik tombol "+Cart" pada salah satu produk untuk memasukkannya ke dalam keranjang belanja.
3. Buka menu **Keranjang** dan klik tombol **Lanjut Checkout**.
4. Di halaman checkout, pilih nomor rekening pembayar dari dropdown (daftar rekening diakses secara langsung dari AppsBank menggunakan API).
5. Klik **Bayar via Bank**.
6. Buka halaman AppsBank di `http://localhost:8000`. Cek informasi saldo rekening pembayar yang telah terpotong, dan pastikan riwayat mutasi baru dengan keterangan dari "AppsEcommerce" telah tercatat.

### Skenario 2: Publikasi Karya Siswa dari AppsPendidikan ke AppsEcommerce
1. Buka browser dan akses halaman AppsPendidikan di `http://localhost:8002`.
2. Masuk ke menu **Produk Karya** dan tambahkan produk karya baru (masukkan nama produk, harga, stok, dan pilih siswa pembuatnya).
3. Setelah produk tersimpan, pada daftar produk klik tombol **Upload**.
4. AppsPendidikan akan mengirim data produk ke AppsEcommerce melalui REST API.
5. Periksa AppsEcommerce di `http://localhost:8001`. Produk baru karya siswa akan muncul di halaman etalase dengan label "AppsPendidikan" dan kategori "siswa".

### Skenario 3: Pembelian Paket Bundling Belanja + Tiket Wisata
1. Akses halaman bundle di `http://localhost:8001/pages/bundle.php`.
2. Pilih produk fisik dari Ecommerce dan tiket perjalanan dari Travel (data tiket dibaca secara langsung dari AppsTravel melalui API).
3. Tentukan nomor rekening bank pembayar dan lakukan checkout.
4. Ecommerce akan mengirim transaksi pemotongan saldo ke AppsBank sekaligus pemesanan tiket dengan potongan diskon 15% ke AppsTravel secara otomatis.
5. Akses AppsTravel di `http://localhost:8003` dan buka menu **Riwayat** untuk memeriksa detail pemesanan tiket dengan tipe transaksi bundle.

### Skenario 4: Penerbitan Voucher Diskon Siswa untuk Pemesanan di AppsTravel
1. Buka halaman diskon travel di AppsPendidikan `http://localhost:8002/pages/diskon_travel.php`.
2. Pilih nama siswa, tentukan persentase diskon yang ingin diberikan, dan klik **Generate & Kirim ke Travel**.
3. Sistem Pendidikan akan menghasilkan kode voucher berformat `EDU-XXXXXX-NN` dan mendaftarkannya ke AppsTravel via API POST.
4. Salin kode voucher tersebut, gunakan untuk memesan tiket perjalanan di AppsTravel, atau gunakan pada transaksi belanja di AppsEcommerce untuk memperoleh potongan harga.

---

## 9. Integrasi Data dan REST API Endpoints

### Matriks Integrasi Data Antar Sistem (Full-Mesh)

| No | Pengirim (Dari) | Penerima (Ke) | Endpoint REST API Tujuan | Parameter / Payload Request |
|---|---|---|---|---|
| 1 | Ecommerce | Bank | POST `:8000/api.php?action=debit` | `no_rek`, `jumlah`, `keterangan` |
| 2 | Pendidikan | Bank | POST `:8000/api.php?action=debit` | `no_rek`, `jumlah`, `keterangan` |
| 3 | Travel | Bank | POST `:8000/api.php?action=debit` | `no_rek`, `jumlah`, `keterangan` |
| 4 | Pendidikan | Ecommerce | POST `:8001/api.php?action=add_produk` | `nama`, `harga`, `stok`, kategori="siswa" |
| 5 | Ecommerce | Travel | GET `:8003/api.php?action=tiket` | (Membaca daftar tiket secara live) |
| 6 | Ecommerce | Travel | POST `:8003/api.php?action=pesan_tiket` | `tiket_id`, `qty`, `pemesan` |
| 7 | Ecommerce | Travel | POST `:8003/api.php?action=apply_diskon` | `kode` (Memvalidasi kupon diskon) |
| 8 | Pendidikan | Travel | POST `:8003/api.php?action=tambah_voucher` | `kode`, `persen`, `siswa_id` |
| 9 | Travel | Ecommerce | GET `:8001/api.php?action=produk` | (Membaca daftar produk live untuk bundle) |

---

### Detail REST API Per Aplikasi

#### A. AppsBank (:8000/api.php?action=[NAMA_AKSI])
* **`ping`** (GET): Health check untuk memverifikasi status koneksi server.
* **`rekening`** (GET): Mendapatkan daftar seluruh rekening bank beserta saldo.
* **`cek_rekening`** (GET): Memeriksa informasi detail satu rekening menggunakan parameter `?no_rek=[NOMOR_REKENING]`.
* **`debit`** (POST): Mengurangi saldo rekening tertentu. Payload: `no_rek`, `jumlah`, `keterangan`, `sumber`.
* **`kredit`** (POST): Menambah saldo rekening tertentu. Payload: `no_rek`, `jumlah`, `keterangan`, `sumber`.
* **`transfer`** (POST): Transfer saldo dari satu rekening ke rekening lain. Payload: `dari`, `ke`, `jumlah`, `keterangan`.
* **`mutasi`** (GET): Mendapatkan daftar mutasi saldo berdasarkan parameter `?no_rek=[NOMOR_REKENING]`.
* **`audit`** (GET): Mendapatkan log histori request integrasi yang masuk dan keluar dari sistem Bank.

#### B. AppsEcommerce (:8001/api.php?action=[NAMA_AKSI])
* **`ping`** (GET): Health check status koneksi server.
* **`produk`** (GET): Mendapatkan daftar seluruh produk di etalase.
* **`add_produk`** (POST): Menambahkan produk baru. Payload: `nama`, `harga`, `stok`, `kategori`, `deskripsi`, `sumber`, `asal_id`.
* **`pesanan`** (GET): Mendapatkan seluruh riwayat pesanan belanja.
* **`checkout`** (POST): Memproses pembayaran produk keranjang. Payload: `user`, `no_rek`, `items` (array berisi `produk_id` dan `qty`).
* **`audit`** (GET): Mendapatkan log histori integrasi pada sistem e-commerce.

#### C. AppsPendidikan (:8002/api.php?action=[NAMA_AKSI])
* **`ping`** (GET): Health check status koneksi server.
* **`siswa`** (GET): Mendapatkan daftar seluruh siswa.
* **`cek_siswa`** (GET): Memeriksa detail siswa tertentu berdasarkan parameter `?id=[ID_SISWA]`.
* **`produk_siswa`** (GET): Mendapatkan daftar produk karya buatan siswa.
* **`spp`** (GET): Mendapatkan riwayat pembayaran uang sekolah (SPP).
* **`audit`** (GET): Mendapatkan log histori integrasi pada sistem Pendidikan.

#### D. AppsTravel (:8003/api.php?action=[NAMA_AKSI])
* **`ping`** (GET): Health check status koneksi server.
* **`tiket`** (GET): Mendapatkan daftar tiket perjalanan yang tersedia.
* **`hotel`** (GET): Mendapatkan daftar hotel.
* **`paket`** (GET): Mendapatkan daftar paket wisata.
* **`voucher`** (GET): Mendapatkan daftar seluruh voucher diskon terdaftar.
* **`tambah_voucher`** (POST): Menambahkan kode voucher perjalanan baru. Payload: `kode`, `persen`, `untuk`, `siswa_id`.
* **`apply_diskon`** (POST): Memeriksa status dan nilai diskon voucher. Payload: `kode`.
* **`pesan_tiket`** (POST): Memesan tiket perjalanan. Payload: `tiket_id`, `qty`, `pemesan`, `no_rek`, `kode_voucher`.
* **`pesan_hotel`** (POST): Memesan hotel. Payload: `hotel_id`, `malam`, `pemesan`, `no_rek`.
* **`pesan_paket`** (POST): Memesan paket liburan. Payload: `paket_id`, `pemesan`, `no_rek`.
* **`pesanan`** (GET): Mendapatkan seluruh daftar riwayat pemesanan perjalanan.
* **`audit`** (GET): Mendapatkan log histori integrasi pada sistem Travel.

---

## 10. Struktur Direktori dan Data Penyimpanan
Setiap sistem dibangun dengan struktur file yang konsisten seperti berikut:
```
apps_[nama]/
├── data/               <-- Penyimpanan data format file JSON (Database)
│   ├── users.json      <-- Data user terdaftar untuk autentikasi login
│   ├── audit.json      <-- Log audit request/response REST API
│   └── [data_lain].json
├── pages/              <-- Halaman fungsional web frontend
├── api.php             <-- Handler routing dan request REST API
├── config.php          <-- File konfigurasi port, URL, dan lokasi database JSON
├── functions.php       <-- Berisi kumpulan logika aplikasi dan helper cURL HTTP
├── index.php           <-- Dashboard dashboard utama (tampilan utama)
├── style.css           <-- File CSS styling (Dark mode dan tata letak responsif)
├── login.php           <-- Logika dan formulir masuk akun
└── logout.php          <-- Logika keluar dari akun
```

---

## 11. Catatan Teknis dan Penanganan Deadlock

### Penanganan Deadlock pada PHP Built-in Server
Server bawaan PHP (`php -S`) beroperasi dalam mode single-threaded secara default. Hal ini berpotensi memicu deadlock jika server Aplikasi A memanggil server Aplikasi B secara bersamaan, atau jika suatu server mencoba memanggil dirinya sendiri menggunakan URL HTTP.

Untuk mencegah deadlock, sistem ini menggunakan solusi teknis berikut:
1. **Multi-Worker Server**: Pada skrip startup, server dijalankan dengan pengaturan `PHP_CLI_SERVER_WORKERS=4`. Hal ini mengizinkan PHP web server bawaan untuk memproses hingga 4 request secara paralel (simultan) di background.
2. **Optimalisasi Pemanggilan Lokal**: Halaman web dalam satu aplikasi tidak menggunakan cURL HTTP untuk memanggil API-nya sendiri. Aplikasi langsung memanggil fungsi PHP lokal pada file `functions.php` yang berinteraksi langsung dengan database JSON. cURL HTTP hanya diperuntukkan untuk pertukaran data lintas aplikasi (cross-server).

### Monitor Integrasi dan Health Check
Setiap aplikasi menyediakan halaman **Monitor Integrasi** di navigasinya. Halaman ini berfungsi untuk:
* Mengirimkan request ping otomatis ke 3 server rekanan setiap 5 detik secara berkala.
* Menampilkan status online/offline beserta latency respon (dalam ms) masing-masing peer.
* Menampilkan tabel log audit secara real-time yang mencatat arah komunikasi API (masuk/keluar), nama peer, status kode HTTP, parameter query, serta payload JSON request dan response.

---

## Smoke Test Output Contoh:
```
Pendidikan generate voucher EDU-DEMO-30 -> Travel: [Sukses] saved
Pendidikan upload "Lukisan Pemandangan" -> Ecommerce: [Sukses] id=P2026...
Ecommerce checkout 350k pakai voucher EDU 30%:
  -> Travel apply_diskon [Sukses] -> Bank debit 245k [Sukses]
  -> saldo 1001: 5jt -> 4,755jt [Sukses]
Travel pesan tiket Yogya 650k pakai voucher EDU 30%:
  -> Bank debit 455k [Sukses]
  -> saldo 1002: 3,5jt -> 3,045jt [Sukses]
```
#   b a n k - t e r i n t e g r a s i 
 
 