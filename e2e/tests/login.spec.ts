import { expect } from '@playwright/test';
import { test } from './fixtures';

test('Login and See Initial Page for a Cooperator', async ({ page }) => {
  await page.goto('./');
  await page.locator('#head_cooperator_s_menu').getByRole('link', { name: 'login' }).click()
  await page.locator('input[name="email"]').fill('Tng_0001@tng.coop');
  await page.locator('input[name="password"]').fill('tng_0001')
  await page.getByRole('button', { name: 'login' }).click()
  await expect(page.getByRole('heading', { name: 'Hello, "TNG Coop Tokyo" !!!' })).toHaveCount(1)
});
