<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Sales History
        </h2>
    </x-slot>

    <div class="container py-4">
        <div class="card shadow-sm">
            <div class="card-body">
                <table id="sales-table" class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Transaction ID</th>
                            <th>Product Name</th>
                            <th>Quantity</th>
                            <th>Price at Sale</th>
                            <th>Subtotal</th>
                            <th>Timestamp</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- DataTables will populate this table body via AJAX -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/2.0.8/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/2.0.8/js/dataTables.bootstrap5.min.js"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/2.0.8/css/dataTables.bootstrap5.min.css">
    
    <script>
        $(function() {
            $('#sales-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{!! route('sales.data') !!}',
                columns: [
                    { data: 'id', name: 'id' },
                    { data: 'product_name', name: 'product_name' },
                    { data: 'quantity', name: 'quantity' },
                    { data: 'price_at_sale', name: 'price_at_sale' },
                    { data: 'subtotal', name: 'subtotal' },
                    { data: 'created_at_formatted', name: 'created_at_formatted' },
                ]
            });
        });
    </script>
    @endpush
</x-app-layout>
