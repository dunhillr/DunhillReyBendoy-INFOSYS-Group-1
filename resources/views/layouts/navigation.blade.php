<nav class="navbar navbar-expand-sm navbar-light bg-light">
    <!-- Primary Navigation Menu -->
    <div class="container-fluid">
        <a class="navbar-brand" href="{{ route('dashboard') }}">
            <!-- Replace with your logo or text -->
            <svg class="block h-9 w-auto" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48">
                <path fill="#676767" d="M38 12L24 26 10 12 8 14 24 30 40 14z" />
            </svg>
        </a>

        <!-- Hamburger -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <!-- Navigation Links -->
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">

    <li class="nav-item">
        <a class="nav-link px-3 py-2 rounded @if(request()->routeIs('dashboard')) active fw-semibold bg-primary text-white @else text-secondary @endif"
           href="{{ route('dashboard') }}">
            Dashboard
        </a>
    </li>

    <li class="nav-item">
        <a class="nav-link px-3 py-2 rounded @if(request()->routeIs('products.*')) active fw-semibold bg-primary text-white @else text-secondary @endif"
           href="{{ route('products.index') }}">
            Products
        </a>
    </li>

    <li class="nav-item">
        <a class="nav-link px-3 py-2 rounded @if(request()->routeIs('record-sales.create')) active fw-semibold bg-primary text-white @else text-secondary @endif"
           href="{{ route('record-sales.create') }}">
            Record Sale
        </a>
    </li>

    <li class="nav-item">
        <a class="nav-link px-3 py-2 rounded @if(request()->routeIs('transactions.index')) active fw-semibold bg-primary text-white @else text-secondary @endif"
           href="{{ route('transactions.index') }}">
            Transactions
        </a>
    </li>

    <li class="nav-item">
        <a class="nav-link px-3 py-2 rounded @if(request()->routeIs('reports.sales-overview')) active fw-semibold bg-primary text-white @else text-secondary @endif"
           href="{{ route('reports.sales-overview') }}">
            Sales Overview
        </a>
    </li>

</ul>

            <!-- Settings Dropdown -->
            <ul class="navbar-nav">
                @auth
                <li class="nav-item dropdown">
                    <button class="nav-link dropdown-toggle btn" type="button" id="navbarDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        {{ Auth::user()->name }}
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                        <li>
                            <a class="dropdown-item" href="{{ route('profile.edit') }}">Profile</a>
                        </li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <a class="dropdown-item" href="{{ route('logout') }}" onclick="event.preventDefault(); this.closest('form').submit();">
                                    Log Out
                                </a>
                            </form>
                        </li>
                    </ul>
                </li>
                @else
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('login') }}">Login</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('register') }}">Register</a>
                </a>
                @endauth
            </ul>
        </div>
    </div>
</nav>