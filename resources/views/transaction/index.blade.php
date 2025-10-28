@push('styles')
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
@endpush

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
@endpush


<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Transaction History
        </h2>
    </x-slot>

    <div class="container py-4">
        <div class="card shadow-sm">
            <div class="card-body">
                <div class="row g-3 mb-3 align-items-end">
                    <div class="col-md-4">
                        <label for="from-date" class="form-label">From Date</label>
                        <input type="date" id="from-date" class="form-control">
                    </div>
                    <div class="col-md-4">
                        <label for="to-date" class="form-label">To Date</label>
                        <input type="date" id="to-date" class="form-control">
                    </div>
                    <div class="col-md-2 d-grid">
                        <button id="filter-btn" class="btn btn-primary">Filter</button>
                    </div>
                    <div class="col-md-2 d-grid">
                        <button id="clear-filter-btn" class="btn btn-secondary">Clear</button>
                    </div>
                </div>

                <div class="table-responsive">
                    <table id="transactions-table" class="table table-bordered table-hover text-center">
                        <thead class="table-light">
                            <tr>
                                <th>Transaction ID</th>
                                <th>Total Amount (₱)</th>
                                <!-- <th>Payment Amount (₱)</th>-->
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- DataTables will populate this table body via AJAX -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Transaction Details Modal -->
<div class="modal fade" id="transactionModal" tabindex="-1" aria-labelledby="transactionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Transaction Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p><strong>Transaction ID:</strong> <span id="modal-transaction-id"></span></p>
                <p><strong>Total Amount:</strong> ₱<span id="modal-total-amount"></span></p>
                <p><strong>Payment Amount:</strong> ₱<span id="modal-payment-amount"></span></p>
                <p><strong>Date:</strong> <span id="modal-date"></span></p>

                <hr>

                <h6>Products</h6>
                <table class="table table-bordered text-center" id="modal-products-table">
                    <thead>
                        <tr>
                            <th>Product Name</th>
                            <th>Price at Sale (₱)</th>
                            <th>Quantity</th>
                            <th>Subtotal (₱)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Populated via JS -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>


    @push('scripts')
    <!-- jQuery + DataTables -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/2.0.8/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/2.0.8/js/dataTables.bootstrap5.min.js"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/2.0.8/css/dataTables.bootstrap5.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <script>
        $(function() {
            let table = $('#transactions-table').DataTable({
                processing: true,
                serverSide: true,
                order: [[1, 'desc']],
                ajax: {
                    url: '{!! route('transactions.data') !!}',
                    data: function (d) {
                        d.from_date = $('#from-date').val();
                        d.to_date = $('#to-date').val();
                    }
                },
                columns: [
                    { data: 'id', name: 'id' },
                    {
                            data: 'total_amount',
                            render: function(data) {
                                return '₱' + parseFloat(data).toLocaleString(undefined, { minimumFractionDigits: 2 });
                            }
                        },
                    /*{
                            data: 'payment_amount',
                            render: function(data) {
                                return '₱' + parseFloat(data).toLocaleString(undefined, { minimumFractionDigits: 2 });
                            }
                        },*/
                    { data: 'created_at_formatted', name: 'created_at_formatted' },
                    { data: 'actions', name: 'actions', orderable: false, searchable: false }
                ]
            });

            $('#filter-btn').on('click', function () {
                table.draw();
            });

            $('#clear-filter-btn').on('click', function () {
                $('#from-date').val('');
                $('#to-date').val('');
                table.draw();
            });
        });

// View Transaction Details
$(document).on('click', '.view-transaction', function() {
    const transactionId = $(this).data('id');
    const url = '{{ route("transactions.show", ":id") }}'.replace(':id', transactionId);

    $.get(url, function(data) {
        // Fill modal data
        $('#modal-transaction-id').text(data.id);
        $('#modal-total-amount').text(parseFloat(data.total_amount).toFixed(2));
        $('#modal-payment-amount').text(parseFloat(data.payment_amount).toFixed(2));
        $('#modal-date').text(data.created_at);

        const tbody = $('#modal-products-table tbody');
        tbody.empty();
        data.details.forEach(function(item) {
            tbody.append(
                `<tr>
                    <td>${item.product_name}</td>
                    <td>${parseFloat(item.price_at_sale).toFixed(2)}</td>
                    <td>${item.quantity}</td>
                    <td>${parseFloat(item.subtotal).toFixed(2)}</td>
                </tr>`
            );
        });

        const modalEl = document.getElementById('transactionModal');
        const modal = new bootstrap.Modal(modalEl);
        modal.show();
    });
});

    </script>
    @endpush
</x-app-layout>
