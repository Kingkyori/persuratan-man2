<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Surat Keluar - MAN 2 Surakarta</title>
    <link rel="stylesheet" href="{{ asset('css/sidebar.css') }}">
    <link rel="stylesheet" href="{{ asset('css/modal.css') }}">
    <link rel="stylesheet" href="{{ asset('css/surat-keluar.css') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body>
    @php
        $statusConfig = \App\Support\DispositionStatus::options();
        $suratKeluar = collect($surat_keluar ?? []);
        $outgoingStats = [
            'total' => $suratKeluar->count(),
            'draft' => $suratKeluar->filter(fn ($item) => \App\Support\DispositionStatus::normalize($item->status) === 'draft')->count(),
            'pending' => $suratKeluar->filter(fn ($item) => \App\Support\DispositionStatus::normalize($item->status) === 'pending_approval')->count(),
            'approved' => $suratKeluar->filter(fn ($item) => \App\Support\DispositionStatus::normalize($item->status) === 'approved')->count(),
        ];
    @endphp

    <div class="main-layout">
        @include('layouts.sidebar')

        <main class="main-content">
            <div class="top-bar">
                <div class="search-container">
                    <input
                        type="text"
                        class="search-input"
                        id="searchInput"
                        placeholder="Cari tujuan, perihal, atau nomor surat..."
                    >
                </div>
                <div class="top-bar-note">
                    Arsip surat keluar tersimpan di database, dan catatan disposisi bisa dilihat dari tombol `!` di samping status.
                </div>
            </div>

            <div class="page-header">
                <div class="header-content">
                    <h1>Surat Keluar</h1>
                    <p>
                        Catat surat keluar resmi, simpan arsip ke database, dan pantau hasil review atau disposisi
                        tanpa perlu membuka halaman lain.
                    </p>
                </div>
                <button class="btn-primary" id="btnOpenModal" type="button">+ Tambah Surat Keluar</button>
            </div>

            <section class="stats-strip">
                <article class="mini-stat">
                    <span class="mini-label">Total Arsip</span>
                    <strong>{{ $outgoingStats['total'] }}</strong>
                </article>
                <article class="mini-stat">
                    <span class="mini-label">Draft</span>
                    <strong>{{ $outgoingStats['draft'] }}</strong>
                </article>
                <article class="mini-stat">
                    <span class="mini-label">Menunggu</span>
                    <strong>{{ $outgoingStats['pending'] }}</strong>
                </article>
                <article class="mini-stat">
                    <span class="mini-label">Disetujui</span>
                    <strong>{{ $outgoingStats['approved'] }}</strong>
                </article>
            </section>

            <section class="archive-section">
                <div class="section-header">
                    <div>
                        <h2>Daftar Arsip Surat Keluar</h2>
                        <p class="section-description">
                            Data yang disimpan meliputi tanggal surat, tujuan, perihal, nomor surat, file arsip, status, dan catatan hasil disposisi.
                        </p>
                    </div>
                    <div class="workflow-note">
                        <strong>Alur kerja:</strong> Draft -> Menunggu Persetujuan -> Revisi / Ditolak / Disetujui
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="archive-table">
                        <thead>
                            <tr>
                                <th>Tanggal Surat</th>
                                <th>Tujuan</th>
                                <th>Perihal</th>
                                <th>Nomor Surat</th>
                                <th>File</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="tableBody">
                            @forelse($suratKeluar as $surat)
                                @php
                                    $statusKey = \App\Support\DispositionStatus::normalize($surat->status);
                                    $statusMeta = $statusConfig[$statusKey] ?? $statusConfig['draft'];
                                    $hasReviewSignal = filled($surat->notes) || $statusKey !== 'draft';
                                @endphp
                                <tr
                                    data-id="{{ $surat->id }}"
                                    data-search="{{ strtolower($surat->destination . ' ' . $surat->subject . ' ' . $surat->letter_number) }}"
                                    data-tanggal="{{ optional($surat->letter_date)->format('d M Y') }}"
                                    data-tujuan="{{ $surat->destination }}"
                                    data-perihal="{{ $surat->subject }}"
                                    data-nomor="{{ $surat->letter_number }}"
                                    data-file="{{ $surat->file_name }}"
                                    data-status="{{ $statusMeta['label'] }}"
                                    data-status-key="{{ $statusKey }}"
                                    data-catatan="{{ $surat->notes ?: '-' }}"
                                    data-link="{{ route('archive.open', ['type' => 'surat-keluar', 'id' => $surat->id]) }}"
                                >
                                    <td>{{ optional($surat->letter_date)->format('d M Y') }}</td>
                                    <td>{{ $surat->destination }}</td>
                                    <td>{{ $surat->subject }}</td>
                                    <td>{{ $surat->letter_number }}</td>
                                    <td>
                                        <span class="file-pill">{{ $surat->file_name }}</span>
                                    </td>
                                    <td>
                                        <div class="status-cell">
                                            <span class="badge {{ $statusMeta['class'] }}">
                                                {{ $statusMeta['label'] }}
                                            </span>
                                            <button
                                                class="review-indicator {{ filled($surat->notes) ? 'has-note' : 'has-status' }}"
                                                type="button"
                                                onclick="openNoteModal('{{ $surat->id }}')"
                                                title="Lihat catatan disposisi"
                                                {{ $hasReviewSignal ? '' : 'hidden' }}
                                            >!</button>
                                        </div>
                                    </td>
                                    <td class="action-cell">
                                        <a href="{{ route('archive.open', ['type' => 'surat-keluar', 'id' => $surat->id]) }}" target="_blank" class="btn-small" rel="noopener">File</a>
                                        <button class="btn-small" type="button" onclick="viewDetail('{{ $surat->id }}')">Detail</button>
                                        <button class="btn-small btn-danger" type="button" onclick="deleteSuratKeluar('{{ $surat->id }}')">Hapus</button>
                                    </td>
                                </tr>
                            @empty
                                <tr id="emptyStateRow">
                                    <td colspan="7" class="empty-state">Belum ada data surat keluar.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="table-footer">
                    <span id="resultCount">{{ $suratKeluar->count() }} surat tampil</span>
                    <span>Setiap surat yang berhasil disimpan akan tercatat ke database dan memiliki tautan Google Drive.</span>
                </div>
            </section>
        </main>
    </div>

    <div class="modal" id="modalRegister">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Input Surat Keluar</h2>
                <button class="modal-close" id="btnCloseModal" type="button">&times;</button>
            </div>

            <form id="formSuratKeluar" class="register-form" enctype="multipart/form-data">
                <div class="form-banner">
                    Nomor surat masih diisi manual. Setelah tersimpan, data masuk ke database dan file dikirim ke Google Drive.
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="destination">Tujuan Surat <span class="required">*</span></label>
                        <input type="text" id="destination" name="destination" class="form-control" required placeholder="Contoh: Kantor Kemenag Kota Surakarta">
                    </div>
                    <div class="form-group">
                        <label for="letter_date">Tanggal Surat <span class="required">*</span></label>
                        <input type="date" id="letter_date" name="letter_date" class="form-control" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="letter_number">Nomor Surat <span class="required">*</span></label>
                        <input type="text" id="letter_number" name="letter_number" class="form-control" required placeholder="Isi manual untuk sementara">
                    </div>
                    <div class="form-group">
                        <label for="status">Status <span class="required">*</span></label>
                        <select id="status" name="status" class="form-control" required>
                            @foreach($statusConfig as $statusKey => $statusMeta)
                                <option value="{{ $statusKey }}">{{ $statusMeta['label'] }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="subject">Perihal <span class="required">*</span></label>
                    <input type="text" id="subject" name="subject" class="form-control" required placeholder="Contoh: Undangan rapat koordinasi">
                </div>

                <div class="form-group">
                    <label for="notes">Catatan</label>
                    <textarea id="notes" name="notes" class="form-control" rows="3" placeholder="Tambahkan keterangan singkat jika diperlukan"></textarea>
                </div>

                <div class="form-group">
                    <label>File Surat <span class="required">*</span></label>
                    <div class="file-upload" id="fileUploadArea">
                        <span class="upload-symbol">[ Upload ]</span>
                        <p id="fileNameDisplay">Pilih file surat keluar</p>
                        <small>Format yang didukung: PDF, DOC, DOCX, JPG, PNG</small>
                        <input
                            type="file"
                            id="letterFile"
                            name="letter_file"
                            accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,image/jpeg,image/png"
                            hidden
                            required
                        >
                    </div>
                </div>

                <div class="form-actions">
                    <button type="button" class="btn-secondary" onclick="closeModal()">Batal</button>
                    <button type="submit" class="btn-success" id="btnSubmit">
                        <span id="submitText">Simpan Surat</span>
                        <span id="submitLoader" style="display: none;">Menyimpan...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal" id="modalDetail">
        <div class="modal-content detail-modal-content">
            <div class="modal-header">
                <h2>Detail Surat Keluar</h2>
                <button class="modal-close" type="button" onclick="closeDetailModal()">&times;</button>
            </div>
            <div class="modal-body detail-info" id="detailContent"></div>
        </div>
    </div>

    <div class="modal" id="modalNote">
        <div class="modal-content note-modal-content">
            <div class="modal-header">
                <h2>Catatan Disposisi</h2>
                <button class="modal-close" type="button" onclick="closeNoteModal()">&times;</button>
            </div>
            <div class="modal-body note-body">
                <div class="note-summary">
                    <strong id="noteStatusLabel">Status surat</strong>
                    <span id="noteStatusValue" class="note-status-pill">Draft</span>
                </div>
                <div class="note-panel">
                    <p id="noteText">Belum ada catatan disposisi.</p>
                </div>
            </div>
        </div>
    </div>

    <script src="{{ asset('js/request-progress.js') }}"></script>
    <script>
        const statusConfig = @json($statusConfig);
        const modal = document.getElementById('modalRegister');
        const detailModal = document.getElementById('modalDetail');
        const noteModal = document.getElementById('modalNote');
        const form = document.getElementById('formSuratKeluar');
        const fileInput = document.getElementById('letterFile');
        const fileUploadArea = document.getElementById('fileUploadArea');
        const fileNameDisplay = document.getElementById('fileNameDisplay');
        const tableBody = document.getElementById('tableBody');
        const searchInput = document.getElementById('searchInput');
        const resultCount = document.getElementById('resultCount');
        const submitButton = document.getElementById('btnSubmit');
        const submitText = document.getElementById('submitText');
        const submitLoader = document.getElementById('submitLoader');

        document.getElementById('btnOpenModal').addEventListener('click', () => {
            modal.style.display = 'flex';
        });

        document.getElementById('btnCloseModal').addEventListener('click', closeModal);

        fileUploadArea.addEventListener('click', () => fileInput.click());

        fileInput.addEventListener('change', () => {
            if (fileInput.files.length > 0) {
                fileNameDisplay.textContent = fileInput.files[0].name;
                fileUploadArea.classList.add('has-file');
            } else {
                resetFileUpload();
            }
        });

        form.addEventListener('submit', async (event) => {
            event.preventDefault();

            if (!fileInput.files.length) {
                alert('Silakan pilih file surat terlebih dahulu.');
                return;
            }

            const formData = new FormData(form);

            try {
                setSubmittingState(true);

                const { response, result } = await RequestProgress.requestJson({
                    url: '{{ route("surat-keluar.store") }}',
                    method: 'POST',
                    data: formData,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    title: 'Mengunggah surat keluar',
                    initialMessage: 'Menyiapkan file surat keluar...',
                    uploadMessage: 'Mengunggah file surat keluar...',
                    processingMessage: 'Menyimpan surat keluar ke database dan Google Drive...',
                    successMessage: 'Surat keluar berhasil diproses.'
                });

                if (!response.ok || !result.success) {
                    throw new Error(result.message || 'Gagal menyimpan surat keluar.');
                }

                prependRow(result.data);
                closeModal();
                updateVisibleCount();
                RequestProgress.showNotice(
                    result.message,
                    result.drive_synced === false ? 'warning' : 'success'
                );
            } catch (error) {
                alert(error.message || 'Terjadi kesalahan saat menyimpan surat keluar.');
            } finally {
                setSubmittingState(false);
            }
        });

        searchInput.addEventListener('input', () => {
            const query = searchInput.value.trim().toLowerCase();
            const rows = tableBody.querySelectorAll('tr[data-id]');

            rows.forEach((row) => {
                const matches = row.dataset.search.includes(query);
                row.style.display = matches ? '' : 'none';
            });

            updateVisibleCount();
        });

        function prependRow(data) {
            removeEmptyState();

            const status = statusConfig[data.status] || statusConfig.draft;
            const row = document.createElement('tr');

            row.dataset.id = data.id;
            row.dataset.search = `${data.tujuan} ${data.perihal} ${data.nomor}`.toLowerCase();
            row.dataset.tanggal = data.tanggal;
            row.dataset.tujuan = data.tujuan;
            row.dataset.perihal = data.perihal;
            row.dataset.nomor = data.nomor;
            row.dataset.file = data.file;
            row.dataset.status = status.label;
            row.dataset.statusKey = data.status || 'draft';
            row.dataset.catatan = data.catatan || '-';
            row.dataset.link = data.google_drive_link || '';

            const driveButton = `<a href="${escapeHtml(data.google_drive_link)}" target="_blank" class="btn-small" rel="noopener">File</a>`;

            row.innerHTML = `
                <td>${escapeHtml(data.tanggal)}</td>
                <td>${escapeHtml(data.tujuan)}</td>
                <td>${escapeHtml(data.perihal)}</td>
                <td>${escapeHtml(data.nomor)}</td>
                <td><span class="file-pill">${escapeHtml(data.file)}</span></td>
                <td>
                    <div class="status-cell">
                        <span class="badge ${status.class}">${status.label}</span>
                        ${renderReviewButton(data.id, data.status, data.catatan)}
                    </div>
                </td>
                <td class="action-cell">${driveButton}<button class="btn-small" type="button" onclick="viewDetail('${data.id}')">Detail</button><button class="btn-small btn-danger" type="button" onclick="deleteSuratKeluar('${data.id}')">Hapus</button></td>
            `;

            tableBody.prepend(row);
        }

        async function deleteSuratKeluar(id) {
            if (!confirm('Hapus surat keluar ini?')) {
                return;
            }

            try {
                const { result } = await RequestProgress.requestJson({
                    url: `/surat-keluar/${id}`,
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    title: 'Menghapus surat keluar',
                    initialMessage: 'Menyiapkan penghapusan surat keluar...',
                    processingMessage: 'Menghapus arsip surat keluar dari database...',
                    successMessage: 'Surat keluar berhasil dihapus.'
                });

                if (result.success) {
                    const row = tableBody.querySelector(`tr[data-id="${id}"]`);
                    if (row) {
                        row.remove();
                    }

                    updateVisibleCount();
                    ensureEmptyState();
                    RequestProgress.showNotice(result.message, 'success');
                } else {
                    alert(result.message || 'Gagal menghapus surat keluar.');
                }
            } catch (error) {
                alert('Gagal menghapus surat keluar.');
            }
        }

        function viewDetail(id) {
            const row = tableBody.querySelector(`tr[data-id="${id}"]`);

            if (!row) {
                return;
            }

            const driveLink = row.dataset.link
                ? `<p><strong>File Arsip:</strong> <a href="${escapeHtml(row.dataset.link)}" target="_blank" rel="noopener">Buka file</a></p>`
                : '';

            document.getElementById('detailContent').innerHTML = `
                <p><strong>Tanggal Surat:</strong> ${escapeHtml(row.dataset.tanggal)}</p>
                <p><strong>Tujuan:</strong> ${escapeHtml(row.dataset.tujuan)}</p>
                <p><strong>Perihal:</strong> ${escapeHtml(row.dataset.perihal)}</p>
                <p><strong>Nomor Surat:</strong> ${escapeHtml(row.dataset.nomor)}</p>
                <p><strong>File:</strong> ${escapeHtml(row.dataset.file)}</p>
                <p><strong>Status:</strong> ${escapeHtml(row.dataset.status)}</p>
                <p><strong>Catatan:</strong> ${escapeHtml(normalizeNote(row.dataset.catatan))}</p>
                ${driveLink}
            `;

            detailModal.style.display = 'flex';
        }

        function openNoteModal(id) {
            const row = tableBody.querySelector(`tr[data-id="${id}"]`);

            if (!row) {
                return;
            }

            const note = normalizeNote(row.dataset.catatan);
            document.getElementById('noteStatusLabel').textContent = 'Status terakhir surat';
            document.getElementById('noteStatusValue').textContent = row.dataset.status || 'Draft';
            document.getElementById('noteText').textContent = note === '-'
                ? 'Belum ada catatan tambahan. Surat ini sudah pernah ditinjau atau statusnya sudah diperbarui.'
                : note;

            noteModal.style.display = 'flex';
        }

        function closeModal() {
            modal.style.display = 'none';
            form.reset();
            resetFileUpload();
            setSubmittingState(false);
        }

        function closeDetailModal() {
            detailModal.style.display = 'none';
        }

        function closeNoteModal() {
            noteModal.style.display = 'none';
        }

        function resetFileUpload() {
            fileNameDisplay.textContent = 'Pilih file surat keluar';
            fileUploadArea.classList.remove('has-file');
        }

        function setSubmittingState(isSubmitting) {
            submitButton.disabled = isSubmitting;
            submitText.style.display = isSubmitting ? 'none' : 'inline';
            submitLoader.style.display = isSubmitting ? 'inline' : 'none';
        }

        function removeEmptyState() {
            const emptyStateRow = document.getElementById('emptyStateRow');
            if (emptyStateRow) {
                emptyStateRow.remove();
            }
        }

        function ensureEmptyState() {
            const hasRows = tableBody.querySelectorAll('tr[data-id]').length > 0;
            const emptyStateRow = document.getElementById('emptyStateRow');

            if (!hasRows && !emptyStateRow) {
                const row = document.createElement('tr');
                row.id = 'emptyStateRow';
                row.innerHTML = '<td colspan="7" class="empty-state">Belum ada data surat keluar.</td>';
                tableBody.appendChild(row);
            }
        }

        function updateVisibleCount() {
            const visibleRows = Array.from(tableBody.querySelectorAll('tr[data-id]'))
                .filter((row) => row.style.display !== 'none').length;

            resultCount.textContent = `${visibleRows} surat tampil`;
        }

        function normalizeNote(note) {
            const value = String(note ?? '').trim();
            return value && value !== 'null' ? value : '-';
        }

        function hasReviewSignal(statusKey, note) {
            return String(statusKey || 'draft') !== 'draft' || normalizeNote(note) !== '-';
        }

        function renderReviewButton(id, statusKey, note) {
            if (!hasReviewSignal(statusKey, note)) {
                return '';
            }

            const buttonClass = normalizeNote(note) !== '-' ? 'has-note' : 'has-status';
            return `<button class="review-indicator ${buttonClass}" type="button" onclick="openNoteModal('${id}')" title="Lihat catatan disposisi">!</button>`;
        }

        function escapeHtml(value) {
            return String(value ?? '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#39;');
        }

        window.addEventListener('click', (event) => {
            if (event.target === modal) {
                closeModal();
            }

            if (event.target === detailModal) {
                closeDetailModal();
            }

            if (event.target === noteModal) {
                closeNoteModal();
            }
        });
    </script>
</body>
</html>
