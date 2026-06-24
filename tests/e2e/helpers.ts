/**
 * Helper: Login function yang bisa dipakai di semua test.
 * Melakukan login lalu kembali ke halaman yang diminta.
 */
import { Page } from '@playwright/test';

export async function loginAsAdmin(page: Page, targetPath: string = '/') {
  // Cek apakah sudah login (ada elemen admin di navbar)
  const alreadyLoggedIn = await page.locator('a[onclick*="logout"], text=Admin').first().isVisible().catch(() => false);

  if (!alreadyLoggedIn) {
    await page.goto('/login');
    await page.locator('input[name="email"]').fill('admin@admin.com');
    await page.locator('input[name="password"]').fill('password');
    await page.getByRole('button', { name: /Masuk/i }).click();
    await page.waitForURL('/');
  }

  await page.goto(targetPath);
  await page.waitForLoadState('networkidle');
}
