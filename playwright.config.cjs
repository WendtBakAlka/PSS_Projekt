const { defineConfig, devices } = require('@playwright/test');

module.exports = defineConfig({
    testDir: './tests/E2E',
    timeout: 30000,
    use: {
        baseURL: 'http://localhost:8000',
        headless: false,
        actionTimeout: 60000,
        navigationTimeout: 60000,
        screenshot: 'only-on-failure',
    },
    webServer: {
        command: 'php artisan serve --port=8000',
        port: 8000,
        reuseExistingServer: true,
    },
});
