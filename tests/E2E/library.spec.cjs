const { test, expect } = require('@playwright/test');

test('user can view and filter library', async ({ page }) => {
    // Logowanie
    await page.goto('http://localhost:8000/login');
    await page.fill('input[name="email"]', 'test@test.test');
    await page.fill('input[name="password"]', 'testtest');
    await page.click('button[type="submit"]');
    await page.waitForURL('http://localhost:8000/games');

    // Przejdź do biblioteki
    await page.goto('http://localhost:8000/library');
    await page.waitForSelector('.card-custom, .col-md-6', { timeout: 10000 });

    // Sprawdź czy filtr działa
    await page.selectOption('select[name="status"]', 'finished');
    await page.click('button:has-text("Filtruj")');

    // Poczekaj na odświeżenie
    await page.waitForTimeout(2000);

    await page.screenshot({ path: 'test-results/library-filtered.png' });
});
