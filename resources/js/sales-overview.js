import 'datatables.net-bs5';
import $ from 'jquery';

$(function() {
    console.log("✅ Sales Overview Script Loaded");

    const tableElement = $('#sales-table');
    if (tableElement.length === 0) return;

    // --- State & Elements ---
    const dataUrl = tableElement.data('url');
    const monthSelect = $('#month');
    const yearSelect = $('#year');
    const filterBtn = $('#filter-btn');
    const periodLabel = $('#current-period-label');

    // --- Helper to update label text ---
    const updatePeriodLabel = () => {
        const mText = monthSelect.find('option:selected').text().trim();
        const yText = yearSelect.find('option:selected').text().trim();
        periodLabel.text(`${mText} ${yText}`);
    };
    updatePeriodLabel(); // Run once on load

    // 1. Initialize DataTable
    let salesTable = tableElement.DataTable({
        processing: true,
        serverSide: true,
        order: [[3, 'desc']], // Sort by Revenue desc
        ajax: {
            url: dataUrl,
            data: function (d) {
                d.month = monthSelect.val();
                d.year = yearSelect.val();
            }
        },
        layout: {
            topStart: 'pageLength',
            topEnd: 'search', 
            bottomStart: 'info',
            bottomEnd: 'paging'
        },
        initComplete: function() {
            const container = $(this.api().table().container());
            container.addClass('p-4');
            
            // Style search & length
            container.find('.dt-length select').addClass('form-select form-select-sm border-secondary-subtle shadow-sm w-auto');
            container.find('.dt-search input').addClass('form-control form-control-sm border-secondary-subtle shadow-sm');
        },
        columns: [
            { 
                data: 'product_id', 
                name: 'product_id',
                className: 'align-middle ps-4'
            },
            { 
                data: 'product_name', 
                name: 'product_name',
                className: 'align-middle fw-medium'
            },
            {
                data: 'total_quantity',
                name: 'total_quantity',
                className: 'text-center align-middle',
                render: function(data) {
                    return `<span class="badge bg-light text-dark border px-3 py-2 rounded-pill fs-6">${parseInt(data).toLocaleString()}</span>`;
                }
            },
            {
                data: 'total_revenue',
                name: 'total_revenue',
                className: 'text-end align-middle pe-4 fw-bold text-success',
                render: function(data) {
                    return '₱' + parseFloat(data).toLocaleString(undefined, { minimumFractionDigits: 2 });
                }
            }
        ],
        language: {
            emptyTable: "No sales records found for this period."
        },
        // --- Footer Calculation Logic ---
        drawCallback: function(settings) {
            const api = this.api();
            let totalQty = 0;
            let totalRevenue = 0;

            // Iterate over the current page data to sum up columns
            // NOTE: In server-side mode, this sums the CURRENT PAGE only.
            // If you want "Grand Total of ALL pages", you need the server to send that separately via 'json' event.
            api.rows({ page: 'current' }).data().each(function(row) {
                totalQty += parseFloat(row.total_quantity);
                // Clean the currency string if it comes pre-formatted, or use raw data if available
                const rev = typeof row.total_revenue === 'string' 
                    ? parseFloat(row.total_revenue.replace(/[₱,]/g, '')) 
                    : parseFloat(row.total_revenue);
                
                if(!isNaN(rev)) totalRevenue += rev;
            });

            // Update footer DOM
            $('#total-quantity').text(totalQty.toLocaleString());
            $('#total-revenue').text('₱' + totalRevenue.toLocaleString(undefined, { minimumFractionDigits: 2 }));
        }
    });

    // 2. Filter Event
    filterBtn.on('click', function(e) {
        e.preventDefault();
        salesTable.draw(); // Reload table
        updatePeriodLabel();
    });
});