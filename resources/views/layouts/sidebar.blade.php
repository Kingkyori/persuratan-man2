<!-- Sidebar Component -->
<link rel="stylesheet" href="{{ asset('css/sidebar.css') }}">

<aside class="sidebar">
    @php
        $userRole = session('user.role', 'admin');
        $isAdmin = $userRole === 'admin';
        $isHeadmaster = $userRole === 'kepala_sekolah';
    @endphp

    <div class="sidebar-header">
        <div class="sidebar-logo">
            <span class="logo-icon">M</span>
        </div>
        <div class="sidebar-title">
            <h3>MAN 2 Surakarta</h3>
            <p>INSTITUTIONAL PORTAL</p>
        </div>
    </div>

    <nav class="sidebar-nav">
        <a href="{{ route('dashboard') }}" class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <span class="nav-icon">&#128202;</span>
            <span class="nav-label">Dashboard</span>
        </a>

        <a href="{{ route('manajemen-surat') }}" class="nav-item {{ request()->routeIs('manajemen-surat') ? 'active' : '' }}">
            <span class="nav-icon">&#128450;</span>
            <span class="nav-label">Manajemen Surat</span>
        </a>

        @if($isAdmin)
            <a href="{{ route('surat-masuk') }}" class="nav-item {{ request()->routeIs('surat-masuk') ? 'active' : '' }}">
                <span class="nav-icon">&#128228;</span>
                <span class="nav-label">Surat Masuk</span>
            </a>

            <a href="{{ route('surat-keluar') }}" class="nav-item {{ request()->routeIs('surat-keluar') ? 'active' : '' }}">
                <span class="nav-icon">&#128233;</span>
                <span class="nav-label">Surat Keluar</span>
            </a>

            <a href="{{ route('sppd') }}" class="nav-item {{ request()->routeIs('sppd') ? 'active' : '' }}">
                <span class="nav-icon">&#128194;</span>
                <span class="nav-label">SPPD & Penugasan</span>
            </a>
        @endif

        @if($isHeadmaster)
            <a href="{{ route('disposisi') }}" class="nav-item {{ request()->routeIs('disposisi') ? 'active' : '' }}">
                <span class="nav-icon">&#128221;</span>
                <span class="nav-label">Disposisi</span>
            </a>
        @endif
    </nav>

    <div class="sidebar-footer">
        <button class="help-btn">
            <span>?</span>
            <span>Help Center</span>
        </button>
        <form method="POST" action="{{ route('logout') }}" style="width: 100%;">
            @csrf
            <button type="submit" class="logout-btn">
                <span>&#128682;</span>
                <span>Logout</span>
            </button>
        </form>
    </div>
</aside>
