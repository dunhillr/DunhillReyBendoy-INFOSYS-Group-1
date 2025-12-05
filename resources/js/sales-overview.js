import 'datatables.net-bs5';
import $ from 'jquery';
// ✅ Import SweetAlert2 (Make sure this is installed: npm install sweetalert2)
import Swal from 'sweetalert2';

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
    updatePeriodLabel(); 

    // 1. Initialize DataTable
    let salesTable = tableElement.DataTable({
        processing: true,
        serverSide: true,
        order: [[3, 'desc']], 
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
            container.find('.dt-length select').addClass('form-select form-select-sm border-secondary-subtle shadow-sm w-auto');
            container.find('.dt-search input').addClass('form-control form-control-sm border-secondary-subtle shadow-sm');
        },
        columns: [
            { data: 'product_id', name: 'product_id', className: 'align-middle ps-4' },
            { data: 'product_name', name: 'product_name', className: 'align-middle fw-medium' },
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
        language: { emptyTable: "No sales records found for this period." },
        drawCallback: function(settings) {
            // ✅ FIX: Get the Grand Totals sent by the Server
            const json = settings.json;

            if (json) {
                // Update footer with the server-calculated totals
                $('#total-quantity').text(parseInt(json.grand_total_quantity || 0).toLocaleString());
                
                $('#total-revenue').text(
                    '₱' + parseFloat(json.grand_total_revenue || 0).toLocaleString(undefined, { minimumFractionDigits: 2 })
                );
            }
        }
    });

    // 2. Filter Event
    filterBtn.on('click', function(e) {
        e.preventDefault();
        salesTable.draw(); 
        updatePeriodLabel();
    });

    // 3. ✅ NEW: Automation Dropdown Logic
    $('.send-report-item').on('click', function(e) {
        e.preventDefault();

        const type = $(this).data('type'); // Get 'daily', 'weekly', or 'monthly'
        const url = "{{ route('reports.send-report') }}"; // Hardcoded for simplicity here since it's same route

        // Show loading (Optional: use Swal loading state for cleaner UI)
        Swal.fire({
            title: 'Sending ' + type + ' report...',
            didOpen: () => { Swal.showLoading() }
        });

        $.ajax({
            url: '/sales-overview/send-report', // Or use window.routes if setup
            type: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                type: type // Pass the type
            },
            success: function(response) {
                Swal.close(); // Close loader
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Sent!',
                        text: response.message,
                        timer: 3000,
                        timerProgressBar: true
                    });
                } else {
                    Swal.fire({ icon: 'error', title: 'Failed', text: response.message });
                }
            },
            error: function(xhr) {
                Swal.close();
                Swal.fire({ icon: 'error', title: 'Error', text: 'Could not connect to server.' });
            }
        });
    });
});