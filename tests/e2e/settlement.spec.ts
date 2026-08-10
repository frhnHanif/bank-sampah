import { expect, Page, test } from '@playwright/test';
import { loginAsAdmin } from './helpers';

async function createCustomer(page: Page) {
  const name = `Settlement E2E ${Date.now()}`;
  await loginAsAdmin(page, '/nasabah');
  await page.getByRole('button', { name: 'Tambah Nasabah' }).click();
  const form = page.locator('#modalCreate form');
  await form.locator('input[name="nama"]').fill(name);
  await form.locator('input[name="rt"]').fill('4');
  await form.locator('input[name="rw"]').fill('1');
  await form.locator('input[name="no_hp"]').fill('081234567891');
  await form.getByRole('button', { name: 'Simpan & Daftarkan' }).click();
  await expect(page.getByText(name, { exact: true })).toBeVisible();
  return name;
}

test('happy path | setoran pending baru bernilai saat settlement penjualan', async ({ page }) => {
  const customer = await createCustomer(page);

  await page.goto('/setor/create');
  await expect(page.getByRole('heading', { name: 'Penerimaan Setoran' })).toBeVisible();
  await page.locator('#customerSearch').fill(customer);
  await page.getByRole('button', { name: new RegExp(customer) }).click();

  const categoryButton = page.locator('#categoryGrid > button').first();
  const category = (await categoryButton.locator('div').first().textContent())!.trim();
  await categoryButton.click();
  await page.locator('#weightInput').fill('2.5');
  await page.getByRole('button', { name: 'Tambahkan' }).click();
  await expect(page.getByText('Nilai belum ditentukan')).toBeVisible();
  await page.getByRole('button', { name: 'Simpan Setoran Pending' }).click();
  await page.getByRole('button', { name: 'Ya, Lanjutkan' }).click();
  await expect(page.getByText(/Setoran dicatat sebagai pending/i)).toBeVisible();

  await page.goto('/nasabah');
  await page.locator('.nasabah-card').filter({ hasText: customer }).getByRole('link', { name: /Tabungan/i }).click();
  await expect(page.getByTestId('available-balance')).toContainText('Rp 0');
  await expect(page.getByText('MENUNGGU')).toBeVisible();

  await page.goto('/stok');
  await page.getByRole('button', { name: /Jual ke Pengepul/i }).click();
  await page.locator('#saleCategory').selectOption({ label: category });
  await page.locator('#saleWeight').fill('2.5');
  await page.locator('#collectorPrice').fill('10000');
  await page.locator('#customerPrice').fill('8000');
  await page.getByRole('button', { name: 'Tambahkan ke Penjualan' }).click();
  await expect(page.locator('#cartRights')).toContainText('20.000');
  await page.getByRole('button', { name: 'Proses Atomic' }).click();
  await page.getByRole('button', { name: 'Ya, Lanjutkan' }).click();
  await expect(page.getByText(/Penjualan dan settlement .*berhasil/i)).toBeVisible();

  await page.goto('/nasabah');
  await page.locator('.nasabah-card').filter({ hasText: customer }).getByRole('link', { name: /Tabungan/i }).click();
  await expect(page.getByTestId('available-balance')).toContainText('Rp 20.000');
  await expect(page.getByText('TERJUAL', { exact: true })).toBeVisible();
  await expect(page.getByText(/Nilai terealisasi:/)).toContainText('Rp 20.000');
});
