<x-app-layout>
    <div class="container py-5">
        <div class="row g-3">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1 class="mb-0">Archived Products</h1>
                    <a href="{{ route('products.index') }}" class="btn btn-secondary">
                        ← Back to Active Products
                    </a>
                </div>

                <div class="card shadow-sm">
                    <div class="card-body">
                        <table id="archived-products-table" class="table table-bordered table-striped align-middle">
                            <thead>
                                <tr>
                                    <th>Product ID</th>
                                    <th>Name</th>
                                    <th>Net Weight</th>
                                    <th>Unit</th>
                                    <th>Price</th>
                                    <th>Category</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- DataTables will populate this table body via AJAX -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <!-- DataTables (same version you use in products.blade.php) -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/2.0.8/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/2.0.8/js/dataTables.bootstrap5.min.js"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/2.0.8/css/dataTables.bootstrap5.min.css">

    <script>
        $(function() {
            // Initialize DataTable
            let archivedTable = $('#archived-products-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{!! route('products.archived.data') !!}',
                    cache: false
                },
                columns: [
                    { data: 'id', name: 'id' },
                    { data: 'name', name: 'name' },
                    { data: 'net_weight', name: 'net_weight' },
                    { data: 'net_weight_unit', name: 'net_weight_unit' },
                    { data: 'price', name: 'price' },
                    { data: 'category_name', name: 'category_name' },
                    { data: 'actions', name: 'actions', orderable: false, searchable: false }
                ],
                order: [[0, 'asc']],
                responsive: true
            });

            // ✅ Restore Product
            $(document).on('click', '.restore-product', function () {
                const productId = $(this).data('id');

                Swal.fire({
                    icon: 'question',
                    title: 'Restore Product?',
                    text: 'This will make the product active again.',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, Restore',
                    cancelButtonText: 'Cancel',
                    confirmButtonColor: '#198754',
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: "{{ url('products') }}/" + productId + "/restore",
                            type: "POST",
                            data: {
                                _token: "{{ csrf_token() }}"
                            },
                            success: function (response) {
                                Swal.fire('Restored!', response.message, 'success');
                                archivedTable.ajax.reload(null, false);
                                productsTable.ajax.reload(null, false);
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
