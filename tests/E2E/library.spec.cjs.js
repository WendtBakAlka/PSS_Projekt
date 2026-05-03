const { test, expect } = require('@playwright/test');

test('user can add game to library from details page', async ({ page }) => {
    // Logowanie
    await page.goto('http://localhost:8000/login');
    await page.fill('input[name="email"]', 'test@test.test');
    await page.fill('input[name="password"]', 'testtest');
    await page.click('button[type="submit"]');
    await page.waitForURL('http://localhost:8000/games');

    // Wyszukaj grę
    await page.fill('#searchInput', 'Witcher');
    await page.click('button:has-text("Szukaj")');
    await page.waitForSelector('.card-custom', { timeout: 10000 });

    // Wejdź w szczegóły gry
    await page.click('.card-custom:first-child .btn-outline-light');
    await page.waitForURL(/\/games\/\d+/);

    // Kliknij "Dodaj do biblioteki" (dostosuj selektor!)
    await page.click('button:has-text("Dodaj do biblioteki"), .add-to-library');

    // Sprawdź potwierdzenie
    await expect(page.locator('.alert-success, .toast-success')).toBeVisible();

    // Przejdź do biblioteki
    await page.goto('http://localhost:8000/library');

    // Sprawdź czy gra jest w bibliotece
    await expect(page.locator('body')).toContainText('Witcher');
});
