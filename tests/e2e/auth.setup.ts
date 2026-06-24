/**
 * Auth Setup — Global authentication via storageState.
 * Semua test akan mewarisi state login setelah file ini dijalankan.
 */
import { test as setup, expect } from '@playwright/test';
import path from 'path';
import { fileURLToPath } from 'url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);
const AUTH_FILE = path.join(__dirname, '.auth', 'user.json');

setup('login dan simpan authentication state', async ({ page }) => {
  await page.goto('/login');

  // Isi email & password
  await page.locator('input[name="email"]').fill('admin@admin.com');
  await page.locator('input[name="password"]').fill('password');

  // Klik tombol Masuk
  await page.getByRole('button', { name: /Masuk/i }).click();

  // Tunggu redirect ke dashboard
  await expect(page).toHaveURL('/');
  await expect(page.getByRole('heading', { name: /Pusat Kendali/i })).toBeVisible();

  // Simpan state ke file
  await page.context().storageState({ path: AUTH_FILE });
});
