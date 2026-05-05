<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Share Surat Keluar - MAN 2 Surakarta</title>
    <link rel="stylesheet" href="{{ asset('css/surat-masuk-share.css') }}">
</head>
<body>
    <main class="share-shell">
        <section class="share-hero">
            <span class="eyebrow">PORTAL SURAT KELUAR</span>
            <h1>Detail Surat Keluar</h1>
            <p>Halaman ini dipakai penerima tautan untuk melihat rincian surat keluar, membuka preview arsip, dan mengunduh file surat.</p>
        </section>

        <section class="share-card">
            <div class="share-card-head">
                <div>
                    <h2>{{ $surat->destination }}</h2>
                    <p>{{ $documentTypeLabel }} - {{ $surat->letter_number }}</p>
                </div>
                <div class="status-group">
                    <span class="status-caption">Tanggal Surat</span>
                    <span class="badge badge-approved">{{ optional($surat->letter_date)->format('d M Y') }}</span>
                </div>
            </div>

            <div class="share-layout">
                <div class="preview-panel">
                    @if($previewUrl)
                        <div class="preview-head">
                            <span>Preview Arsip</span>
                            <small>{{ $surat->file_name ?: 'File surat' }}</small>
                        </div>
                        <iframe src="{{ $previewUrl }}" class="preview-frame" title="Preview surat keluar"></iframe>
                    @else
                        <div class="preview-placeholder">
                            <strong>Preview belum tersedia</strong>
                            <p>File belum memiliki tautan preview. Silakan gunakan tombol buka atau download di sisi kanan.</p>
                        </div>
                    @endif
                </div>

                <div class="detail-panel">
                    <div class="detail-grid">
                        <div class="detail-item">
                            <span>Jenis Arsip</span>
                            <strong>{{ $documentTypeLabel }}</strong>
                        </div>
                        <div class="detail-item">
                            <span>Tanggal Surat</span>
                            <strong>{{ optional($surat->letter_date)->format('d M Y') }}</strong>
                        </div>
                        <div class="detail-item">
                            <span>Tujuan</span>
                            <strong>{{ $surat->destination }}</strong>
                        </div>
                        <div class="detail-item">
                            <span>Nomor Surat</span>
                            <strong>{{ $surat->letter_number }}</strong>
                        </div>
                        <div class="detail-item detail-item-wide">
                            <span>Perihal</span>
                            <strong>{{ $surat->subject }}</strong>
                        </div>
                        <div class="detail-item detail-item-wide">
                            <span>Catatan</span>
                            <strong>{{ $surat->notes ?: '-' }}</strong>
                        </div>
                    </div>

                    <div class="receipt-form">
                        <div class="form-actions">
                            @if($openUrl)
                                <a href="{{ $openUrl }}" target="_blank" rel="noopener" class="btn-secondary">Buka File Asli</a>
                            @endif
                            @if($downloadUrl)
                                <a href="{{ $downloadUrl }}" target="_blank" rel="noopener" class="btn-secondary">Download PDF/File</a>
                            @endif
                            @if($docxUrl)
                                <a href="{{ $docxUrl }}" target="_blank" rel="noopener" class="btn-success">Download DOCX</a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>
</body>
</html>
