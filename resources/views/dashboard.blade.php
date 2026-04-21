<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - MAN 2 Surakarta</title>
    <link rel="stylesheet" href="{{ asset('css/sidebar.css') }}">
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
</head>
<body>
    <div class="main-container">
        <!-- Sidebar -->
        @include('layouts.sidebar')

        <!-- Content Wrapper -->
        <div class="content-wrapper">
            <!-- Top Bar -->
            <div class="top-bar">
                <div class="search-bar">
                    <span class="search-icon">🔍</span>
                    <input type="text" placeholder="Search records, letters, or students...">
                </div>
                <div class="top-bar-right">
                    <button class="icon-btn">🔔</button>
                    <button class="icon-btn">⚙️</button>
                    <div class="user-info">
                        <span style="font-size: 14px; color: #706f6c;">
                            <strong style="color: #1b1b18;">{{ session('user.name') ?? 'Administrator' }}</strong><br>
                            <span style="font-size: 12px;">SUPER USER</span>
                        </span>
                        <div class="user-badge">{{ substr(session('user.name') ?? 'Admin', 0, 1) }}</div>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="content">
                <!-- Welcome Section -->
                <div class="welcome-section">
                    <div class="welcome-text">
                        <h1>Selamat Datang, Admin</h1>
                        <p>Monitoring the institutional workflow of MAN 2 Surakarta. You have 12 pending dispositions requiring immediate attention.</p>
                        <div class="welcome-buttons">
                            <button class="btn btn-primary">
                                <span>✉️</span>
                                Register New Mail
                            </button>
                            <button class="btn btn-secondary">
                                <span>📋</span>
                                Create SPPD
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Stats Grid -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-header">
                            <span class="stat-icon">📬</span>
                            <span class="stat-label">+5 Today</span>
                        </div>
                        <div class="stat-value">1,284</div>
                        <div class="stat-title">Surat Masuk</div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-header">
                            <span class="stat-icon">📤</span>
                            <span class="stat-label">8 Pending</span>
                        </div>
                        <div class="stat-value">842</div>
                        <div class="stat-title">Surat Keluar</div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-header">
                            <span class="stat-icon">📋</span>
                            <span class="stat-label">Urgent</span>
                        </div>
                        <div class="stat-value">12</div>
                        <div class="stat-title">Disposisi Aktif</div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-header">
                            <span class="stat-icon">📃</span>
                            <span class="stat-label">This Month</span>
                        </div>
                        <div class="stat-value">45</div>
                        <div class="stat-title">Total SPPD</div>
                    </div>
                </div>

                <!-- Two Column Grid -->
                <div class="grid-2">
                    <!-- Letter Tracking Status -->
                    <div class="card">
                        <div class="card-header">
                            <div>
                                <h3 class="card-title">Letter Tracking Status</h3>
                                <p class="card-subtitle">Real-time status overview of all registered documents</p>
                            </div>
                        </div>

                        <div class="chart-container">
                            <div class="chart-bar" style="height: 35%;"><span class="chart-bar-label">Pending</span></div>
                            <div class="chart-bar" style="height: 55%;"><span class="chart-bar-label">Processed</span></div>
                            <div class="chart-bar" style="height: 20%;"><span class="chart-bar-label">Archived</span></div>
                        </div>

                        <div class="chart-stats">
                            <div class="stat-box">
                                <div class="stat-box-label">Efficiency</div>
                                <div class="stat-box-value">94.2%</div>
                            </div>
                            <div class="stat-box">
                                <div class="stat-box-label">Avg. Speed</div>
                                <div class="stat-box-value">1.2 Days</div>
                            </div>
                            <div class="stat-box">
                                <div class="stat-box-label">Volume</div>
                                <div class="stat-box-value">+12%</div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Activity -->
                    <div class="card">
                        <div class="card-header">
                            <div>
                                <h3 class="card-title">Recent Activity</h3>
                            </div>
                            <a href="#" class="view-all-link">View All</a>
                        </div>

                        <div class="activity-item">
                            <div class="activity-icon">📄</div>
                            <div class="activity-content">
                                <div class="activity-title">New Surat Masuk Registered</div>
                                <div class="activity-desc">Subject: Undangan Workshop Kurikulum Merdeka Fase F</div>
                                <div class="activity-time">1m ago</div>
                                <span class="activity-badge">Keasraman</span>
                            </div>
                        </div>

                        <div class="activity-item">
                            <div class="activity-icon">✅</div>
                            <div class="activity-content">
                                <div class="activity-title">Disposition Updated</div>
                                <div class="activity-desc">Status change: "Pending" → "Processed" for Letter ID #202241DAM008</div>
                                <div class="activity-time">3h ago</div>
                                <span class="activity-badge" style="background-color: #f0f4f8; color: #1565c0;">Kepala Madrasah</span>
                            </div>
                        </div>

                        <div class="activity-item">
                            <div class="activity-icon">📑</div>
                            <div class="activity-content">
                                <div class="activity-title">SPPD Issued</div>
                                <div class="activity-desc">Destination: Semarang (Kanwil Kemenag Jatengi)</div>
                                <div class="activity-time">5h ago</div>
                                <span class="activity-badge" style="background-color: #fff3e0; color: #e65100;">Administrative</span>
                            </div>
                        </div>

                        <div class="need-help">
                            <h3>Need Assistance?</h3>
                            <p>Connect with the IT Support team for institutional portal issues.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
