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
            const api = this.api();
            let totalQty = 0;
            let totalRevenue = 0;

            api.rows({ page: 'current' }).data().each(function(row) {
                totalQty += parseFloat(row.total_quantity);
                const rev = typeof row.total_revenue === 'string' 
                    ? parseFloat(row.total_revenue.replace(/[₱,]/g, '')) 
                    : parseFloat(row.total_revenue);
                if(!isNaN(rev)) totalRevenue += rev;
            });

            $('#total-quantity').text(totalQty.toLocaleString());
            $('#total-revenue').text('₱' + totalRevenue.toLocaleString(undefined, { minimumFractionDigits: 2 }));
        }
    });

    // 2. Filter Event
    filterBtn.on('click', function(e) {
        e.preventDefault();
        salesTable.draw(); 
        updatePeriodLabel();
    });

    // 3. Automation Button Logic
    $('#send-report-btn').on('click', function() {
        const btn = $(this);
        const originalContent = btn.html();
        
        const url = btn.data('route'); 

        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Sending...');

        $.ajax({
            url: url, 
            type: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content') 
            },
            success: function(response) {
                if (response.success) {
                    // ✅ CUSTOM SUCCESS MESSAGE
                    Swal.fire({
                        icon: 'success',
                        title: 'Report Sent!',
                        text: 'The daily sales report has been emailed successfully.',
                        confirmButtonColor: '#198754', // Bootstrap Success Green
                        timer: 3000,
                        timerProgressBar: true
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Sending Failed',
                        text: response.message,
                        confirmButtonColor: '#dc3545'
                    });
                }
            },
            error: function(xhr) {
                console.error(xhr);
                Swal.fire({
                    icon: 'error',
                    title: 'System Error',
                    text: 'Could not connect to the server. Please try again later.',
                    confirmButtonColor: '#dc3545'
                });
            },
            complete: function() {
                btn.prop('disabled', false).html(originalContent);
            }
        });
    });
});