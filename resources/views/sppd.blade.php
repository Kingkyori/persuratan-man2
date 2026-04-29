<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SPPD - MAN 2 Surakarta</title>
    <link rel="stylesheet" href="{{ asset('css/sidebar.css') }}">
    <link rel="stylesheet" href="{{ asset('css/modal.css') }}">
    <link rel="stylesheet" href="{{ asset('css/sppd.css') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body>
    @php
        $statusConfig = \App\Support\DispositionStatus::options();
        $records = collect($sppd ?? []);
        $travelStats = [
            'total' => $records->count(),
            'draft' => $records->filter(fn ($item) => \App\Support\DispositionStatus::normalize($item->status) === 'draft')->count(),
            'pending' => $records->filter(fn ($item) => \App\Support\DispositionStatus::normalize($item->status) === 'pending_approval')->count(),
            'approved' => $records->filter(fn ($item) => \App\Support\DispositionStatus::normalize($item->status) === 'approved')->count(),
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
                        placeholder="Cari pegawai, tujuan, nomor surat, atau kepentingan dinas..."
                    >
                </div>
                <div class="top-bar-note">
                    Arsip SPPD manual dan surat penugasan otomatis tersimpan di database. Catatan hasil disposisi bisa dilihat dari tombol `!` di samping status.
                </div>
            </div>

            <div class="page-header">
                <div class="header-content">
                    <h1>SPPD</h1>
                    <p>
                        Kelola upload lampiran SPPD dan buat surat penugasan otomatis dalam satu daftar arsip yang rapi,
                        lengkap dengan status, hasil review, PDF, dan DOCX.
                    </p>
                </div>
                <div class="header-actions">
                    <button class="btn-secondary btn-toolbar" id="btnOpenGeneratedModal" type="button">+ Buat SPPD</button>
                    <button class="btn-primary btn-toolbar" id="btnOpenModal" type="button">+ Tambah SPPD</button>
                </div>
            </div>

            <section class="stats-strip">
                <article class="mini-stat">
                    <span class="mini-label">Total Arsip</span>
                    <strong>{{ $travelStats['total'] }}</strong>
                </article>
                <article class="mini-stat">
                    <span class="mini-label">Draft</span>
                    <strong>{{ $travelStats['draft'] }}</strong>
                </article>
                <article class="mini-stat">
                    <span class="mini-label">Menunggu</span>
                    <strong>{{ $travelStats['pending'] }}</strong>
                </article>
                <article class="mini-stat">
                    <span class="mini-label">Disetujui</span>
                    <strong>{{ $travelStats['approved'] }}</strong>
                </article>
            </section>

            <section class="archive-section">
                <div class="section-header">
                    <div>
                        <h2>Daftar Arsip SPPD</h2>
                        <p class="section-description">
                            Arsip ini menampung upload lampiran SPPD dan surat penugasan hasil generate template:
                            pegawai, tujuan, tanggal, durasi, nomor surat, file, status, dan catatan review.
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
                                <th>Tanggal</th>
                                <th>Pegawai</th>
                                <th>Tujuan</th>
                                <th>Durasi</th>
                                <th>Kepentingan</th>
                                <th>File</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="tableBody">
                            @forelse($records as $item)
                                @php
                                    $statusKey = \App\Support\DispositionStatus::normalize($item->status);
                                    $statusMeta = $statusConfig[$statusKey] ?? $statusConfig['draft'];
                                    $hasReviewSignal = filled($item->notes) || $statusKey !== 'draft';
                                    $docxUrl = $item->generated_docx_path ? route('sppd.downloadDocx', $item->id) : '';
                                    $recordTypeLabel = $item->entry_type === 'generated_assignment' ? 'Surat Penugasan' : 'Upload Lampiran';
                                @endphp
                                <tr
                                    data-id="{{ $item->id }}"
                                    data-search="{{ strtolower(($item->employee_name ?? '') . ' ' . $item->destination . ' ' . $item->purpose . ' ' . ($item->document_number ?? '')) }}"
                                    data-tanggal="{{ optional($item->departure_date)->format('d M Y') }}"
                                    data-pegawai="{{ $item->employee_name ?? '-' }}"
                                    data-tujuan="{{ $item->destination }}"
                                    data-durasi="{{ $item->duration_days }} hari"
                                    data-kepentingan="{{ $item->purpose }}"
                                    data-status="{{ $statusMeta['label'] }}"
                                    data-status-key="{{ $statusKey }}"
                                    data-file="{{ $item->file_name }}"
                                    data-link="{{ route('archive.open', ['type' => 'sppd', 'id' => $item->id]) }}"
                                    data-docx-link="{{ $docxUrl }}"
                                    data-catatan="{{ $item->notes ?: '-' }}"
                                    data-record-type="{{ $recordTypeLabel }}"
                                    data-document-number="{{ $item->document_number ?: '-' }}"
                                >
                                    <td>{{ optional($item->departure_date)->format('d M Y') }}</td>
                                    <td>{{ $item->employee_name ?? '-' }}</td>
                                    <td>{{ $item->destination }}</td>
                                    <td>{{ $item->duration_days }} hari</td>
                                    <td>{{ \Illuminate\Support\Str::limit($item->purpose, 45) }}</td>
                                    <td><span class="file-pill">{{ $item->file_name }}</span></td>
                                    <td>
                                        <div class="status-cell">
                                            <span class="badge {{ $statusMeta['class'] }}">{{ $statusMeta['label'] }}</span>
                                            <button
                                                class="review-indicator {{ filled($item->notes) ? 'has-note' : 'has-status' }}"
                                                type="button"
                                                onclick="openNoteModal('{{ $item->id }}')"
                                                title="Lihat catatan disposisi"
                                                {{ $hasReviewSignal ? '' : 'hidden' }}
                                            >!</button>
                                        </div>
                                    </td>
                                    <td class="action-cell">
                                        <a href="{{ route('archive.open', ['type' => 'sppd', 'id' => $item->id]) }}" target="_blank" class="btn-small" rel="noopener">File</a>
                                        @if($docxUrl)
                                            <a href="{{ $docxUrl }}" class="btn-small" rel="noopener">DOCX</a>
                                        @endif
                                        <button class="btn-small" type="button" onclick="viewDetail('{{ $item->id }}')">Detail</button>
                                        <button class="btn-small btn-danger" type="button" onclick="deleteSppd('{{ $item->id }}')">Hapus</button>
                                    </td>
                                </tr>
                            @empty
                                <tr id="emptyStateRow">
                                    <td colspan="8" class="empty-state">Belum ada data SPPD.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="table-footer">
                    <span id="resultCount">{{ $records->count() }} data tampil</span>
                    <span>Setiap input atau hasil generate SPPD yang berhasil disimpan akan muncul di daftar arsip ini.</span>
                </div>
            </section>
        </main>
    </div>

    <div class="modal" id="modalRegister">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Input SPPD</h2>
                <button class="modal-close" id="btnCloseModal" type="button">&times;</button>
            </div>

            <form id="formSppd" class="register-form" enctype="multipart/form-data">
                <div class="form-banner">
                    Nama pegawai diisi manual. Lampiran mendukung PDF, PNG, dan JPG.
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="employee_name">Nama Pegawai <span class="required">*</span></label>
                        <input type="text" id="employee_name" name="employee_name" class="form-control" required placeholder="Contoh: Ahmad Fauzi">
                    </div>
                    <div class="form-group">
                        <label for="departure_date">Tanggal Berangkat <span class="required">*</span></label>
                        <input type="date" id="departure_date" name="departure_date" class="form-control" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="destination">Tujuan <span class="required">*</span></label>
                        <input type="text" id="destination" name="destination" class="form-control" required placeholder="Contoh: Jakarta">
                    </div>
                    <div class="form-group">
                        <label for="duration_days">Durasi (hari) <span class="required">*</span></label>
                        <input type="number" id="duration_days" name="duration_days" class="form-control" min="1" max="365" required placeholder="Contoh: 3">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group form-group-full">
                        <label for="purpose">Kepentingan Dinas <span class="required">*</span></label>
                        <textarea id="purpose" name="purpose" class="form-control" rows="3" required placeholder="Jelaskan tujuan perjalanan dinas"></textarea>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="status">Status <span class="required">*</span></label>
                        <select id="status" name="status" class="form-control" required>
                            @foreach($statusConfig as $statusKey => $statusMeta)
                                <option value="{{ $statusKey }}">{{ $statusMeta['label'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="notes">Catatan</label>
                        <input type="text" id="notes" name="notes" class="form-control" placeholder="Tambahkan catatan singkat">
                    </div>
                </div>

                <div class="form-group">
                    <label>File Lampiran <span class="required">*</span></label>
                    <div class="file-upload" id="fileUploadArea">
                        <span class="upload-symbol">[ Upload ]</span>
                        <p id="fileNameDisplay">Pilih file lampiran SPPD</p>
                        <small>Format yang didukung: PDF, PNG, JPG, JPEG</small>
                        <input
                            type="file"
                            id="attachmentFile"
                            name="attachment_file"
                            accept=".pdf,.png,.jpg,.jpeg,application/pdf,image/png,image/jpeg"
                            hidden
                            required
                        >
                    </div>
                </div>

                <div class="form-actions">
                    <button type="button" class="btn-secondary" onclick="closeModal()">Batal</button>
                    <button type="submit" class="btn-success" id="btnSubmit">
                        <span id="submitText">Simpan SPPD</span>
                        <span id="submitLoader" style="display: none;">Menyimpan...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal" id="modalGenerate">
        <div class="modal-content modal-content-wide">
            <div class="modal-header">
                <h2>Buat Surat Penugasan</h2>
                <button class="modal-close" id="btnCloseGeneratedModal" type="button">&times;</button>
            </div>

            <form id="formGeneratedSppd" class="register-form">
                <div class="form-banner">
                    Kop surat, kalimat pembuka, dasar poin 1, dan format penutup otomatis mengikuti template MAN 2.
                    Pengguna cukup mengisi inti surat seperti nomor, petugas, agenda, waktu, dan tempat tugas.
                    Bagian dasar surat poin 2 di bawah ini opsional, jadi kalau kosong tidak akan ditampilkan di surat.
                </div>

                <div class="form-section-title">Informasi Surat</div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="generated_document_number">Nomor Surat <span class="required">*</span></label>
                        <input type="text" id="generated_document_number" name="document_number" class="form-control" required placeholder="Contoh: 072/Ma.11.31.02/KP.01.1/04/2026">
                    </div>
                    <div class="form-group">
                        <label for="generated_document_date">Tanggal Surat <span class="required">*</span></label>
                        <input type="date" id="generated_document_date" name="document_date" class="form-control" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="generated_duration_days">Durasi (hari) <span class="required">*</span></label>
                        <input type="number" id="generated_duration_days" name="duration_days" class="form-control" min="1" max="365" required value="1">
                    </div>
                    <div class="form-group">
                        <label for="generated_city">Kota Penandatanganan <span class="required">*</span></label>
                        <input type="text" id="generated_city" name="city" class="form-control" required value="Surakarta">
                    </div>
                </div>

                <div class="form-section-title">Dasar Surat (Poin 2 - Opsional)</div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="generated_reference_from">Surat dari</label>
                        <input type="text" id="generated_reference_from" name="reference_from" class="form-control" placeholder="Contoh: Kankemenag Surakarta">
                    </div>
                    <div class="form-group">
                        <label for="generated_reference_number">Nomor Surat</label>
                        <input type="text" id="generated_reference_number" name="reference_number" class="form-control" placeholder="Contoh: 123/ABC/2026">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="generated_reference_date">Tanggal Surat Dasar</label>
                        <input type="date" id="generated_reference_date" name="reference_date" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="generated_reference_subject">Perihal</label>
                        <input type="text" id="generated_reference_subject" name="reference_subject" class="form-control" placeholder="Contoh: Undangan koordinasi">
                    </div>
                </div>

                <div class="form-section-title form-section-title-inline">
                    <span>Petugas yang Ditugaskan</span>
                    <button type="button" class="btn-secondary btn-inline" id="btnAddAssignee">+ Tambah Petugas</button>
                </div>
                <div class="assignee-list" id="assigneeList"></div>

                <div class="form-section-title">Agenda Penugasan</div>
                <div class="form-row">
                    <div class="form-group form-group-full">
                        <label for="generated_assignment_agenda">Agenda / Tugas <span class="required">*</span></label>
                        <textarea id="generated_assignment_agenda" name="assignment_agenda" class="form-control" rows="3" required placeholder="Contoh: Konsultasi rekonstruksi pembangunan cagar budaya MAN 2 Surakarta"></textarea>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="generated_activity_date">Tanggal Kegiatan <span class="required">*</span></label>
                        <input type="date" id="generated_activity_date" name="activity_date" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="generated_activity_time">Waktu Mulai <span class="required">*</span></label>
                        <input type="time" id="generated_activity_time" name="activity_time" class="form-control" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group form-group-full">
                        <label for="generated_activity_location">Tempat Kegiatan <span class="required">*</span></label>
                        <textarea id="generated_activity_location" name="activity_location" class="form-control" rows="2" required placeholder="Contoh: Kantor Dinas PUPR Surakarta, Jl. Blimbing No 10, Kerten, Laweyan, Surakarta"></textarea>
                    </div>
                </div>

                <div class="form-section-title">Penandatangan dan Arsip</div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="generated_signer_title">Jabatan Penandatangan <span class="required">*</span></label>
                        <input type="text" id="generated_signer_title" name="signer_title" class="form-control" required value="Plt. Kepala">
                    </div>
                    <div class="form-group">
                        <label for="generated_signer_name">Nama Penandatangan <span class="required">*</span></label>
                        <input type="text" id="generated_signer_name" name="signer_name" class="form-control" required value="Sita Kurniasari">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="generated_signer_nip">NIP Penandatangan</label>
                        <input type="text" id="generated_signer_nip" name="signer_nip" class="form-control" placeholder="Opsional">
                    </div>
                    <div class="form-group">
                        <label for="generated_status">Status <span class="required">*</span></label>
                        <select id="generated_status" name="status" class="form-control" required>
                            @foreach($statusConfig as $statusKey => $statusMeta)
                                <option value="{{ $statusKey }}">{{ $statusMeta['label'] }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group form-group-full">
                        <label for="generated_notes">Catatan Arsip</label>
                        <textarea id="generated_notes" name="notes" class="form-control" rows="2" placeholder="Tambahkan catatan singkat bila perlu"></textarea>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="button" class="btn-secondary" onclick="closeGeneratedModal()">Batal</button>
                    <button type="submit" class="btn-success" id="btnGeneratedSubmit">
                        <span id="generatedSubmitText">Simpan dan Buat Dokumen</span>
                        <span id="generatedSubmitLoader" style="display: none;">Membuat...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal" id="modalDetail">
        <div class="modal-content detail-modal-content">
            <div class="modal-header">
                <h2>Detail SPPD</h2>
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
                    <strong id="noteStatusLabel">Status dokumen</strong>
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
        const generatedModal = document.getElementById('modalGenerate');
        const detailModal = document.getElementById('modalDetail');
        const noteModal = document.getElementById('modalNote');
        const form = document.getElementById('formSppd');
        const generatedForm = document.getElementById('formGeneratedSppd');
        const fileInput = document.getElementById('attachmentFile');
        const fileUploadArea = document.getElementById('fileUploadArea');
        const fileNameDisplay = document.getElementById('fileNameDisplay');
        const tableBody = document.getElementById('tableBody');
        const searchInput = document.getElementById('searchInput');
        const resultCount = document.getElementById('resultCount');
        const submitButton = document.getElementById('btnSubmit');
        const submitText = document.getElementById('submitText');
        const submitLoader = document.getElementById('submitLoader');
        const generatedSubmitButton = document.getElementById('btnGeneratedSubmit');
        const generatedSubmitText = document.getElementById('generatedSubmitText');
        const generatedSubmitLoader = document.getElementById('generatedSubmitLoader');
        const assigneeList = document.getElementById('assigneeList');

        document.getElementById('btnOpenModal').addEventListener('click', () => {
            modal.style.display = 'flex';
        });

        document.getElementById('btnOpenGeneratedModal').addEventListener('click', () => {
            generatedModal.style.display = 'flex';
        });

        document.getElementById('btnCloseModal').addEventListener('click', closeModal);
        document.getElementById('btnCloseGeneratedModal').addEventListener('click', closeGeneratedModal);
        document.getElementById('btnAddAssignee').addEventListener('click', () => addAssigneeBlock());

        if (fileUploadArea) {
            fileUploadArea.addEventListener('click', () => fileInput.click());
        }

        if (fileInput) {
            fileInput.addEventListener('change', () => {
                if (fileInput.files.length > 0) {
                    fileNameDisplay.textContent = fileInput.files[0].name;
                    fileUploadArea.classList.add('has-file');
                } else {
                    resetFileUpload();
                }
            });
        }

        form.addEventListener('submit', async (event) => {
            event.preventDefault();

            if (!fileInput.files.length) {
                alert('Silakan pilih file lampiran terlebih dahulu.');
                return;
            }

            const formData = new FormData(form);

            try {
                setSubmittingState(submitButton, submitText, submitLoader, true);

                const { response, result } = await RequestProgress.requestJson({
                    url: '{{ route("sppd.store") }}',
                    method: 'POST',
                    data: formData,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    title: 'Mengunggah data SPPD',
                    initialMessage: 'Menyiapkan lampiran SPPD...',
                    uploadMessage: 'Mengunggah lampiran SPPD...',
                    processingMessage: 'Menyimpan data SPPD ke database dan Google Drive...',
                    successMessage: 'Data SPPD berhasil diproses.'
                });

                if (!response.ok || !result.success) {
                    throw new Error(result.message || 'Gagal menyimpan data SPPD.');
                }

                prependRow(result.data);
                closeModal();
                updateVisibleCount();
                RequestProgress.showNotice(
                    result.message,
                    result.drive_synced === false ? 'warning' : 'success'
                );
            } catch (error) {
                alert(error.message || 'Terjadi kesalahan saat menyimpan data SPPD.');
            } finally {
                setSubmittingState(submitButton, submitText, submitLoader, false);
            }
        });

        generatedForm.addEventListener('submit', async (event) => {
            event.preventDefault();

            if (assigneeList.querySelectorAll('.assignee-card').length === 0) {
                alert('Tambahkan minimal satu petugas terlebih dahulu.');
                return;
            }

            const formData = new FormData(generatedForm);

            try {
                setSubmittingState(generatedSubmitButton, generatedSubmitText, generatedSubmitLoader, true);

                const { response, result } = await RequestProgress.requestJson({
                    url: '{{ route("sppd.storeGenerated") }}',
                    method: 'POST',
                    data: formData,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    title: 'Membuat surat penugasan',
                    initialMessage: 'Menyiapkan data surat penugasan...',
                    processingMessage: 'Membuat file PDF dan DOCX lalu menyimpan arsip...',
                    successMessage: 'Surat penugasan berhasil dibuat.'
                });

                if (!response.ok || !result.success) {
                    throw new Error(result.message || 'Gagal membuat surat penugasan.');
                }

                prependRow(result.data);
                closeGeneratedModal();
                updateVisibleCount();
                RequestProgress.showNotice(result.message, 'success');
            } catch (error) {
                alert(error.message || 'Terjadi kesalahan saat membuat surat penugasan.');
            } finally {
                setSubmittingState(generatedSubmitButton, generatedSubmitText, generatedSubmitLoader, false);
            }
        });

        searchInput.addEventListener('input', () => {
            const query = searchInput.value.trim().toLowerCase();
            const rows = tableBody.querySelectorAll('tr[data-id]');

            rows.forEach((row) => {
                row.style.display = row.dataset.search.includes(query) ? '' : 'none';
            });

            updateVisibleCount();
        });

        function prependRow(data) {
            removeEmptyState();

            const status = statusConfig[data.status] || statusConfig.draft;
            const row = document.createElement('tr');

            row.dataset.id = data.id;
            row.dataset.search = `${data.pegawai} ${data.tujuan} ${data.kepentingan} ${data.document_number || ''}`.toLowerCase();
            row.dataset.tanggal = data.tanggal;
            row.dataset.pegawai = data.pegawai;
            row.dataset.tujuan = data.tujuan;
            row.dataset.durasi = data.durasi;
            row.dataset.kepentingan = data.kepentingan;
            row.dataset.status = status.label;
            row.dataset.statusKey = data.status || 'draft';
            row.dataset.file = data.file;
            row.dataset.link = data.google_drive_link || '';
            row.dataset.docxLink = data.docx_download_url || '';
            row.dataset.catatan = data.catatan || '-';
            row.dataset.recordType = data.record_type || 'Upload Lampiran';
            row.dataset.documentNumber = data.document_number || '-';

            const driveButton = `<a href="${escapeHtml(data.google_drive_link)}" target="_blank" class="btn-small" rel="noopener">File</a>`;
            const docxButton = data.docx_download_url
                ? `<a href="${escapeHtml(data.docx_download_url)}" class="btn-small" rel="noopener">DOCX</a>`
                : '';

            row.innerHTML = `
                <td>${escapeHtml(data.tanggal)}</td>
                <td>${escapeHtml(data.pegawai)}</td>
                <td>${escapeHtml(data.tujuan)}</td>
                <td>${escapeHtml(data.durasi)}</td>
                <td>${escapeHtml(limitText(data.kepentingan, 45))}</td>
                <td><span class="file-pill">${escapeHtml(data.file)}</span></td>
                <td>
                    <div class="status-cell">
                        <span class="badge ${status.class}">${status.label}</span>
                        ${renderReviewButton(data.id, data.status, data.catatan)}
                    </div>
                </td>
                <td class="action-cell">${driveButton}${docxButton}<button class="btn-small" type="button" onclick="viewDetail('${data.id}')">Detail</button><button class="btn-small btn-danger" type="button" onclick="deleteSppd('${data.id}')">Hapus</button></td>
            `;

            tableBody.prepend(row);
        }

        async function deleteSppd(id) {
            if (!confirm('Hapus data SPPD ini?')) {
                return;
            }

            try {
                const { result } = await RequestProgress.requestJson({
                    url: `/sppd/${id}`,
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    title: 'Menghapus data SPPD',
                    initialMessage: 'Menyiapkan penghapusan data SPPD...',
                    processingMessage: 'Menghapus arsip SPPD dari database...',
                    successMessage: 'Data SPPD berhasil dihapus.'
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
                    alert(result.message || 'Gagal menghapus data SPPD.');
                }
            } catch (error) {
                alert('Gagal menghapus data SPPD.');
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

            const docxLink = row.dataset.docxLink
                ? `<p><strong>File DOCX:</strong> <a href="${escapeHtml(row.dataset.docxLink)}" rel="noopener">Unduh DOCX</a></p>`
                : '';

            const documentNumber = row.dataset.documentNumber && row.dataset.documentNumber !== '-'
                ? `<p><strong>Nomor Surat:</strong> ${escapeHtml(row.dataset.documentNumber)}</p>`
                : '';

            document.getElementById('detailContent').innerHTML = `
                <p><strong>Jenis Arsip:</strong> ${escapeHtml(row.dataset.recordType || 'SPPD')}</p>
                ${documentNumber}
                <p><strong>Tanggal Berangkat:</strong> ${escapeHtml(row.dataset.tanggal)}</p>
                <p><strong>Pegawai:</strong> ${escapeHtml(row.dataset.pegawai)}</p>
                <p><strong>Tujuan:</strong> ${escapeHtml(row.dataset.tujuan)}</p>
                <p><strong>Durasi:</strong> ${escapeHtml(row.dataset.durasi)}</p>
                <p><strong>Kepentingan Dinas:</strong> ${escapeHtml(row.dataset.kepentingan)}</p>
                <p><strong>Status:</strong> ${escapeHtml(row.dataset.status)}</p>
                <p><strong>File:</strong> ${escapeHtml(row.dataset.file)}</p>
                <p><strong>Catatan:</strong> ${escapeHtml(normalizeNote(row.dataset.catatan))}</p>
                ${driveLink}
                ${docxLink}
            `;

            detailModal.style.display = 'flex';
        }

        function openNoteModal(id) {
            const row = tableBody.querySelector(`tr[data-id="${id}"]`);

            if (!row) {
                return;
            }

            const note = normalizeNote(row.dataset.catatan);
            document.getElementById('noteStatusLabel').textContent = 'Status terakhir dokumen';
            document.getElementById('noteStatusValue').textContent = row.dataset.status || 'Draft';
            document.getElementById('noteText').textContent = note === '-'
                ? 'Belum ada catatan tambahan. Dokumen ini sudah pernah ditinjau atau statusnya sudah diperbarui.'
                : note;

            noteModal.style.display = 'flex';
        }

        function addAssigneeBlock(values = {}) {
            const card = document.createElement('div');
            card.className = 'assignee-card';
            card.innerHTML = `
                <div class="assignee-card-header">
                    <strong>Petugas</strong>
                    <button type="button" class="btn-link-danger assignee-remove">Hapus</button>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Nama <span class="required">*</span></label>
                        <input type="text" class="form-control assignee-name" required value="${escapeHtmlAttr(values.name || '')}" placeholder="Nama petugas">
                    </div>
                    <div class="form-group">
                        <label>NIP</label>
                        <input type="text" class="form-control assignee-nip" value="${escapeHtmlAttr(values.nip || '')}" placeholder="NIP">
                    </div>
                </div>
                <div class="form-row form-row-tight">
                    <div class="form-group">
                        <label>Pangkat</label>
                        <input type="text" class="form-control assignee-rank" value="${escapeHtmlAttr(values.rank || '')}" placeholder="Contoh: Penata Tk.I, III/d">
                    </div>
                    <div class="form-group">
                        <label>Jabatan</label>
                        <input type="text" class="form-control assignee-position" value="${escapeHtmlAttr(values.position || '')}" placeholder="Contoh: Bendahara DIPA">
                    </div>
                </div>
            `;

            card.querySelector('.assignee-remove').addEventListener('click', () => {
                card.remove();
                if (assigneeList.querySelectorAll('.assignee-card').length === 0) {
                    addAssigneeBlock();
                } else {
                    reindexAssigneeBlocks();
                }
            });

            assigneeList.appendChild(card);
            reindexAssigneeBlocks();
        }

        function reindexAssigneeBlocks() {
            const cards = assigneeList.querySelectorAll('.assignee-card');

            cards.forEach((card, index) => {
                const heading = card.querySelector('.assignee-card-header strong');
                const removeButton = card.querySelector('.assignee-remove');

                heading.textContent = `Petugas ${index + 1}`;
                removeButton.style.visibility = cards.length === 1 ? 'hidden' : 'visible';

                const nameInput = card.querySelector('.assignee-name');
                const nipInput = card.querySelector('.assignee-nip');
                const rankInput = card.querySelector('.assignee-rank');
                const positionInput = card.querySelector('.assignee-position');

                nameInput.name = `assignees[${index}][name]`;
                nipInput.name = `assignees[${index}][nip]`;
                rankInput.name = `assignees[${index}][rank]`;
                positionInput.name = `assignees[${index}][position]`;
            });
        }

        function closeModal() {
            modal.style.display = 'none';
            form.reset();
            resetFileUpload();
            setSubmittingState(submitButton, submitText, submitLoader, false);
        }

        function closeGeneratedModal() {
            generatedModal.style.display = 'none';
            resetGeneratedForm();
            setSubmittingState(generatedSubmitButton, generatedSubmitText, generatedSubmitLoader, false);
        }

        function closeDetailModal() {
            detailModal.style.display = 'none';
        }

        function closeNoteModal() {
            noteModal.style.display = 'none';
        }

        function resetFileUpload() {
            fileNameDisplay.textContent = 'Pilih file lampiran SPPD';
            fileUploadArea.classList.remove('has-file');
        }

        function resetGeneratedForm() {
            generatedForm.reset();
            assigneeList.innerHTML = '';
            addAssigneeBlock();
            document.getElementById('generated_duration_days').value = 1;
            document.getElementById('generated_city').value = 'Surakarta';
            document.getElementById('generated_signer_title').value = 'Plt. Kepala';
            document.getElementById('generated_signer_name').value = 'Sita Kurniasari';
        }

        function setSubmittingState(button, textElement, loaderElement, isSubmitting) {
            if (!button) {
                return;
            }

            button.disabled = isSubmitting;
            textElement.style.display = isSubmitting ? 'none' : 'inline';
            loaderElement.style.display = isSubmitting ? 'inline' : 'none';
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
                row.innerHTML = '<td colspan="8" class="empty-state">Belum ada data SPPD.</td>';
                tableBody.appendChild(row);
            }
        }

        function updateVisibleCount() {
            const visibleRows = Array.from(tableBody.querySelectorAll('tr[data-id]'))
                .filter((row) => row.style.display !== 'none').length;

            resultCount.textContent = `${visibleRows} data tampil`;
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

        function limitText(value, maxLength) {
            const text = String(value ?? '');
            return text.length > maxLength ? `${text.slice(0, maxLength)}...` : text;
        }

        function escapeHtml(value) {
            return String(value ?? '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#39;');
        }

        function escapeHtmlAttr(value) {
            return escapeHtml(value).replace(/`/g, '&#96;');
        }

        window.addEventListener('click', (event) => {
            if (event.target === modal) {
                closeModal();
            }

            if (event.target === generatedModal) {
                closeGeneratedModal();
            }

            if (event.target === detailModal) {
                closeDetailModal();
            }

            if (event.target === noteModal) {
                closeNoteModal();
            }
        });

        resetGeneratedForm();
    </script>
</body>
</html>
