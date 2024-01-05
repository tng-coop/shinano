import { Page, test as baseTest } from '@playwright/test';

const pageFactory:any = async ({ browser }, use) => {
    const pages : Page[] = [];

    // Function to create new pages
    async function createPage() {
      const page = await browser.newPage();
      pages.push(page);
      return page;
    }

    // Provide the factory function to the test
    await use(createPage);

    // Cleanup: close all pages created by the factory
    for (const page of pages) {
      await page.close();
    }
  }
export { pageFactory }; 