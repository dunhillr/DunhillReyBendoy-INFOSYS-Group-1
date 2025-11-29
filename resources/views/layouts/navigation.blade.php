<aside class="sidebar bg-white border-end p-3">
    <ul class="nav flex-column">

        <li class="nav-item mb-1">
            <a class="nav-link d-flex align-items-center gap-2 @if(request()->routeIs('dashboard')) active text-white bg-primary @else text-dark @endif"
               href="{{ route('dashboard') }}">
               <i class="bi bi-speedometer2"></i> Dashboard
            </a>
        </li>

        <li class="nav-item mb-1">
            <a class="nav-link d-flex align-items-center gap-2 @if(request()->routeIs('products.*')) active text-white bg-primary @else text-dark @endif"
               href="{{ route('products.index') }}">
               <i class="bi bi-box-seam"></i> Products
            </a>
        </li>

        <li class="nav-item mb-1">
            <a class="nav-link d-flex align-items-center gap-2 @if(request()->routeIs('record-sales.create')) active text-white bg-primary @else text-dark @endif"
               href="{{ route('record-sales.create') }}">
               <i class="bi bi-cart-plus"></i> Create Sale
            </a>
        </li>

        <li class="nav-item mb-1">
            <a class="nav-link d-flex align-items-center gap-2 @if(request()->routeIs('transactions.index')) active text-white bg-primary @else text-dark @endif"
               href="{{ route('transactions.index') }}">
               <i class="bi bi-currency-exchange"></i> Transactions
            </a>
        </li>

        <li class="nav-item mb-1">
            <a class="nav-link d-flex align-items-center gap-2 @if(request()->routeIs('reports.sales-overview')) active text-white bg-primary @else text-dark @endif"
               href="{{ route('reports.sales-overview') }}">
               <i class="bi bi-graph-up"></i> Sales Overview
            </a>
        </li>

    </ul>
</aside>
