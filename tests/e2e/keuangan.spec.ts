import { expect, Locator, test } from '@playwright/test';
import { loginAsAdmin } from './helpers';

const money = async (locator: Locator) => {
  const text = (await locator.textContent()) ?? '';
  const match = text.match(/Rp\s*(-?[\d.]+)/);
  expect(match, `Nominal Rupiah tidak ditemukan pada: ${text}`).toBeTruthy();
  return Number(match![1].replace(/\./g, ''));
};

test.describe('Keuangan setelah settlement', () => {
  test.beforeEach(async ({ page }) => {
    await loginAsAdmin(page, '/keuangan');
    await expect(page.getByRole('heading', { name: 'Buku Kas & Keuangan' })).toBeVisible();
  });

  test('TC-09 | memisahkan kas, omset, kewajiban, dan laba', async ({ page }) => {
    await expect(page.getByText('Kas Aktual', { exact: true })).toBeVisible();
    await expect(page.getByText('Omset Penjualan Pengepul', { exact: true })).toBeVisible();
    await expect(page.getByText('Kewajiban Tabungan Nasabah', { exact: true })).toBeVisible();
    await expect(page.getByText('Laba Setelah Operasional', { exact: true })).toBeVisible();
    await expect(page.getByTestId('metric-cash')).toContainText('Rp');
    await expect(page.getByTestId('metric-revenue')).toContainText('Rp');
    await expect(page.getByTestId('metric-liability')).toContainText('Rp');
    await expect(page.getByTestId('metric-profit')).toContainText('Rp');
  });

  test('TC-10 | pengeluaran operasional mengurangi kas dan laba', async ({ page }) => {
    const cashBefore = await money(page.getByTestId('metric-cash'));
    const profitBefore = await money(page.getByTestId('metric-profit'));
    const description = `Operasional E2E ${Date.now()}`;

    await page.getByRole('button', { name: /Catat Operasional/i }).click();
    const form = page.locator('form[action*="keuangan/operasional"]');
    await expect(form).toBeVisible();
    await form.locator('input[name="tanggal"]').fill(new Date().toISOString().slice(0, 10));
    await form.locator('input[name="nominal"]').fill('35000');
    await form.locator('input[name="keterangan"]').fill(description);
    await form.getByRole('button', { name: 'Simpan Transaksi' }).click();

    await expect(page.getByText(/berhasil dicatat/i)).toBeVisible();
    await expect(page.locator('table').getByText(description)).toBeVisible();
    expect(await money(page.getByTestId('metric-cash'))).toBe(cashBefore - 35_000);
    expect(await money(page.getByTestId('metric-profit'))).toBe(profitBefore - 35_000);
  });

  test('TC-11 | filter bulan mempertahankan halaman jurnal', async ({ page }) => {
    await page.getByRole('button', { name: /Semua Waktu/i }).click();
    await page.getByRole('button', { name: 'Juni', exact: true }).click();
    await expect(page).toHaveURL(/bulan=6/);
    await expect(page.getByRole('heading', { name: 'Buku Kas & Keuangan' })).toBeVisible();
  });

  test('TC-16 | kas aktual sama dengan pemasukan dikurangi pengeluaran', async ({ page }) => {
    const parseCell = async (cell: Locator) => {
      const text = (await cell.textContent()) ?? '';
      const match = text.match(/Rp\s*([\d.]+)/);
      return match ? Number(match[1].replace(/\./g, '')) : 0;
    };
    let incoming = 0;
    let outgoing = 0;
    const incomingCells = page.locator('table tbody td:nth-child(4)');
    const outgoingCells = page.locator('table tbody td:nth-child(5)');
    for (let i = 0; i < await incomingCells.count(); i++) incoming += await parseCell(incomingCells.nth(i));
    for (let i = 0; i < await outgoingCells.count(); i++) outgoing += await parseCell(outgoingCells.nth(i));

    expect(await money(page.getByTestId('metric-cash'))).toBe(incoming - outgoing);
  });

  test('TC-17 | laba adalah margin terealisasi dikurangi operasional', async ({ page }) => {
    const grossMargin = await money(page.getByTestId('metric-gross-margin'));
    const operational = await money(page.getByTestId('metric-operational'));
    const profit = await money(page.getByTestId('metric-profit'));
    expect(profit).toBe(grossMargin - operational);

    const cashAfterLiability = await money(page.getByTestId('metric-cash-after-liability'));
    const cash = await money(page.getByTestId('metric-cash'));
    const liability = await money(page.getByTestId('metric-liability'));
    expect(cashAfterLiability).toBe(cash - liability);
  });

  test('TC-18 | kontrol utama tetap mudah dibaca dan disentuh', async ({ page }) => {
    const heading = page.getByRole('heading', { name: 'Buku Kas & Keuangan' });
    expect(parseFloat(await heading.evaluate(el => getComputedStyle(el).fontSize))).toBeGreaterThanOrEqual(18);
    const button = page.getByRole('button', { name: /Catat Operasional/i });
    const box = await button.boundingBox();
    expect(box).toBeTruthy();
    expect(box!.height).toBeGreaterThanOrEqual(36);
    expect(box!.width).toBeGreaterThanOrEqual(100);
    expect(await button.evaluate(el => getComputedStyle(el).backgroundColor)).not.toBe('rgba(0, 0, 0, 0)');
  });
});
