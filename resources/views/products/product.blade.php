<x-app-layout>
    <div class="container-fluid py-4 px-4">
        
        {{-- 1. HEADER SECTION --}}
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
            <div>
                <h1 class="h3 fw-bold mb-1 text-dark">Product Catalog</h1>
                <p class="text-muted mb-0">Manage your product list and prices.</p>
            </div>
            
            <div class="d-flex gap-2">
                <a href="{{ route('products.create') }}" class="btn btn-primary d-flex align-items-center shadow-sm">
                    <i class="fas fa-plus me-2"></i> New Product
                </a>
                <a href="{{ route('products.archived') }}" class="btn btn-outline-secondary d-flex align-items-center shadow-sm">
                    <i class="fas fa-archive me-2"></i> Archives
                </a>
            </div>
        </div>

        {{-- 2. CONTROL TOOLBAR --}}
        <div class="card shadow-sm border-0 mb-4 rounded-3">
            <div class="card-body p-3">
                <div class="row g-3 align-items-center">
                    <div class="col-md-4">
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0">
                                <i class="fas fa-search text-muted"></i>
                            </span>
                            <input type="text" id="product-search-input" class="form-control border-start-0 bg-light" placeholder="Search products...">
                        </div>
                    </div>
                    <div class="col-md-auto ms-auto">
                        <button type="button" class="btn btn-white border shadow-sm text-secondary" data-bs-toggle="modal" data-bs-target="#categoryFilterModal">
                            <i class="fas fa-filter me-2 text-primary"></i> Filter Categories
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- 3. ACTIVE FILTERS DISPLAY --}}
        <div id="selected-categories-display" class="d-none align-items-center mb-3 p-3 bg-primary bg-opacity-10 border border-primary border-opacity-25 rounded-3">
            <div class="d-flex align-items-center me-3">
                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 24px; height: 24px;">
                    <i class="fas fa-check list-group-item text-xs" style="font-size: 0.7rem;"></i>
                </div>
                <span class="fw-bold text-primary">Active Filters:</span>
            </div>
            <span id="selected-categories-text" class="text-dark me-auto"></span>
            <button type="button" class="btn btn-sm btn-link text-danger text-decoration-none fw-bold p-0" id="clear-filters-btn">Clear All</button>
        </div>

        {{-- 4. MAIN DATA TABLE CARD --}}
        {{-- ✅ STYLE UPDATE: Added 'border-top border-4 border-primary' to match Archive style --}}
        <div class="card shadow-lg border-0 border-top border-4 border-primary rounded-3">
            <div class="card-body p-4">
                <div class="table-responsive">
                    {{-- ✅ STYLE UPDATE: Added 'w-100' and explicit widths to match Archive --}}
                    <table id="products-table" class="table table-hover align-middle mb-0 w-100">
                        <thead class="bg-light">
                            <tr>
                                <th class="py-3 ps-4" style="width: 5%">ID</th>
                                <th class="py-3">Product Name</th>
                                <th class="py-3" style="width: 15%">Price</th>
                                <th class="py-3" style="width: 15%">Category</th>
                                <th class="py-3" style="width: 15%">Weight / Unit</th>
                                <th class="py-3 pe-4 text-end" style="width: 10%">Actions</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>

    </div> {{-- End Container --}}

    {{-- 5. CATEGORY FILTER MODAL (Same as previous) --}}
    <div class="modal fade" id="categoryFilterModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header border-bottom-0 pb-0">
                    <h5 class="modal-title fw-bold">Filter by Category</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="list-group list-group-flush border rounded-3 overflow-hidden">
                        @foreach($categories as $category)
                            <label class="list-group-item list-group-item-action d-flex justify-content-between align-items-center cursor-pointer py-3">
                                <div class="d-flex align-items-center">
                                    <input class="form-check-input me-3 category-checkbox" 
                                           style="width: 1.2em; height: 1.2em;"
                                           type="checkbox" 
                                           value="{{ $category->id }}" 
                                           data-name="{{ $category->name }}"
                                           data-count="{{ $category->products_count }}">
                                    <span class="fw-medium">{{ $category->name }}</span>
                                </div>
                                <span class="badge bg-light text-dark border rounded-pill">{{ $category->products_count }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary px-4" id="apply-category-filter">Apply Filters</button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        @vite('resources/js/products.js')
        {{-- CSS for pagination layout --}}
        <style>
            /* 1. Spacing inside the table cells (from previous request) */
            #products-table tbody td, 
            #products-table thead th {
                padding-top: 15px;
                padding-bottom: 15px;
            }
        
            /* 2. Spacing ABOVE the table (pushes "Show Entries" up) */
            div.dt-container .row:first-child {
                margin-bottom: 1rem; /* Space between "Show 10 entries" and the Table Header */
            }
        
            /* 3. Spacing BELOW the table (pushes Pagination down) */
            div.dt-container .row:last-child {
                margin-top: 1rem; /* Space between the Table Footer and "Showing 1 to 10" */
            }
        
            /* 4. Optional: Cleaner Pagination Buttons */
            .page-link {
                border-radius: 50px !important; /* Makes buttons rounded/pill-shaped */
                margin: 0 3px;
                border: none;
                color: #6c757d;
            }
            .page-item.active .page-link {
                background-color: #0d6efd;
                color: white;
                font-weight: bold;
            }
            .dataTables_wrapper .dataTables_paginate .paginate_button { padding: 0; margin: 0; }
            table.dataTable.no-footer { border-bottom: none; }
        </style>
    @endpush
</x-app-layout>