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
        $maxDistribution = max(array_merge(array_values($statusDistribution ?: ['Draft' => 1]), [1]));
    @endphp

    <div class="main-container">
        @include('layouts.sidebar')

        <div class="content-wrapper">
            <div class="top-bar">
                <div>
                    <p class="eyebrow">Ringkasan Persuratan</p>
                    <h1 class="top-title">Dashboard Monitoring Surat</h1>
                </div>
                <div class="user-chip">
                    <span class="user-avatar">{{ strtoupper(substr($userName, 0, 1)) }}</span>
                    <div>
                        <strong>{{ $userName }}</strong>
                        <small>Pengelola sistem persuratan</small>
                    </div>
                </div>
            </div>

            <main class="content">
                <section class="hero-panel">
                    <div class="hero-copy">
                        <p class="eyebrow">Ikhtisar Hari Ini</p>
                        <h2>Semua status surat dan disposisi terpantau dalam satu halaman.</h2>
                        <p>
                            Saat ini ada <strong>{{ $stats['pending'] }}</strong> dokumen menunggu persetujuan,
                            <strong>{{ $stats['revision'] }}</strong> dokumen perlu revisi, dan
                            <strong>{{ $stats['reviewed'] }}</strong> dokumen sudah memiliki catatan atau perubahan status.
                        </p>
                    </div>
                    <div class="hero-actions">
                        <a href="{{ route('surat-masuk') }}" class="hero-link primary-link">Buka Surat Masuk</a>
                        <a href="{{ route('surat-keluar') }}" class="hero-link">Buka Surat Keluar</a>
                        <a href="{{ route('sppd') }}" class="hero-link">Buka SPPD</a>
                        <a href="{{ route('disposisi') }}" class="hero-link">Buka Disposisi</a>
                    </div>
                </section>

                <section class="stats-grid">
                    <article class="stat-card">
                        <span class="stat-kicker">Arsip</span>
                        <strong class="stat-value">{{ $stats['surat_masuk'] }}</strong>
                        <span class="stat-title">Surat Masuk</span>
                    </article>
                    <article class="stat-card">
                        <span class="stat-kicker">Arsip</span>
                        <strong class="stat-value">{{ $stats['surat_keluar'] }}</strong>
                        <span class="stat-title">Surat Keluar</span>
                    </article>
                    <article class="stat-card">
                        <span class="stat-kicker">Perjalanan Dinas</span>
                        <strong class="stat-value">{{ $stats['sppd'] }}</strong>
                        <span class="stat-title">Total SPPD</span>
                    </article>
                    <article class="stat-card highlight">
                        <span class="stat-kicker">Status Awal</span>
                        <strong class="stat-value">{{ $stats['draft'] }}</strong>
                        <span class="stat-title">Masih Draft</span>
                    </article>
                    <article class="stat-card">
                        <span class="stat-kicker">Antrian</span>
                        <strong class="stat-value">{{ $stats['pending'] }}</strong>
                        <span class="stat-title">Menunggu Persetujuan</span>
                    </article>
                    <article class="stat-card">
                        <span class="stat-kicker">Perlu Tindak Lanjut</span>
                        <strong class="stat-value">{{ $stats['revision'] }}</strong>
                        <span class="stat-title">Perlu Revisi</span>
                    </article>
                    <article class="stat-card approved">
                        <span class="stat-kicker">Surat</span>
                        <strong class="stat-value">{{ $stats['approved_letters'] }}</strong>
                        <span class="stat-title">Surat Disetujui</span>
                    </article>
                    <article class="stat-card rejected">
                        <span class="stat-kicker">Surat</span>
                        <strong class="stat-value">{{ $stats['rejected_letters'] }}</strong>
                        <span class="stat-title">Surat Ditolak</span>
                    </article>
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
                                    <div class="activity-mark">{{ $activity['icon'] }}</div>
                                    <div class="activity-copy">
                                        <strong>{{ $activity['title'] }}</strong>
                                        <p>{{ \Illuminate\Support\Str::limit($activity['description'], 90) }}</p>
                                        <div class="activity-meta">
                                            <span class="activity-type">{{ $activity['type'] }}</span>
                                            <span class="activity-badge">{{ $activity['badge'] }}</span>
                                            <small>{{ $activity['time_label'] }}</small>
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
