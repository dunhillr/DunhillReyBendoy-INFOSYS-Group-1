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
    $(document).on('click', '.delete-product', function() { 
    
        // ✅ Get the valid URL directly from the button attribute
        var archiveUrl = $(this).data('route'); 

        Swal.fire({
            title: 'Archive Product?',
            text: "This product will be hidden from the active list.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, archive it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: archiveUrl, // Uses the variable from data-route
                    type: 'DELETE',
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        Swal.fire('Archived!', response.message, 'success');
                        $('#products-table').DataTable().ajax.reload();
                    },
                    error: function(xhr) {
                        console.error(xhr.responseText);
                        Swal.fire('Error!', 'Could not archive product.', 'error');
                    }
                });
            }
        });
    });
});