const { test, expect } = require('@playwright/test');

test('user can search for a game and add to library', async ({ page }) => {
    // 1. Logowanie
    await page.goto('http://localhost:8000/login');
    await page.fill('input[name="email"]', 'test@test.test');
    await page.fill('input[name="password"]', 'testtest');
    await page.click('button[type="submit"]');
    await page.waitForURL('http://localhost:8000/games');

    // 2. Wyszukaj grę
    await page.fill('#searchInput', 'witcher');
    await page.click('button:has-text("Szukaj")');

    // 3. Poczekaj na wyniki i kliknij w Szczegóły
    await page.waitForSelector('.card-custom', { timeout: 15000 });
    await page.click('.card-custom:first-child .btn-outline-light');

    // 4. CZEKAMY NA STRONĘ SZCZEGÓŁÓW (kluczowe!)
    await page.waitForURL(/\/games\/\d+/, { timeout: 15000 });

    // 5. Czekamy na formularz biblioteki (może być długo, bo dane z RAWG)
    await page.waitForSelector('#libraryForm', { timeout: 20000 });
    await page.waitForSelector('select[name="status"]', { timeout: 10000 });

    // 6. Dodatkowe opóźnienie – RAWG API może być wolne
    await page.waitForTimeout(2000);

    // 7. Wybierz status i dodaj ocenę
    await page.selectOption('select[name="status"]', 'completed');
    await page.fill('input[name="rating"]', '9');

    // 8. Kliknij przycisk "Dodaj do biblioteki"
    await page.click('button[type="submit"]');

    // 9. Sprawdź komunikat sukcesu
    await page.waitForSelector('.alert-success', { timeout: 10000 });
    await expect(page.locator('.alert-success')).toBeVisible();

    // 10. Przejdź do biblioteki i sprawdź
    await page.goto('http://localhost:8000/library');
    await page.waitForSelector('.card-custom', { timeout: 10000 });
    await expect(page.locator('.card-custom')).toContainText('The Witcher');
});
