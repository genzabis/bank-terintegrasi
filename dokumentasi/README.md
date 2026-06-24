# AppsDistribusi: Ekosistem Aplikasi Terdistribusi Berbasis REST API

AppsDistribusi adalah sekumpulan empat aplikasi *web* mandiri yang terintegrasi penuh melalui arsitektur REST API berkonsep saling terhubung (Full-Mesh). Proyek ini mensimulasikan ekosistem transaksi terdistribusi dunia nyata, menghubungkan perbankan, toko online (ecommerce), akademik (pendidikan), dan biro perjalanan (travel).

---

## 🌟 Fitur Unggulan

1. **Arsitektur Independen Berbasis API** 
   Setiap aplikasi berjalan secara mandiri dengan basis data lokal (`.json` flat-file) sendiri dan saling bertukar data/instruksi secara aman melalui HTTP REST API (cURL) antar-aplikasi.
2. **Otentikasi Keamanan PIN Terpusat** 
   Seluruh transaksi finansial di semua aplikasi kini dilindungi dengan modul *Security PIN* berbasis *interceptor JavaScript* asli (PIN Bawaan: **`12345`**).
3. **Pencegahan Duplikasi Tagihan** 
   Validasi ketat pada sistem tagihan SPP (Apps Pendidikan) yang mencegah penagihan/pembayaran ganda untuk bulan yang sama.
4. **Sistem Notifikasi Post-Redirect-Get (PRG)** 
   Memberikan _feedback_ visual kepada pengguna melalui pesan _toast_ (Berhasil, Gagal, Peringatan) di seluruh aplikasi tanpa risiko proses ulang saat melakukan *refresh*.
