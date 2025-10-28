<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Admin Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    {{ __("You're logged in!") }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Admin Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-4">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Point of Sales Chart</h3>
                        <button class="btn btn-primary" id="downloadSalesChart">Download</button>
                    </div>
                    <div id="salesChart"></div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Top Selling Products</h3>
                        <button class="btn btn-primary" id="downloadTopSellingChart">Download</button>
                    </div>
                    <div id="topSellingProductsChart"></div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Sales Chart
            var salesChartOptions = {
                chart: {
                    type: 'bar',
                    height: 240
                },
                series: [{
                    name: 'sales',
                    data: []
                }],
                xaxis: {
                    categories: []
                }
            };
            var salesChart = new ApexCharts(document.querySelector("#salesChart"), salesChartOptions);
            salesChart.render();

            function fetchSalesData(period) {
                fetch(`{{ route('dashboard.sales-data') }}?period=${period}`)
                    .then(response => response.json())
                    .then(data => {
                        let categories = data.map(item => item.date);
                        let seriesData = data.map(item => item.total);
                        salesChart.updateOptions({
                            xaxis: {
                                categories: categories
                            },
                            series: [{
                                data: seriesData
                            }]
                        });
                    });
            }
            fetchSalesData('week');

            document.getElementById('downloadSalesChart').addEventListener('click', function() {
                salesChart.dataURI().then(({ imgURI }) => {
                    const a = document.createElement('a');
                    a.href = imgURI;
                    a.download = 'point-of-sales-chart.png';
                    a.click();
                });
            });

            // Top Selling Products Chart
            var topSellingProductsChartOptions = {
                chart: {
                    type: 'bar',
                    height: 240
                },
                series: [{
                    name: 'Quantity Sold',
                    data: []
                }],
                xaxis: {
                    categories: []
                },
                plotOptions: {
                    bar: {
                        horizontal: true
                    }
                }
            };
            var topSellingProductsChart = new ApexCharts(document.querySelector("#topSellingProductsChart"), topSellingProductsChartOptions);
            topSellingProductsChart.render();

            function fetchTopSellingProductsData(period) {
                fetch(`{{ route('dashboard.top-selling-products') }}?period=${period}&limit=5`)
                    .then(response => response.json())
                    .then(data => {
                        let categories = data.map(item => item.name);
                        let seriesData = data.map(item => item.total_quantity);
                        topSellingProductsChart.updateOptions({
                            xaxis: {
                                categories: categories
                            },
                            series: [{
                                data: seriesData
                            }]
                        });
                    });
            }
            fetchTopSellingProductsData('week');

            document.getElementById('downloadTopSellingChart').addEventListener('click', function() {
                topSellingProductsChart.dataURI().then(({ imgURI }) => {
                    const a = document.createElement('a');
                    a.href = imgURI;
                    a.download = 'top-selling-products-chart.png';
                    a.click();
                });
            });
        });
    </script>
    @endpush
</x-app-layout>
