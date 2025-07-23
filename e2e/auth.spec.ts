import { test, expect } from '@playwright/test';

test.describe('Authentication Flow', () => {
  test('should allow user to register and login', async ({ page }) => {
    // Registration
    await page.goto('/register');
    await page.fill('[name="email"]', `test${Date.now()}@example.com`);
    await page.fill('[name="password"]', 'TestPassword123!');
    await page.fill('[name="name"]', 'Test User');
    await page.click('button[type="submit"]');
    
    // Verify redirect to dashboard
    await expect(page).toHaveURL('/dashboard');
    
    // Verify user info is displayed
    await expect(page.locator('[data-testid="user-name"]')).toContainText('Test User');
    
    // Logout
    await page.click('[data-testid="logout-button"]');
    await expect(page).toHaveURL('/login');
    
    // Login
    await page.fill('[name="email"]', `test${Date.now()}@example.com`);
    await page.fill('[name="password"]', 'TestPassword123!');
    await page.click('button[type="submit"]');
    
    // Verify login success
    await expect(page).toHaveURL('/dashboard');
  });

  test('should show error messages for invalid inputs', async ({ page }) => {
    await page.goto('/register');
    
    // Try submitting empty form
    await page.click('button[type="submit"]');
    
    // Verify error messages
    await expect(page.locator('text=Email is required')).toBeVisible();
    await expect(page.locator('text=Password is required')).toBeVisible();
    await expect(page.locator('text=Name is required')).toBeVisible();
    
    // Try invalid email
    await page.fill('[name="email"]', 'invalid-email');
    await page.click('button[type="submit"]');
    await expect(page.locator('text=Invalid email address')).toBeVisible();
    
    // Try weak password
    await page.fill('[name="email"]', 'test@example.com');
    await page.fill('[name="password"]', '123');
    await page.click('button[type="submit"]');
    await expect(page.locator('text=Password must be at least 8 characters')).toBeVisible();
  });
});

test.describe('Navigation Features', () => {
  test('should show current location on map', async ({ page }) => {
    // Login first
    await page.goto('/login');
    await page.fill('[name="email"]', 'test@example.com');
    await page.fill('[name="password"]', 'TestPassword123!');
    await page.click('button[type="submit"]');
    
    // Go to map page
    await page.goto('/map');
    
    // Wait for map to load
    await page.waitForSelector('[data-testid="map-container"]');
    
    // Verify map controls are present
    await expect(page.locator('[data-testid="zoom-in"]')).toBeVisible();
    await expect(page.locator('[data-testid="zoom-out"]')).toBeVisible();
    
    // Verify location marker is shown
    await expect(page.locator('[data-testid="current-location-marker"]')).toBeVisible();
  });
});
