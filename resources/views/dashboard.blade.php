<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h4 mb-0 text-gray-800">{{ __('Dashboard Overview') }}</h2>
            <small class="text-muted">{{ now()->format('F j, Y') }}</small>
        </div>
    </x-slot>

    <div class="py-4">
        <div class="container-fluid">

            <!-- SECTION 1: KPIs (ALWAYS VISIBLE) -->
            <div class="row mb-4">
                <x-summary-card 
                    title="Total Sales (Current Month)" 
                    value="₱{{ number_format($monthlyRevenue, 2) }}" 
                    color="primary"
                    icon="dollar-sign" 
                    change="{{ $revenueChange }}"
                />
                <x-summary-card 
                    title="Transactions (Current Month)" 
                    value="{{ number_format($totalSales) }}" 
                    color="success"
                    icon="receipt" 
                    change="{{ $salesChange }}"
                />
                <x-summary-card 
                    title="Total Products Sold (Current Month)" 
                    value="{{ number_format($productsSold) }}" 
                    color="warning"
                    icon="boxes" 
                    change="{{ $productsChange }}"
                />
            </div>

            <!-- SECTION 2: TABBED DATA NAVIGATION -->
            <div class="card shadow-sm border-0 rounded-3 mb-5">
                <div class="card-header bg-white border-bottom-0 pt-4 pb-0 px-4">
                    <ul class="nav nav-tabs card-header-tabs" id="dashboardTabs" role="tablist">
                        {{-- Tab 1: Analytics --}}
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active fw-bold text-dark" id="analytics-tab" data-bs-toggle="tab" data-bs-target="#analytics" type="button" role="tab" aria-controls="analytics" aria-selected="true">
                                <i class="fas fa-chart-line me-2 text-primary"></i>Sales Analytics
                            </button>
                        </li>
                        {{-- Tab 2: Forecast/Recommendations --}}
                        <li class="nav-item" role="presentation">
                            <button class="nav-link fw-bold text-dark" id="insights-tab" data-bs-toggle="tab" data-bs-target="#insights" type="button" role="tab" aria-controls="insights" aria-selected="false">
                                <i class="fas fa-lightbulb me-2 text-warning"></i>Market Insights
                            </button>
                        </li>
                    </ul>
                </div>

                <div class="card-body p-4">
                    {{-- Add overflow: visible to prevent clipping --}}
                    <div class="tab-content h-100" id="dashboardTabsContent" style="overflow: visible;">
                        
                        <!-- TAB PANE 1: SALES ANALYTICS -->
                        <div class="tab-pane fade show active" id="analytics" role="tabpanel" aria-labelledby="analytics-tab">
                            <div class="row">
                                
                                {{-- 1. Monthly Revenue Trend --}}
                                <div class="col-12 mb-4">
                                    <div class="card shadow-sm h-100 border-0 bg-light">
                                        <div class="card-body">
                                            <h5 class="card-title mb-3 fw-bold">Monthly Revenue Trend</h5>
                                            <div id="monthlyRevenueChart" style="height: 300px;" class="d-flex justify-content-center align-items-center">
                                                <div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            
                                {{-- 2. Top Selling Products (UPDATED HEADER) --}}
                                <div class="col-lg-6 mb-4">
                                    <div class="card shadow-sm h-100 border-0 bg-light">
                                        <div class="card-body">
                                            {{-- 💡 UX IMPROVEMENT: Added dynamic month name --}}
                                            <h5 class="card-title mb-3 fw-bold">
                                                Top 10 Selling Products 
                                                <small class="text-muted fw-normal ms-1">({{ $recs['currentMonthName'] }})</small>
                                            </h5>
                                            {{-- ✅ FIX: Changed height to min-height to allow expansion --}}
                                            <div id="topSellingProductsChart" style="min-height: 350px;" class="d-flex justify-content-center align-items-center">
                                                <div class="spinner-border text-secondary" role="status"><span class="visually-hidden">Loading...</span></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- 3. Sales by Category (UPDATED HEADER) --}}
                                <div class="col-lg-6 mb-4">
                                    <div class="card shadow-sm h-100 border-0 bg-light">
                                        <div class="card-body">
                                            {{-- 💡 UX IMPROVEMENT: Added dynamic month name --}}
                                            <h5 class="card-title mb-3 fw-bold">
                                                Sales Contribution by Category
                                                <small class="text-muted fw-normal ms-1">({{ $recs['currentMonthName'] }})</small>
                                            </h5>
                                            <div id="salesByCategoryChart" style="height: 450px;" class="d-flex justify-content-center align-items-center">
                                                <div class="spinner-border text-success" role="status"><span class="visually-hidden">Loading...</span></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>

                        <!-- TAB PANE 2: MARKET INSIGHTS -->
                        <div class="tab-pane fade" id="insights" role="tabpanel" aria-labelledby="insights-tab">
                            <div class="row">
                                <div class="col-12 mb-3">
                                    <div class="alert alert-info border-0 d-flex align-items-center" role="alert">
                                        <i class="fas fa-info-circle me-2 fs-4"></i>
                                        <div>
                                            <strong>Historical Trends:</strong> Charts below show cumulative top performing products from 
                                            <strong>all previous years</strong> for these months.
                                        </div>
                                    </div>
                                </div>

                                {{-- Current Month Recs --}}
                                <div class="col-md-6 mb-3">
                                    <div class="card h-100 shadow-sm {{ !$recs['isSecondHalf'] ? 'border-primary border-top border-4' : 'border-light' }}">
                                        <div class="card-body">
                                            <h6 class="mb-3 fw-bold {{ !$recs['isSecondHalf'] ? 'text-primary' : 'text-muted' }}">
                                                📅 {{ $recs['currentMonthName'] }} All-Time Bestsellers
                                                @if(!$recs['isSecondHalf']) <span class="badge bg-primary ms-2">Focus Now</span> @endif
                                            </h6>
                                            <div id="currentMonthRecsChart" style="height: 300px;" class="d-flex justify-content-center align-items-center">
                                                <div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Next Month Recs --}}
                                <div class="col-md-6 mb-3">
                                    <div class="card h-100 shadow-sm {{ $recs['isSecondHalf'] ? 'border-success border-top border-4' : 'border-light' }}">
                                        <div class="card-body">
                                            <h6 class="mb-3 fw-bold {{ $recs['isSecondHalf'] ? 'text-success' : 'text-muted' }}">
                                                🚀 Upcoming: {{ $recs['nextMonthName'] }} All-Time Bestsellers
                                                @if($recs['isSecondHalf']) <span class="badge bg-success ms-2">Prepare Stock</span> @endif
                                            </h6>
                                            <div id="nextMonthRecsChart" style="height: 300px;" class="d-flex justify-content-center align-items-center">
                                                <div class="spinner-border text-success" role="status"><span class="visually-hidden">Loading...</span></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div> {{-- End Tab Content --}}
                </div>
            </div>

        </div>
    </div>

    <script>
    window.routes = {
        salesData: "{{ route('dashboard.summary') }}"
    };
    </script>

    @push('scripts')
        @vite('resources/js/dashboard.js') 
        <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
        
        <style>
            /* Essential Styles for Tab/Chart stability */
            .tab-pane { width: 100% !important; }
            #currentMonthRecsChart, #nextMonthRecsChart, #monthlyRevenueChart, #topSellingProductsChart, #salesByCategoryChart {
                min-height: 300px !important;
                width: 100% !important;
                display: block;
            }
        </style>

        <script>
            document.addEventListener('DOMContentLoaded', function () {
                var triggerTabList = [].slice.call(document.querySelectorAll('button[data-bs-toggle="tab"]'))
                triggerTabList.forEach(function (triggerEl) {
                    triggerEl.addEventListener('shown.bs.tab', function (event) {
                        // Double trigger to handle animation timing
                        window.dispatchEvent(new Event('resize'));
                        setTimeout(function() {
                            window.dispatchEvent(new Event('resize'));
                        }, 200); 
                    })
                })
            });
        </script>
    @endpush
</x-app-layout>