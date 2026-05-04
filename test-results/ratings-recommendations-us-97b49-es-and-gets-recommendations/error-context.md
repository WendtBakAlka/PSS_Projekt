# Instructions

- Following Playwright test failed.
- Explain why, be concise, respect Playwright best practices.
- Provide a snippet of code with the fix, if possible.

# Test info

- Name: ratings-recommendations.spec.cjs >> user rates games and gets recommendations
- Location: tests\E2E\ratings-recommendations.spec.cjs:3:1

# Error details

```
Error: page.click: Target page, context or browser has been closed
Call log:
  - waiting for locator('.game-card:first-child .rating-star-5')

```

# Test source

```ts
  1  | const { test, expect } = require('@playwright/test');
  2  | 
  3  | test('user rates games and gets recommendations', async ({ page }) => {
  4  |     // Logowanie
  5  |     await page.goto('/login');
  6  |     await page.fill('input[name="email"]', 'test@test.test');
  7  |     await page.fill('input[name="password"]', 'testtest');
  8  |     await page.click('button[type="submit"]');
  9  | 
  10 |     // Przejście do strony z grami
  11 |     await page.goto('/games');
  12 | 
  13 |     // Oceń pierwszą grę na 5 gwiazdek
> 14 |     await page.click('.game-card:first-child .rating-star-5');
     |                ^ Error: page.click: Target page, context or browser has been closed
  15 |     await expect(page.locator('.toast-success')).toBeVisible();
  16 | 
  17 |     // Oceń drugą grę na 4 gwiazdki
  18 |     await page.click('.game-card:nth-child(2) .rating-star-4');
  19 |     await expect(page.locator('.toast-success')).toBeVisible();
  20 | 
  21 |     // Odczekaj na przetworzenie rekomendacji
  22 |     await page.waitForTimeout(3000);
  23 | 
  24 |     // Przejdź do strony rekomendacji
  25 |     await page.goto('/recommendations');
  26 | 
  27 |     // Sprawdź czy pojawiły się rekomendacje
  28 |     await expect(page.locator('.recommendation-card')).toBeVisible({ timeout: 10000 });
  29 | });
  30 | 
```