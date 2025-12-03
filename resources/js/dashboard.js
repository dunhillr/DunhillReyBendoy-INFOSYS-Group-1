// ✅ FIX: Import Bootstrap so we can use new bootstrap.Modal()
import * as bootstrap from 'bootstrap';

document.addEventListener('DOMContentLoaded', () => {

    // Helper function to handle the loading state
    function hideLoader(elementId) {
        const chartDiv = document.querySelector(elementId);
        if (!chartDiv) return; 
        
        const spinner = chartDiv.querySelector('.spinner-border');
        if (spinner) {
            spinner.remove();
            chartDiv.classList.remove('d-flex', 'justify-content-center', 'align-items-center');
        }
    }

    // Helper to show error/empty message
    function showMessage(elementId, message) {
        const chartDiv = document.querySelector(elementId);
        if (chartDiv) {
            chartDiv.innerHTML = `<p class="text-muted text-center py-5">${message}</p>`;
            chartDiv.classList.remove('d-flex', 'justify-content-center', 'align-items-center');
        }
    }

    // ---------------------------
    // FETCH DASHBOARD DATA
    // ---------------------------
    // Check if route exists to prevent errors on other pages
    if (window.routes && window.routes.salesData) {
        fetch(window.routes.salesData)
        .then(res => res.json())
        .then(data => {

            // 1. Monthly Revenue Chart
            if (data.currentMonthRevenue) {
                const revenueChart = new ApexCharts(document.querySelector("#monthlyRevenueChart"), {
                    chart: { type: 'area', height: 300, toolbar: { show: false } },
                    stroke: { curve: 'smooth', width: 3 },
                    series: [
                        { name: 'Current Month', data: data.currentMonthRevenue },
                        { name: 'Previous Month', data: data.prevMonthRevenue }
                    ],
                    xaxis: { categories: data.currentMonthRevenue.map((_, i) => String(i + 1).padStart(2, '0')) },
                    colors: ['#0d6efd', '#ffc107'],
                    dataLabels: { enabled: false },
                    grid: { borderColor: '#dee2e6' },
                    fill: { type: 'gradient', gradient: { opacityFrom: 0.4, opacityTo: 0.05 } },
                });
                revenueChart.render();
                hideLoader("#monthlyRevenueChart");
            } else {
                showMessage("#monthlyRevenueChart", "No revenue data available.");
            }

            // 2. Top Selling Products Chart
            setTimeout(() => {
                if (data.top_products && data.top_products.length > 0) {
                    const productsCount = data.top_products.length;
                    const dynamicHeight = productsCount * 50 + 100;

                    const topProductsChart = new ApexCharts(document.querySelector("#topSellingProductsChart"), {
                        chart: { type: 'bar', height: dynamicHeight < 300 ? 300 : dynamicHeight, toolbar: { show: false }, padding: { left: 20, right: 0 } },
                        plotOptions: { bar: { horizontal: true, borderRadius: 6 } },
                        series: [{ name: 'Units Sold', data: data.top_products.map(p => p.total_quantity) }],
                        xaxis: { categories: data.top_products.map(p => p.product_name) },
                        dataLabels: { enabled: true, style: { colors: ['#fff'] } }, 
                        grid: { borderColor: '#dee2e6' },
                        colors: ['#198754'],
                        tooltip: { theme: 'light', y: { formatter: (val) => val.toLocaleString() + " units" } },
                        yaxis: { labels: { minWidth: 150 } }
                    });
                    topProductsChart.render();
                    hideLoader("#topSellingProductsChart");
                } else {
                    showMessage("#topSellingProductsChart", "No product sales data yet.");
                }
            }, 50);

            // 3. Sales by Category Chart
            if (data.sales_by_category && data.sales_by_category.length > 0) {
                const categoryLabels = data.sales_by_category.map(c => c.category_name);
                const categorySeries = data.sales_by_category.map(c => parseFloat(c.total_revenue));

                const categoryChart = new ApexCharts(document.querySelector("#salesByCategoryChart"), {
                    chart: { type: 'donut', height: 450, fontFamily: 'inherit' },
                    labels: categoryLabels,
                    series: categorySeries,
                    colors: ['#0d6efd', '#6610f2', '#6f42c1', '#d63384', '#fd7e14', '#6c757d'], 
                    plotOptions: {
                        pie: {
                            donut: {
                                size: '55%',
                                labels: {
                                    show: true,
                                    total: {
                                        show: true,
                                        label: 'Total Sales',
                                        formatter: function (w) {
                                            const total = w.globals.seriesTotals.reduce((a, b) => a + b, 0);
                                            return '₱' + total.toLocaleString();
                                        }
                                    }
                                }
                            }
                        }
                    },
                    dataLabels: { enabled: true, formatter: (val) => val.toFixed(1) + "%" }, 
                    legend: { position: 'bottom', offsetY: 10 },
                    tooltip: {
                        y: {
                            formatter: function(value, { w }) {
                                let total = w.globals.seriesTotals.reduce((a, b) => a + b, 0);
                                let percent = (value / total) * 100;
                                return "₱" + value.toLocaleString() + " (" + percent.toFixed(1) + "%)";
                            }
                        }
                    }
                });
                categoryChart.render();
                hideLoader("#salesByCategoryChart");
            } else {
                showMessage("#salesByCategoryChart", "No category sales data yet.");
            }

            // 4. MARKET INSIGHTS: Seasonal Forecast Charts
            const createRecChart = (elementId, chartData, color) => {
                if (chartData && chartData.length > 0) {
                    const options = {
                        chart: { type: 'bar', height: 300, toolbar: { show: false } },
                        plotOptions: { bar: { horizontal: true, borderRadius: 4, barHeight: '60%' } },
                        series: [{ name: 'Sold', data: chartData.map(i => i.total_sold_last_year) }],
                        xaxis: { categories: chartData.map(i => i.product_name) },
                        colors: [color],
                        grid: { borderColor: '#f1f1f1' },
                        dataLabels: { enabled: true, style: { colors: ['#fff'] } },
                        tooltip: { y: { formatter: (val) => val.toLocaleString() + " units" } }
                    };
                    const chart = new ApexCharts(document.querySelector(elementId), options);
                    chart.render();
                    hideLoader(elementId);
                } else {
                    showMessage(elementId, "No sufficient data for trends.");
                }
            };

            if (data.recs) {
                createRecChart("#currentMonthRecsChart", data.recs.current, '#0d6efd');
                createRecChart("#nextMonthRecsChart", data.recs.next, '#198754');
            } else {
                showMessage("#currentMonthRecsChart", "Data unavailable.");
                showMessage("#nextMonthRecsChart", "Data unavailable.");
            }

        })
        .catch(err => {
            console.error('Error fetching dashboard data:', err);
            showMessage("#monthlyRevenueChart", "Failed to load data.");
            showMessage("#topSellingProductsChart", "Failed to load data.");
            showMessage("#salesByCategoryChart", "Failed to load data.");
        });
    }

    // 5. AI ANALYST LOGIC
    const askAiBtn = document.getElementById('ask-ai-btn');
    if (askAiBtn) {
        askAiBtn.addEventListener('click', function() {
            const aiModalEl = document.getElementById('aiModal');
            
            // ✅ FIX: Use 'getOrCreateInstance' to prevent duplicate modals/backdrops
            const aiModal = bootstrap.Modal.getOrCreateInstance(aiModalEl);
            aiModal.show();

            document.getElementById('ai-loading').classList.remove('d-none');
            document.getElementById('ai-content').classList.add('d-none');

            // Note: This URL must be defined in your routes/web.php
            fetch(window.routes.askAi)
                .then(res => res.json())
                .then(data => {
                    document.getElementById('ai-loading').classList.add('d-none');
                    document.getElementById('ai-content').classList.remove('d-none');

                    if (data.success) {
                        document.getElementById('ai-response-text').innerText = data.message;
                    } else {
                        document.getElementById('ai-response-text').innerHTML = 
                            `<span class="text-danger fw-bold"><i class="fas fa-exclamation-triangle me-2"></i>${data.message}</span>`;
                    }
                })
                .catch(err => {
                    document.getElementById('ai-loading').classList.add('d-none');
                    document.getElementById('ai-content').classList.remove('d-none');
                    document.getElementById('ai-response-text').innerHTML = 
                        `<span class="text-danger fw-bold"><i class="fas fa-wifi me-2"></i>Connection Error. Ensure Ollama is running and route is defined.</span>`;
                });
        });
    }
});