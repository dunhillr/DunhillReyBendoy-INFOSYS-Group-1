<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h4 mb-0 text-gray-800">{{ __('Dashboard Overview') }}</h2>
            <small class="text-muted">{{ now()->format('F j, Y') }}</small>
        </div>
    </x-slot>

    <div class="py-4">
        <div class="container-fluid">

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
                    <div class="tab-content h-100" id="dashboardTabsContent" style="overflow: visible;">
                        
                        <div class="tab-pane fade show active" id="analytics" role="tabpanel" aria-labelledby="analytics-tab">
                            <div class="row">
                                
                                {{-- 1. Monthly Revenue Trend (Full Width) --}}
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
                            
                                {{-- 2. Top Selling Products (Half Width) --}}
                                <div class="col-lg-6 mb-4">
                                    <div class="card shadow-sm h-100 border-0 bg-light">
                                        <div class="card-body">
                                            <h5 class="card-title mb-3 fw-bold">Top Selling Products</h5>
                                            <div id="topSellingProductsChart" style="height: 350px;" class="d-flex justify-content-center align-items-center">
                                                <div class="spinner-border text-secondary" role="status"><span class="visually-hidden">Loading...</span></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- 3. Sales by Category (Half Width) --}}
                                <div class="col-lg-6 mb-4">
                                    <div class="card shadow-sm h-100 border-0 bg-light">
                                        <div class="card-body">
                                            <h5 class="card-title mb-3 fw-bold">Sales Contribution by Category</h5>
                                            {{-- ✅ UPDATED: Increased height to 450px --}}
                                            <div id="salesByCategoryChart" style="height: 450px;" class="d-flex justify-content-center align-items-center">
                                                <div class="spinner-border text-success" role="status"><span class="visually-hidden">Loading...</span></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>

                        <div class="tab-pane fade" id="insights" role="tabpanel" aria-labelledby="insights-tab">
                            <div class="row">
                                <div class="col-12 mb-3">
                                    <div class="alert alert-info border-0 d-flex align-items-center" role="alert">
                                        <i class="fas fa-info-circle me-2 fs-4"></i>
                                        <div>
                                            <strong>Historical Data:</strong> These recommendations are based on sales performance from 
                                            <strong>{{ now()->subYear()->format('F Y') }}</strong>. Use this to plan your inventory.
                                        </div>
                                    </div>
                                </div>

                                {{-- Current Month Recs --}}
                                <div class="col-md-6 mb-3">
                                    <div class="card h-100 shadow-sm {{ !$recs['isSecondHalf'] ? 'border-primary border-top border-4' : 'border-light' }}">
                                        <div class="card-header bg-white d-flex justify-content-between align-items-center">
                                            <h6 class="mb-0 fw-bold {{ !$recs['isSecondHalf'] ? 'text-primary' : 'text-muted' }}">
                                                📅 {{ $recs['currentMonthName'] }} Trends
                                                @if(!$recs['isSecondHalf']) <span class="badge bg-primary ms-2">Focus Now</span> @endif
                                            </h6>
                                        </div>
                                        <div class="card-body p-0">
                                            <ul class="list-group list-group-flush">
                                                @forelse($recs['current'] as $item)
                                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                                        <span class="fw-medium">{{ $item->product_name }}</span>
                                                        <span class="badge bg-light text-dark border">
                                                            {{ number_format($item->total_sold_last_year) }} sold
                                                        </span>
                                                    </li>
                                                @empty
                                                    <li class="list-group-item text-center text-muted py-4">No data available.</li>
                                                @endforelse
                                            </ul>
                                        </div>
                                    </div>
                                </div>

                                {{-- Next Month Recs --}}
                                <div class="col-md-6 mb-3">
                                    <div class="card h-100 shadow-sm {{ $recs['isSecondHalf'] ? 'border-success border-top border-4' : 'border-light' }}">
                                        <div class="card-header bg-white d-flex justify-content-between align-items-center">
                                            <h6 class="mb-0 fw-bold {{ $recs['isSecondHalf'] ? 'text-success' : 'text-muted' }}">
                                                🚀 Upcoming: {{ $recs['nextMonthName'] }}
                                                @if($recs['isSecondHalf']) <span class="badge bg-success ms-2">Prepare Stock</span> @endif
                                            </h6>
                                        </div>
                                        <div class="card-body p-0">
                                            <ul class="list-group list-group-flush">
                                                @forelse($recs['next'] as $item)
                                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                                        <span class="fw-medium">{{ $item->product_name }}</span>
                                                        <span class="badge bg-light text-dark border">
                                                            {{ number_format($item->total_sold_last_year) }} sold
                                                        </span>
                                                    </li>
                                                @empty
                                                    <li class="list-group-item text-center text-muted py-4">No data available.</li>
                                                @endforelse
                                            </ul>
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
        
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                var triggerTabList = [].slice.call(document.querySelectorAll('button[data-bs-toggle="tab"]'))
                triggerTabList.forEach(function (triggerEl) {
                    triggerEl.addEventListener('shown.bs.tab', function (event) {
                        // 1. Dispatch resize for ApexCharts (Keep this)
                        window.dispatchEvent(new Event('resize'));

                        // 2. Force the scrollable container to recognize the new height
                        // We do this by slightly nudging the scroll position or simply reading the height
                        const scrollContainer = document.querySelector('.content-wrapper');
                        if(scrollContainer) {
                            // This forces a "Reflow" / Layout Recalculation
                            const forceReflow = scrollContainer.offsetHeight; 
                        }
                    })
                })
            });
        </script>
    @endpush
</x-app-layout>