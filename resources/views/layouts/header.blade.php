<nav class="navbar navbar-light bg-white shadow-sm px-3">
    <div class="d-flex align-items-center">
        <!-- Logo -->
        <a class="navbar-brand fw-bold" href="{{ route('dashboard') }}">
            Sari-Sales
        </a>
    </div>

    <!-- Account Dropdown -->
    <div>
        @auth
        <div class="dropdown">
            <button class="btn dropdown-toggle d-flex align-items-center gap-2" type="button" id="dropdownMenu" data-bs-toggle="dropdown">
                <i class="bi bi-person-circle"></i>
                {{ Auth::user()->name }}
            </button>

            <ul class="dropdown-menu dropdown-menu-end">
                <li><a class="dropdown-item" href="{{ route('profile.edit') }}">Profile</a></li>
                <li>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <a class="dropdown-item" href="#" onclick="event.preventDefault(); this.closest('form').submit();">
                            Log Out
                        </a>
                    </form>
                </li>
            </ul>
        </div>
        @endauth
    </div>
</nav>
