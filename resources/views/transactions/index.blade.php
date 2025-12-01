<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 font-weight-bold text-gray-800 mb-0">
            {{ __('Transaction History') }}
        </h2>
    </x-slot>

    <div class="container-fluid py-4 px-4">
        
        {{-- Filter Card --}}
        <div class="card shadow-sm border-0 mb-4 rounded-3">
            <div class="card-body p-4">
                <div class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label for="from-date" class="form-label fw-bold text-secondary small text-uppercase">From Date</label>
                        <input type="date" id="from-date" class="form-control bg-light border-0">
                    </div>
                    <div class="col-md-4">
                        <label for="to-date" class="form-label fw-bold text-secondary small text-uppercase">To Date</label>
                        <input type="date" id="to-date" class="form-control bg-light border-0">
                    </div>
                    <div class="col-md-4 d-flex gap-2">
                        <button id="filter-btn" class="btn btn-primary px-4 shadow-sm flex-grow-1">
                            <i class="fas fa-filter me-2"></i>Filter
                        </button>
                        <button id="clear-filter-btn" class="btn btn-light border flex-grow-1">
                            Clear
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Transactions Table --}}
        <div class="card shadow-lg border-0 rounded-3">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table id="transactions-table" class="table table-hover align-middle mb-0 w-100" 
                           data-url="{{ route('transactions.data') }}"
                           data-show-url="{{ route('transactions.show', ':id') }}">
                        <thead class="bg-light text-secondary">
                            <tr>
                                <th class="py-3 ps-4">ID</th>
                                <th class="py-3">Total Amount</th>
                                <th class="py-3">Date Recorded</th>
                                <th class="py-3 pe-4 text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Transaction Details Modal --}}
    <div class="modal fade" id="transactionModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header border-bottom-0 pb-0">
                    <h5 class="modal-title fw-bold">Transaction #<span id="modal-transaction-id"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    
                    {{-- Summary Row --}}
                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <div class="p-3 bg-light rounded-3">
                                <small class="text-uppercase text-muted fw-bold d-block mb-1">Total Amount</small>
                                <h4 class="text-primary fw-bold mb-0">₱<span id="modal-total-amount"></span></h4>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 bg-light rounded-3">
                                <small class="text-uppercase text-muted fw-bold d-block mb-1">Payment</small>
                                <h4 class="text-dark fw-bold mb-0">₱<span id="modal-payment-amount"></span></h4>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 bg-light rounded-3">
                                <small class="text-uppercase text-muted fw-bold d-block mb-1">Date</small>
                                <h6 class="text-secondary fw-bold mb-0 pt-1"><span id="modal-date"></span></h6>
                            </div>
                        </div>
                    </div>

                    <h6 class="fw-bold mb-3 border-bottom pb-2">Purchased Items</h6>
                    <div class="table-responsive">
                        <table class="table table-sm align-middle" id="modal-products-table">
                            <thead class="text-muted small text-uppercase">
                                <tr>
                                    <th>Product</th>
                                    <th class="text-end">Price</th>
                                    <th class="text-center">Qty</th>
                                    <th class="text-end">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Close</button>
                    {{-- Optional: Print Button --}}
                    {{-- <button type="button" class="btn btn-primary px-4"><i class="fas fa-print me-2"></i>Print Receipt</button> --}}
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        @vite('resources/js/transactions.js')
        
        <style>
            .dataTables_wrapper .dataTables_paginate .paginate_button { padding: 0; margin: 0; }
            table.dataTable.no-footer { border-bottom: none; }
            /* Hide Default Search to rely on filters? Or keep it? keeping it for ID search */
        </style>
    @endpush
</x-app-layout>