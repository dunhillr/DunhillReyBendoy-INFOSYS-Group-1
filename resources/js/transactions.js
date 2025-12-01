import * as bootstrap from 'bootstrap';
import 'datatables.net-bs5';
import $ from 'jquery';

// 1. Import Flatpickr & Theme
import flatpickr from "flatpickr";
import "flatpickr/dist/flatpickr.min.css";
// ✅ UI UPGRADE: Using 'airbnb' theme for a cleaner calendar popup
import "flatpickr/dist/themes/airbnb.css";

$(function() {
    console.log("✅ Transactions Script Loaded");

    const tableElement = $('#transactions-table');
    
    if (tableElement.length === 0) return;

    const dataUrl = tableElement.data('url');
    const showUrlTemplate = tableElement.data('show-url'); 

    // --- 🆕 Modern Date Picker Integration ---
    const datePickerConfig = {
        altInput: true,
        altFormat: "F j, Y",
        dateFormat: "Y-m-d",
        allowInput: true,
        altInputClass: "form-control bg-white border px-3", 
    };

    const fromDatePicker = flatpickr("#from-date", { ...datePickerConfig });
    const toDatePicker = flatpickr("#to-date", { ...datePickerConfig });
    // ----------------------------------------

    // 2. Initialize DataTable
    let table = tableElement.DataTable({
        processing: true,
        serverSide: true,
        
        // ✅ FIX: Sort by Column Index 2 (Date Created) to show latest first
        // Column 0 = ID, Column 1 = Amount, Column 2 = Date
        order: [[2, 'desc']], 
        
        ajax: {
            url: dataUrl,
            data: function (d) {
                d.from_date = $('#from-date').val();
                d.to_date = $('#to-date').val();
            }
        },
        layout: {
            topStart: 'pageLength',
            topEnd: 'search', 
            bottomStart: 'info',
            bottomEnd: 'paging'
        },
        initComplete: function() {
            const tableContainer = $(this.api().table().container());

            // ✅ UI FIX: Add padding to container
            tableContainer.addClass('p-4');
            
            // ✅ UI FIX: Improve "Entries Per Page" Selection
            const lengthMenu = tableContainer.find('.dt-length');
            lengthMenu.addClass('d-flex align-items-center gap-2 text-secondary small fw-bold text-uppercase');
            lengthMenu.find('select').addClass('form-select form-select-sm border-secondary-subtle w-auto shadow-sm');

            // ✅ UI FIX: Improve Search Box consistency
            const searchBox = tableContainer.find('.dt-search');
            searchBox.addClass('d-flex align-items-center gap-2');
            searchBox.find('input').addClass('form-control form-control-sm border-secondary-subtle shadow-sm');
        },
        columns: [
            { 
                data: 'id', 
                name: 'transactions.id', 
                width: '10%',
                className: 'text-center align-middle p-3' 
            },
            { 
                data: 'total_amount', 
                name: 'transactions.total_amount',
                className: 'text-center align-middle p-3', 
                render: function(data) {
                    return `<span class="fw-bold text-success">₱${parseFloat(data).toLocaleString(undefined, { minimumFractionDigits: 2 })}</span>`;
                }
            },
            { 
                data: 'created_at_formatted', 
                name: 'transactions.created_at',
                className: 'text-center align-middle p-3', 
                render: function(data, type) {
                    if (!data) return '';
                    if (type === 'sort' || type === 'type') {
                        return new Date(data).getTime();
                    }
                    const date = new Date(data);
                    return date.toLocaleString('en-PH', {
                        year: 'numeric', month: 'short', day: 'numeric',
                        hour: '2-digit', minute: '2-digit', hour12: true
                    });
                }
            },
            { 
                data: 'actions', 
                name: 'actions', 
                orderable: false, 
                searchable: false, 
                className: 'text-center align-middle p-3' 
            }
        ]
    });

    // 3. Filter Logic
    $('#filter-btn').on('click', function () {
        table.draw();
    });

    $('#clear-filter-btn').on('click', function () {
        fromDatePicker.clear();
        toDatePicker.clear();
        table.draw();
    });

    // 4. View Details Modal Logic
    $(document).on('click', '.view-transaction', function() {
        const transactionId = $(this).data('id');
        const url = showUrlTemplate.replace(':id', transactionId);

        $('#modal-products-table tbody').html('<tr><td colspan="4" class="text-center py-4 text-muted">Loading details...</td></tr>');
        
        const modalEl = document.getElementById('transactionModal');
        const modal = new bootstrap.Modal(modalEl);
        modal.show();

        $.get(url, function(data) {
            $('#modal-transaction-id').text(data.id);
            $('#modal-total-amount').text(parseFloat(data.total_amount).toLocaleString(undefined, { minimumFractionDigits: 2 }));
            $('#modal-payment-amount').text(parseFloat(data.payment_amount).toLocaleString(undefined, { minimumFractionDigits: 2 }));
            
            const date = new Date(data.created_at);
            $('#modal-date').text(date.toLocaleString('en-PH', { 
                month: 'short', day: 'numeric', year: 'numeric', hour: '2-digit', minute: '2-digit' 
            }));

            const tbody = $('#modal-products-table tbody');
            tbody.empty();
            
            if (data.details && data.details.length > 0) {
                data.details.forEach(function(item) {
                    tbody.append(
                        `<tr>
                            <td class="text-center p-3"> 
                                <span class="fw-medium text-dark">${item.product_name}</span>
                            </td>
                            <td class="text-center p-3"> 
                                ₱${parseFloat(item.price_at_sale).toLocaleString(undefined, { minimumFractionDigits: 2 })}
                            </td>
                            <td class="text-center p-3"> 
                                x${item.quantity}
                            </td>
                            <td class="text-center p-3 fw-bold"> 
                                ₱${parseFloat(item.subtotal).toLocaleString(undefined, { minimumFractionDigits: 2 })}
                            </td>
                        </tr>`
                    );
                });
            } else {
                tbody.html('<tr><td colspan="4" class="text-center py-3">No items found.</td></tr>');
            }

        }).fail(function() {
            $('#modal-products-table tbody').html('<tr><td colspan="4" class="text-center text-danger py-3">Failed to load data.</td></tr>');
        });
    });
});