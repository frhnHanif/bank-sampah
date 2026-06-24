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
    await expect(page.getByRole('heading', { name: /Stok Gudang/i })).toBeVisible();

    // Tombol "Jual ke Pengepul" ada
    const jualBtn = page.getByRole('button', { name: /Jual ke Pengepul/i });
    await expect(jualBtn).toBeVisible();

    // Harus ada kartu stok (setidaknya 1)
    const stokCards = page.locator('[class*="rounded"]').filter({ hasText: /Kg/i });
    const count = await stokCards.count();
    expect(count).toBeGreaterThanOrEqual(1);

    // Setiap kartu menampilkan nama kategori & berat
    const firstCard = stokCards.first();
    await expect(firstCard.locator('h3')).toBeVisible();
    await expect(firstCard.locator('h2')).toBeVisible();
  });
});
