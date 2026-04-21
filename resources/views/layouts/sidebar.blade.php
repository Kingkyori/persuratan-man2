<!-- Sidebar Component -->
<link rel="stylesheet" href="{{ asset('css/sidebar.css') }}">

<aside class="sidebar">
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
            <span class="nav-icon">📊</span>
            <span class="nav-label">Dashboard</span>
        </a>
        
        <a href="{{ route('surat-masuk') }}" class="nav-item {{ request()->routeIs('surat-masuk') ? 'active' : '' }}">
            <span class="nav-icon">�</span>
            <span class="nav-label">Surat Masuk</span>
        </a>
        
        <a href="#" class="nav-item">
            <span class="nav-icon">�</span>
            <span class="nav-label">Surat Keluar</span>
        </a>
        
        <a href="#" class="nav-item">
            <span class="nav-icon">�</span>
            <span class="nav-label">Manajemen Surat</span>
        </a>
        
        <a href="#" class="nav-item">
            <span class="nav-icon">📑</span>
            <span class="nav-label">SPPD & Penugasan</span>
        </a>
    </nav>

    <div class="sidebar-footer">
        <button class="help-btn">
            <span>❓</span>
            <span>Help Center</span>
        </button>
        <form method="POST" action="{{ route('logout') }}" style="width: 100%;">
            @csrf
            <button type="submit" class="logout-btn">
                <span>🚪</span>
                <span>Logout</span>
            </button>
        </form>
    </div>
</aside>
