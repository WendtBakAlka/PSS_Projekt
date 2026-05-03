const { test, expect } = require('@playwright/test');

test('user rates games and gets recommendations', async ({ page }) => {
    // Logowanie
    await page.goto('/login');
    await page.fill('input[name="email"]', 'test@test.test');
    await page.fill('input[name="password"]', 'testtest');
    await page.click('button[type="submit"]');

    // Przejście do strony z grami
    await page.goto('/games');

    // Oceń pierwszą grę na 5 gwiazdek
    await page.click('.game-card:first-child .rating-star-5');
    await expect(page.locator('.toast-success')).toBeVisible();

    // Oceń drugą grę na 4 gwiazdki
    await page.click('.game-card:nth-child(2) .rating-star-4');
    await expect(page.locator('.toast-success')).toBeVisible();

    // Odczekaj na przetworzenie rekomendacji
    await page.waitForTimeout(3000);

    // Przejdź do strony rekomendacji
    await page.goto('/recommendations');

    // Sprawdź czy pojawiły się rekomendacje
    await expect(page.locator('.recommendation-card')).toBeVisible({ timeout: 10000 });
});
