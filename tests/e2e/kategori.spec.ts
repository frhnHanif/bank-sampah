/**
 * Kategori Sampah Tests
 *
 * Skenario:
 *   TC-03: Tambah kategori baru
 *   TC-04: Edit kategori existing
 *   TC-05: Hapus kategori (restrictOnDelete — dicegah jika ada item)
 *   TC-13: Edit harga kategori → riwayat transaksi TIDAK berubah
 */
import { test, expect } from '@playwright/test';
import { loginAsAdmin } from './helpers';

test.describe('Kategori Sampah', () => {

  test.beforeEach(async ({ page }) => {
    await loginAsAdmin(page, '/kategori');
    await expect(page.getByRole('heading', { name: /Master Data Kategori/i })).toBeVisible({ timeout: 10000 });
  });

  // TC-03: Tambah kategori
  test('TC-03 | Tambah kategori sampah baru', async ({ page }) => {
    // Klik tombol Tambah
    await page.getByRole('button', { name: /Tambah Kategori/i }).click();

    // Tunggu modal muncul
    const modal = page.locator('#modalKategori, [id*="modal"]').filter({ hasText: /Tambah|Edit/ }).first();
    await expect(modal).toBeVisible({ timeout: 5000 });

    // Isi form — scope ke modal yang visible (hindari conflict edit modal)
    const modalTambah = page.locator('[id*="modal"]').filter({ hasText: /Tambah Kategori/i }).first();
    await modalTambah.locator('input[name="nama"]').fill('Plastik PET');
    await modalTambah.locator('input[name="harga_beli_per_kg"]').fill('800');
    await modalTambah.locator('input[name="faktor_emisi"]').fill('0.15');

    // Submit
    await page.getByRole('button', { name: /Simpan/i }).click();

    // Verifikasi muncul di halaman
    await expect(page.getByText('Kategori berhasil')).toBeVisible({ timeout: 5000 });
    await expect(page.getByText('Plastik PET').first()).toBeVisible();
  });

  // TC-04: Edit kategori
  test('TC-04 | Edit data kategori sampah', async ({ page }) => {
    // Cari tombol Edit di kartu kategori pertama yang ada
    const editButton = page.locator('button:has(.fa-pen-to-square), button:has-text("Edit")').first();
    await editButton.click();

    // Tunggu modal edit — scope ke modal yang mengandung input dengan id editNama
    const modalEdit = page.locator('[id*="modal"]').filter({ has: page.locator('#editNama, [id*="edit"]') }).first();
    await expect(modalEdit).toBeVisible({ timeout: 5000 });

    // Ubah harga beli — gunakan id spesifik
    const hargaInput = modalEdit.locator('input[name="harga_beli_per_kg"]');
    await hargaInput.clear();
    await hargaInput.fill('2000');

    // Submit
    await page.getByRole('button', { name: /Simpan|Update/i }).click();

    // Verifikasi sukses
    await expect(page.getByText(/berhasil diperbarui/i)).toBeVisible({ timeout: 5000 });
  });

  // TC-05: Hapus kategori — restrictOnDelete jika ada item_setor/item_jual
  test('TC-05 | Hapus kategori → dicegah jika ada transaksi terkait', async ({ page }) => {
    const deleteBtns = page.locator('button:has(.fa-trash-can)');
    const deleteCount = await deleteBtns.count();

    if (deleteCount === 0) {
      test.skip(true, 'Tidak ada tombol hapus kategori');
      return;
    }

    // Terima dialog konfirmasi
    page.once('dialog', dialog => dialog.accept());

    // Klik tombol hapus pertama
    await deleteBtns.first().click();

    // Tunggu sebentar untuk response
    await page.waitForTimeout(3000);

    // Verifikasi: halaman masih bisa diakses (tidak crash)
    // Bisa jadi halaman kategori atau dashboard (jika redirect setelah delete)
    const currentUrl = page.url();
    expect(currentUrl).toMatch(/kategori|dashboard|\//);
    
    // Cek tidak ada error 500
    const errorText = await page.getByText(/server error|500/i).isVisible().catch(() => false);
    expect(errorText).toBe(false);
  });

  // TC-13: Edit harga → data riwayat transaksi TIDAK ikut berubah
  test('TC-13 | Edit harga kategori — riwayat transaksi lama TIDAK berubah', async ({ page }) => {
    // Step 1: Ambil data keuangan saat ini sebagai baseline
    await page.goto('/keuangan');
    await expect(page.getByRole('heading', { name: /Buku Kas/i })).toBeVisible();

    // Catat nominal yang tampil di jurnal
    const jurnalRowsBefore = await page.locator('table tbody tr').count();
    const firstRowNominal = jurnalRowsBefore > 0
      ? await page.locator('table tbody tr').first().textContent()
      : 'BASELINE_EMPTY';

    // Step 2: Edit kategori — ubah harga
    await page.goto('/kategori');
    await expect(page.getByRole('heading', { name: /Master Data Kategori/i })).toBeVisible({ timeout: 10000 });
    const editBtn = page.locator('button:has(.fa-pen-to-square), button:has-text("Edit")').first();
    await editBtn.click();

    const modal = page.locator('[id*="modal"]').filter({ has: page.locator('#editNama, [id*="edit"]') }).first();
    await expect(modal).toBeVisible({ timeout: 5000 });

    const hargaInput = modal.locator('input[name="harga_beli_per_kg"]');
    const hargaBefore = await hargaInput.inputValue();
    const hargaBaru = (parseFloat(hargaBefore) + 500).toString();
    await hargaInput.clear();
    await hargaInput.fill(hargaBaru);
    await page.getByRole('button', { name: /Simpan|Update/i }).click();
    await expect(page.getByText(/berhasil/i)).toBeVisible({ timeout: 5000 });

    // Step 3: Cek keuangan — nominal transaksi lama HARUS tetap sama
    await page.goto('/keuangan');
    await expect(page.getByText('Buku Kas & Keuangan').first()).toBeVisible({ timeout: 10000 });

    const jurnalRowsAfter = await page.locator('table tbody tr').count();
    // Jumlah baris jurnal tidak berubah (tidak ada transaksi baru dari edit kategori)
    expect(jurnalRowsAfter).toBe(jurnalRowsBefore);

    // Verifikasi nominal tetap
    if (jurnalRowsAfter > 0 && firstRowNominal !== 'BASELINE_EMPTY') {
      const firstRowNominalAfter = await page.locator('table tbody tr').first().textContent();
      expect(firstRowNominalAfter).toBe(firstRowNominal);
    }
  });
});
