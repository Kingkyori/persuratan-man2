<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Surat Keluar - MAN 2 Surakarta</title>
    <link rel="stylesheet" href="{{ asset('css/sidebar.css') }}">
    <link rel="stylesheet" href="{{ asset('css/surat-keluar.css') }}">
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
                    <input type="text" class="search-input" placeholder="Search correspondence...">
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
                    <h1>Outgoing Mail Hub</h1>
                    <p>Manage institutional correspondence, drafts, and approval workflows with the centralized mailing system.</p>
                </div>
                <button class="btn-primary create-draft-btn">
                    <span>✚</span>
                    Create New Draft
                </button>
            </div>

            <!-- Tabs -->
            <div class="tabs-container">
                <div class="tabs">
                    <button class="tab-btn active" data-tab="all">All Correspondence</button>
                    <button class="tab-btn" data-tab="drafts">My Drafts</button>
                    <button class="tab-btn" data-tab="pending">Pending Approval</button>
                </div>
                <div class="tab-actions">
                    <button class="btn-secondary edit-btn">✏️ Edit</button>
                    <button class="btn-secondary export-btn">📤 Export</button>
                </div>
            </div>

            <!-- Main Layout -->
            <div class="layout-container">
                <!-- Left: Creation Workspace -->
                <div class="creation-workspace">
                    <div class="workspace-header">
                        <span class="workspace-icon">✏️</span>
                        <h2>Creation Workspace</h2>
                    </div>

                    <form class="draft-form">
                        <div class="form-group">
                            <label>RECIPIENT / DESTINATION</label>
                            <input type="text" placeholder="e.g. Kantor Wilayah Kemendikbud" class="form-control">
                        </div>

                        <div class="form-group">
                            <label>CLASSIFICATION</label>
                            <select class="form-control">
                                <option>e.g. PD-10.8 Relocation</option>
                                <option>PD-10.8 Relocation</option>
                                <option>PD-20.1 Administrative</option>
                                <option>PD-30.5 Educational</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>DATE</label>
                            <input type="text" placeholder="mm/dd/yyyy" class="form-control date-input">
                        </div>

                        <div class="form-group">
                            <label>LETTER CONTENT / DRAFT</label>
                            <textarea placeholder="Write letter brief or body here..." class="form-control textarea-control" rows="8"></textarea>
                        </div>

                        <div class="form-actions">
                            <button type="button" class="btn-draft">Save as Draft</button>
                            <button type="submit" class="btn-submit">Submit for Approval</button>
                        </div>
                    </form>
                </div>

                <!-- Right: Correspondence List -->
                <div class="correspondence-list">
                    <div class="list-header">
                        <h3>Recent Correspondences</h3>
                    </div>

                    <div class="correspondence-item">
                        <div class="item-icon">📧</div>
                        <div class="item-content">
                            <h4>Undangan Rapat Koordinasi Kurikulum</h4>
                            <p>To No. 11 | Dated: 06 & 10/12023</p>
                        </div>
                        <div class="item-meta">
                            <span class="date">May 8</span>
                            <span class="badge draft-badge">DRAFT</span>
                        </div>
                    </div>

                    <div class="correspondence-item">
                        <div class="item-icon">📮</div>
                        <div class="item-content">
                            <h4>Pengajuan Dana Untuk Renovasi</h4>
                            <p>To No. 12 | Dated: 08 & 10/2024</p>
                        </div>
                        <div class="item-meta">
                            <span class="date">May 5</span>
                            <span class="badge pending-badge">WAITING FOR APPROVAL</span>
                        </div>
                    </div>

                    <div class="correspondence-item">
                        <div class="item-icon">📄</div>
                        <div class="item-content">
                            <h4>Surat Keterangan Akif Mengajar</h4>
                            <p>To No. 18 | Dated: 07 & 11/2024</p>
                        </div>
                        <div class="item-meta">
                            <span class="date">May 15</span>
                            <span class="badge draft-badge">DRAFT</span>
                        </div>
                    </div>

                    <div class="correspondence-item">
                        <div class="item-icon">✓</div>
                        <div class="item-content">
                            <h4>Laporan Tahunan Kesehatan</h4>
                            <p>To No. 21 | Dated: 09 & 11/2024</p>
                        </div>
                        <div class="item-meta">
                            <span class="date">May 20</span>
                            <span class="badge approved-badge">✓ APPROVED</span>
                        </div>
                    </div>

                    <!-- Pagination -->
                    <div class="list-pagination">
                        <span>Showing 1-4 of 234 records</span>
                        <div class="pagination">
                            <button class="pagination-btn">←</button>
                            <button class="pagination-btn active">1</button>
                            <button class="pagination-btn">2</button>
                            <button class="pagination-btn">3</button>
                            <button class="pagination-btn">→</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Monthly Stats -->
            <div class="stats-section">
                <div class="stat-card-large">
                    <div class="stat-icon">📊</div>
                    <div class="stat-content">
                        <h3>MONTHLY MAIL VOLUME</h3>
                        <div class="stat-number">128</div>
                        <p>Letters sent</p>
                        <span class="stat-trend">↑ 11% from last month</span>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="{{ asset('js/surat-keluar.js') }}"></script>
</body>
</html>
