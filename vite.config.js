import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/css/notifications.css', 'resources/css/users.css', 'resources/css/auth-motion.css', 'resources/css/dashboard-polish.css', 'resources/css/asset-scanner.css', 'resources/css/asset-qr-codes.css', 'resources/css/asset-reports.css', 'resources/css/asset-labels.css', 'resources/css/school-settings.css', 'resources/css/school-identity.css', 'resources/css/asset-mutations.css', 'resources/css/asset-loans.css', 'resources/css/asset-maintenances.css', 'resources/css/asset-disposals.css', 'resources/js/app.js', 'resources/js/users.js', 'resources/js/login.js', 'resources/js/dashboard.js', 'resources/js/asset-scanner.js', 'resources/js/asset-qr-codes.js', 'resources/js/asset-categories.js', 'resources/js/asset-locations.js', 'resources/js/funding-sources.js', 'resources/js/asset-form.js', 'resources/js/asset-labels.js', 'resources/js/school-settings.js', 'resources/js/asset-mutations.js', 'resources/js/asset-loans.js', 'resources/js/asset-maintenances.js', 'resources/js/asset-disposals.js'],
            refresh: true,
        }),
    ],
});
