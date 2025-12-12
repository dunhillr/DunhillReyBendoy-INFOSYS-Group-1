<x-app-layout>
    <div class="container-fluid py-4 px-4">
        
        {{-- Header Section --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-1 text-secondary">
                    <i class="fas fa-archive me-2"></i>Archived Products
                </h1>
                <p class="text-muted mb-0">View and restore previously deleted items.</p>
            </div>
            <a href="{{ route('products.index') }}" class="btn btn-outline-secondary shadow-sm">
                <i class="fas fa-arrow-left me-1"></i> Back to Inventory
            </a>
        </div>

        {{-- Main Content Card --}}
        {{-- Added a top border to visually distinguish this as the "Archive" area --}}
        <div class="card shadow-lg border-0 border-top border-4 border-secondary">
            <div class="card-body p-4">
                <div class="table-responsive">
                    <table id="archived-products-table" class="table table-hover align-middle w-100">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 5%">ID</th>
                                <th>Product Name</th>
                                <th style="width: 10%">Price</th>
                                <th style="width: 15%">Category</th>
                                <th style="width: 15%">Weight / Unit</th>
                                <th style="width: 10%" class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    {{-- Ensure jQuery and DataTables are loaded --}}
    @vite(['resources/js/app.js']) {{-- Or wherever your bootstrap/jquery is --}}
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/2.0.8/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/2.0.8/js/dataTables.bootstrap5.min.js"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/2.0.8/css/dataTables.bootstrap5.min.css">

    <script>
        $(function() {
            // 1. Global AJAX Setup for CSRF (Best Practice)
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            // 2. Initialize DataTable
            let archivedTable = $('#archived-products-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{!! route('products.archived.data') !!}',
                    cache: false
                },
                order: [[0, 'desc']], // Sort by ID descending usually makes more sense for archives
                columns: [
                    { data: 'id', name: 'id' },
                    { 
                        data: 'name', 
                        name: 'name',
                        render: function(data) {
                            return `<span class="fw-medium">${data}</span>`;
                        }
                    },
                    { 
                        data: 'price', 
                        name: 'price',
                        render: (data) => `₱${parseFloat(data).toFixed(2)}`
                    },
                    { 
                        data: 'category_name', 
                        name: 'category_name',
                        render: (data) => `<span class="badge bg-light text-dark border">${data}</span>`
                    },
                    { 
                        // Combined Column logic (Same as main products page)
                        data: null, 
                        name: 'net_weight', 
                        orderable: false, 
                        render: function(data, type, row) {
                            // Check if both weight and unit string exist
                            if (row.net_weight && row.net_weight_unit) {
                                // ✅ CORRECT: Use row.net_weight_unit directly (it's already "kg")
                                return `${row.net_weight} ${row.net_weight_unit}`;
                            }
                            return '<span class="text-muted">-</span>';
                        }
                    },
                    { 
                        data: 'actions', 
                        name: 'actions', 
                        orderable: false, 
                        searchable: false,
                        className: 'text-end' 
                    }
                ]
            });

            // 3. Restore Product Logic
            $(document).on('click', '.restore-product', function () {
                // ❌ DELETE THIS: const productId = $(this).data('id');
                
                // ✅ ADD THIS: Get the full valid URL from the button
                const restoreUrl = $(this).data('route');
            
                Swal.fire({
                    icon: 'question',
                    title: 'Restore Product?',
                    text: 'This item will be moved back to the active inventory.',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, Restore',
                    cancelButtonText: 'Cancel',
                    confirmButtonColor: '#198754',
                    cancelButtonColor: '#6c757d'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            // ✅ USE THE VARIABLE HERE
                            url: restoreUrl, 
                            type: "POST",
                            // Ensure CSRF token is present if not globally setup
                            data: {
                                _token: $('meta[name="csrf-token"]').attr('content')
                            },
                            success: function (response) {
                                Swal.fire('Restored!', response.message, 'success');
                                archivedTable.ajax.reload(null, false);
                            },
                            error: function (xhr) {
                                console.error(xhr.responseText);
                                Swal.fire('Error', 'Failed to restore the product.', 'error');
                            }
                        });
                    }
                });
            });
        });
    </script>
    @endpush
</x-app-layout>