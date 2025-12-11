import inject from '@rollup/plugin-inject';
import laravel from 'laravel-vite-plugin';
import path from 'path';
import { defineConfig } from 'vite';

export default defineConfig({
    plugins: [
        inject({
            $: 'jquery',
            jQuery: 'jquery',
        }),
        laravel({
             input: [
                'resources/css/app.css',
                'resources/js/app.js',          // Global stuff (Bootstrap/Alpine)
                'resources/js/dashboard.js',    // Dashboard only (Charts)
                'resources/js/products.js',     // Product List only (DataTables)
                'resources/js/create-sales.js',
                'resources/js/sales-overview.js',
                'resources/js/transactions.js',
                'resources/js/product-create.js',  // POS Terminal only
            ],
            refresh: true,
        }),
    ],
    resolve: {
        alias: {
            'bootstrap': path.resolve(__dirname, 'node_modules/bootstrap'),
        }
    },
});