5. **Tema Antarmuka Estetik** 
   Tampilan *dashboard* modern menggunakan *CSS kustom* responsif dengan *micro-animations*, dilengkapi ikon [Lucide](https://lucide.dev) dan tema warna eksklusif per aplikasi:
   - 🏦 **AppsBank**: Biru Kepercayaan (Trust Blue)
   - 🛒 **AppsEcommerce**: Oren Menyala (Bold Orange/Amber)
   - 🎓 **AppsPendidikan**: Ungu Elegan (Indigo)
   - ✈️ **AppsTravel**: Cyan Tropis (Teal)

---

## 👥 Akun Pengujian (Testing)

Semua sistem membagikan basis data identitas yang sinkron. Anda dapat masuk *(login)* menggunakan akun-akun berikut:

- **niam** (Password: `123` | Role: Admin)
- **isna** (Password: `123` | Role: User)
- **linda** (Password: `123` | Role: User)

> **Catatan Keamanan Transaksi:**
> Setiap kali akan melakukan *Checkout*, Pesan Tiket, atau Bayar SPP, sistem akan meminta **PIN: 12345**.

---

## ⚙️ Persyaratan dan Cara Menjalankan

1. Pastikan Anda memiliki modul lingkungan PHP lokal aktif (seperti XAMPP, Laragon, dll).
2. _Clone_ atau ekstrak repositori ini ke dalam direktori *web server* lokal Anda (misalnya: `C:\xampp\htdocs\appsdistribusi`).
3. Secara bawaan, seluruh aplikasi mendeteksi URL dan *port* di dalam direktori `apps_bank/config.php` dll.
4. Buka akses melalui peramban:
   - **Bank**: `http://localhost/appsdistribusi/apps_bank`
   - **Ecommerce**: `http://localhost/appsdistribusi/apps_ecommerce`
   - **Pendidikan**: `http://localhost/appsdistribusi/apps_pendidikan`
   - **Travel**: `http://localhost/appsdistribusi/apps_travel`

*(Konfigurasi URL sistem berada di file `_auth.php` atau `config.php` masing-masing jika port virtual/hostname ingin diubah).*

---

## 🏗️ 1. Diagram Arsitektur

```text
                    AppsDistribusi - Full-Mesh REST API
                    ====================================

       ┌─────────────────────┐         ┌─────────────────────┐
       │   AppsBank (A)      │◄────────┤  AppsEcommerce (B)  │
       │   localhost/..bank  │  debit  │  localhost/..ecom   │
       │                     │  json   │                     │
       │ - rekening.json     │         │ - produk.json       │
       │ - mutasi.json       │         │ - keranjang.json    │
       └──┬──────────────┬───┘         │ - pesanan.json      │
          │              │             └──┬──────────────┬───┘
   debit  │              │ debit          │              │
   json   │              │ json           │ pesan_tiket  │
          │              │                │ bundle json  │
          │              │                ▼              │
       ┌──┴──────────────┴───┐         ┌──────────────────┴──┐
       │ AppsPendidikan (C)  │────────►│   AppsTravel (D)    │
       │ localhost/..pend    │ voucher │   localhost/..trav  │
       │                     │ json    │                     │
       │ - siswa.json        │         │ - tiket.json        │
       │ - spp.json          │         │ - hotel.json        │
       │ - produk_siswa.json │────────►│ - voucher.json      │
       └─────────────────────┘ produk  │ - pesanan.json      │
                              json     └─────────────────────┘
                                       (Pendidikan→Ecommerce
                                        kirim produk siswa)
```

## 🗄️ 2. Entitas Data (ERD Sederhana)

```text
APPSBANK
┌──────────────┐         ┌────────────────┐
│  rekening    │ 1     N │   mutasi       │
├──────────────┤◄────────┤────────────────┤
│ no_rek (PK)  │         │ id (PK)        │
│ nama         │         │ no_rek (FK)    │
│ saldo        │         │ tipe           │
│ dibuat       │         │ jumlah         │
└──────────────┘         │ keterangan     │
                         │ sumber         │
                         │ tanggal        │
                         └────────────────┘

APPSECOMMERCE
┌──────────────┐    ┌────────────┐    ┌────────────────┐
│ produk       │    │ keranjang  │    │ pesanan        │
├──────────────┤    ├────────────┤    ├────────────────┤
│ id (PK)      │    │ user       │    │ id (PK)        │
│ nama         │    │ items[]    │    │ user           │
│ harga, stok  │    │  produk_id │    │ no_rek         │
│ kategori     │    │  qty       │    │ items[]        │
│ sumber       │    └────────────┘    │ total, status  │
│ asal_id      │                      │ metode         │
└──────────────┘                      │ bundle         │
                                      └────────────────┘

APPSPENDIDIKAN
┌──────────────┐ 1   N ┌──────────────┐    ┌─────────────────┐
│ siswa        ├───────┤ spp          │    │ produk_siswa    │
├──────────────┤       ├──────────────┤    ├─────────────────┤
│ id (PK)      │       │ id (PK)      │    │ id (PK)         │
│ nama, kelas  │       │ siswa_id (FK)│    │ siswa_id (FK)   │
│ no_rek       │       │ bulan        │    │ nama, harga     │
│ dibuat       │       │ jumlah       │    │ stok, deskripsi │
└──────────────┘       │ status       │    │ ecommerce_id    │
                       └──────────────┘    └─────────────────┘

APPSTRAVEL
┌──────────────┐    ┌──────────────┐    ┌──────────────────┐
│ tiket        │    │ hotel        │    │ voucher          │
├──────────────┤    ├──────────────┤    ├──────────────────┤
│ id (PK)      │    │ id (PK)      │    │ kode (PK)        │
│ nama, rute   │    │ nama, lokasi │    │ persen           │
│ harga, kuota │    │ harga/malam  │    │ untuk, siswa_id  │
│ tipe         │    │ rating       │    │ sumber, dipakai  │
└──────────────┘    └──────────────┘    └──────────────────┘
```

## 🔄 3. Flow Komunikasi Integrasi

*Skenario Utama yang Diimplementasikan:*

**1. PEMBAYARAN VIA BANK**
Semua sistem (Ecommerce, Pendidikan, Travel) akan menembak *endpoint* Bank secara sinkron lewat `POST /api.php?action=debit`. Jika saldo kurang atau rekening salah, transaksi di aplikasi asal akan dibatalkan/ditolak.

**2. UPLOAD KARYA SISWA**
Sekolah di AppsPendidikan dapat melempar produk kreasi siswanya ke AppsEcommerce melalui `POST /api.php?action=add_produk`. Jika berhasil terdaftar di *database* Ecommerce, produk langsung dapat dibeli secara massal.

**3. VOUCHER & BUNDLE (LINTAS SISTEM)**
Siswa berprestasi di Pendidikan dapat meminta *reward voucher* terbang dari AppsTravel. Voucher tersebut kemudian bisa diaplikasikan/di-_redeem_ di AppsEcommerce sebagai diskon barang (karena ada kerja sama API antara ketiganya).  Ada pula skenario di mana pengguna membeli paket *Bundle* (Barang + Tiket) lewat 1 tombol pembayaran.

---

## 📡 4. Dokumentasi Endpoint REST API Terbuka

### A. AppsBank
| Method | Endpoint | Body / Param |
|--------|----------|--------------|
| GET    | `/api.php?action=ping`        | -                                   |
| GET    | `/api.php?action=rekening`    | -                                   |
| GET    | `/api.php?action=cek_rekening`| `?no_rek=`                          |
| POST   | `/api.php?action=debit`       | `{no_rek, jumlah, keterangan, sumber}` |
| POST   | `/api.php?action=kredit`      | `{no_rek, jumlah, keterangan, sumber}` |
| POST   | `/api.php?action=transfer`    | `{dari, ke, jumlah, keterangan}`    |
| GET    | `/api.php?action=mutasi`      | `?no_rek=`                          |

### B. AppsEcommerce
| Method | Endpoint | Body / Param |
|--------|----------|--------------|
| GET    | `/api.php?action=ping`        | -                                            |
| GET    | `/api.php?action=produk`      | -                                            |
| POST   | `/api.php?action=add_produk`  | `{nama, harga, stok, kategori, deskripsi, sumber, asal_id}` |
| GET    | `/api.php?action=pesanan`     | -                                            |
| POST   | `/api.php?action=checkout`    | `{user, no_rek, items:[{produk_id, qty}]}`   |

### C. AppsPendidikan
| Method | Endpoint | Body / Param |
|--------|----------|--------------|
| GET    | `/api.php?action=ping`         | -          |
| GET    | `/api.php?action=siswa`        | -          |
| GET    | `/api.php?action=cek_siswa`    | `?id=`     |
| GET    | `/api.php?action=produk_siswa` | -          |
| GET    | `/api.php?action=spp`          | -          |

### D. AppsTravel
| Method | Endpoint | Body / Param |
|--------|----------|--------------|
| GET    | `/api.php?action=ping`           | -                                                                  |
| GET    | `/api.php?action=tiket`          | -                                                                  |
| GET    | `/api.php?action=hotel`          | -                                                                  |
| GET    | `/api.php?action=voucher`        | -                                                                  |
| POST   | `/api.php?action=tambah_voucher` | `{kode, persen, untuk, siswa_id, sumber}`                          |
| POST   | `/api.php?action=apply_diskon`   | `{kode}`                                                           |
| POST   | `/api.php?action=pesan_tiket`    | `{tiket_id, qty, pemesan, no_rek?, kode_voucher?, diskon?, sumber}`|
| POST   | `/api.php?action=pesan_hotel`    | `{hotel_id, malam, pemesan, no_rek}`                               |
| GET    | `/api.php?action=pesanan`        | -                                                                  |
