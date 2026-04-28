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
    @php
        $userName = session('user.name') ?? 'Administrator';
        $userRole = session('user.role', 'admin');
        $isAdmin = $userRole === 'admin';
        $isHeadmaster = $userRole === 'kepala_sekolah';
        $totalDocuments = ($stats['surat_masuk'] ?? 0) + ($stats['surat_keluar'] ?? 0) + ($stats['sppd'] ?? 0);
        $maxDistribution = max(array_merge(array_values($statusDistribution ?: ['Draft' => 1]), [1]));
        $statusOverview = [
            ['label' => 'Draft', 'value' => $stats['draft'] ?? 0],
            ['label' => 'Menunggu', 'value' => $stats['pending'] ?? 0],
            ['label' => 'Revisi', 'value' => $stats['revision'] ?? 0],
            ['label' => 'Disetujui', 'value' => $stats['approved_letters'] ?? 0],
            ['label' => 'Ditolak', 'value' => $stats['rejected_letters'] ?? 0],
        ];
    @endphp

    <div class="main-container">
        @include('layouts.sidebar')

        <div class="content-wrapper">
            <div class="top-bar">
                <div class="page-intro">
                    <p class="eyebrow">Ringkasan Persuratan</p>
                    <h1 class="top-title">Dashboard Monitoring Surat</h1>
                </div>
                <div class="user-chip">
                    <span class="user-avatar">{{ strtoupper(substr($userName, 0, 1)) }}</span>
                    <div>
                        <strong>{{ $userName }}</strong>
                        <small>{{ $isHeadmaster ? 'Persetujuan dan disposisi surat' : 'Pengelola sistem persuratan' }}</small>
                    </div>
                </div>
            </div>

            <main class="content">
                <section class="hero-panel">
                    <div class="hero-copy">
                        <p class="eyebrow">Ikhtisar Hari Ini</p>
                        <h2>Pantau seluruh surat dan disposisi tanpa tampilan yang berlebihan.</h2>
                        <p>
                            Total ada <strong>{{ $totalDocuments }}</strong> dokumen aktif. Saat ini
                            <strong>{{ $stats['pending'] }}</strong> menunggu persetujuan,
                            <strong>{{ $stats['revision'] }}</strong> perlu revisi, dan
                            <strong>{{ $stats['reviewed'] }}</strong> sudah memiliki catatan atau perubahan status.
                        </p>
                    </div>

                    <div class="hero-side">
                        <div class="hero-highlights">
                            <article class="highlight-card">
                                <span>Menunggu Persetujuan</span>
                                <strong>{{ $stats['pending'] }}</strong>
                            </article>
                            <article class="highlight-card">
                                <span>Perlu Revisi</span>
                                <strong>{{ $stats['revision'] }}</strong>
                            </article>
                            <article class="highlight-card">
                                <span>Sudah Ditinjau</span>
                                <strong>{{ $stats['reviewed'] }}</strong>
                            </article>
                        </div>

                        <div class="quick-actions">
                            <a href="{{ route('manajemen-surat') }}" class="quick-link quick-link-primary">Manajemen Surat</a>
                            @if($isAdmin)
                                <a href="{{ route('surat-masuk') }}" class="quick-link">Surat Masuk</a>
                                <a href="{{ route('surat-keluar') }}" class="quick-link">Surat Keluar</a>
                                <a href="{{ route('sppd') }}" class="quick-link">SPPD</a>
                            @endif
                            @if($isHeadmaster)
                                <a href="{{ route('disposisi') }}" class="quick-link">Disposisi</a>
                            @endif
                        </div>
                    </div>
                </section>

                <section class="stats-grid">
                    <article class="stat-card">
                        <span class="stat-label">Surat Masuk</span>
                        <strong class="stat-value">{{ $stats['surat_masuk'] }}</strong>
                        <span class="stat-caption">Arsip surat yang diterima</span>
                    </article>
                    <article class="stat-card">
                        <span class="stat-label">Surat Keluar</span>
                        <strong class="stat-value">{{ $stats['surat_keluar'] }}</strong>
                        <span class="stat-caption">Arsip surat yang dikirim</span>
                    </article>
                    <article class="stat-card">
                        <span class="stat-label">SPPD</span>
                        <strong class="stat-value">{{ $stats['sppd'] }}</strong>
                        <span class="stat-caption">Perjalanan dinas aktif</span>
                    </article>
                    <article class="stat-card stat-card-accent">
                        <span class="stat-label">Surat Disetujui</span>
                        <strong class="stat-value">{{ $stats['approved_letters'] }}</strong>
                        <span class="stat-caption">Dokumen yang sudah lolos persetujuan</span>
                    </article>
                </section>

                <section class="status-strip">
                    @foreach($statusOverview as $item)
                        <article class="status-tile">
                            <span>{{ $item['label'] }}</span>
                            <strong>{{ $item['value'] }}</strong>
                        </article>
                    @endforeach
                </section>

                <section class="dashboard-grid">
                    <article class="panel">
                        <div class="panel-header">
                            <div>
                                <p class="eyebrow">Distribusi Status</p>
                                <h3>Status Dokumen Saat Ini</h3>
                            </div>
                        </div>

                        <div class="distribution-list">
                            @foreach($statusDistribution as $label => $value)
                                <div class="distribution-row">
                                    <div class="distribution-meta">
                                        <span>{{ $label }}</span>
                                        <strong>{{ $value }}</strong>
                                    </div>
                                    <div class="distribution-track">
                                        <span class="distribution-bar" style="width: {{ $maxDistribution > 0 ? ($value / $maxDistribution) * 100 : 0 }}%;"></span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </article>

                    <article class="panel">
                        <div class="panel-header">
                            <div>
                                <p class="eyebrow">Aktivitas Terbaru</p>
                                <h3>Pembaruan Arsip dan Disposisi</h3>
                            </div>
                        </div>

                        <div class="activity-list">
                            @forelse($recentActivities as $activity)
                                <div class="activity-item">
                                    <div class="activity-copy">
                                        <div class="activity-head">
                                            <strong>{{ $activity['title'] }}</strong>
                                            <small>{{ $activity['time_label'] }}</small>
                                        </div>
                                        <p>{{ \Illuminate\Support\Str::limit($activity['description'], 90) }}</p>
                                        <div class="activity-meta">
                                            <span class="activity-type">{{ $activity['type'] }}</span>
                                            <span class="activity-badge">{{ $activity['badge'] }}</span>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="empty-box">Belum ada aktivitas yang tercatat.</div>
                            @endforelse
                        </div>
                    </article>
                </section>
            </main>
        </div>
    </div>
</body>
</html>
