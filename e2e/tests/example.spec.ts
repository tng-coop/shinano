import { expect } from '@playwright/test';
import { test } from './fixtures';
import { promises as fsPromises } from 'fs/promises'; // Import fsPromises correctly
import config from '../auto-converted-php-config.json';
import fs from 'fs';
import path from 'path';

test('Login and See Initial Page for a Cooperator', async ({ page }) => {
  await page.goto('/');
  await page.locator('#head_cooperator_s_menu').getByRole('link', { name: 'login' }).click()
  await page.locator('input[name="email"]').fill('Tng_0001@tng.coop');
  await page.locator('input[name="password"]').fill('tng_0001')
  await page.getByRole('button', { name: 'login' }).click()
  await expect(page.getByRole('heading', { name: 'Hello, "TNG Coop Tokyo" !!!' })).toHaveCount(1)
});

test('Create a new ', async ({ pageFactory, lockFactory }) => {
  const phpServerIp = config.development.php_server_ip;
  const phpServerPort = config.development.php_server_port;
  const mailDevPort = '1080';
  const mailDevURL = `http://${phpServerIp}:${mailDevPort}/`;
  const phpServerURL = `http://${phpServerIp}:${phpServerPort}/`;
  const page = await pageFactory();
  const page2 = await pageFactory();
  const pageM = await pageFactory();

  const releaseLock = await lockFactory('email');
  await pageM.goto(mailDevURL);
  await pageM.getByRole('link', { name: '' }).dblclick();
  await page.goto('/');
  await page.locator('#content_actual').getByRole('link', { name: 'create' }).click();
  await page.locator('input[name="email"]').click();
  await page.locator('input[name="email"]').fill('yasu@yasuaki.com');
  await page.getByRole('button', { name: 'Check for Email' }).click()
  await pageM.getByRole('link', { name: '[Shinano] Account Create step' }).click()
  // Locate the iframe and then the link within it
  const frame = await pageM.frameLocator('iframe').first();
  const link = await frame.getByRole('link', { name: phpServerURL });

  // Get the href attribute from the link
  const url = await link.getAttribute('href');

  // Now you can use the URL as needed
  await page.goto(url);
  await page.getByLabel('Name').fill('Yasuaki Kudo');
  await page.getByLabel('Name').press('Tab');
  await page.getByLabel('Email').press('Tab');
  await page.getByLabel('Password', { exact: true }).fill('asdfQWER12#$');
  await page.getByLabel('Password', { exact: true }).press('Tab');
  await page.getByLabel('Confirm Password').fill('asdfQWER12#$');
  await page.getByRole('button', { name: 'Check for Create Account' }).click();

  await page.goto('/');
  await expect(page.getByRole('heading', { name: 'Hello! Yasuaki Kudo' })).toHaveCount(1)

  await page2.goto('/');
  await page2.locator('#head_cooperator_s_menu').getByRole('link', { name: 'login' }).click()
  await page2.locator('input[name="email"]').fill('yasu@yasuaki.com');
  await page2.locator('input[name="password"]').fill('asdfQWER12#$');
  await page2.getByRole('button', { name: 'login' }).click()
  await expect(page2.getByRole('heading', { name: 'Hello, "Yasuaki Kudo" !!!' })).toHaveCount(1)

  await releaseLock();

});

test('Test Lock System', async ({ lockFactory }) => {
  let sharedCounter = 0;
  const numberOfTasks = 5;
  const delay = ms => new Promise(resolve => setTimeout(resolve, ms));

  // Function to increment the counter with a delay, simulating a more complex operation
  const incrementCounter = async () => {
    const releaseLock = await lockFactory('counterLock');

    const currentCount = sharedCounter; // Read the current value
    await delay(50);                  // Simulate a delay, during which race conditions can occur
    sharedCounter = currentCount + 1;  // Update the counter

    await releaseLock();
  };

  // Run multiple tasks concurrently
  const tasks = Array.from({ length: numberOfTasks }, () => incrementCounter());
  await Promise.all(tasks);

  // Verify that the counter has been incremented correctly
  expect(sharedCounter).toBe(numberOfTasks);
});
