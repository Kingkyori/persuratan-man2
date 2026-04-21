<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Surat Masuk - MAN 2 Surakarta</title>
    <link rel="stylesheet" href="{{ asset('css/sidebar.css') }}">
    <link rel="stylesheet" href="{{ asset('css/surat-masuk.css') }}">
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
                    <input type="text" class="search-input" placeholder="Search entries...">
                </div>
                <div class="top-bar-actions">
                    <button class="icon-btn notification-btn">
                        <span>🔔</span>
                    </button>
                    <button class="icon-btn settings-btn">
                        <span>⚙️</span>
                    </button>
                    <div class="user-profile">
                        <span class="profile-avatar">👤</span>
                    </div>
                </div>
            </div>

            <!-- Page Header -->
            <div class="page-header">
                <div class="header-content">
                    <h1>Incoming Correspondence</h1>
                    <p>Centralized management system for all institutional communications. Efficiently track, archive, and dispose official letters received by the secretariat.</p>
                </div>
                <button class="btn-primary register-btn">
                    <span>✚</span>
                    Register New Surat Masuk
                </button>
            </div>

            <!-- Stats Cards -->
            <div class="stats-container">
                <div class="stat-card">
                    <div class="stat-label">TOTAL RECEIVED</div>
                    <div class="stat-value">1,284</div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">DISPOSED</div>
                    <div class="stat-value">1,120</div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">PENDING</div>
                    <div class="stat-value">164</div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">MONTH-OVER-MONTH</div>
                    <div class="stat-value">+12%</div>
                </div>
            </div>

            <!-- Archive Ledger -->
            <div class="archive-section">
                <div class="section-header">
                    <h2>Archive Ledger</h2>
                    <div class="section-actions">
                        <button class="btn-secondary filter-btn">🔽 Filter</button>
                        <button class="btn-secondary export-btn">📤 Export</button>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="archive-table">
                        <thead>
                            <tr>
                                <th>Date Received</th>
                                <th>From / Origin</th>
                                <th>Subject</th>
                                <th>Reference No.</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>
                                    <div class="date-cell">Oct 24, 2023</div>
                                    <small>08:15 AM</small>
                                </td>
                                <td>
                                    <div>Kemenag Kota Surakarta</div>
                                    <small>Educational Ministry</small>
                                </td>
                                <td>Undangan Rapat Koordinasi Evaluasi Kurikulum...</td>
                                <td>SIK/KES-23.12.31/IV-06/18/2023</td>
                                <td><span class="badge badge-success">✓ Done</span></td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="date-cell">Oct 25, 2023</div>
                                    <small>02:30 PM</small>
                                </td>
                                <td>
                                    <div>Yayasan Pendidikan Modern</div>
                                    <small>Educational</small>
                                </td>
                                <td>Permohonan Kerjasama Program Beasiswa S...</td>
                                <td>SKI/YPM-261/2023</td>
                                <td><span class="badge badge-danger">⚠ Pending</span></td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="date-cell">Oct 25, 2023</div>
                                    <small>09:45 AM</small>
                                </td>
                                <td>
                                    <div>Dinas Pendidikan Provinsi Jawa Tengah</div>
                                    <small>Provincial Education</small>
                                </td>
                                <td>Pemberhentian Sosialisasi Data BOS Tahun II</td>
                                <td>401-1/283/2023</td>
                                <td><span class="badge badge-danger">⚠ Pending</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="pagination">
                    <button class="pagination-btn">←</button>
                    <button class="pagination-btn active">1</button>
                    <button class="pagination-btn">2</button>
                    <button class="pagination-btn">3</button>
                    <button class="pagination-btn">→</button>
                </div>
            </div>

            <!-- Bottom Section -->
            <div class="bottom-section">
                <!-- Register New Entry -->
                <div class="register-section">
                    <div class="section-header">
                        <h2>✏️ Register New Entry</h2>
                    </div>
                    <p class="section-description">Add a new incoming letter to the system</p>

                    <form class="register-form">
                        <div class="form-row">
                            <div class="form-group">
                                <label>ORIGIN / SENDER</label>
                                <input type="text" placeholder="e.g. Kemenag KS" class="form-control">
                            </div>
                            <div class="form-group">
                                <label>RECEPTION DATE</label>
                                <input type="text" placeholder="mm / dd / yyyy" class="form-control">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label>LETTER NUMBER</label>
                                <input type="text" placeholder="Official Reference Number" class="form-control">
                            </div>
                            <div class="form-group">
                                <label>SUBJECT MATTER</label>
                                <input type="text" placeholder="Short description of content" class="form-control">
                            </div>
                        </div>

                        <div class="form-group">
                            <label>LETTER SCAN (PDF/IMG)</label>
                            <div class="file-upload">
                                <span>📎</span>
                                <p>Drop file to upload</p>
                                <small>Maximum size 5MB only</small>
                            </div>
                        </div>

                        <div class="form-actions">
                            <button type="button" class="btn-secondary">Discard</button>
                            <button type="submit" class="btn-success">Submit Registration</button>
                        </div>
                    </form>
                </div>

                <!-- Right Sidebar -->
                <aside class="right-sidebar">
                    <!-- Administrative Guidelines -->
                    <div class="info-card guidelines-card">
                        <div class="info-header">
                            <span class="info-icon">ℹ️</span>
                            <h3>Administrative Guidelines</h3>
                        </div>
                        <ul class="info-list">
                            <li>
                                <span class="check-mark">✓</span>
                                <span>Ensure all incoming mail is registered within 1 business day of reception to maintain institutional efficiency.</span>
                            </li>
                            <li>
                                <span class="check-mark">✓</span>
                                <span>Attach scanned documents and direct to the relevant office.</span>
                            </li>
                        </ul>
                    </div>

                    <!-- Recent Activity -->
                    <div class="info-card activity-card">
                        <h3>Recent Activity</h3>
                        <div class="activity-item">
                            <div class="activity-title">Mail Received</div>
                            <small>15 mins ago</small>
                        </div>
                        <div class="activity-item">
                            <div class="activity-title">New Mail Logged</div>
                            <small>2 hours ago</small>
                        </div>
                    </div>
                </aside>
            </div>
        </main>
    </div>

    <script src="{{ asset('js/surat-masuk.js') }}"></script>
</body>
</html>
