// resources/js/app.js

import Alpine from 'alpinejs';
import './bootstrap';


// Use this to expose jQuery globally for older libraries that need it
import jQuery from 'jquery';
window.$ = window.jQuery = jQuery;

import './dashboard.js';
import './products.js';

// Import Bootstrap's full JS bundle
import 'bootstrap';

// Import all of jQuery UI.
import 'jquery-ui/dist/jquery-ui';

// Import jQuery UI's CSS files
import 'jquery-ui/themes/base/all.css';

// Step 1: Import the core DataTables library
import 'datatables.net';

// Step 2: Import the Bootstrap 5 integration files
import 'datatables.net-bs5/css/dataTables.bootstrap5.css';
import 'datatables.net-bs5/js/dataTables.bootstrap5.js';


window.Alpine = Alpine;
Alpine.start();

