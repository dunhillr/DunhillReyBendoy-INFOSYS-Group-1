<x-app-layout>
    <div class="container py-5">
        <div class="row g-3">
            <!-- Sidebar Column -->
            <x-product-sidebar :categories="$categories" :show-categories="$showCategories" />

            <!-- Main Content Column -->
            <div class="col-md-9">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h1 class="mb-0">
                            @if(request()->has('category_id'))
                                {{ $category->name }} Products
                            @else
                                All Products
                            @endif
                        </h1>
                    </div>
                    <div>
                        <a href="{{ route('products.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Add Product
                        </a>
                        <a href="{{ route('products.archived') }}" class="btn btn-secondary">
                            <i class="fas fa-archive"></i> Archived Products
                        </a>
                    </div>
                </div>

                <div class="card shadow-sm">
                    <div class="card-body">
                        <table id="products-table" class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>ID</th>
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
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/2.0.8/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/2.0.8/js/dataTables.bootstrap5.min.js"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/2.0.8/css/dataTables.bootstrap5.min.css">
    
    <script>
        $(function() {
            // Declare productsTable in a wider scope
            let productsTable;

            const getCategoryId = () => new URLSearchParams(window.location.search).get('category_id');

            // Assign the DataTable instance to the productsTable variable
            productsTable = $('#products-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{!! route('products.data') !!}',
                    cache: false,
                    data: function(d) {
                        const categoryId = getCategoryId();
                        if (categoryId) {
                            d.category_id = categoryId;
                        } else {
                            d.category_id = null;
                        }
                    }
                },
                columns: [
                    { data: 'id', name: 'id' },
                    { data: 'id', name: 'id' },
                    { data: 'name', name: 'name' },
                    { data: 'net_weight', name: 'net_weight' },
                    { data: 'net_weight_unit', name: 'net_weight_unit' },
                    { data: 'price', name: 'price' },
                    { data: 'category_name', name: 'category_name' },
                    { data: 'actions', name: 'actions', orderable: false, searchable: false }
                ]
            });

// ✅ Delete Product Confirmation
$(document).on('click', '.delete-product', function () {
    const productId = $(this).data('id');

    Swal.fire({
        icon: 'warning',
        title: 'Delete Product?',
        text: 'This action cannot be undone.',
        showCancelButton: true,
        confirmButtonText: 'Yes, Delete',
        cancelButtonText: 'No, Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: "{{ url('products') }}/" + productId,
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    _method: 'DELETE'
                },
                success: function (response) {
                    Swal.fire('Deleted!', response.message, 'success');
                    $('#products-table').DataTable().ajax.reload(null, false); // Reload without page refresh
                },
                error: function (xhr) {
                    const errorMsg = xhr.responseJSON?.message || 'An error occurred.';
                    Swal.fire('Error', errorMsg, 'error');
                }
            });
        }
    });
});



            // Function to set the active state on the correct link and update the title
            function setActiveCategoryAndTitle() {
                const activeId = getCategoryId();
                $('.list-group-item').removeClass('active');
                if (activeId) {
                    const activeLink = $(`.list-group-item[data-category-id="${activeId}"]`);
                    activeLink.addClass('active');
                    const categoryName = activeLink.data('categoryName');
                    $('h1.mb-0').text(categoryName + ' Products');
                } else {
                    $('h1.mb-0').text('All Products');
                }
            }

            // Run on page load
            setActiveCategoryAndTitle();

            // Event listener for category links
            $(document).on('click', '.list-group-item', function(event) {
                event.preventDefault();

                const categoryId = $(this).data('categoryId');
                const newUrl = categoryId ? `?category_id=${categoryId}` : '/products';
                history.pushState(null, '', newUrl);

                // Update active state and title
                setActiveCategoryAndTitle();

                // Reload the DataTable with the new filter
                productsTable.ajax.reload();
            });

            // Handle browser's back/forward buttons
            $(window).on('popstate', function() {
                setActiveCategoryAndTitle();
                productsTable.ajax.reload();
            });

            // Handle "Back" link in the sidebar with full page reload
            $(document).on('click', '.card-header a', function(event) {
                if ($(this).text().trim() === 'Back') {
                    event.preventDefault();
                    window.location.href = $(this).attr('href'); // Force full reload
                }
            });
        });
        console.log("✅ Autocomplete script loaded");
    </script>
    @endpush
</x-app-layout>
