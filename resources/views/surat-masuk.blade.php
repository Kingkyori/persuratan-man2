<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Surat Masuk - MAN 2 Surakarta</title>
    <link rel="stylesheet" href="{{ asset('css/sidebar.css') }}">
    <link rel="stylesheet" href="{{ asset('css/surat-masuk.css') }}">
    <link rel="stylesheet" href="{{ asset('css/modal.css') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body>
    @php
        $statusConfig = \App\Support\DispositionStatus::options();
    @endphp

    <div class="main-layout">
        @include('layouts.sidebar')

        <main class="main-content">
            <div class="top-bar">
                <div class="search-container">
                    <input type="text" class="search-input" id="searchInput" placeholder="Cari pengirim, perihal, atau nomor surat...">
                </div>
                <div class="top-bar-note">
                    Status dan catatan disposisi pada surat masuk akan sinkron dengan halaman disposisi.
                </div>
            </div>

            <div class="page-header">
                <div class="header-content">
                    <h1>Surat Masuk</h1>
                    <p>Kelola arsip surat masuk, pantau status persetujuan, dan lihat catatan disposisi langsung dari daftar surat.</p>
                </div>
                <button class="btn-primary register-btn" id="btnOpenModal" type="button">
                    <span>+</span> Tambah Surat Masuk
                </button>
            </div>

            <div class="stats-container">
                <div class="stat-card">
                    <div class="stat-label">Total Surat</div>
                    <div class="stat-value">{{ $stats['total_received'] ?? 0 }}</div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Disetujui</div>
                    <div class="stat-value">{{ $stats['approved'] ?? 0 }}</div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Menunggu</div>
                    <div class="stat-value">{{ $stats['pending_approval'] ?? 0 }}</div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Revisi</div>
                    <div class="stat-value">{{ $stats['revision'] ?? 0 }}</div>
                </div>
            </div>

            <div class="archive-section">
                <div class="section-header">
                    <div>
                        <h2>Daftar Arsip Surat Masuk</h2>
                        <p class="section-description">Klik tanda `!` di samping status untuk melihat catatan disposisi atau perubahan review.</p>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="archive-table">
                        <thead>
                            <tr>
                                <th>Tanggal Terima</th>
                                <th>Asal Surat</th>
                                <th>Perihal</th>
                                <th>Nomor Referensi</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="tableBody">
                            @forelse($surat_masuk as $surat)
                                @php
                                    $statusKey = \App\Support\DispositionStatus::normalize($surat->status);
                                    $statusMeta = $statusConfig[$statusKey] ?? $statusConfig['draft'];
                                    $hasReviewSignal = filled($surat->notes) || $statusKey !== 'draft';
                                @endphp
                                <tr
                                    data-id="{{ $surat->id }}"
                                    data-search="{{ strtolower($surat->origin . ' ' . $surat->subject . ' ' . ($surat->reference_number ?? $surat->letter_number)) }}"
                                    data-catatan="{{ $surat->notes ?: '-' }}"
                                    data-status="{{ $statusMeta['label'] }}"
                                    data-status-key="{{ $statusKey }}"
                                    data-tanggal="{{ $surat->reception_date->format('d M Y H:i') }}"
                                    data-pengirim="{{ $surat->origin }}"
                                    data-perihal="{{ $surat->subject }}"
                                    data-referensi="{{ $surat->reference_number ?? $surat->letter_number }}"
                                    data-link="{{ $surat->google_drive_link }}"
                                >
                                    <td>
                                        <div class="date-cell">{{ $surat->reception_date->format('d M Y') }}</div>
                                        <small>{{ $surat->reception_date->format('H:i') }}</small>
                                    </td>
                                    <td>
                                        <strong>{{ $surat->origin }}</strong>
                                    </td>
                                    <td>{{ \Illuminate\Support\Str::limit($surat->subject, 55) }}</td>
                                    <td>{{ $surat->reference_number ?? $surat->letter_number }}</td>
                                    <td>
                                        <div class="status-cell">
                                            <select class="status-select" onchange="updateStatus({{ $surat->id }}, this)">
                                                @foreach($statusConfig as $optionKey => $statusMetaOption)
                                                    <option value="{{ $optionKey }}" {{ $statusKey === $optionKey ? 'selected' : '' }}>
                                                        {{ $statusMetaOption['label'] }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            <button
                                                class="review-indicator {{ filled($surat->notes) ? 'has-note' : 'has-status' }}"
                                                type="button"
                                                onclick="openNoteModal({{ $surat->id }})"
                                                title="Lihat catatan disposisi"
                                                {{ $hasReviewSignal ? '' : 'hidden' }}
                                            >!</button>
                                        </div>
                                    </td>
                                    <td class="action-cell">
                                        @if($surat->google_drive_link)
                                            <a href="{{ $surat->google_drive_link }}" target="_blank" class="btn-small" rel="noopener" title="Buka di Drive">Drive</a>
                                        @endif
                                        <button class="btn-small" type="button" onclick="viewDetail({{ $surat->id }})">Detail</button>
                                        <button class="btn-small btn-danger" type="button" onclick="deleteSurat({{ $surat->id }})">Hapus</button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="empty-state">Data surat masuk belum tersedia.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="pagination-container">
                    {{ $surat_masuk->links() }}
                </div>
            </div>
        </main>
    </div>

    <div class="modal" id="modalRegister">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Tambah Surat Masuk</h2>
                <button class="modal-close" id="btnCloseModal" type="button">&times;</button>
            </div>

            <form id="formRegisterSurat" enctype="multipart/form-data">
                @csrf
                <div class="form-row">
                    <div class="form-group">
                        <label>Asal / Pengirim <span class="required">*</span></label>
                        <input type="text" name="origin" class="form-control" required placeholder="Contoh: Kemenag Solo">
                    </div>
                    <div class="form-group">
                        <label>Tanggal Terima <span class="required">*</span></label>
                        <input type="date" name="reception_date" class="form-control" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Nomor Surat <span class="required">*</span></label>
                        <input type="text" name="letter_number" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Perihal <span class="required">*</span></label>
                        <input type="text" name="subject" class="form-control" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Nomor Referensi</label>
                        <input type="text" name="reference_number" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>Status</label>
                        <select name="status" class="form-control">
                            @foreach($statusConfig as $statusKey => $statusMeta)
                                <option value="{{ $statusKey }}">{{ $statusMeta['label'] }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label>Catatan</label>
                    <textarea name="notes" class="form-control" rows="2" placeholder="Tambahkan catatan singkat bila perlu"></textarea>
                </div>

                <div class="form-group">
                    <label>File Scan Surat <span class="required">*</span></label>
                    <div class="file-upload" id="fileUploadArea">
                        <span id="icon-upload">[ Upload ]</span>
                        <p id="file-name-display">Klik untuk memilih file surat</p>
                        <small>Format yang didukung: PDF, JPG, JPEG, PNG</small>
                        <input type="file" name="letter_scan" id="letter_scan_input" accept=".pdf,.jpg,.jpeg,.png,application/pdf,image/jpeg,image/png" hidden required>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="button" class="btn-secondary" onclick="closeModal()">Batal</button>
                    <button type="submit" class="btn-success" id="btnSubmit">
                        <span id="submitText">Simpan Surat</span>
                        <span id="submitLoader" style="display: none;">Mengunggah...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal" id="modalDetail">
        <div class="modal-content detail-modal-content">
            <div class="modal-header">
                <h2>Detail Surat Masuk</h2>
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
        const modal = document.getElementById('modalRegister');
        const detailModal = document.getElementById('modalDetail');
        const noteModal = document.getElementById('modalNote');
        const form = document.getElementById('formRegisterSurat');
        const fileInput = document.getElementById('letter_scan_input');
        const fileArea = document.getElementById('fileUploadArea');
        const fileNameDisplay = document.getElementById('file-name-display');
        const searchInput = document.getElementById('searchInput');

        document.getElementById('btnOpenModal').onclick = () => {
            modal.style.display = 'flex';
        };

        document.getElementById('btnCloseModal').onclick = () => closeModal();

        fileArea.onclick = () => fileInput.click();
        fileInput.onchange = () => {
            if (fileInput.files.length > 0) {
                fileNameDisplay.innerText = `File: ${fileInput.files[0].name}`;
                fileArea.classList.add('has-file');
            } else {
                resetFileState();
            }
        };

        if (searchInput) {
            searchInput.addEventListener('input', () => {
                const query = searchInput.value.trim().toLowerCase();
                document.querySelectorAll('#tableBody tr[data-id]').forEach((row) => {
                    row.style.display = row.dataset.search.includes(query) ? '' : 'none';
                });
            });
        }

        form.onsubmit = async (event) => {
            event.preventDefault();

            const submitBtn = document.getElementById('btnSubmit');
            const loader = document.getElementById('submitLoader');
            const btnText = document.getElementById('submitText');

            if (!fileInput.files[0]) {
                alert('Harap pilih file scan surat.');
                return;
            }

            const formData = new FormData(form);

            try {
                submitBtn.disabled = true;
                loader.style.display = 'inline';
                btnText.style.display = 'none';

                const { response, result } = await RequestProgress.requestJson({
                    url: '{{ route("surat-masuk.store") }}',
                    method: 'POST',
                    data: formData,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    title: 'Mengunggah surat masuk',
                    initialMessage: 'Menyiapkan scan surat masuk...',
                    uploadMessage: 'Mengunggah scan surat masuk...',
                    processingMessage: 'Menyimpan surat masuk ke database dan Google Drive...',
                    successMessage: 'Surat masuk berhasil diproses.'
                });

                if (response.ok && result.success) {
                    RequestProgress.showNotice(
                        result.message,
                        result.drive_synced === false ? 'warning' : 'success'
                    );
                    location.reload();
                } else {
                    alert(`Gagal: ${result.message || 'Upload surat gagal.'}`);
                }
            } catch (error) {
                console.error(error);
                alert('Terjadi kesalahan koneksi/server.');
            } finally {
                submitBtn.disabled = false;
                loader.style.display = 'none';
                btnText.style.display = 'inline';
            }
        };

        async function updateStatus(id, selectElement) {
            const row = document.querySelector(`tr[data-id="${id}"]`);
            const previousStatus = row.dataset.statusKey || selectElement.value;
            const newStatus = selectElement.value;

            try {
                const { response, result } = await RequestProgress.requestJson({
                    url: `/surat-masuk/${id}/status`,
                    method: 'POST',
                    data: { status: newStatus },
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    title: 'Menyimpan status surat',
                    initialMessage: 'Mengirim perubahan status surat masuk...',
                    processingMessage: 'Menyimpan perubahan status ke database...',
                    successMessage: 'Status surat berhasil diperbarui.'
                });

                if (!response.ok || !result.success) {
                    throw new Error(result.message || 'Gagal mengubah status.');
                }

                row.dataset.statusKey = result.data.status;
                row.dataset.status = result.data.status_label;
                row.dataset.catatan = result.data.notes || row.dataset.catatan || '-';
                renderReviewIndicator(row);
                RequestProgress.showNotice(result.message, 'success');
            } catch (error) {
                selectElement.value = previousStatus;
                alert(error.message || 'Gagal mengubah status.');
            }
        }

        async function deleteSurat(id) {
            if (!confirm('Hapus surat ini?')) {
                return;
            }

            try {
                const { result } = await RequestProgress.requestJson({
                    url: `/surat-masuk/${id}`,
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    title: 'Menghapus surat',
                    initialMessage: 'Menyiapkan penghapusan surat...',
                    processingMessage: 'Menghapus surat dari database...',
                    successMessage: 'Surat berhasil dihapus.'
                });

                if (result.success) {
                    location.reload();
                } else {
                    alert(result.message || 'Gagal menghapus data.');
                }
            } catch (error) {
                alert('Gagal menghapus data.');
            }
        }

        function viewDetail(id) {
            const row = document.querySelector(`tr[data-id="${id}"]`);

            if (!row) {
                return;
            }

            const driveLink = row.dataset.link
                ? `<p><strong>Google Drive:</strong> <a href="${escapeHtml(row.dataset.link)}" target="_blank" rel="noopener">Buka file</a></p>`
                : '';

            document.getElementById('detailContent').innerHTML = `
                <p><strong>Tanggal Terima:</strong> ${escapeHtml(row.dataset.tanggal)}</p>
                <p><strong>Asal Surat:</strong> ${escapeHtml(row.dataset.pengirim)}</p>
                <p><strong>Perihal:</strong> ${escapeHtml(row.dataset.perihal)}</p>
                <p><strong>Nomor Referensi:</strong> ${escapeHtml(row.dataset.referensi)}</p>
                <p><strong>Status:</strong> ${escapeHtml(row.dataset.status)}</p>
                <p><strong>Catatan:</strong> ${escapeHtml(normalizeNote(row.dataset.catatan))}</p>
                ${driveLink}
            `;

            detailModal.style.display = 'flex';
        }

        function openNoteModal(id) {
            const row = document.querySelector(`tr[data-id="${id}"]`);

            if (!row) {
                return;
            }

            const note = normalizeNote(row.dataset.catatan);
            const status = row.dataset.status || 'Draft';

            document.getElementById('noteStatusLabel').textContent = 'Status terakhir surat';
            document.getElementById('noteStatusValue').textContent = status;
            document.getElementById('noteText').textContent = note === '-'
                ? 'Belum ada catatan tambahan. Surat ini sudah pernah ditinjau atau statusnya sudah diperbarui.'
                : note;

            noteModal.style.display = 'flex';
        }

        function closeModal() {
            modal.style.display = 'none';
            form.reset();
            resetFileState();
        }

        function closeDetailModal() {
            detailModal.style.display = 'none';
        }

        function closeNoteModal() {
            noteModal.style.display = 'none';
        }

        function resetFileState() {
            fileNameDisplay.innerText = 'Klik untuk memilih file surat';
            fileArea.classList.remove('has-file');
        }

        function normalizeNote(note) {
            const value = String(note ?? '').trim();
            return value && value !== 'null' ? value : '-';
        }

        function hasReviewSignal(statusKey, note) {
            return String(statusKey || 'draft') !== 'draft' || normalizeNote(note) !== '-';
        }

        function renderReviewIndicator(row) {
            const button = row.querySelector('.review-indicator');
            const note = normalizeNote(row.dataset.catatan);
            const statusKey = row.dataset.statusKey || 'draft';
            const visible = hasReviewSignal(statusKey, note);

            button.hidden = !visible;
            button.classList.toggle('has-note', note !== '-');
            button.classList.toggle('has-status', note === '-' && visible);
        }

        function escapeHtml(value) {
            return String(value ?? '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#39;');
        }

        window.onclick = (event) => {
            if (event.target === modal) {
                closeModal();
            }

            if (event.target === detailModal) {
                closeDetailModal();
            }

            if (event.target === noteModal) {
                closeNoteModal();
            }
        };
    </script>
</body>
</html>
