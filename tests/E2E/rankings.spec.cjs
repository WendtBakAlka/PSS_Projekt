const { test, expect } = require('@playwright/test');

test('rankings page loads and shows table', async ({ page }) => {
    // Logowanie
    await page.goto('http://localhost:8000/login');
    await page.fill('input[name="email"]', 'test@test.test');
    await page.fill('input[name="password"]', 'testtest');
    await page.click('button[type="submit"]');
    await page.waitForURL('http://localhost:8000/games');

    // Kliknij w link "Rankingi" w navbarze
    await page.click('a:has-text("Rankingi")');
    await page.waitForURL(/\/rankings\/top-rated/, { timeout: 10000 });

    // Sprawdź czy tabela istnieje
    await page.waitForSelector('.table-gamelist', { timeout: 10000 });
    await expect(page.locator('.table-gamelist')).toBeVisible();

    // Sprawdź czy są zakładki
    await expect(page.locator('.nav-tabs')).toBeVisible();

    // Kliknij w drugą zakładkę "Najpopularniejsze"
    await page.click('a:has-text("Najpopularniejsze")');
    await page.waitForURL(/\/rankings\/most-popular/, { timeout: 5000 });

    await page.screenshot({ path: 'test-results/rankings.png' });
});
