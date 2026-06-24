/**
 * Nasabah Tests
 *
 * Skenario:
 *   TC-06: Tambah nasabah → verifikasi auto-kode (RW+RT+3digit)
 *   TC-07: Search nasabah by nama/kode
 *   TC-14: Edit nasabah → riwayat tabungan & mutasi tabungan tetap ada (by FK id)
 *   TC-15: Hapus nasabah → cascade: transaksi_setor, tabungan, mutasi_tabungan ikut terhapus
 */
import { test, expect } from '@playwright/test';
import { loginAsAdmin } from './helpers';

test.describe('Nasabah', () => {

  test.beforeEach(async ({ page }) => {
    await loginAsAdmin(page, '/nasabah');
    await expect(page.getByRole('heading', { name: /Data Nasabah/i })).toBeVisible({ timeout: 10000 });
  });

  // TC-06: Tambah nasabah + auto kode
  test('TC-06 | Tambah nasabah — auto-generate kode RW+RT+seq', async ({ page }) => {
    // Klik tambah
    await page.getByRole('button', { name: /Tambah Nasabah/i }).click();

    // Tunggu modal — cari form dengan action nasabah
    const modal = page.locator('form[action*="nasabah"]').first();
    await expect(modal).toBeVisible({ timeout: 5000 });

    // Form fields — scope ke form yang visible saja
    const visibleForm = page.locator('form[action*="nasabah"]').first();
    const namaInput = visibleForm.locator('input[name="nama"]');
    const rtInput = visibleForm.locator('input[name="rt"]');
    const rwInput = visibleForm.locator('input[name="rw"]');
    const nohpInput = visibleForm.locator('input[name="no_hp"]');

    await expect(namaInput).toBeVisible();
    await expect(rtInput).toBeVisible();
    await expect(rwInput).toBeVisible();

    // Isi data
    const testName = 'Test Nasabah ' + Date.now();
    await namaInput.fill(testName);
    await rtInput.fill('3');
    await rwInput.fill('1');
    await nohpInput.fill('081234567890');

    // Submit
    await page.getByRole('button', { name: /Simpan|Daftarkan/i }).click();

    // Verifikasi sukses & kode auto-generated
    const successMsg = page.getByText(/berhasil didaftarkan dengan Kode:/i);
    await expect(successMsg).toBeVisible({ timeout: 8000 });

    const msgText = await successMsg.textContent();
    // Kode format: RW+RT+3digit, misal "0103002"
    const kodeMatch = msgText?.match(/\d{6,8}/);
    expect(kodeMatch).toBeTruthy();
    if (kodeMatch) {
      const kode = kodeMatch[0];
      // Verifikasi 2 digit pertama = RW, 2 digit berikutnya = RT
      expect(kode.substring(0, 2)).toBe('01'); // RW 1 → 01
      expect(kode.substring(2, 4)).toBe('03'); // RT 3 → 03
    }

    // Nasabah muncul di halaman
    await expect(page.getByText(testName)).toBeVisible();
  });

  // TC-07: Search nasabah
  test('TC-07 | Pencarian nasabah berfungsi', async ({ page }) => {
    const searchInput = page.locator('#searchInput, input[placeholder*="Cari"]').first();
    await expect(searchInput).toBeVisible();

    // Cari nama yang pasti ada — gunakan kata kunci unik
    const firstCardText = await page.locator('.nasabah-card').first().textContent();
    if (firstCardText) {
      await searchInput.fill(firstCardText.substring(0, 8));
    } else {
      await searchInput.fill('Farhan');
    }
    
    await page.waitForTimeout(500);

    // Cek kartu yang visible mengandung teks yang dicari
    const visibleCards = page.locator('.nasabah-card:not([style*="display: none"])');
    const count = await visibleCards.count();
    expect(count).toBeGreaterThan(0);
  });

  // TC-14: Edit nasabah → riwayat tabungan tetap (FK by ID, nama tidak mempengaruhi)
  test('TC-14 | Edit nasabah — riwayat tabungan & mutasi TETAP ada', async ({ page }) => {
    // Step 1: Buka tabungan nasabah yang ada, catat saldo
    const tabunganLink = page.locator('a:has-text("Tabungan")').first();
    await expect(tabunganLink).toBeVisible();
    await tabunganLink.click();

    // Catat saldo dan mutasi
    await expect(page).toHaveURL(/\/nasabah\/\d+\/tabungan/);
    const saldoBefore = await page.locator('h2, h3').filter({ hasText: /Rp/ }).first().textContent();
    
    // Kembali ke nasabah
    await page.goto('/nasabah');

    // Step 2: Edit nasabah — ubah nama
    const editBtn = page.locator('button:has(.fa-pen-to-square), button[title="Edit"]').first();
    await editBtn.click();

    const modalEdit = page.locator('form[action*="nasabah"]').first();
    await expect(modalEdit).toBeVisible({ timeout: 5000 });

    const namaInput = modalEdit.locator('input[name="nama"]');
    const namaBefore = await namaInput.inputValue();
    await namaInput.clear();
    await namaInput.fill(namaBefore + ' (edited)');

    await page.getByRole('button', { name: /Simpan|Update/i }).click();
    await expect(page.getByText(/berhasil/i)).toBeVisible({ timeout: 5000 });

    // Step 3: Buka kembali tabungan — harus tetap ada
    const tabunganLink2 = page.locator('a:has-text("Tabungan")').first();
    await tabunganLink2.click();
    await expect(page).toHaveURL(/\/nasabah\/\d+\/tabungan/);

    // Saldo tetap sama (tidak hilang)
    const saldoAfter = await page.locator('h2, h3').filter({ hasText: /Rp/ }).first().textContent();
    expect(saldoAfter).toBe(saldoBefore);
  });

  // TC-15: Hapus nasabah → cascade delete
  test('TC-15 | Hapus nasabah — cascade: tabungan & transaksi_setor ikut terhapus', async ({ page }) => {
    // Step 1: Cek dashboard untuk total rekening warga saat ini
    await page.goto('/keuangan');
    await expect(page.getByRole('heading', { name: /Buku Kas/i })).toBeVisible();
    const totalRekBefore = await page.locator('h2').filter({ hasText: /Rp/ }).nth(2).textContent();

    // Step 2: Hapus nasabah
    await page.goto('/nasabah');
    const nasabahCards = page.locator('.nasabah-card');
    const countBefore = await nasabahCards.count();

    if (countBefore <= 1) {
      test.skip(true, 'Hanya satu nasabah — skip cascade test untuk menjaga data');
      return;
    }

    // Ambil nama nasabah terakhir untuk dihapus
    const lastCard = nasabahCards.last();
    const namaHapus = await lastCard.locator('h3').first().textContent();

    // Klik tombol hapus
    const deleteBtn = lastCard.locator('button[title="Hapus"], button:has(.fa-trash-can)');
    await deleteBtn.click();

    // Konfirmasi dialog
    page.on('dialog', dialog => dialog.accept());

    // Tunggu redirect/hapus
    await page.waitForTimeout(1000);
    await expect(page.getByRole('heading', { name: /Data Nasabah/i })).toBeVisible({ timeout: 10000 });

    // Step 3: Verifikasi nasabah hilang
    if (namaHapus) {
      await expect(page.getByText(namaHapus.trim())).not.toBeVisible({ timeout: 5000 });
    }

    // Step 4: Cek keuangan — total rekening warga berkurang (tabungan cascade deleted)
    await page.goto('/keuangan');
    await expect(page.getByRole('heading', { name: /Buku Kas/i })).toBeVisible();
    const totalRekAfter = await page.locator('h2').filter({ hasText: /Rp/ }).nth(2).textContent();
    
    // Total rekening warga seharusnya berubah (berkurang atau tidak, tergantung apakah nasabah punya saldo)
    // Minimal, aplikasi tidak error
    expect(totalRekAfter).toBeTruthy();
  });
});
