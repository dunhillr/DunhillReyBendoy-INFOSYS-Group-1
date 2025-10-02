<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Top Selling Products ({{ ucfirst($period) }})
        </h2>
    </x-slot>

    <div class="container py-4">
        <div class="d-flex justify-content-end mb-3">
            <div class="btn-group" role="group" aria-label="Sales Period">
                <a href="{{ route('reports.topSelling', 'weekly') }}" class="btn btn-primary {{ $period === 'weekly' ? 'active' : '' }}">Weekly</a>
                <a href="{{ route('reports.topSelling', 'monthly') }}" class="btn btn-primary {{ $period === 'monthly' ? 'active' : '' }}">Monthly</a>
                <a href="{{ route('reports.topSelling', 'yearly') }}" class="btn btn-primary {{ $period === 'yearly' ? 'active' : '' }}">Yearly</a>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-body">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Rank</th>
                            <th>Product Name</th>
                            <th>Total Quantity Sold</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($topSellingProducts as $index => $sale)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $sale->product->name ?? 'Product Not Found' }}</td>
                                <td>{{ $sale->total_quantity_sold }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center">No sales data available for this period.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
