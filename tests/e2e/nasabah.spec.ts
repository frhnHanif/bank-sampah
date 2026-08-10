import { expect, Page, test } from '@playwright/test';
import { loginAsAdmin } from './helpers';

async function createCustomer(page: Page, prefix = 'Nasabah E2E') {
  const name = `${prefix} ${Date.now()}`;
  await page.getByRole('button', { name: 'Tambah Nasabah' }).click();
  const form = page.locator('#modalCreate form');
  await expect(form).toBeVisible();
  await form.locator('input[name="nama"]').fill(name);
  await form.locator('input[name="rt"]').fill('3');
  await form.locator('input[name="rw"]').fill('1');
  await form.locator('input[name="no_hp"]').fill('081234567890');
  await form.getByRole('button', { name: 'Simpan & Daftarkan' }).click();
  await expect(page.getByText(/berhasil didaftarkan dengan Kode:/i)).toBeVisible();
  await expect(page.getByText(name, { exact: true })).toBeVisible();
  return name;
}

test.describe('Nasabah', () => {
  test.beforeEach(async ({ page }) => {
    await loginAsAdmin(page, '/nasabah');
    await expect(page.getByRole('heading', { name: 'Data Nasabah', exact: true })).toBeVisible();
  });

  test('TC-06 | tambah nasabah membuat kode RW+RT+urutan', async ({ page }) => {
    await createCustomer(page);
    const message = page.getByText(/berhasil didaftarkan dengan Kode:/i);
    const code = ((await message.textContent()) ?? '').match(/\d{7}/)?.[0];
    expect(code).toBeTruthy();
    expect(code!.slice(0, 4)).toBe('0103');
  });

  test('TC-07 | pencarian nasabah memakai nama', async ({ page }) => {
    const name = await createCustomer(page, 'Cari Unik');
    await page.locator('#searchInput').fill(name);
    await page.locator('#searchInput').dispatchEvent('keyup');
    await expect(page.locator('.nasabah-card:visible')).toHaveCount(1);
    await expect(page.locator('.nasabah-card:visible')).toContainText(name);
  });

  test('TC-14 | edit profil mempertahankan buku tabungan', async ({ page }) => {
    const name = await createCustomer(page, 'Edit Unik');
    const card = page.locator('.nasabah-card').filter({ hasText: name });
    await card.locator('button[title="Edit"]').click();
    const form = page.locator('#modalEdit form');
    const editedName = `${name} Diperbarui`;
    await form.locator('input[name="nama"]').fill(editedName);
    await form.getByRole('button', { name: 'Simpan', exact: true }).click();
    await expect(page.getByText(/profil nasabah berhasil diperbarui/i)).toBeVisible();

    const editedCard = page.locator('.nasabah-card').filter({ hasText: editedName });
    await editedCard.getByRole('link', { name: /Tabungan/i }).click();
    await expect(page.getByRole('heading', { name: 'Buku Tabungan Nasabah' })).toBeVisible();
    await expect(page.getByText(editedName, { exact: true })).toBeVisible();
  });

  test('TC-15 | nonaktifkan nasabah menjaga data historis', async ({ page }) => {
    const name = await createCustomer(page, 'Nonaktif Unik');
    const card = page.locator('.nasabah-card').filter({ hasText: name });
    await card.getByTitle('Hapus').click();
    await expect(page.getByText('Konfirmasi Nonaktifkan Nasabah')).toBeVisible();
    await page.getByRole('button', { name: 'Ya, Lanjutkan' }).click();
    await expect(page.getByText(/berhasil dinonaktifkan/i)).toBeVisible();
    await expect(page.getByText(name, { exact: true })).toHaveCount(0);
  });
});
