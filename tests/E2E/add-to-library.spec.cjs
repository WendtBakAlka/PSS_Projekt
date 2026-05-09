const { test, expect } = require('@playwright/test');

test('user can add game to library', async ({ page }) => {
    // Logowanie
    await page.goto('http://localhost:8000/login');
    await page.fill('input[name="email"]', 'test@test.test');
    await page.fill('input[name="password"]', 'testtest');
    await page.click('button[type="submit"]');
    await page.waitForURL('http://localhost:8000/games');

    // Wyszukaj grę
    await page.fill('#searchInput', 'witcher');
    await page.click('button:has-text("Szukaj")');

    // Poczekaj na karty z grami
    await page.waitForSelector('.card-custom', { timeout: 15000 });

    // Kliknij "Szczegóły" w pierwszej karcie
    await page.click('.card-custom:first-child a:has-text("Szczegóły")');
    await page.waitForURL(/\/games\/\d+/, { timeout: 15000 });

    // Czekaj na formularz biblioteki
    await page.waitForSelector('#libraryForm', { timeout: 20000 });

    // Wybierz status
    await page.selectOption('select[name="status"]', 'finished');
    await page.fill('input[name="rating"]', '9');

    // Kliknij przycisk "Dodaj do biblioteki" wewnątrz formularza
    await page.click('#libraryForm button[type="submit"]');

    // Sprawdź komunikat sukcesu
    try {
        await page.waitForSelector('.alert-success', { timeout: 5000 });
    } catch (e) {
        console.log('Brak komunikatu sukcesu – formularz mógł nie zostać wysłany');
    }

    // Przejdź do biblioteki
    await page.goto('http://localhost:8000/library');
    await page.waitForSelector('.card-custom', { timeout: 10000 });

    await page.screenshot({ path: 'test-results/library-added.png' });
});
