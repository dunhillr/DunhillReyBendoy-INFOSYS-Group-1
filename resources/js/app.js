// resources/js/app.js

import Alpine from 'alpinejs';
import './bootstrap';

// 1. Global jQuery Setup
import jQuery from 'jquery';
window.$ = window.jQuery = jQuery;

// 2. Import Bootstrap & Plugins
import 'bootstrap';
import 'datatables.net';
import 'datatables.net-bs5/css/dataTables.bootstrap5.css';
import 'datatables.net-bs5/js/dataTables.bootstrap5.js';
import 'jquery-ui/dist/jquery-ui';
import 'jquery-ui/themes/base/all.css';

// 4. Initialize Alpine
window.Alpine = Alpine;
Alpine.start();

