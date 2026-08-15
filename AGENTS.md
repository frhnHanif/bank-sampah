# Catatan untuk AI Agent Berikutnya

## Arsitektur domain

Baseline aplikasi memakai klasifikasi dua tingkat:

```text
KelompokMaterial
  -> JenisSampah
     -> ItemSetor / Stok / ItemJual
```

- Faktor emisi dan metadata sumber/versi/tanggal berlaku dimiliki `KelompokMaterial`.
- `JenisSampah` adalah pilihan operasional pada transaksi dan mempunyai unit pencatatan `KG` atau `PCS`.
- Form transaksi hanya menerima `jenis_sampah_id` dari master aktif; tidak ada free-text atau quick-create jenis sampah.
- Nama master dinormalisasi dan unik agar perbedaan kapitalisasi/spasi tidak membuat duplikasi.

## Alur bisnis

1. Setoran mencatat Jenis Sampah dan berat, kemudian menambah stok fisik.
2. Setoran tidak menambah saldo nasabah.
3. Penjualan mengalokasikan sisa Item Setor secara FIFO per Jenis Sampah.
4. Harga pengepul dan hak nasabah tetap dihitung per kg.
5. Penjualan mengurangi stok dan mengkredit tabungan nasabah tepat satu kali.
6. `berat_kg` adalah source of truth untuk stok, FIFO, uang, dan emisi.
7. Jenis `PCS` wajib menyimpan `jumlah_pcs` integer positif serta berat total kg. Jumlah pcs hanya metadata dan tidak dialokasikan pada penjualan parsial.
8. Stok dipisahkan per Jenis Sampah, meskipun beberapa jenis berada dalam Kelompok Material yang sama.

## Emisi

- Faktor transaksi diwarisi melalui `ItemJual -> JenisSampah -> KelompokMaterial`.
- Bila faktor tersedia saat alokasi, simpan snapshot dan status `REALIZED`.
- Bila faktor belum tersedia, status `PENDING`, sedangkan faktor snapshot dan CO2 tetap `NULL` (bukan nol).
- Saat faktor kelompok kemudian diisi, `EmissionRealizationService` merealisasikan alokasi yang masih pending secara transactional.
- Snapshot yang sudah `REALIZED` tidak dihitung ulang ketika master faktor berubah.

## Data dan kompatibilitas

Database dimulai dari baseline bersih. Tidak ada opening inventory, flow version, source type, atau cabang kompatibilitas transaksi lama. Jangan menghidupkan kembali struktur tersebut.

Master awal hanya berisi user pengembangan, Kelompok Material, dan Jenis Sampah. Faktor emisi seed sengaja `NULL` dan harus diisi admin dari sumber yang dapat dipertanggungjawabkan. Data transaksi untuk test dibuat di setup/factory test, bukan production seeder.

## UI dan perubahan schema

Pertahankan layout, sidebar/navbar, card, tabel, modal, dan pola responsif yang ada. Perubahan domain dimasukkan pada flow/modal existing; jangan melakukan redesign tanpa permintaan eksplisit.

Project masih berada pada tahap development dan schema baseline boleh dirapikan sebelum data nyata digunakan. Tetap periksa `git status`, jangan menimpa perubahan pengguna yang tidak terkait, dan jangan memakai perintah Git destruktif.

## Verifikasi minimum

Jalankan:

```bash
php artisan migrate:fresh --seed
php artisan bank-sampah:audit
php artisan test
npm run build
```

Jika environment utama tidak memiliki database aktif, migration dapat diverifikasi memakai SQLite sementara. Pastikan test KG, PCS, validasi, stok per jenis, oversell/rollback, FIFO, kredit tepat sekali, emisi pending/realized/snapshot, dashboard, dan PDF tetap tercakup.
