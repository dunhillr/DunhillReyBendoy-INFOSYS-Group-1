<x-app-layout>
    <div class="container py-5">
        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm border-0 rounded-4">
                    <div class="card-body">
                        <h1 class="fw-bold text-primary mb-4">
                            <i class="bi bi-bar-chart-fill me-2"></i>Sales Overview
                        </h1>

                        <!-- Filter Controls -->
                        <form id="filter-form" class="row g-3 align-items-end mb-4">
                            <div class="col-md-3">
                                <label for="month" class="form-label fw-semibold">Month</label>
                                <select id="month" name="month" class="form-select shadow-sm">
                                    @foreach ($availableMonths as $monthNum => $monthName)
                                        <option value="{{ $monthNum }}" {{ $monthNum == $selectedMonth ? 'selected' : '' }}>
                                            {{ $monthName }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-2">
                                <label for="year" class="form-label fw-semibold">Year</label>
                                <select id="year" name="year" class="form-select shadow-sm">
                                    @foreach ($availableYears as $year)
                                        <option value="{{ $year }}" {{ $year == $selectedYear ? 'selected' : '' }}>
                                            {{ $year }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-2 pt-md-4">
                                <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm">
                                    <i class="bi bi-funnel-fill me-1"></i> Filter
                                </button>
                            </div>
                        </form>

                        <!-- DataTable -->
                        <div class="table-responsive">
                            <table id="sales-table" class="table table-striped table-hover align-middle border rounded-3">
                                <thead class="table-primary text-start">
                                    <tr>
                                        <th>Product ID</th>
                                        <th>Product Name</th>
                                        <th>Total Quantity</th>
                                        <th>Total Revenue (₱)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- DataTables fills this -->
                                </tbody>
                                <tfoot class="table-light fw-bold">
                                    <tr>
                                        <td colspan="2" class="text-end">Total:</td>
                                        <td id="total-quantity" class="text-center">0</td>
                                        <td id="total-revenue" class="text-end text-success">₱0.00</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            $(function() {
                let salesTable;

                salesTable = $('#sales-table').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: {
                        url: '{!! route('reports.sales-data') !!}',
                        data: function (d) {
                            d.month = $('#month').val();
                            d.year = $('#year').val();
                        }
                    },
                    order: [[3, 'desc']],
                    columns: [
                        { data: 'product_id', name: 'product_id'},
                        { data: 'product_name', name: 'product_name' },
                        {
                            data: 'total_quantity',
                            name: 'total_quantity',
                            render: function(data) {
                                return parseInt(data).toLocaleString();
                            }
                        },
                        {
                            data: 'total_revenue',
                            name: 'total_revenue',
                            render: function(data) {
                                return '₱' + parseFloat(data).toLocaleString(undefined, { minimumFractionDigits: 2 });
                            }
                        }
                    ],
                    
                    
                    language: {
                        emptyTable: "No sales found for this month and year."
                    },
                    drawCallback: function(settings) {
                        const api = this.api();
                        let totalQty = 0;
                        let totalRevenue = 0;

                        // Loop through data and sum values
                        api.rows({ page: 'current' }).data().each(function(row) {
                            totalQty += parseFloat(row.total_quantity);
                            // Remove ₱ and commas before parsing
                            totalRevenue += parseFloat(
                                String(row.total_revenue).replace(/[₱,]/g, '')
                            );
                        });

                        // Update footer
                        $('#total-quantity').text(totalQty.toLocaleString());
                        $('#total-revenue').text('₱' + totalRevenue.toLocaleString(undefined, { minimumFractionDigits: 2 }));
                    }
                });

                // Filter form submit
                $('#filter-form').on('submit', function(e) {
                    e.preventDefault();
                    salesTable.ajax.reload();
                });
            }); console.log("✅ Autocomplete script loaded");
        </script>
    @endpush
</x-app-layout>
