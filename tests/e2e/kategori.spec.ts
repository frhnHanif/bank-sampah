import { test, expect } from '@playwright/test';
import { loginAsAdmin } from './helpers';

test.describe('Jenis sampah tanpa harga', () => {
  test.beforeEach(async ({ page }) => {
    await loginAsAdmin(page, '/kategori');
    await expect(page.getByRole('heading', { name: 'Jenis Sampah' })).toBeVisible();
  });

  test('membuat jenis baru tanpa field harga dan mencegah duplikat normalized', async ({ page }) => {
    await expect(page.locator('input[name="harga_beli_per_kg"]')).toHaveCount(0);
    await page.getByRole('button', { name: /Tambah Jenis/i }).click();
    const modal = page.locator('#categoryModal');
    await expect(modal).toBeVisible();
    const name = `Dinamo E2E ${Date.now()}`;
    await modal.locator('input[name="nama"]').fill(`  ${name}  `);
    await modal.getByRole('button', { name: 'Simpan' }).click();
    await expect(page.getByText(name).first()).toBeVisible();
    await expect(page.getByText('Belum diklasifikasikan').first()).toBeVisible();

    await page.getByRole('button', { name: /Tambah Jenis/i }).click();
    await modal.locator('input[name="nama"]').fill(name.toUpperCase());
    await modal.getByRole('button', { name: 'Simpan' }).click();
    await expect(page.getByText(/nama yang sama sudah aktif/i)).toBeVisible();
  });

  test('mengubah nama kategori tidak menambahkan transaksi kas', async ({ page }) => {
    await page.goto('/keuangan');
    const rowsBefore = await page.locator('table tbody tr').count();
    await page.goto('/kategori');
    await page.getByRole('button', { name: 'Edit' }).first().click();
    const input = page.locator('#categoryName');
    const current = await input.inputValue();
    await input.fill(`${current} E2E`);
    await page.locator('#categoryModal').getByRole('button', { name: 'Simpan' }).click();
    await expect(page.getByText(/berhasil diperbarui/i)).toBeVisible();
    await page.goto('/keuangan');
    await expect(page.locator('table tbody tr')).toHaveCount(rowsBefore);
  });
});
