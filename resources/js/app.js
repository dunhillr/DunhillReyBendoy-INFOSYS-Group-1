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

// 3. Import Your Page Scripts
// These will now run on EVERY page, so they must be written carefully (see step 4)
import './create-sales.js'; // ✅ Make sure this filename matches your actual file
import './dashboard.js';
import './products.js';

// 4. Initialize Alpine
window.Alpine = Alpine;
Alpine.start();

