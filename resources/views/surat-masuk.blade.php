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
        $departmentStatusConfig = \App\Support\DepartmentReceiptStatus::options();
    @endphp

    <div class="main-layout">
        @include('layouts.sidebar')

        <main class="main-content">
            <div class="top-bar">
                <div class="search-container">
                    <input type="text" class="search-input" id="searchInput" placeholder="Cari pengirim, perihal, nomor surat, atau departemen tujuan...">
                </div>
                <div class="top-bar-note">
                    Status kepala sekolah diperbarui dari halaman disposisi, sedangkan status departemen diisi lewat link share yang dibagikan admin.
                </div>
            </div>

            <div class="page-header">
                <div class="header-content">
                    <span class="eyebrow">ARSIP MASUK</span>
                    <h1>Surat Masuk</h1>
                    <p>Kelola surat masuk, tentukan departemen tujuan, bagikan link penerimaan ke departemen terkait, dan pantau dua alur status dalam satu tabel.</p>
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
                    <div class="stat-label">ACC Kepala Sekolah</div>
                    <div class="stat-value">{{ $stats['approved'] ?? 0 }}</div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Menunggu ACC</div>
                    <div class="stat-value">{{ $stats['pending_approval'] ?? 0 }}</div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Dept. Sudah Terima</div>
                    <div class="stat-value">{{ $stats['department_received'] ?? 0 }}</div>
                </div>
            </div>

            <div class="archive-section">
                <div class="section-header">
                    <div>
                        <h2>Daftar Arsip Surat Masuk</h2>
                        <p class="section-description">
                            Status kepala sekolah hanya berubah dari halaman disposisi. Tombol `!` di kolom departemen menampilkan catatan dari departemen ke admin, dan tombol share menyalin link halaman konfirmasi penerimaan.
                        </p>
                    </div>
                    <div class="workflow-note">
                        <strong>Alur singkat:</strong>
                        Admin input surat -> Kepala sekolah ACC di disposisi -> Admin kirim link ke departemen -> Departemen konfirmasi terima
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="archive-table">
                        <thead>
                            <tr>
                                <th>Tanggal Terima</th>
                                <th>Asal Surat</th>
                                <th>Perihal</th>
                                <th>Departemen Tujuan</th>
                                <th>Status Kepala Sekolah</th>
                                <th>Status Departemen</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="tableBody">
                            @forelse($surat_masuk as $surat)
                                @php
                                    $headmasterStatusKey = \App\Support\DispositionStatus::normalize($surat->status);
                                    $headmasterStatusMeta = $statusConfig[$headmasterStatusKey] ?? $statusConfig['draft'];
                                    $departmentStatusKey = \App\Support\DepartmentReceiptStatus::normalize($surat->department_status);
                                    $departmentStatusMeta = $departmentStatusConfig[$departmentStatusKey] ?? $departmentStatusConfig['pending'];
                                @endphp
                                <tr
                                    data-id="{{ $surat->id }}"
                                    data-search="{{ strtolower(implode(' ', [$surat->origin, $surat->subject, $surat->letter_number, $surat->reference_number, $surat->department_destination])) }}"
                                    data-tanggal="{{ $surat->reception_date->format('d M Y H:i') }}"
                                    data-pengirim="{{ $surat->origin }}"
                                    data-perihal="{{ $surat->subject }}"
                                    data-departemen="{{ $surat->department_destination ?: '-' }}"
                                    data-nomor-surat="{{ $surat->letter_number }}"
                                    data-referensi="{{ $surat->reference_number ?: '-' }}"
                                    data-file-name="{{ $surat->file_name ?: 'File belum tersedia' }}"
                                    data-link="{{ $surat->google_drive_link }}"
                                    data-share-url="{{ $surat->share_token ? route('surat-masuk.share', $surat->share_token) : '' }}"
                                    data-headmaster-status="{{ $headmasterStatusMeta['label'] }}"
                                    data-headmaster-status-key="{{ $headmasterStatusKey }}"
                                    data-headmaster-note="{{ $surat->notes ?: '-' }}"
                                    data-department-status="{{ $departmentStatusMeta['label'] }}"
                                    data-department-status-key="{{ $departmentStatusKey }}"
                                    data-department-note="{{ $surat->department_notes ?: '-' }}"
                                >
                                    <td>
                                        <div class="date-cell">{{ $surat->reception_date->format('d M Y') }}</div>
                                        <small>{{ $surat->reception_date->format('H:i') }}</small>
                                    </td>
                                    <td>
                                        <strong>{{ $surat->origin }}</strong>
                                        <small class="sub-copy">{{ $surat->reference_number ?: $surat->letter_number }}</small>
                                    </td>
                                    <td>
                                        <div class="subject-cell">{{ \Illuminate\Support\Str::limit($surat->subject, 70) }}</div>
                                    </td>
                                    <td>
                                        <div class="department-cell">
                                            <strong>{{ $surat->department_destination ?: '-' }}</strong>
                                            <small>Link konfirmasi akan diarahkan ke departemen ini</small>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="headmaster-status-cell">
                                            <div class="status-stack">
                                                <span class="badge {{ $headmasterStatusMeta['class'] }}">{{ $headmasterStatusMeta['label'] }}</span>
                                                <small>Diubah dari disposisi</small>
                                            </div>
                                            <button
                                                class="icon-button note-button {{ filled($surat->notes) ? 'has-note' : '' }}"
                                                type="button"
                                                onclick="openHeadmasterNoteModal({{ $surat->id }})"
                                                title="Lihat catatan kepala sekolah"
                                            >!</button>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="department-status-cell">
                                            <span class="badge {{ $departmentStatusMeta['class'] }}">{{ $departmentStatusMeta['label'] }}</span>
                                            <button
                                                class="icon-button note-button {{ filled($surat->department_notes) ? 'has-note' : '' }}"
                                                type="button"
                                                onclick="openDepartmentNoteModal({{ $surat->id }})"
                                                title="Lihat catatan departemen"
                                            >!</button>
                                            <button
                                                class="icon-button share-button"
                                                type="button"
                                                onclick="copyShareLink({{ $surat->id }})"
                                                title="Salin link untuk departemen"
                                            >
                                                <svg viewBox="0 0 24 24" aria-hidden="true">
                                                    <path d="M15 8a3 3 0 0 1 0 6h-1v-2h1a1 1 0 0 0 0-2h-4a1 1 0 0 0 0 2h1v2h-1a3 3 0 1 1 0-6h4Zm-7 3h2v2H8a4 4 0 0 1 0-8h4v2H8a2 2 0 1 0 0 4Zm8 0h-2V9h2a4 4 0 1 1 0 8h-4v-2h4a2 2 0 0 0 0-4Z"></path>
                                                </svg>
                                            </button>
                                        </div>
                                    </td>
                                    <td class="action-cell">
                                        @if($surat->google_drive_link)
                                            <a href="{{ $surat->google_drive_link }}" target="_blank" class="btn-small" rel="noopener" title="Buka file asli">Drive</a>
                                        @endif
                                        <button class="btn-small" type="button" onclick="viewDetail({{ $surat->id }})">Detail</button>
                                        <button class="btn-small btn-danger" type="button" onclick="deleteSurat({{ $surat->id }})">Hapus</button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="empty-state">Data surat masuk belum tersedia.</td>
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
        <div class="modal-content large-modal-content">
            <div class="modal-header">
                <div>
                    <h2>Tambah Surat Masuk</h2>
                    <p class="modal-subtitle">Setelah tersimpan, status kepala sekolah otomatis menunggu persetujuan dan link konfirmasi departemen langsung tersedia.</p>
                </div>
                <button class="modal-close" id="btnCloseModal" type="button">&times;</button>
            </div>

            <form id="formRegisterSurat" class="register-form" enctype="multipart/form-data">
                @csrf

                <div class="form-banner">
                    Admin hanya menginput data surat dan menentukan departemen tujuan. Persetujuan kepala sekolah dilakukan lewat halaman disposisi.
                </div>

                <div class="form-grid">
                    <div class="form-group">
                        <label>Asal / Pengirim <span class="required">*</span></label>
                        <input type="text" name="origin" class="form-control" required placeholder="Contoh: Kemenag Kota Surakarta">
                    </div>
                    <div class="form-group">
                        <label>Tanggal Terima <span class="required">*</span></label>
                        <input type="date" name="reception_date" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Nomor Surat <span class="required">*</span></label>
                        <input type="text" name="letter_number" class="form-control" required placeholder="Contoh: 421.5/SM/2026">
                    </div>
                    <div class="form-group">
                        <label>Nomor Referensi</label>
                        <input type="text" name="reference_number" class="form-control" placeholder="Opsional">
                    </div>
                    <div class="form-group form-group-wide">
                        <label>Perihal <span class="required">*</span></label>
                        <input type="text" name="subject" class="form-control" required placeholder="Contoh: Permohonan data siswa penerima bantuan">
                    </div>
                    <div class="form-group form-group-wide">
                        <label>Departemen Tujuan <span class="required">*</span></label>
                        <input type="text" name="department_destination" class="form-control" required placeholder="Contoh: Tata Usaha / Kurikulum / Humas">
                    </div>
                </div>

                <div class="form-group">
                    <label>Catatan Admin</label>
                    <textarea name="notes" class="form-control" rows="3" placeholder="Catatan internal untuk kepala sekolah atau admin lain"></textarea>
                </div>

                <div class="form-group">
                    <label>File Scan Surat <span class="required">*</span></label>
                    <div class="file-upload" id="fileUploadArea">
                        <span class="upload-symbol">[ Upload ]</span>
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
            <div class="modal-body detail-body" id="detailContent"></div>
        </div>
    </div>

    <div class="modal" id="modalDepartmentNote">
        <div class="modal-content note-modal-content">
            <div class="modal-header">
                <h2>Catatan Departemen</h2>
                <button class="modal-close" type="button" onclick="closeDepartmentNoteModal()">&times;</button>
            </div>
            <div class="modal-body note-body">
                <div class="note-summary">
                    <div>
                        <strong id="departmentNoteTitle">Status departemen</strong>
                        <p id="departmentNoteDepartment" class="note-department-name">-</p>
                    </div>
                    <span id="departmentNoteStatus" class="note-status-pill">Belum Diterima</span>
                </div>
                <div class="note-panel">
                    <p id="departmentNoteText">Belum ada catatan dari departemen.</p>
                </div>
            </div>
        </div>
    </div>

    <div class="modal" id="modalHeadmasterNote">
        <div class="modal-content note-modal-content">
            <div class="modal-header">
                <h2>Catatan Kepala Sekolah</h2>
                <button class="modal-close" type="button" onclick="closeHeadmasterNoteModal()">&times;</button>
            </div>
            <div class="modal-body note-body">
                <div class="note-summary">
                    <div>
                        <strong id="headmasterNoteTitle">Status kepala sekolah</strong>
                        <p id="headmasterNoteSource" class="note-department-name">Catatan dari halaman disposisi</p>
                    </div>
                    <span id="headmasterNoteStatus" class="note-status-pill">Draft</span>
                </div>
                <div class="note-panel">
                    <p id="headmasterNoteText">Belum ada catatan dari kepala sekolah.</p>
                </div>
            </div>
        </div>
    </div>

    <script src="{{ asset('js/request-progress.js') }}"></script>
    <script>
        const modal = document.getElementById('modalRegister');
        const detailModal = document.getElementById('modalDetail');
        const departmentNoteModal = document.getElementById('modalDepartmentNote');
        const headmasterNoteModal = document.getElementById('modalHeadmasterNote');
        const form = document.getElementById('formRegisterSurat');
        const fileInput = document.getElementById('letter_scan_input');
        const fileArea = document.getElementById('fileUploadArea');
        const fileNameDisplay = document.getElementById('file-name-display');
        const searchInput = document.getElementById('searchInput');
        const tableBody = document.getElementById('tableBody');

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
            const row = tableBody.querySelector(`tr[data-id="${id}"]`);

            if (!row) {
                return;
            }

            const driveLink = row.dataset.link
                ? `<a href="${escapeHtml(row.dataset.link)}" target="_blank" rel="noopener" class="btn-small">Buka File Asli</a>`
                : '<span class="inline-empty">File asli belum tersedia</span>';

            const downloadLink = row.dataset.link
                ? `<a href="${escapeHtml(buildDownloadUrl(row.dataset.link))}" target="_blank" rel="noopener" class="btn-small btn-secondary-inline">Download File</a>`
                : '';

            const shareLink = row.dataset.shareUrl
                ? `<button type="button" class="btn-small" onclick="copyShareLink(${id})">Salin Link Departemen</button>`
                : '';

            document.getElementById('detailContent').innerHTML = `
                <div class="detail-grid">
                    <div class="detail-card">
                        <span>Tanggal Terima</span>
                        <strong>${escapeHtml(row.dataset.tanggal)}</strong>
                    </div>
                    <div class="detail-card">
                        <span>Asal Surat</span>
                        <strong>${escapeHtml(row.dataset.pengirim)}</strong>
                    </div>
                    <div class="detail-card">
                        <span>Perihal</span>
                        <strong>${escapeHtml(row.dataset.perihal)}</strong>
                    </div>
                    <div class="detail-card">
                        <span>Departemen Tujuan</span>
                        <strong>${escapeHtml(row.dataset.departemen)}</strong>
                    </div>
                    <div class="detail-card">
                        <span>Nomor Surat</span>
                        <strong>${escapeHtml(row.dataset.nomorSurat)}</strong>
                    </div>
                    <div class="detail-card">
                        <span>Nomor Referensi</span>
                        <strong>${escapeHtml(row.dataset.referensi)}</strong>
                    </div>
                    <div class="detail-card">
                        <span>Status Kepala Sekolah</span>
                        <strong>${escapeHtml(row.dataset.headmasterStatus)}</strong>
                    </div>
                    <div class="detail-card">
                        <span>Status Departemen</span>
                        <strong>${escapeHtml(row.dataset.departmentStatus)}</strong>
                    </div>
                </div>

                <div class="detail-note-group">
                    <div class="detail-note-card">
                        <span>Catatan Admin / Disposisi</span>
                        <p>${escapeHtml(normalizeNote(row.dataset.headmasterNote))}</p>
                    </div>
                    <div class="detail-note-card">
                        <span>Catatan Departemen</span>
                        <p>${escapeHtml(normalizeNote(row.dataset.departmentNote))}</p>
                    </div>
                </div>

                <div class="detail-actions">
                    ${driveLink}
                    ${downloadLink}
                    ${shareLink}
                </div>
            `;

            detailModal.style.display = 'flex';
        }

        function openDepartmentNoteModal(id) {
            const row = tableBody.querySelector(`tr[data-id="${id}"]`);

            if (!row) {
                return;
            }

            document.getElementById('departmentNoteTitle').textContent = 'Status departemen saat ini';
            document.getElementById('departmentNoteDepartment').textContent = row.dataset.departemen || '-';
            document.getElementById('departmentNoteStatus').textContent = row.dataset.departmentStatus || 'Belum Diterima';
            document.getElementById('departmentNoteText').textContent = normalizeNote(row.dataset.departmentNote) === '-'
                ? 'Belum ada catatan dari departemen tujuan.'
                : normalizeNote(row.dataset.departmentNote);

            departmentNoteModal.style.display = 'flex';
        }

        function openHeadmasterNoteModal(id) {
            const row = tableBody.querySelector(`tr[data-id="${id}"]`);

            if (!row) {
                return;
            }

            document.getElementById('headmasterNoteTitle').textContent = 'Status kepala sekolah saat ini';
            document.getElementById('headmasterNoteSource').textContent = 'Catatan dari disposisi / kepala sekolah';
            document.getElementById('headmasterNoteStatus').textContent = row.dataset.headmasterStatus || 'Draft';
            document.getElementById('headmasterNoteText').textContent = normalizeNote(row.dataset.headmasterNote) === '-'
                ? 'Belum ada catatan dari kepala sekolah. Jika status berubah tanpa catatan, perubahan tetap tercatat dari halaman disposisi.'
                : normalizeNote(row.dataset.headmasterNote);

            headmasterNoteModal.style.display = 'flex';
        }

        async function copyShareLink(id) {
            const row = tableBody.querySelector(`tr[data-id="${id}"]`);
            const shareUrl = row?.dataset.shareUrl || '';

            if (!shareUrl) {
                alert('Link share belum tersedia untuk surat ini.');
                return;
            }

            try {
                if (navigator.clipboard && navigator.clipboard.writeText) {
                    await navigator.clipboard.writeText(shareUrl);
                } else {
                    fallbackCopyText(shareUrl);
                }

                RequestProgress.showNotice('Link departemen berhasil disalin.', 'success');
            } catch (error) {
                fallbackCopyText(shareUrl);
                RequestProgress.showNotice('Link departemen berhasil disalin.', 'success');
            }
        }

        function closeModal() {
            modal.style.display = 'none';
            form.reset();
            resetFileState();
        }

        function closeDetailModal() {
            detailModal.style.display = 'none';
        }

        function closeDepartmentNoteModal() {
            departmentNoteModal.style.display = 'none';
        }

        function closeHeadmasterNoteModal() {
            headmasterNoteModal.style.display = 'none';
        }

        function resetFileState() {
            fileNameDisplay.innerText = 'Klik untuk memilih file surat';
            fileArea.classList.remove('has-file');
        }

        function normalizeNote(note) {
            const value = String(note ?? '').trim();
            return value && value !== 'null' ? value : '-';
        }

        function buildDownloadUrl(url) {
            const filePathMatch = String(url).match(/drive\.google\.com\/file\/d\/([^/]+)/);
            if (filePathMatch) {
                return `https://drive.google.com/uc?export=download&id=${filePathMatch[1]}`;
            }

            const idQueryMatch = String(url).match(/[?&]id=([^&]+)/);
            if (idQueryMatch) {
                return `https://drive.google.com/uc?export=download&id=${idQueryMatch[1]}`;
            }

            return url;
        }

        function fallbackCopyText(text) {
            const textarea = document.createElement('textarea');
            textarea.value = text;
            textarea.setAttribute('readonly', '');
            textarea.style.position = 'absolute';
            textarea.style.left = '-9999px';
            document.body.appendChild(textarea);
            textarea.select();
            document.execCommand('copy');
            textarea.remove();
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

            if (event.target === departmentNoteModal) {
                closeDepartmentNoteModal();
            }

            if (event.target === headmasterNoteModal) {
                closeHeadmasterNoteModal();
            }
        };
    </script>
</body>
</html>
