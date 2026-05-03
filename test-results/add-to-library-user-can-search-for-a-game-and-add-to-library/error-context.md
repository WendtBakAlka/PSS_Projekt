# Instructions

- Following Playwright test failed.
- Explain why, be concise, respect Playwright best practices.
- Provide a snippet of code with the fix, if possible.

# Test info

- Name: add-to-library.spec.cjs >> user can search for a game and add to library
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
    5 × waiting for element to be visible and enabled
      - did not find some options
    - retrying select option action
      - waiting 500ms

```

# Test source

```ts
  1  | const { test, expect } = require('@playwright/test');
  2  | 
  3  | test('user can search for a game and add to library', async ({ page }) => {
  4  |     // 1. Logowanie
  5  |     await page.goto('http://localhost:8000/login');
  6  |     await page.fill('input[name="email"]', 'test@test.test');
  7  |     await page.fill('input[name="password"]', 'testtest');
  8  |     await page.click('button[type="submit"]');
  9  |     await page.waitForURL('http://localhost:8000/games');
  10 | 
  11 |     // 2. Wyszukaj grę
  12 |     await page.fill('#searchInput', 'witcher');
  13 |     await page.click('button:has-text("Szukaj")');
  14 | 
  15 |     // 3. Poczekaj na wyniki i kliknij w Szczegóły
  16 |     await page.waitForSelector('.card-custom', { timeout: 15000 });
  17 |     await page.click('.card-custom:first-child .btn-outline-light');
  18 | 
  19 |     // 4. CZEKAMY NA STRONĘ SZCZEGÓŁÓW (kluczowe!)
  20 |     await page.waitForURL(/\/games\/\d+/, { timeout: 15000 });
  21 | 
  22 |     // 5. Czekamy na formularz biblioteki (może być długo, bo dane z RAWG)
  23 |     await page.waitForSelector('#libraryForm', { timeout: 20000 });
  24 |     await page.waitForSelector('select[name="status"]', { timeout: 10000 });
  25 | 
  26 |     // 6. Dodatkowe opóźnienie – RAWG API może być wolne
  27 |     await page.waitForTimeout(2000);
  28 | 
  29 |     // 7. Wybierz status i dodaj ocenę
> 30 |     await page.selectOption('select[name="status"]', 'completed');
     |                ^ Error: page.selectOption: Target page, context or browser has been closed
  31 |     await page.fill('input[name="rating"]', '9');
  32 | 
  33 |     // 8. Kliknij przycisk "Dodaj do biblioteki"
  34 |     await page.click('button[type="submit"]');
  35 | 
  36 |     // 9. Sprawdź komunikat sukcesu
  37 |     await page.waitForSelector('.alert-success', { timeout: 10000 });
  38 |     await expect(page.locator('.alert-success')).toBeVisible();
  39 | 
  40 |     // 10. Przejdź do biblioteki i sprawdź
  41 |     await page.goto('http://localhost:8000/library');
  42 |     await page.waitForSelector('.card-custom', { timeout: 10000 });
  43 |     await expect(page.locator('.card-custom')).toContainText('The Witcher');
  44 | });
  45 | 
```