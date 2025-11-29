document.addEventListener('DOMContentLoaded', () => {

    // Helper function to handle the loading state
    function hideLoader(elementId) {
        const chartDiv = document.querySelector(elementId);
        const spinner = chartDiv.querySelector('.spinner-border');
        if (spinner) {
            spinner.remove();
            chartDiv.classList.remove('d-flex', 'justify-content-center', 'align-items-center');
        }
    }

    // ---------------------------
    // FETCH DASHBOARD DATA
    // ---------------------------
    fetch(window.routes.salesData)
    .then(res => res.json())
    .then(data => {

        // 1. Monthly Revenue Chart
        const revenueChart = new ApexCharts(document.querySelector("#monthlyRevenueChart"), {
            chart: { type: 'area', height: 300, toolbar: { show: false } },
            stroke: { curve: 'smooth', width: 3 },
            series: [
                { name: 'Current Month', data: data.currentMonthRevenue },
                { name: 'Previous Month', data: data.prevMonthRevenue }
            ],
            xaxis: {
                categories: data.currentMonthRevenue.map((_, i) => String(i + 1).padStart(2, '0'))
            },
            colors: ['#0d6efd', '#ffc107'],
            dataLabels: { enabled: false },
            grid: { borderColor: '#dee2e6' },
            fill: { type: 'gradient', gradient: { opacityFrom: 0.4, opacityTo: 0.05 } },
        });
        revenueChart.render();
        hideLoader("#monthlyRevenueChart");


        // 2. Top Selling Products Chart (Wrapped in Timeout for Layout Safety)
        setTimeout(() => {
            const productsCount = data.top_products.length;
            const dynamicHeight = productsCount * 50 + 100;

            const topProductsChart = new ApexCharts(document.querySelector("#topSellingProductsChart"), {
                chart: { 
                    type: 'bar', 
                    height: dynamicHeight < 300 ? 300 : dynamicHeight,
                    toolbar: { show: false },
                    animations: { enabled: false }, 
                    padding: { left: 20, right: 0 } 
                },
                plotOptions: { bar: { horizontal: true, borderRadius: 6 } },
                series: [{ name: 'Units Sold', data: data.top_products.map(p => p.total_quantity) }],
                xaxis: { categories: data.top_products.map(p => p.product_name) },
                dataLabels: { enabled: true, style: { colors: ['#fff'] } }, 
                grid: { borderColor: '#dee2e6' },
                colors: ['#198754'],
                tooltip: {
                    theme: 'light',
                    style: { fontSize: '14px', fontFamily: 'Roboto, Arial, sans-serif' },
                    y: { formatter: (val) => val.toLocaleString() + " units" },
                },
                yaxis: {
                    labels: { minWidth: 150 }
                }
            });
            topProductsChart.render();
            hideLoader("#topSellingProductsChart");
        }, 50);


        // 3. Sales by Category Chart (Donut)
        if (data.sales_by_category && data.sales_by_category.length > 0) {
            const categoryLabels = data.sales_by_category.map(c => c.category_name);
            const categorySeries = data.sales_by_category.map(c => parseFloat(c.total_revenue));

            const categoryChart = new ApexCharts(document.querySelector("#salesByCategoryChart"), {
                chart: {
                    type: 'donut',
                    height: 450, // ✅ UPDATED: Match the blade container height
                    fontFamily: 'inherit'
                },
                labels: categoryLabels,
                series: categorySeries,
                
                // ✅ UPDATED: Added Gray (#6c757d) at the end for "Others"
                colors: ['#0d6efd', '#6610f2', '#6f42c1', '#d63384', '#fd7e14', '#6c757d'], 
                
                plotOptions: {
                    pie: {
                        donut: {
                            size: '55%', // ✅ UPDATED: Smaller hole = Thicker/Bigger slices
                            labels: {
                                show: true,
                                name: {
                                    fontSize: '22px', // Bigger font for center label
                                },
                                value: {
                                    fontSize: '16px',
                                    fontWeight: 600,
                                },
                                total: {
                                    show: true,
                                    label: 'Total Sales',
                                    fontSize: '18px', // Bigger font for "Total Sales" text
                                    formatter: function (w) {
                                        const total = w.globals.seriesTotals.reduce((a, b) => a + b, 0);
                                        return '₱' + total.toLocaleString();
                                    }
                                }
                            }
                        }
                    }
                },
                dataLabels: { 
                    enabled: true,
                    style: {
                        fontSize: '14px', // Bigger percentage text on slices
                        fontWeight: 'bold',
                    },
                    formatter: function (val) {
                        return val.toFixed(1) + "%";
                    }
                }, 
                legend: { 
                    position: 'bottom',
                    fontSize: '14px', // Bigger legend text
                    offsetY: 10 
                },
                tooltip: {
                    y: {
                        formatter: function(value, { series, seriesIndex, dataPointIndex, w }) {
                            let total = w.globals.seriesTotals.reduce((a, b) => a + b, 0);
                            let percent = (value / total) * 100;
                            // Show both Amount and Percentage in tooltip
                            return "₱" + value.toLocaleString() + " (" + percent.toFixed(1) + "%)";
                        }
                    }
                }
            });

            categoryChart.render();
            hideLoader("#salesByCategoryChart");
        } else {
            document.querySelector("#salesByCategoryChart").innerHTML = '<p class="text-muted text-center py-5">No category sales data yet.</p>';
        }

    })
    .catch(err => {
        console.error('Error fetching dashboard data:', err);
        document.querySelector("#monthlyRevenueChart").innerHTML = '<p class="text-danger text-center">Failed to load revenue chart data.</p>';
    });
});