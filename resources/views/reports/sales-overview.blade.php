<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 font-weight-bold text-gray-800 mb-0">
            {{ __('Sales Overview') }}
        </h2>
    </x-slot>

    <div class="container-fluid py-4 px-4">
        
        {{-- 1. Filter Section --}}
        <div class="card shadow-sm border-0 mb-4 rounded-3">
            <div class="card-body p-4">
                <div class="row g-3 align-items-end">
                    {{-- Year Selection --}}
                    <div class="col-md-3">
                        <label for="year" class="form-label fw-bold text-secondary small text-uppercase">Year</label>
                        <select id="year" name="year" class="form-select bg-light border-0">
                            @foreach ($availableYears as $year)
                                <option value="{{ $year }}" {{ $year == $selectedYear ? 'selected' : '' }}>
                                    {{ $year }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Month Selection (With 'Whole Year' Option) --}}
                    <div class="col-md-3">
                        <label for="month" class="form-label fw-bold text-secondary small text-uppercase">Month</label>
                        <select id="month" name="month" class="form-select bg-light border-0">
                            {{-- ✅ NEW: Option to view entire year --}}
                            <option value="all" {{ $selectedMonth == 'all' ? 'selected' : '' }}>Whole Year</option>
                            
                            @foreach ($availableMonths as $monthNum => $monthName)
                                <option value="{{ $monthNum }}" {{ $monthNum == $selectedMonth ? 'selected' : '' }}>
                                    {{ $monthName }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Filter Button --}}
                    <div class="col-md-3">
                        <button id="filter-btn" class="btn btn-primary w-100 shadow-sm">
                            <i class="fas fa-filter me-2"></i>Apply Filters
                        </button>
                    </div>
                    
                    {{-- Actions (Print & Automation) --}}
                    <div class="col-md-3 d-flex gap-2">
                        {{-- Print Button --}}
                        <button class="btn btn-outline-secondary flex-grow-1" onclick="window.print()" title="Print Page">
                            <i class="fas fa-print"></i>
                        </button>

                        {{-- ✅ NEW: Automation Dropdown --}}
                        <div class="btn-group flex-grow-1">
                            <button type="button" class="btn btn-success text-white shadow-sm dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-paper-plane me-2"></i>Email Report
                            </button>
                            <ul class="dropdown-menu">
                                <li>
                                    <button class="dropdown-item send-report-item" data-type="daily">
                                        <i class="fas fa-calendar-day me-2 text-muted"></i>Daily Report
                                    </button>
                                </li>
                                <li>
                                    <button class="dropdown-item send-report-item" data-type="weekly">
                                        <i class="fas fa-calendar-week me-2 text-muted"></i>Weekly Report
                                    </button>
                                </li>
                                <li>
                                    <button class="dropdown-item send-report-item" data-type="monthly">
                                        <i class="fas fa-calendar-alt me-2 text-muted"></i>Monthly Report
                                    </button>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- 2. Sales Data Table --}}
        <div class="card shadow-lg border-0 rounded-3">
            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold text-primary">
                    <i class="fas fa-table me-2"></i>Sales Breakdown
                </h6>
                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary px-3 py-2 rounded-pill">
                    <i class="fas fa-calendar-alt me-1"></i> <span id="current-period-label">Current View</span>
                </span>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table id="sales-table" class="table table-hover align-middle mb-0 w-100" data-url="{{ route('reports.sales-data') }}">
                        <thead class="bg-light text-secondary">
                            <tr>
                                <th class="py-3 ps-4" style="width: 15%">Product ID</th>
                                <th class="py-3" style="width: 35%">Product Name</th>
                                <th class="py-3 text-center" style="width: 25%">Total Quantity Sold</th>
                                <th class="py-3 text-end pe-4" style="width: 25%">Total Revenue</th>
                            </tr>
                        </thead>
                        <tbody>
                            {{-- DataTables populates this --}}
                        </tbody>
                        <tfoot class="bg-light fw-bold text-dark border-top">
                            <tr>
                                <td colspan="2" class="text-end py-3 text-uppercase small text-muted">Grand Total:</td>
                                <td id="total-quantity" class="text-center py-3 fs-5">0</td>
                                <td id="total-revenue" class="text-end pe-4 py-3 fs-5 text-success">₱0.00</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        @vite('resources/js/sales-overview.js')
        
        <style>
            /* Custom print styles to ensure table looks good on paper */
            @media print {
                body * { visibility: hidden; }
                .card, .card * { visibility: visible; }
                .card { position: absolute; left: 0; top: 0; width: 100%; border: none !important; shadow: none !important; }
                #filter-btn, button { display: none !important; }
            }
            .dataTables_wrapper .dataTables_paginate .paginate_button { padding: 0; margin: 0; }
            table.dataTable.no-footer { border-bottom: none; }
        </style>
    @endpush
</x-app-layout>