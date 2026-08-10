/**
 * Stok Gudang Tests
 *
 * Skenario:
 *   TC-08: Halaman stok menampilkan inventori & tombol jual
 */
import { test, expect } from '@playwright/test';
import { loginAsAdmin } from './helpers';

test.describe('Stok Gudang', () => {

  test('TC-08 | Halaman stok menampilkan data inventori sampah', async ({ page }) => {
    await loginAsAdmin(page, '/stok');
    await expect(page.getByRole('heading', { name: /Stok & Settlement/i })).toBeVisible();

    // Tombol "Jual ke Pengepul" ada
    const jualBtn = page.getByRole('button', { name: /Jual ke Pengepul/i });
    await expect(jualBtn).toBeVisible();

    // Jika gudang berisi stok, kartu membedakan legacy dan pending baru.
    const stokCards = page.locator('article').filter({ hasText: /Stok fisik/i });
    const count = await stokCards.count();
    if (count > 0) {
      await expect(page.getByText('Legacy').first()).toBeVisible();
      await expect(page.getByText('Pending baru').first()).toBeVisible();
    } else {
      await expect(page.getByText(/Gudang masih kosong/i)).toBeVisible();
    }
  });
});
