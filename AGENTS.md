# Catatan untuk AI Agent Berikutnya

## Arah produk

Aplikasi memakai proses bisnis baru:

1. Setoran hanya mencatat jenis dan berat sampah serta menambah stok.
2. Harga nasabah tidak ditentukan saat setoran.
3. Saat sampah dijual ke pengepul, stok setoran dialokasikan FIFO.
4. Hak nasabah baru dihitung dan masuk ke tabungan pada saat penjualan.
5. Transaksi dan histori lama tidak boleh dihapus.

Istilah `legacy`, `settlement`, `flow baru`, dan istilah teknis sejenis tidak boleh ditampilkan kepada pengguna. Gunakan istilah langsung seperti `Sisa stok`, `Terjual`, `Uang masuk`, dan `Hasil penjualan sampah`.

## Kondisi database saat ini

Walaupun istilah legacy sudah dihilangkan dari UI, struktur berikut masih aktif untuk kompatibilitas histori dan belum boleh langsung dihapus:

- `legacy_inventory`: menyimpan stok pembukaan yang hak nasabahnya sudah pernah dikreditkan sebelum proses bisnis baru. Tabel ini mencegah pembayaran ganda ketika stok lama dijual.
- `item_setor.is_legacy`: membedakan item historis yang sudah dibayar dari setoran baru yang menunggu penjualan.
- `transaksi_setor.flow_version` dan `transaksi_jual.flow_version`: masih dipakai untuk membedakan cara perhitungan transaksi historis.
- `alokasi_penjualan.legacy_inventory_id`: masih menjadi foreign key histori penjualan stok pembukaan.
- `alokasi_penjualan.sumber_tipe`: masih dapat berisi `LEGACY_OPENING` untuk histori internal.
- `kategori_sampah.harga_beli_per_kg` dan `kategori_sampah.faktor_emisi`: kolom lama yang sudah tidak menjadi sumber utama proses baru.

Jangan mengubah `is_legacy=true` menjadi `false`. Tindakan tersebut dapat membuat stok historis dianggap sebagai setoran belum dibayar dan mengkredit saldo nasabah untuk kedua kalinya.

## Sumber data proses baru

- Sisa stok utama: `stok.total_berat_kg`.
- Total terjual: jumlah `item_jual.berat_kg` per kategori.
- Setoran belum terjual: `item_setor.berat_kg - item_setor.berat_teralokasi_kg`, hanya untuk status `PENDING` atau `PARTIAL`.
- Nilai hak nasabah: `alokasi_penjualan.nilai_hak_nasabah`.
- Riwayat uang nasabah: `mutasi_tabungan`; semua kredit ditampilkan sebagai `Uang Masuk`.
- Faktor emisi kategori: relasi `kategori_sampah.faktor_emisi_id` ke tabel `faktor_emisi`.

## Rencana aman menghapus struktur legacy

Lakukan dalam migrasi baru. Jangan mengedit migrasi yang sudah pernah dijalankan pada database produksi.

### Tahap 1: audit sebelum perubahan

Wajib membuat backup dan memeriksa:

- Total `legacy_inventory.berat_tersisa_kg` harus `0` untuk semua kategori.
- Tidak boleh ada stok fisik yang hanya direpresentasikan oleh sisa `legacy_inventory`.
- Saldo `tabungan.saldo_saat_ini` harus sama dengan akumulasi `mutasi_tabungan`.
- `stok.total_berat_kg` harus sama dengan jumlah berat setoran `PENDING/PARTIAL` yang belum teralokasi setelah stok pembukaan habis.
- Seluruh `alokasi_penjualan` yang mengacu ke `legacy_inventory_id` harus tetap dapat dibaca sebagai histori setelah foreign key dilepas.

Gunakan dan perluas command audit yang ada di `app/Console/Commands/AuditBankSampah.php` sebelum membuat migrasi penghapusan.

### Tahap 2: refactor kode sebelum drop kolom

Hapus ketergantungan kode terhadap struktur lama terlebih dahulu:

- `app/Services/PenjualanSettlementService.php`: setelah stok pembukaan benar-benar habis, hilangkan cabang alokasi `LegacyInventory`; alokasikan hanya dari `ItemSetor` berstatus belum selesai.
- `app/Http/Controllers/StokController.php`: jangan lagi mengambil `LegacyInventory` untuk preview internal.
- `app/Console/Commands/AuditBankSampah.php`: ubah invariant stok agar hanya memakai sisa `ItemSetor`.
- Controller dashboard, tabungan, dan keuangan: ganti filter `is_legacy`/`flow_version` dengan status dan relasi transaksi yang eksplisit.
- Model dan enum: hapus relasi `legacyInventory()` dan enum `LegacyOpening` hanya setelah histori tidak lagi bergantung padanya.

Pastikan pencarian berikut tidak menghasilkan pemakaian runtime sebelum drop:

```bash
rg -n "LegacyInventory|legacy_inventory|is_legacy|LegacyOpening|LEGACY_OPENING|flow_version" app resources tests
```

### Tahap 3: migrasi database

Setelah Tahap 1 dan 2 lolos:

1. Pertahankan semua baris transaksi historis.
2. Pada `alokasi_penjualan`, lepaskan foreign key `legacy_inventory_id`, lalu null-kan atau pindahkan ID lama ke kolom arsip non-FK jika masih dibutuhkan untuk audit.
3. Drop kolom `alokasi_penjualan.legacy_inventory_id` jika histori sudah cukup diwakili snapshot berat, harga, cost basis, margin, dan `sumber_tipe`.
4. Drop tabel `legacy_inventory`.
5. Drop index terkait lalu drop `item_setor.is_legacy`.
6. Drop `flow_version` hanya setelah seluruh query histori tidak membutuhkannya.
7. Drop `kategori_sampah.harga_beli_per_kg` dan `kategori_sampah.faktor_emisi` setelah memastikan semua kategori memakai `faktor_emisi_id` dan tidak ada kode yang membaca kolom lama.
8. Bila diinginkan, ubah nilai internal `LEGACY_OPENING` menjadi nama arsip netral sebelum enum lama dihapus. Jangan mengubah angka historis.

Urutan foreign key harus diperhatikan: lepaskan referensi `alokasi_penjualan.legacy_inventory_id` sebelum menjatuhkan `legacy_inventory`.

## Kriteria selesai

- Setoran baru tidak mengubah saldo tabungan.
- Penjualan mengurangi stok dan mengkredit hak nasabah tepat satu kali.
- Tidak ada kemungkinan pembayaran ganda terhadap transaksi historis.
- Seluruh transaksi, mutasi tabungan, mutasi kas, dan snapshot emisi lama tetap tersedia.
- Audit stok, saldo, kas, FIFO, dan emisi lulus.
- UI tidak menampilkan istilah internal/legacy.
- Test alur setoran, penjualan parsial/penuh, oversell, rollback, tabungan, stok, serta laporan PDF lulus.

## Catatan pengerjaan

Workspace dapat memiliki perubahan pengguna yang belum di-commit. Jangan mereset atau menimpa perubahan yang tidak terkait. Gunakan migrasi baru untuk perubahan skema produksi dan jangan membersihkan database tanpa backup serta persetujuan eksplisit pengguna.
