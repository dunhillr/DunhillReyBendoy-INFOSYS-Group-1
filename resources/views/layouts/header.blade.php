<nav class="navbar navbar-light bg-white shadow-sm px-3 sticky-top">
    <div class="container-fluid">
        <div class="d-flex align-items-center">
            <!-- ✅ Logo & Brand Name -->
            <a class="navbar-brand d-flex align-items-center gap-2" href="{{ route('dashboard') }}">
                
                {{-- Custom SVG Logo: "Storefront + Analytics" --}}
                <div class="bg-primary bg-gradient text-white rounded-3 d-flex align-items-center justify-content-center shadow-sm" 
                     style="width: 40px; height: 40px;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        {{-- Store Roof --}}
                        <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                        {{-- Analytics Bars inside the door --}}
                        <line x1="9" y1="22" x2="9" y2="12" style="stroke-width: 2px; stroke: rgba(255,255,255,0.8);"></line>
                        <line x1="15" y1="22" x2="15" y2="15" style="stroke-width: 2px; stroke: rgba(255,255,255,0.8);"></line>
                    </svg>
                </div>

                <div style="line-height: 1.2;">
                    {{-- ✅ UPDATED NAME: SariSmart --}}
                    <span class="fw-black text-primary text-uppercase" style="font-weight: 800; letter-spacing: -0.5px; font-size: 1.2rem;">
                        Sari<span class="text-dark">Smart</span>
                    </span>
                    <small class="d-block text-muted text-uppercase" style="font-size: 0.65rem; letter-spacing: 1px; font-weight: 600;">
                        Smart POS System
                    </small>
                </div>
            </a>
        </div>

        <!-- Account Dropdown -->
        <div>
            @auth
            <div class="dropdown">
                <button class="btn btn-light bg-light border-0 dropdown-toggle d-flex align-items-center gap-2 rounded-pill px-3 py-2" type="button" id="dropdownMenu" data-bs-toggle="dropdown">
                    
                    {{-- User Avatar (Initials) --}}
                    <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" 
                         style="width: 32px; height: 32px; font-size: 0.9rem; font-weight: bold;">
                        {{ substr(Auth::user()->name, 0, 1) }}
                    </div>
                    
                    <span class="fw-medium text-dark d-none d-md-inline">{{ Auth::user()->name }}</span>
                </button>

                <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 rounded-3 mt-2 p-2">
                    <li class="px-2 py-1 text-muted small text-uppercase fw-bold">Account</li>
                    <li>
                        <a class="dropdown-item rounded-2" href="{{ route('profile.edit') }}">
                            <i class="fas fa-user-cog me-2 text-secondary"></i>Profile Settings
                        </a>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <a class="dropdown-item rounded-2 text-danger" href="#" onclick="event.preventDefault(); this.closest('form').submit();">
                                <i class="fas fa-sign-out-alt me-2"></i>Log Out
                            </a>
                        </form>
                    </li>
                </ul>
            </div>
            @endauth
        </div>
    </div>
</nav>