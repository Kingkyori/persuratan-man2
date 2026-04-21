<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SPPD & Penugasan - MAN 2 Surakarta</title>
    <link rel="stylesheet" href="{{ asset('css/sidebar.css') }}">
    <link rel="stylesheet" href="{{ asset('css/sppd.css') }}">
</head>
<body>
    <div class="main-layout">
        <!-- Sidebar -->
        @include('layouts.sidebar')

        <!-- Main Content -->
        <main class="main-content">
            <!-- Top Bar -->
            <div class="top-bar">
                <div class="search-container">
                    <input type="text" class="search-input" placeholder="Search reports...">
                </div>
                <div class="top-bar-actions">
                    <button class="icon-btn notification-btn">
                        <span>🔔</span>
                    </button>
                    <button class="icon-btn settings-btn">
                        <span>⚙️</span>
                    </button>
                    <div class="user-profile">
                        <span class="profile-text">Admin Administrator<br><small>Logged Admin</small></span>
                        <span class="profile-avatar">👤</span>
                    </div>
                </div>
            </div>

            <!-- Page Header -->
            <div class="page-header-reporting">
                <div>
                    <p class="section-label">INSTITUTIONAL INTELLIGENCE</p>
                    <h1>Comprehensive<br>Administrative<br>Oversight.</h1>
                </div>
                <p class="header-description">Access, analyze, and archive official documentation with academic precision backed by robust encryption.</p>
            </div>

            <!-- Main Layout -->
            <div class="reporting-container">
                <!-- Left: Reports Section -->
                <div class="reports-section">
                    <!-- Rekaptulasi Reports -->
                    <div class="rekaptulasi-section">
                        <h2>Rekaptulasi Reports</h2>
                        <p class="section-description">Generate periodic summaries for institutional communications</p>

                        <div class="report-types">
                            <button class="report-type-btn">
                                <span class="icon-large">📋</span>
                                <span>SURAT MASUK</span>
                            </button>
                            <button class="report-type-btn">
                                <span class="icon-large">📤</span>
                                <span>SURAT KELUAR</span>
                            </button>
                            <button class="report-type-btn">
                                <span class="icon-large">🎯</span>
                                <span>SPPD</span>
                            </button>
                        </div>

                        <!-- Date Range Filter -->
                        <div class="filter-section">
                            <div class="filter-label">FILTER BY DATE</div>
                            <div class="date-inputs">
                                <input type="text" placeholder="mm/dd/yyyy" class="date-input from-date">
                                <span class="separator">—</span>
                                <input type="text" placeholder="mm/dd/yyyy" class="date-input to-date">
                            </div>
                            <select class="department-select">
                                <option>All Departments</option>
                                <option>Student Affairs</option>
                                <option>Academic</option>
                                <option>Administrative</option>
                            </select>
                        </div>

                        <button class="btn-generate-report">Generate Comprehensive Export</button>
                    </div>

                    <!-- Generated Reports Table -->
                    <div class="generated-reports-section">
                        <div class="section-header">
                            <h2>Generated Reports</h2>
                            <div class="view-options">
                                <button class="view-btn active">☰</button>
                                <button class="view-btn">⊞</button>
                            </div>
                        </div>

                        <table class="reports-table">
                            <thead>
                                <tr>
                                    <th>REPORT DETAILS</th>
                                    <th>TYPE</th>
                                    <th>DATE GENERATED</th>
                                    <th>ACTIONS</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="report-row">
                                    <td>
                                        <div class="report-item">
                                            <div class="report-icon surat-masuk">📥</div>
                                            <div>
                                                <h4>Rekapitulasi Surat Masuk 4 of 2023</h4>
                                                <small>Filtered by: 48 Departments | 1,521 Documents</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td><span class="badge badge-green">SURAT MASUK</span></td>
                                    <td>Dec 28, 2023 | 14:25</td>
                                    <td>
                                        <div class="action-buttons">
                                            <button class="action-btn pdf-btn">📄 PDF</button>
                                            <button class="action-btn email-btn">📧 Email</button>
                                        </div>
                                    </td>
                                </tr>
                                <tr class="report-row">
                                    <td>
                                        <div class="report-item">
                                            <div class="report-icon surat-keluar">📤</div>
                                            <div>
                                                <h4>Laporan Perjalanan Dinas Tahunan</h4>
                                                <small>Filtered by: Administrative S | 150 Documents</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td><span class="badge badge-danger">SPPD</span></td>
                                    <td>Jan 03, 2024 | 09:15</td>
                                    <td>
                                        <div class="action-buttons">
                                            <button class="action-btn pdf-btn">📄 PDF</button>
                                            <button class="action-btn email-btn">📧 Email</button>
                                        </div>
                                    </td>
                                </tr>
                                <tr class="report-row">
                                    <td>
                                        <div class="report-item">
                                            <div class="report-icon general">📊</div>
                                            <div>
                                                <h4>Arsip Surat Kebur semester 1</h4>
                                                <small>Filtered by: 48 Departments | 320 Documents</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td><span class="badge badge-yellow">SURAT KELUAR</span></td>
                                    <td>Jan 12, 2024 | 8:45</td>
                                    <td>
                                        <div class="action-buttons">
                                            <button class="action-btn pdf-btn">📄 PDF</button>
                                            <button class="action-btn email-btn">📧 Email</button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        <!-- Pagination -->
                        <div class="pagination-section">
                            <span class="pagination-info">Showing 1-3 of 48 reports</span>
                            <div class="pagination">
                                <button class="pagination-btn">1</button>
                                <button class="pagination-btn">2</button>
                                <button class="pagination-btn">3</button>
                                <button class="pagination-btn">→</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right: Secure Archive Section -->
                <aside class="secure-archive-panel">
                    <div class="archive-card">
                        <div class="archive-header">
                            <span class="archive-icon">🔒</span>
                            <h3>Secure Archive</h3>
                        </div>
                        <p class="archive-description">Digitally secure historical records from the encrypted institutional vault.</p>

                        <div class="archive-options">
                            <div class="option-item">
                                <input type="checkbox" id="reference-search" class="checkbox">
                                <label for="reference-search">Reference ID or Search...</label>
                            </div>
                            <div class="option-group">
                                <button class="btn-secondary">🔑️ Field Assistance</button>
                                <button class="btn-secondary">🔐 Whitelist ID</button>
                            </div>
                        </div>

                        <div class="archive-info">
                            <div class="info-item">
                                <span class="info-label">🔐 Protected by SSL 256</span>
                                <span class="info-value">Military-Grade Encryption</span>
                            </div>
                        </div>
                    </div>

                    <!-- Additional Info -->
                    <div class="info-card">
                        <p>All archived documents are protected with military-grade encryption and comply with institutional data protection standards.</p>
                    </div>
                </aside>
            </div>
        </main>
    </div>

    <script src="{{ asset('js/sppd.js') }}"></script>
</body>
</html>
