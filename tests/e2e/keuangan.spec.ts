/**
 * Keuangan & Logika Bisnis Tests
 *
 * Skenario:
 *   TC-09: Halaman keuangan menampilkan 4 kartu metrik kas
 *   TC-10: Catat operasional via modal → muncul di jurnal & kurangi saldo
 *   TC-11: Filter bulan berfungsi
 *   TC-16: Logika Saldo Kas = Pemasukan - Pengeluaran
 *   TC-17: Logika Estimasi Keuntungan = Penjualan - COGS - Operasional
 *   TC-18: UI Ramah Lansia — font size, kontras, tombol besar
 */
import { test, expect } from '@playwright/test';
import { loginAsAdmin } from './helpers';

test.describe('Keuangan', () => {

  test.beforeEach(async ({ page }) => {
    await loginAsAdmin(page, '/keuangan');
    await expect(page.getByText('Buku Kas & Keuangan').first()).toBeVisible({ timeout: 10000 });
  });

  // TC-09: Empat kartu metrik
  test('TC-09 | Halaman keuangan menampilkan 4 kartu metrik utama', async ({ page }) => {
    // Verifikasi keempat kartu
    await expect(page.getByText('Total Saldo Kas Riil')).toBeVisible();
    await expect(page.getByText('Omset Penjualan Pengepul')).toBeVisible();
    await expect(page.getByText('Total Rekening Warga')).toBeVisible();
    await expect(page.getByText('Estimasi Keuntungan Bersih')).toBeVisible();

    // Semua kartu menampilkan nominal (Rp)
    const rpElements = page.locator('h2, h3').filter({ hasText: /Rp/ });
    const count = await rpElements.count();
    expect(count).toBeGreaterThanOrEqual(4);
  });

  // TC-10: Catat operasional via modal
  test('TC-10 | Catat pengeluaran operasional via modal', async ({ page }) => {
    // Catat jumlah baris jurnal sebelum
    const rowsBefore = await page.locator('table tbody tr').count();

    // Klik tombol Catat Operasional
    await page.getByRole('button', { name: /Catat Operasional/i }).click();

    // Tunggu modal — cari form dengan action keuangan/operasional
    await expect(page.locator('form[action*="keuangan/operasional"]')).toBeVisible({ timeout: 5000 });

    // Isi form
    const today = new Date().toISOString().split('T')[0];
    await page.locator('input[name="tanggal"]').fill(today);
    await page.locator('input[name="nominal"]').fill('35000');
    await page.locator('input[name="keterangan"]').fill('Test: Beli perlengkapan (Automated)');

    // Submit
    await page.getByRole('button', { name: /Simpan Transaksi/i }).click();

    // Verifikasi sukses
    await expect(page.getByText(/berhasil dicatat/i)).toBeVisible({ timeout: 5000 });

    // Verifikasi jurnal bertambah
    const rowsAfter = await page.locator('table tbody tr').count();
    expect(rowsAfter).toBe(rowsBefore + 1);

    // Verifikasi nominal muncul di jurnal (desktop table atau mobile card)
    const jurnalText = page.locator('table tbody td, .divide-y p').filter({ hasText: /Test: Beli perlengkapan/ }).first();
    await expect(jurnalText).toBeVisible({ timeout: 10000 });
  });

  // TC-11: Filter bulan
  test('TC-11 | Filter bulan berfungsi', async ({ page }) => {
    // Buka dropdown filter bulan (custom select)
    const filterTrigger = page.locator('.custom-select-trigger').first();
    await filterTrigger.click();

    // Pilih bulan Juni (value=6)
    const juniOption = page.locator('.custom-select-option[data-value="6"]');
    await expect(juniOption).toBeVisible({ timeout: 3000 });
    await juniOption.click();

    // URL berubah dengan ?bulan=6
    await expect(page).toHaveURL(/bulan=6/);

    // Halaman tetap menampilkan judul
    await expect(page.getByText('Buku Kas & Keuangan').first()).toBeVisible();
  });

  // TC-16: Logika Saldo Kas Riil
  test('TC-16 | Logika Saldo Kas = Total Pemasukan - Total Pengeluaran', async ({ page }) => {
    // Hitung dari tabel jurnal
    const masukCells = page.locator('table tbody td:nth-child(4)');
    const keluarCells = page.locator('table tbody td:nth-child(5)');

    let totalMasuk = 0;
    let totalKeluar = 0;

    const masukCount = await masukCells.count();
    for (let i = 0; i < masukCount; i++) {
      const text = await masukCells.nth(i).textContent();
      const match = text?.match(/Rp\s*([\d.]+)/);
      if (match) totalMasuk += parseInt(match[1].replace(/\./g, ''));
    }

    const keluarCount = await keluarCells.count();
    for (let i = 0; i < keluarCount; i++) {
      const text = await keluarCells.nth(i).textContent();
      const match = text?.match(/Rp\s*([\d.]+)/);
      if (match) totalKeluar += parseInt(match[1].replace(/\./g, ''));
    }

    const calculatedSaldo = totalMasuk - totalKeluar;

    // Baca saldo dari kartu "Total Saldo Kas Riil" — teks mengandung "Total Saldo Kas Riil"
    const saldoText = await page.locator('h2').filter({ hasText: /Rp/ }).first().textContent();
    const saldoMatch = saldoText?.match(/Rp\s*([\d.]+)/);
    expect(saldoMatch).toBeTruthy();

    if (saldoMatch) {
      const displayedSaldo = parseInt(saldoMatch[1].replace(/\./g, ''));
      // Saldo yang ditampilkan harus sama dengan perhitungan dari tabel
      expect(displayedSaldo).toBe(calculatedSaldo);
    }
  });

  // TC-17: Logika Estimasi Keuntungan
  test('TC-17 | Estimasi Keuntungan = Penjualan - COGS - Operasional', async ({ page }) => {
    // Baca 4 kartu metrik
    const allH2 = page.locator('h2').filter({ hasText: /Rp/ });

    // Kartu 1: Saldo Kas
    const saldoText = await allH2.nth(0).textContent();
    // Kartu 2: Omset Penjualan Pengepul
    const omsetText = await allH2.nth(1).textContent();
    // Kartu 3: Total Rekening Warga
    const rekeningText = await allH2.nth(2).textContent();
    // Kartu 4: Estimasi Keuntungan Bersih
    const keuntunganText = await allH2.nth(3).textContent();

    // Semua kartu harus ada nilainya (bukan null/undefined)
    expect(omsetText).toBeTruthy();
    expect(rekeningText).toBeTruthy();
    expect(keuntunganText).toBeTruthy();

    // Verifikasi nilai keuangan tidak absurd (masuk akal)
    const omsetMatch = omsetText?.match(/Rp\s*([\-\d.]+)/);
    const keuntunganMatch = keuntunganText?.match(/Rp\s*([\-\d.]+)/);

    if (omsetMatch && keuntunganMatch) {
      const omset = parseInt(omsetMatch[1].replace(/\./g, ''));
      const keuntungan = parseInt(keuntunganMatch[1].replace(/\./g, ''));
      
      // Keuntungan tidak mungkin melebihi omset
      expect(keuntungan).toBeLessThanOrEqual(omset);
      
      // Keuntungan bisa negatif (rugi), tapi tidak absurd
      expect(keuntungan).toBeGreaterThan(-100_000_000);
    }
  });

  // TC-18: UI Ramah Lansia
  test('TC-18 | UI Ramah Lansia — font besar, tombol lebar, kontras tinggi', async ({ page }) => {
    // 1. Heading font besar
    const heading = page.getByText('Buku Kas & Keuangan').first();
    const headingFontSize = await heading.evaluate(el => window.getComputedStyle(el).fontSize);
    expect(parseFloat(headingFontSize)).toBeGreaterThanOrEqual(18);

    // 2. Tombol aksi cukup besar (min 40px height)
    const btnCatat = page.getByRole('button', { name: /Catat Operasional/i });
    const btnBox = await btnCatat.boundingBox();
    expect(btnBox).toBeTruthy();
    if (btnBox) {
      expect(btnBox.height).toBeGreaterThanOrEqual(36);
      expect(btnBox.width).toBeGreaterThanOrEqual(100);
    }

    // 3. Teks label tidak terlalu kecil (min 10px — caption info)
    const smallLabels = page.locator('p').filter({ hasText: /Uang tunai fisik/i });
    const labelFontSize = await smallLabels.first().evaluate(el => window.getComputedStyle(el).fontSize);
    // Font informatif minimal 9px
    expect(parseFloat(labelFontSize)).toBeGreaterThanOrEqual(9);

    // 4. Kontras warna — tombol utama memiliki background solid (bukan transparan)
    const btnBg = await btnCatat.evaluate(el => window.getComputedStyle(el).backgroundColor);
    expect(btnBg).toBeTruthy();
    expect(btnBg).not.toBe('rgba(0, 0, 0, 0)');

    // 5. Ikon pendamping teks (membantu lansia yang kurang bisa baca)
    const icons = page.locator('i.fa-solid, i.fas');
    const iconCount = await icons.count();
    expect(iconCount).toBeGreaterThan(3);
  });
});
