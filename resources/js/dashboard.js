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

            // 1. Revenue Chart Logic
            let revenueChart; // Declare variable

            const initRevenueChart = () => {
                if (data.currentMonthRevenue) {
                    const options = {
                        chart: { 
                            type: 'area', 
                            height: 300, 
                            toolbar: { show: false },
                            id: 'revenue-main-chart' // ID for updates
                        },
                        stroke: { curve: 'smooth', width: 3 },
                        // Default to Monthly View
                        series: [
                            { name: 'Current Month', data: data.currentMonthRevenue },
                            { name: 'Previous Month', data: data.prevMonthRevenue }
                        ],
                        xaxis: { 
                            categories: data.currentMonthRevenue.map((_, i) => String(i + 1).padStart(2, '0')) 
                        },
                        colors: ['#0d6efd', '#ffc107'], // Blue vs Yellow
                        dataLabels: { enabled: false },
                        grid: { borderColor: '#dee2e6' },
                        fill: { type: 'gradient', gradient: { opacityFrom: 0.4, opacityTo: 0.05 } },
                        tooltip: {
                            y: { formatter: (val) => "₱" + (val || 0).toLocaleString() }
                        }
                    };
                    
                    revenueChart = new ApexCharts(document.querySelector("#monthlyRevenueChart"), options);
                    revenueChart.render();
                    hideLoader("#monthlyRevenueChart");
                } else {
                    showMessage("#monthlyRevenueChart", "No revenue data available.");
                }
            };

            initRevenueChart();

            // ✅ LISTENER: Handle Timeframe Toggle
            const timeframeSelect = document.getElementById('revenue-timeframe');
            if(timeframeSelect && revenueChart) {
                timeframeSelect.addEventListener('change', function() {
                    const timeframe = this.value;
                    
                    if (timeframe === 'yearly') {
                        // Update to Yearly Data (Bar Chart style usually better for months)
                        revenueChart.updateOptions({
                            chart: { type: 'bar' }, // Switch to Bar for year view
                            xaxis: { categories: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'] },
                            colors: ['#6610f2', '#adb5bd'], // Purple (Current) vs Gray (Last Year)
                        });
                        revenueChart.updateSeries([
                            { name: `Current Year (${data.yearlyData.currentYearLabel})`, data: data.yearlyData.current },
                            { name: `Previous Year (${data.yearlyData.prevYearLabel})`, data: data.yearlyData.previous }
                        ]);
                    } else {
                        // Revert to Monthly Data (Area Chart)
                        revenueChart.updateOptions({
                            chart: { type: 'area' },
                            xaxis: { categories: data.currentMonthRevenue.map((_, i) => String(i + 1).padStart(2, '0')) },
                            colors: ['#0d6efd', '#ffc107'],
                        });
                        revenueChart.updateSeries([
                            { name: 'Current Month', data: data.currentMonthRevenue },
                            { name: 'Previous Month', data: data.prevMonthRevenue }
                        ]);
                    }
                });
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

            // 4. MARKET INSIGHTS: Seasonal Forecast Charts (Combo: Average vs Spike)
            const createRecChart = (elementId, chartData, color) => {
                // 🛑 SAFETY CHECK: Ensure chartData exists and has length
                if (chartData && Array.isArray(chartData) && chartData.length > 0) {
                    
                    const options = {
                        chart: { 
                            type: 'line', 
                            height: 350, // ✅ Increased from 300 to 350 for better full-width aspect ratio
                            toolbar: { show: false },
                            parentHeightOffset: 0
                        },
                        series: [
                            {
                                name: 'Normal Monthly Average',
                                type: 'column',
                                // ✅ FIX: Ensure value is a number, default to 0
                                data: chartData.map(i => parseFloat(i.avg_sales || 0))
                            },
                            {
                                name: 'Seasonal Spike (Forecast)',
                                type: 'line',
                                // ✅ FIX: Ensure value is a number, default to 0
                                data: chartData.map(i => parseFloat(i.target_sales || 0))
                            }
                        ],
                        xaxis: { 
                            // ✅ FIX: Ensure categories exist
                            categories: chartData.map(i => i.product_name || 'Unknown Product') 
                        },
                        colors: ['#adb5bd', color], 
                        
                        stroke: { 
                            width: [0, 4], 
                            curve: 'smooth'
                        },
                        
                        plotOptions: { 
                            bar: { 
                                columnWidth: '50%', 
                                borderRadius: 4 
                            } 
                        },
                        
                        grid: { borderColor: '#f1f1f1' },
                        
                        markers: { size: 5 }, 
                        
                        dataLabels: { 
                            enabled: true, 
                            enabledOnSeries: [1], 
                            style: { colors: [color] } 
                        },
                        
                        tooltip: {
                            y: {
                                formatter: function(value) {
                                    return parseInt(value).toLocaleString() + " units";
                                }
                            }
                        }
                    };
                    
                    // Clear previous contents (if any) to prevent duplicate rendering issues
                    document.querySelector(elementId).innerHTML = "";
                    
                    const chart = new ApexCharts(document.querySelector(elementId), options);
                    chart.render();
                    hideLoader(elementId);
                } else {
                    showMessage(elementId, "No sufficient data for trends.");
                }
            };

            // Render Charts
            if (data.recs) {
                // Current Month: Blue Line
                createRecChart("#currentMonthRecsChart", data.recs.current, '#0d6efd');
                // Next Month: Green Line
                createRecChart("#nextMonthRecsChart", data.recs.next, '#198754');
            } else {
                showMessage("#currentMonthRecsChart", "Data unavailable.");
                showMessage("#nextMonthRecsChart", "Data unavailable.");
            }
    });

    // 5. AI ANALYST LOGIC
    const askAiBtn = document.getElementById('ask-ai-btn');
    const langSelect = document.getElementById('ai-language'); 

    if (askAiBtn) {
        askAiBtn.addEventListener('click', function() {
            const aiModalEl = document.getElementById('aiModal');
            const aiModal = bootstrap.Modal.getOrCreateInstance(aiModalEl);
            aiModal.show();

            document.getElementById('ai-loading').classList.remove('d-none');
            document.getElementById('ai-content').classList.add('d-none');

            // Get selected language
            const selectedLang = langSelect ? langSelect.value : 'en'; 
            
            // Construct URL
            const url = `${window.routes.askAi}?lang=${selectedLang}`;

            fetch(url)
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
                    console.error("AI Error:", err); // ✅ FIX: Log the error here
                    document.getElementById('ai-loading').classList.add('d-none');
                    document.getElementById('ai-content').classList.remove('d-none');
                    document.getElementById('ai-response-text').innerHTML = 
                        `<span class="text-danger fw-bold"><i class="fas fa-wifi me-2"></i>Connection Error. Ensure Ollama is running and route is defined.</span>`;
                });
        });
    }}
}); 