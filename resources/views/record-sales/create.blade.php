<x-app-layout>
    <div class="container-fluid py-4 px-4">
        
        <div class="row g-4">
            
            {{-- LEFT COLUMN: Product Search & Cart --}}
            <div class="col-lg-8">
                <div class="card shadow-lg border-0 h-100 rounded-3">
                    <div class="card-header bg-white py-3 border-bottom-0">
                        <h5 class="fw-bold text-primary mb-0"><i class="fas fa-shopping-cart me-2"></i>Current Sale</h5>
                    </div>
                    
                    <div class="card-body p-4">
                        {{-- 1. Large Search Bar (Standard Search) --}}
                        <div class="mb-4">
                            <label for="product_search" class="form-label fw-bold text-secondary small text-uppercase">Find Product</label>
                            <div class="input-group input-group-lg shadow-sm">
                                <span class="input-group-text bg-light border-0 ps-3"><i class="fas fa-search text-muted"></i></span>
                                <input type="text" 
                                    id="product_search" 
                                    class="form-control bg-light border-0" 
                                    placeholder="Type product name to search..." 
                                    autocomplete="off"
                                    data-route="{{ route('products.search') }}"
                                    autofocus>
                            </div>
                        </div>

                        {{-- 2. Cart Table --}}
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light text-secondary">
                                    <tr>
                                        <th class="py-3 ps-3" style="width: 35%">Product</th>
                                        <th class="py-3 text-center" style="width: 15%">Price</th>
                                        {{-- ✅ UI FIX: Increased width from 20% to 25% --}}
                                        <th class="py-3 text-center" style="width: 25%">Qty</th>
                                        <th class="py-3 text-end" style="width: 15%">Subtotal</th>
                                        <th class="py-3 text-center" style="width: 10%"></th>
                                    </tr>
                                </thead>
                                <tbody id="sale-items-table">
                                    {{-- JS will populate this --}}
                                </tbody>
                            </table>
                            
                            {{-- Empty State --}}
                            <div id="empty-cart-message" class="text-center py-5">
                                <div class="mb-3">
                                    <i class="fas fa-basket-shopping text-muted opacity-25" style="font-size: 4rem;"></i>
                                </div>
                                <h6 class="text-muted fw-bold">Cart is empty</h6>
                                <p class="text-muted small mb-0">Scan items or search to start a sale.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- RIGHT COLUMN: Payment & Summary --}}
            <div class="col-lg-4">
                <div class="card shadow-lg border-0 rounded-3 sticky-top" style="top: 20px;">
                    <div class="card-body p-4">
                        <form action="{{ route('record-sales.store') }}" method="POST" id="sale-form">
                            @csrf
                            
                            <h6 class="text-uppercase fw-bold text-muted mb-4 small">Payment Summary</h6>

                            {{-- 1. Total Display --}}
                            <div class="text-center mb-4 p-4 bg-primary bg-opacity-10 rounded-3 border border-primary border-opacity-25">
                                <span class="d-block text-primary fw-bold text-uppercase small mb-1">Total Due</span>
                                <h1 class="display-4 fw-bolder text-primary mb-0" id="total-display">
                                    <span class="fs-4 align-top">₱</span><span id="total-amount">0.00</span>
                                </h1>
                            </div>

                            {{-- 2. Payment Input --}}
                            <div class="mb-4">
                                <label for="payment_amount" class="form-label fw-bold text-dark">Amount Received</label>
                                <div class="input-group input-group-lg">
                                    <span class="input-group-text bg-white border-end-0 text-muted">₱</span>
                                    <input type="number" 
                                        step="0.01" 
                                        id="payment_amount" 
                                        name="payment_amount" 
                                        class="form-control border-start-0 ps-0 fw-bold text-dark" 
                                        placeholder="0.00">
                                </div>
                            </div>

                            <hr class="border-secondary border-opacity-10 my-4">

                            {{-- 3. Change Display --}}
                            <div class="d-flex justify-content-between align-items-end mb-4">
                                <span class="text-muted fw-medium">Change</span>
                                <h3 class="fw-bold text-secondary mb-0" id="change-display">
                                    ₱<span id="change-amount">0.00</span>
                                </h3>
                            </div>

                            {{-- 4. Submit Button --}}
                            <button type="submit" class="btn btn-primary btn-lg w-100 py-3 shadow-sm fw-bold text-uppercase letter-spacing-1">
                                <i class="fas fa-check-circle me-2"></i> Complete Sale
                            </button>
                        </form>
                    </div>
                </div>
            </div>

        </div>
    </div>

    @push('scripts')
        {{-- Load the separate JS file (ensure this matches your file name) --}}
        @vite('resources/js/create-sales.js')
        
        {{-- CSS Fixes --}}
        <style>
            /* 1. Fix Autocomplete being hidden behind other elements */
            .ui-autocomplete {
                z-index: 9999 !important; /* Force it on top */
                max-height: 300px;
                overflow-y: auto;
                overflow-x: hidden;
                border: 0;
                box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
                border-radius: 0.5rem;
                padding: 0.5rem;
                background-color: white; /* Ensure background is white */
            }
            .ui-menu-item .ui-menu-item-wrapper {
                padding: 0.5rem 1rem;
                border-radius: 0.25rem;
                cursor: pointer;
            }
            .ui-menu-item .ui-menu-item-wrapper.ui-state-active {
                background: #0d6efd;
                color: white;
                border: none;
            }

            /* 2. Number Input Styling (Hide Arrows) */
            input::-webkit-outer-spin-button,
            input::-webkit-inner-spin-button {
                -webkit-appearance: none;
                margin: 0;
            }
            input[type=number] {
                -moz-appearance: textfield;
            }
            .letter-spacing-1 { letter-spacing: 1px; }
        </style>
    @endpush
</x-app-layout>