/**
 * Authentication Tests
 * 
 * Skenario:
 *   TC-01: Login dengan kredensial valid
 *   TC-02: Login dengan password salah → pesan error
 *   TC-12: Logout → kembali ke dashboard publik
 */
import { test, expect } from '@playwright/test';
import { loginAsAdmin } from './helpers';

test.describe('Authentication', () => {

  // TC-01: Login valid
  test('TC-01 | Login dengan kredensial valid', async ({ page }) => {
    await page.goto('/login');

    await expect(page.getByRole('heading', { name: /Login Pengurus/i })).toBeVisible();
    await expect(page.locator('input[name="email"]')).toBeVisible();
    await expect(page.locator('input[name="password"]')).toBeVisible();
    await expect(page.getByRole('button', { name: /Masuk/i })).toBeVisible();

    // Pelabelan besar & jelas untuk lansia
    const emailInput = page.locator('input[name="email"]');
    const emailFontSize = await emailInput.evaluate(el => window.getComputedStyle(el).fontSize);
    expect(parseFloat(emailFontSize)).toBeGreaterThanOrEqual(14);

    // Isi & submit
    await emailInput.fill('admin@admin.com');
    await page.locator('input[name="password"]').fill('password');
    await page.getByRole('button', { name: /Masuk/i }).click();

    // Assert redirect ke dashboard
    await expect(page).toHaveURL('/');
    await expect(page.getByRole('heading', { name: /Pusat Kendali/i })).toBeVisible();
  });

  // TC-02: Login gagal — validasi error
  test('TC-02 | Login dengan password salah → pesan error', async ({ page }) => {
    await page.goto('/login');

    await page.locator('input[name="email"]').fill('admin@admin.com');
    await page.locator('input[name="password"]').fill('password_salah');
    await page.getByRole('button', { name: /Masuk/i }).click();

    // Tetap di halaman login
    await expect(page).toHaveURL('/login');
    // Pesan error muncul
    await expect(page.getByText(/Email atau password salah/i)).toBeVisible({ timeout: 5000 });
  });

  // TC-12: Logout
  test('TC-12 | Logout kembali ke dashboard publik', async ({ page }) => {
    // Login dulu
    await loginAsAdmin(page);

    // Klik tombol logout di navbar (link dengan onclick submit form)
    await page.locator('a[onclick*="logout"]').click();

    // Redirect ke dashboard publik
    await expect(page).toHaveURL('/');
    // Tidak ada link admin
    await expect(page.getByText('Admin')).not.toBeVisible();
  });
});
