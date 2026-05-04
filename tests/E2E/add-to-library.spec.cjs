const { test, expect } = require('@playwright/test');

test('user can add game to library', async ({ page }) => {
    // 1. Logowanie
    await page.goto('http://localhost:8000/login');
    await page.fill('input[name="email"]', 'test@test.test');
    await page.fill('input[name="password"]', 'testtest');
    await page.click('button[type="submit"]');
    await page.waitForURL('http://localhost:8000/games');

    // 2. Idź do szczegółów gry
    await page.goto('http://localhost:8000/games/1');

    // 3. Czekamy na nagłówek "Biblioteka" (formularz jest pod nim)
    await page.waitForSelector('h4:has-text("Biblioteka")', { timeout: 15000 });

    // 4. Czekamy na select
    await page.waitForSelector('select[name="status"]', { timeout: 5000 });

    // 5. Wybierz status
    await page.selectOption('select[name="status"]', 'completed');

    // 6. Wypełnij ocenę
    await page.fill('input[name="rating"]', '9');

    // 7. Kliknij przycisk "Dodaj do biblioteki"
    await page.click('button:has-text("Dodaj do biblioteki")');

    // 8. Sprawdź komunikat sukcesu
    await page.waitForSelector('.alert-success', { timeout: 5000 });
    await expect(page.locator('.alert-success')).toBeVisible();

    // 9. Przejdź do biblioteki
    await page.goto('http://localhost:8000/library');
    await page.waitForSelector('.card-custom', { timeout: 10000 });

    await page.screenshot({ path: 'test-results/final-success.png' });
});
