# Instructions

- Following Playwright test failed.
- Explain why, be concise, respect Playwright best practices.
- Provide a snippet of code with the fix, if possible.

# Test info

- Name: add-to-library.spec.cjs >> user can add game to library
- Location: tests\E2E\add-to-library.spec.cjs:3:1

# Error details

```
Error: page.selectOption: Target page, context or browser has been closed
Call log:
  - waiting for locator('select[name="status"]')
    - locator resolved to <select required="" name="status" class="form-select search-input">…</select>
  - attempting select option action
    2 × waiting for element to be visible and enabled
      - did not find some options
    - retrying select option action
    - waiting 20ms
    2 × waiting for element to be visible and enabled
      - did not find some options
    - retrying select option action
      - waiting 100ms
    8 × waiting for element to be visible and enabled
      - did not find some options
    - retrying select option action
      - waiting 500ms

```

# Test source

```ts
  1  | const { test, expect } = require('@playwright/test');
  2  | 
  3  | test('user can add game to library', async ({ page }) => {
  4  |     // 1. Logowanie
  5  |     await page.goto('http://localhost:8000/login');
  6  |     await page.fill('input[name="email"]', 'test@test.test');
  7  |     await page.fill('input[name="password"]', 'testtest');
  8  |     await page.click('button[type="submit"]');
  9  |     await page.waitForURL('http://localhost:8000/games');
  10 | 
  11 |     // 2. Idź do szczegółów gry
  12 |     await page.goto('http://localhost:8000/games/1');
  13 | 
  14 |     // 3. Czekamy na nagłówek "Biblioteka" (formularz jest pod nim)
  15 |     await page.waitForSelector('h4:has-text("Biblioteka")', { timeout: 15000 });
  16 | 
  17 |     // 4. Czekamy na select
  18 |     await page.waitForSelector('select[name="status"]', { timeout: 5000 });
  19 | 
  20 |     // 5. Wybierz status
> 21 |     await page.selectOption('select[name="status"]', 'completed');
     |                ^ Error: page.selectOption: Target page, context or browser has been closed
  22 | 
  23 |     // 6. Wypełnij ocenę
  24 |     await page.fill('input[name="rating"]', '9');
  25 | 
  26 |     // 7. Kliknij przycisk "Dodaj do biblioteki"
  27 |     await page.click('button:has-text("Dodaj do biblioteki")');
  28 | 
  29 |     // 8. Sprawdź komunikat sukcesu
  30 |     await page.waitForSelector('.alert-success', { timeout: 5000 });
  31 |     await expect(page.locator('.alert-success')).toBeVisible();
  32 | 
  33 |     // 9. Przejdź do biblioteki
  34 |     await page.goto('http://localhost:8000/library');
  35 |     await page.waitForSelector('.card-custom', { timeout: 10000 });
  36 | 
  37 |     await page.screenshot({ path: 'test-results/final-success.png' });
  38 | });
  39 | 
```