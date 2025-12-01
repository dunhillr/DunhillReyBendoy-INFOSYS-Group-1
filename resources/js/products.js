import 'datatables.net-bs5';
import $ from 'jquery';
import Swal from 'sweetalert2';

$(function() {
    
    // 🛑 GUARD: Stop if product table doesn't exist
    if ($('#products-table').length === 0) return;

    $.ajaxSetup({
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
    });

    let selectedCategoryIds = [];

    if (!$.fn.DataTable.isDataTable('#products-table')) {
        window.productsTable = $('#products-table').DataTable({
            processing: true,
            serverSide: true,
            
            // Layout config to hide default search box
            layout: {
                topStart: 'pageLength',
                topEnd: null, 
                bottomStart: 'info',
                bottomEnd: 'paging'
            },
            
            ajax: {
                url: document.querySelector('meta[name="products-data-url"]').content,
                cache: false,
                data: function(d) {
                    d.category_ids = selectedCategoryIds; 
                }
            },
            order: [[0, 'desc']],
            columns: [
                { data: 'id', name: 'id' },
                
                // ✅ STYLE UPDATE: Bold Name
                { 
                    data: 'name', 
                    name: 'name',
                    render: function(data) {
                        return `<span class="fw-medium text-dark">${data}</span>`;
                    }
                },
                
                { 
                    data: 'price', 
                    name: 'price', 
                    render: (data) => `₱${parseFloat(data).toFixed(2)}`
                },
                
                // ✅ STYLE UPDATE: Improved Category Badge (Larger, Pill shape)
                { 
                    data: 'category_name', 
                    name: 'category.name', 
                    render: function(data) {
                        // Removed 'badge' class to prevent tiny font.
                        // Added 'rounded-pill', 'px-3', 'py-1' for a nice tag look.
                        return `<span class="d-inline-block px-3 py-2 rounded-pill bg-light border border-secondary-subtle text-secondary fw-bold shadow-sm" style="font-size: 0.85rem;">${data}</span>`;
                    }
                },
                
                // Combined Weight/Unit
                { 
                    data: null, 
                    name: 'net_weight', 
                    orderable: false, 
                    render: function(data, type, row) {
                        // Handle Unit (String vs Object)
                        let unitData = row.net_weight_unit || row.unit;
                        let unitName = '';
                        if (unitData) {
                            unitName = (typeof unitData === 'object') ? unitData.name : unitData;
                        }

                        if (row.net_weight && unitName) {
                            return `${row.net_weight} ${unitName}`;
                        }
                        return '<span class="text-muted">-</span>';
                    }
                },
                
                // Actions (Align Right to match Archive)
                { 
                    data: 'actions', 
                    name: 'actions', 
                    orderable: false, 
                    searchable: false, 
                    className: 'text-end' 
                }
            ]
        });
    }

    // ... (Keep Search, Filter, and Delete logic exactly the same as before) ...
    // (Search, Modal, Delete logic here is unchanged from previous version)
    
    // Custom Search Input Logic
    $('#product-search-input').on('keyup', function() {
        window.productsTable.search(this.value).draw();
    });

    // Category Modal Logic
    $('#apply-category-filter').on('click', function() {
        selectedCategoryIds = []; 
        let displayTextParts = [];

        $('.category-checkbox:checked').each(function() {
            selectedCategoryIds.push($(this).val());
            displayTextParts.push(`${$(this).data('name')} <span class="fw-bold text-dark">${$(this).data('count')}</span>`);
        });

        if (selectedCategoryIds.length > 0) {
            $('#selected-categories-text').html(displayTextParts.join(' <span class="text-muted mx-2">|</span> '));
            $('#selected-categories-display').removeClass('d-none').addClass('d-flex');
        } else {
            $('#selected-categories-display').addClass('d-none').removeClass('d-flex');
        }

        if (window.productsTable) {
            window.productsTable.ajax.reload();
        }
        $('#categoryFilterModal').modal('hide'); 
    });

    $('#clear-filters-btn').on('click', function() {
        $('.category-checkbox').prop('checked', false);
        selectedCategoryIds = [];
        $('#selected-categories-display').addClass('d-none').removeClass('d-flex');
        window.productsTable.ajax.reload();
    });

    // Delete Logic
    $(document).on('click', '.delete-product', function (e) {
        e.preventDefault();
        const productId = $(this).data('id');
        const deleteUrl = $(this).data('route'); 

        Swal.fire({
            icon: 'warning',
            title: 'Archive Product?',
            text: 'This will move the product to the archive.',
            showCancelButton: true,
            confirmButtonText: 'Yes, Archive',
            cancelButtonText: 'No, Cancel',
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6'
        }).then(result => {
            if (result.isConfirmed) {
                $.ajax({
                    url: deleteUrl, 
                    type: 'POST',
                    data: { _method: 'DELETE' },
                    success: function(response) {
                        Swal.fire('Archived!', response.message, 'success');
                        if (window.productsTable) {
                            window.productsTable.ajax.reload(null, false);
                        }
                    },
                    error: function(xhr) {
                        Swal.fire('Error', xhr.responseJSON?.message || 'An error occurred.', 'error');
                    }
                });
            }
        });
    });
});