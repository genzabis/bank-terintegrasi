# Dokumentasi AppsDistribusi

Folder ini berisi diagram dan laporan tugas. File biner (PNG/PDF) belum di-generate; di bawah ini disediakan versi **text-based** sebagai placeholder yang bisa dipakai untuk membuat versi PNG di tools seperti draw.io / Mermaid / Excalidraw.

> Ganti file di bawah dengan PNG/PDF asli sebelum mengumpulkan tugas:
> - `arsitektur.png` - Diagram arsitektur full-mesh 4 sistem
> - `ERD.png` - Entity Relationship Diagram
> - `flowchart.png` - Flowchart komunikasi REST
> - `laporan.pdf` - Laporan tugas

---

## 1. Diagram Arsitektur (text)

```
                    AppsDistribusi - Full-Mesh REST API
                    ====================================

       ┌─────────────────────┐         ┌─────────────────────┐
       │   AppsBank (A)      │◄────────┤  AppsEcommerce (B)  │
       │   localhost:8000    │  debit  │  localhost:8001     │
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
       │ localhost:8002      │ voucher │   localhost:8003    │
       │                     │ json    │                     │
       │ - siswa.json        │         │ - tiket.json        │
       │ - spp.json          │         │ - hotel.json        │
       │ - produk_siswa.json │────────►│ - voucher.json      │
       └─────────────────────┘ produk  │ - pesanan.json      │
                              json     └─────────────────────┘
                                       (Pendidikan→Ecommerce
                                        kirim produk siswa)

   Komunikasi: HTTP REST + JSON via cURL · Tanpa SPOF
```

## 2. ERD (text)

```
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
                                        ┌──────────────────┐
                                        │ pesanan          │
                                        ├──────────────────┤
                                        │ id, jenis        │
                                        │ tiket/hotel id   │
                                        │ qty, total       │
                                        │ pemesan, no_rek  │
                                        │ kode, status     │
                                        └──────────────────┘
```

## 3. Flowchart Komunikasi (text)

```
SKENARIO 1: PEMBAYARAN DI ECOMMERCE
-----------------------------------
User → [B] Ecommerce/checkout.php
     → POST /api.php?action=checkout (internal)
     → POST [A]/api.php?action=debit  (REST)
     → [A] Bank validasi & update saldo
     ← reply {success, saldo}
     → [B] simpan pesanan, kurangi stok
     ← reply ke user

SKENARIO 2: UPLOAD PRODUK SISWA
-------------------------------
Sekolah → [C] Pendidikan/jual_produk.php (klik Upload)
        → POST [B]/api.php?action=add_produk (REST)
        → [B] simpan produk dgn kategori=siswa, sumber=AppsPendidikan
        ← reply {success, data:{id}}
        → [C] update produk_siswa.ecommerce_id
        → tampil di [B] Ecommerce/index.php

SKENARIO 3: BUNDLE ECOMMERCE + TRAVEL
-------------------------------------
User → [B] Ecommerce/bundle.php
     → GET [D]/api.php?action=tiket  (load tiket live)
     → GET [A]/api.php?action=rekening (load rekening)
     User submit → 
     → POST [A]/api.php?action=debit (bayar total)
     → POST [D]/api.php?action=pesan_tiket (qty=1, diskon=15)
     ← [D] reply {success, kode}
     → [B] simpan pesanan dgn metode=BANK+BUNDLE

SKENARIO 4: DISKON SISWA → TRAVEL
---------------------------------
Admin → [C] Pendidikan/diskon_travel.php
      → POST [D]/api.php?action=tambah_voucher
        body: {kode, persen, untuk, siswa_id, sumber}
      ← [D] reply {success, data}
      → tampil kode untuk diberikan ke siswa

Siswa → [D] Travel/pesan.php
      → POST [D]/api.php?action=pesan_tiket
        body: {tiket_id, qty, kode_voucher, no_rek}
      → [D] cek voucher, hitung diskon
      → POST [A]/api.php?action=debit (bayar total - diskon)
      ← reply ke siswa
```

## 4. Tabel Endpoint REST API

### A. AppsBank (:8000)
| Method | Endpoint | Body / Param |
|--------|----------|--------------|
| GET    | `/api.php?action=ping`        | -                                   |
| GET    | `/api.php?action=rekening`    | -                                   |
| GET    | `/api.php?action=cek_rekening`| `?no_rek=`                          |
| POST   | `/api.php?action=debit`       | `{no_rek, jumlah, keterangan, sumber}` |
| POST   | `/api.php?action=kredit`      | `{no_rek, jumlah, keterangan, sumber}` |
| POST   | `/api.php?action=transfer`    | `{dari, ke, jumlah, keterangan}`    |
| GET    | `/api.php?action=mutasi`      | `?no_rek=`                          |

### B. AppsEcommerce (:8001)
| Method | Endpoint | Body / Param |
|--------|----------|--------------|
| GET    | `/api.php?action=ping`        | -                                            |
| GET    | `/api.php?action=produk`      | -                                            |
| POST   | `/api.php?action=add_produk`  | `{nama, harga, stok, kategori, deskripsi, sumber, asal_id}` |
| GET    | `/api.php?action=pesanan`     | -                                            |
| POST   | `/api.php?action=checkout`    | `{user, no_rek, items:[{produk_id, qty}]}`   |

### C. AppsPendidikan (:8002)
| Method | Endpoint | Body / Param |
|--------|----------|--------------|
| GET    | `/api.php?action=ping`         | -          |
| GET    | `/api.php?action=siswa`        | -          |
| GET    | `/api.php?action=cek_siswa`    | `?id=`     |
| GET    | `/api.php?action=produk_siswa` | -          |
| GET    | `/api.php?action=spp`          | -          |

### D. AppsTravel (:8003)
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
