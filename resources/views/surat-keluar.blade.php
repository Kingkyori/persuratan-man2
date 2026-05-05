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
        $suratKeluar = collect($surat_keluar ?? []);
        $outgoingStats = [
            'total' => $suratKeluar->count(),
            'generated' => $suratKeluar->where('entry_type', 'generated_letter')->count(),
            'upload' => $suratKeluar->filter(fn ($item) => ($item->entry_type ?: 'upload') === 'upload')->count(),
            'shared' => $suratKeluar->filter(fn ($item) => filled($item->share_token))->count(),
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
                    Arsip surat keluar tersimpan di database. Tombol share menyalin tautan publik untuk penerima surat.
                </div>
            </div>

            <div class="page-header">
                <div class="header-content">
                    <h1>Surat Keluar</h1>
                    <p>
                        Catat surat keluar resmi, buat konsep surat otomatis, simpan arsip ke database,
                        dan bagikan tautan file ke penerima.
                    </p>
                </div>
                <div class="header-actions">
                    <button class="btn-secondary btn-toolbar" id="btnOpenLetterTypeModal" type="button">+ Buat Surat</button>
                    <button class="btn-primary btn-toolbar" id="btnOpenModal" type="button">+ Tambah Surat Keluar</button>
                </div>
            </div>

            <section class="stats-strip">
                <article class="mini-stat">
                    <span class="mini-label">Total Arsip</span>
                    <strong>{{ $outgoingStats['total'] }}</strong>
                </article>
                <article class="mini-stat">
                    <span class="mini-label">Dibuat Sistem</span>
                    <strong>{{ $outgoingStats['generated'] }}</strong>
                </article>
                <article class="mini-stat">
                    <span class="mini-label">Upload Manual</span>
                    <strong>{{ $outgoingStats['upload'] }}</strong>
                </article>
                <article class="mini-stat">
                    <span class="mini-label">Link Tersedia</span>
                    <strong>{{ $outgoingStats['shared'] }}</strong>
                </article>
            </section>

            <section class="archive-section">
                <div class="section-header">
                    <div>
                        <h2>Daftar Arsip Surat Keluar</h2>
                        <p class="section-description">
                            Data yang disimpan meliputi tanggal surat, tujuan, perihal, nomor surat, file arsip, dan tautan share untuk penerima.
                        </p>
                    </div>
                    <div class="workflow-note">
                        <strong>Alur kerja:</strong> Buat / upload surat -> Arsip tersimpan -> Bagikan link ke penerima
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
                                <th>Share Link</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="tableBody">
                            @forelse($suratKeluar as $surat)
                                @php
                                    $docxUrl = $surat->generated_docx_path ? route('surat-keluar.downloadDocx', $surat->id) : '';
                                    $shareUrl = $surat->share_token ? route('surat-keluar.share', $surat->share_token) : '';
                                    $recordTypeLabel = $surat->document_type
                                        ? ($generatedLetterTypes[$surat->document_type]['label'] ?? 'Surat Otomatis')
                                        : 'Upload Surat Keluar';
                                @endphp
                                <tr
                                    data-id="{{ $surat->id }}"
                                    data-search="{{ strtolower($surat->destination . ' ' . $surat->subject . ' ' . $surat->letter_number) }}"
                                    data-tanggal="{{ optional($surat->letter_date)->format('d M Y') }}"
                                    data-tujuan="{{ $surat->destination }}"
                                    data-perihal="{{ $surat->subject }}"
                                    data-nomor="{{ $surat->letter_number }}"
                                    data-file="{{ $surat->file_name }}"
                                    data-catatan="{{ $surat->notes ?: '-' }}"
                                    data-link="{{ route('archive.open', ['type' => 'surat-keluar', 'id' => $surat->id]) }}"
                                    data-download-link="{{ route('archive.download', ['type' => 'surat-keluar', 'id' => $surat->id]) }}"
                                    data-docx-link="{{ $docxUrl }}"
                                    data-share-url="{{ $shareUrl }}"
                                    data-record-type="{{ $recordTypeLabel }}"
                                >
                                    <td>{{ optional($surat->letter_date)->format('d M Y') }}</td>
                                    <td>{{ $surat->destination }}</td>
                                    <td>{{ $surat->subject }}</td>
                                    <td>{{ $surat->letter_number }}</td>
                                    <td>
                                        <span class="file-pill">{{ $surat->file_name }}</span>
                                    </td>
                                    <td>
                                        <button
                                            class="icon-button share-button"
                                            type="button"
                                            onclick="copyShareLink('{{ $surat->id }}')"
                                            title="Salin link surat keluar"
                                        >
                                            <svg viewBox="0 0 24 24" aria-hidden="true">
                                                <path d="M15 8a3 3 0 0 1 0 6h-1v-2h1a1 1 0 0 0 0-2h-4a1 1 0 0 0 0 2h1v2h-1a3 3 0 1 1 0-6h4Zm-7 3h2v2H8a4 4 0 0 1 0-8h4v2H8a2 2 0 1 0 0 4Zm8 0h-2V9h2a4 4 0 1 1 0 8h-4v-2h4a2 2 0 0 0 0-4Z"></path>
                                            </svg>
                                        </button>
                                    </td>
                                    <td class="action-cell">
                                        <a href="{{ route('archive.open', ['type' => 'surat-keluar', 'id' => $surat->id]) }}" target="_blank" class="btn-small" rel="noopener">File</a>
                                        @if($docxUrl)
                                            <a href="{{ $docxUrl }}" class="btn-small" rel="noopener">DOCX</a>
                                        @endif
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
                    Nomor surat untuk upload manual masih diisi manual. Setelah tersimpan, sistem membuat link share yang bisa dibagikan ke penerima.
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
                    <div class="form-group form-group-full">
                        <label for="letter_number">Nomor Surat <span class="required">*</span></label>
                        <input type="text" id="letter_number" name="letter_number" class="form-control" required placeholder="Isi manual untuk sementara">
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

    <div class="modal" id="modalLetterType">
        <div class="modal-content note-modal-content">
            <div class="modal-header">
                <div>
                    <h2>Pilih Jenis Surat</h2>
                    <p class="modal-subtitle">Jenis surat menentukan form berikutnya dan kode nomor surat otomatis.</p>
                </div>
                <button class="modal-close" id="btnCloseLetterTypeModal" type="button">&times;</button>
            </div>
            <form id="formLetterType" class="register-form">
                <div class="form-group">
                    <label for="letterTypeSelect">Jenis Surat <span class="required">*</span></label>
                    <select id="letterTypeSelect" class="form-control" required>
                        @foreach($generatedLetterTypes as $typeKey => $typeMeta)
                            <option value="{{ $typeKey }}">{{ $typeMeta['label'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="letter-number-preview">
                    <span>Nomor otomatis berikutnya</span>
                    <strong id="letterTypeNumberPreview">{{ $nextLetterNumbers['undangan'] ?? '-' }}</strong>
                </div>
                <div class="form-actions">
                    <button type="button" class="btn-secondary" onclick="closeLetterTypeModal()">Batal</button>
                    <button type="submit" class="btn-success">Selanjutnya</button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal" id="modalGeneratedLetter">
        <div class="modal-content modal-content-wide">
            <div class="modal-header">
                <div>
                    <h2 id="generatedLetterTitle">Buat Surat Keluar</h2>
                    <p class="modal-subtitle">Nomor surat dibuat otomatis oleh sistem. PDF, DOCX, dan link share akan masuk ke arsip surat keluar.</p>
                </div>
                <button class="modal-close" id="btnCloseGeneratedLetterModal" type="button">&times;</button>
            </div>

            <form id="formGeneratedLetter" class="register-form">
                <input type="hidden" name="document_type" id="generated_document_type">

                <div class="generated-letter-summary">
                    <div>
                        <span>Jenis Surat</span>
                        <strong id="generatedLetterTypeLabel">Surat Undangan</strong>
                    </div>
                    <div>
                        <span>Nomor Surat</span>
                        <strong id="generatedLetterNumber">-</strong>
                    </div>
                </div>

                <div class="form-section-title">Informasi Surat</div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="generated_document_date">Tanggal Surat <span class="required">*</span></label>
                        <input type="date" id="generated_document_date" name="document_date" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="generated_subject">Perihal</label>
                        <input type="text" id="generated_subject" name="subject" class="form-control" placeholder="Terisi otomatis bila dikosongkan">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="generated_recipient_name">Tujuan / Penerima <span class="required">*</span></label>
                        <input type="text" id="generated_recipient_name" name="recipient_name" class="form-control" required placeholder="Contoh: Bapak/Ibu Orang Tua/Wali Siswa">
                    </div>
                    <div class="form-group">
                        <label for="generated_city">Kota Surat <span class="required">*</span></label>
                        <input type="text" id="generated_city" name="city" class="form-control" required value="Surakarta">
                    </div>
                </div>

                <div class="form-group">
                    <label for="generated_recipient_address">Alamat / Tempat Tujuan</label>
                    <textarea id="generated_recipient_address" name="recipient_address" class="form-control" rows="2" placeholder="Contoh: Di Tempat"></textarea>
                </div>

                <div class="form-section-title">Isi Surat</div>
                <div id="generatedDynamicFields"></div>

                <div class="form-section-title">Penandatangan dan Arsip</div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="generated_signer_title">Jabatan Penandatangan <span class="required">*</span></label>
                        <input type="text" id="generated_signer_title" name="signer_title" class="form-control" required value="Kepala MAN 2 Surakarta">
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
                        <label for="generated_notes">Catatan Arsip</label>
                        <input type="text" id="generated_notes" name="notes" class="form-control" placeholder="Opsional">
                    </div>
                </div>

                <div class="form-actions">
                    <button type="button" class="btn-secondary" onclick="backToLetterTypeModal()">Kembali</button>
                    <button type="submit" class="btn-success" id="btnGeneratedLetterSubmit">
                        <span id="generatedLetterSubmitText">Simpan dan Buat Dokumen</span>
                        <span id="generatedLetterSubmitLoader" style="display: none;">Membuat...</span>
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

    <script src="{{ asset('js/request-progress.js') }}"></script>
    <script>
        const generatedLetterTypes = @json($generatedLetterTypes);
        const nextLetterNumbers = @json($nextLetterNumbers);
        const modal = document.getElementById('modalRegister');
        const letterTypeModal = document.getElementById('modalLetterType');
        const generatedLetterModal = document.getElementById('modalGeneratedLetter');
        const detailModal = document.getElementById('modalDetail');
        const form = document.getElementById('formSuratKeluar');
        const letterTypeForm = document.getElementById('formLetterType');
        const generatedLetterForm = document.getElementById('formGeneratedLetter');
        const letterTypeSelect = document.getElementById('letterTypeSelect');
        const generatedDynamicFields = document.getElementById('generatedDynamicFields');
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
        document.getElementById('btnOpenLetterTypeModal').addEventListener('click', () => {
            updateLetterTypeNumberPreview();
            letterTypeModal.style.display = 'flex';
        });
        document.getElementById('btnCloseLetterTypeModal').addEventListener('click', closeLetterTypeModal);
        document.getElementById('btnCloseGeneratedLetterModal').addEventListener('click', closeGeneratedLetterModal);
        letterTypeSelect.addEventListener('change', updateLetterTypeNumberPreview);
        letterTypeForm.addEventListener('submit', (event) => {
            event.preventDefault();
            openGeneratedLetterModal(letterTypeSelect.value);
        });

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

        generatedLetterForm.addEventListener('submit', async (event) => {
            event.preventDefault();

            const submitBtn = document.getElementById('btnGeneratedLetterSubmit');
            const loader = document.getElementById('generatedLetterSubmitLoader');
            const btnText = document.getElementById('generatedLetterSubmitText');
            const formData = new FormData(generatedLetterForm);

            try {
                submitBtn.disabled = true;
                loader.style.display = 'inline';
                btnText.style.display = 'none';

                const { response, result } = await RequestProgress.requestJson({
                    url: '{{ route("surat-keluar.storeGenerated") }}',
                    method: 'POST',
                    data: formData,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    title: 'Membuat surat keluar',
                    initialMessage: 'Menyiapkan konsep surat...',
                    processingMessage: 'Membuat file PDF dan DOCX lalu menyimpan arsip...',
                    successMessage: 'Surat keluar berhasil dibuat.'
                });

                if (!response.ok || !result.success) {
                    throw new Error(result.message || 'Gagal membuat surat keluar.');
                }

                prependRow(result.data);
                closeGeneratedLetterModal();
                updateVisibleCount();
                RequestProgress.showNotice(result.message, 'success');
            } catch (error) {
                alert(error.message || 'Terjadi kesalahan saat membuat surat keluar.');
            } finally {
                submitBtn.disabled = false;
                loader.style.display = 'none';
                btnText.style.display = 'inline';
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

            const row = document.createElement('tr');

            row.dataset.id = data.id;
            row.dataset.search = `${data.tujuan} ${data.perihal} ${data.nomor}`.toLowerCase();
            row.dataset.tanggal = data.tanggal;
            row.dataset.tujuan = data.tujuan;
            row.dataset.perihal = data.perihal;
            row.dataset.nomor = data.nomor;
            row.dataset.file = data.file;
            row.dataset.catatan = data.catatan || '-';
            row.dataset.link = data.google_drive_link || '';
            row.dataset.downloadLink = data.download_url || '';
            row.dataset.docxLink = data.docx_download_url || '';
            row.dataset.shareUrl = data.share_url || '';
            row.dataset.recordType = data.document_type_label || 'Upload Surat Keluar';

            const driveButton = `<a href="${escapeHtml(data.google_drive_link)}" target="_blank" class="btn-small" rel="noopener">File</a>`;
            const docxButton = data.docx_download_url
                ? `<a href="${escapeHtml(data.docx_download_url)}" class="btn-small" rel="noopener">DOCX</a>`
                : '';

            row.innerHTML = `
                <td>${escapeHtml(data.tanggal)}</td>
                <td>${escapeHtml(data.tujuan)}</td>
                <td>${escapeHtml(data.perihal)}</td>
                <td>${escapeHtml(data.nomor)}</td>
                <td><span class="file-pill">${escapeHtml(data.file)}</span></td>
                <td>
                    <button class="icon-button share-button" type="button" onclick="copyShareLink('${data.id}')" title="Salin link surat keluar">
                        <svg viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M15 8a3 3 0 0 1 0 6h-1v-2h1a1 1 0 0 0 0-2h-4a1 1 0 0 0 0 2h1v2h-1a3 3 0 1 1 0-6h4Zm-7 3h2v2H8a4 4 0 0 1 0-8h4v2H8a2 2 0 1 0 0 4Zm8 0h-2V9h2a4 4 0 1 1 0 8h-4v-2h4a2 2 0 0 0 0-4Z"></path>
                        </svg>
                    </button>
                </td>
                <td class="action-cell">${driveButton}${docxButton}<button class="btn-small" type="button" onclick="viewDetail('${data.id}')">Detail</button><button class="btn-small btn-danger" type="button" onclick="deleteSuratKeluar('${data.id}')">Hapus</button></td>
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
            const downloadLink = row.dataset.downloadLink
                ? `<p><strong>Download:</strong> <a href="${escapeHtml(row.dataset.downloadLink)}" target="_blank" rel="noopener">Download file</a></p>`
                : '';
            const docxLink = row.dataset.docxLink
                ? `<p><strong>File DOCX:</strong> <a href="${escapeHtml(row.dataset.docxLink)}" rel="noopener">Unduh DOCX</a></p>`
                : '';
            const shareLink = row.dataset.shareUrl
                ? `<p><strong>Share Link:</strong> <button class="btn-small" type="button" onclick="copyShareLink('${id}')">Salin link</button></p>`
                : '';

            document.getElementById('detailContent').innerHTML = `
                <p><strong>Jenis Arsip:</strong> ${escapeHtml(row.dataset.recordType || 'Surat Keluar')}</p>
                <p><strong>Tanggal Surat:</strong> ${escapeHtml(row.dataset.tanggal)}</p>
                <p><strong>Tujuan:</strong> ${escapeHtml(row.dataset.tujuan)}</p>
                <p><strong>Perihal:</strong> ${escapeHtml(row.dataset.perihal)}</p>
                <p><strong>Nomor Surat:</strong> ${escapeHtml(row.dataset.nomor)}</p>
                <p><strong>File:</strong> ${escapeHtml(row.dataset.file)}</p>
                <p><strong>Catatan:</strong> ${escapeHtml(normalizeNote(row.dataset.catatan))}</p>
                ${driveLink}
                ${downloadLink}
                ${docxLink}
                ${shareLink}
            `;

            detailModal.style.display = 'flex';
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

                RequestProgress.showNotice('Link surat keluar berhasil disalin.', 'success');
            } catch (error) {
                fallbackCopyText(shareUrl);
                RequestProgress.showNotice('Link surat keluar berhasil disalin.', 'success');
            }
        }

        function closeModal() {
            modal.style.display = 'none';
            form.reset();
            resetFileUpload();
            setSubmittingState(false);
        }

        function closeLetterTypeModal() {
            letterTypeModal.style.display = 'none';
        }

        function closeGeneratedLetterModal() {
            generatedLetterModal.style.display = 'none';
            resetGeneratedLetterForm();
        }

        function backToLetterTypeModal() {
            generatedLetterModal.style.display = 'none';
            letterTypeModal.style.display = 'flex';
        }

        function closeDetailModal() {
            detailModal.style.display = 'none';
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

        function openGeneratedLetterModal(type) {
            const meta = generatedLetterTypes[type] || generatedLetterTypes.undangan;

            closeLetterTypeModal();
            resetGeneratedLetterForm();
            document.getElementById('generated_document_type').value = type;
            document.getElementById('generatedLetterTitle').textContent = `Buat ${meta.label}`;
            document.getElementById('generatedLetterTypeLabel').textContent = meta.label;
            document.getElementById('generated_subject').placeholder = `Contoh: ${meta.subject_prefix}`;
            document.getElementById('generated_subject').value = meta.subject_prefix;
            document.getElementById('generated_document_date').value = todayDate();
            renderGeneratedFields(type);
            updateGeneratedLetterNumber();
            generatedLetterModal.style.display = 'flex';
        }

        function resetGeneratedLetterForm() {
            generatedLetterForm.reset();
            generatedDynamicFields.innerHTML = '';
            document.getElementById('generated_city').value = 'Surakarta';
            document.getElementById('generated_signer_title').value = 'Kepala MAN 2 Surakarta';
            document.getElementById('generated_signer_name').value = 'Sita Kurniasari';
        }

        function updateLetterTypeNumberPreview() {
            const type = letterTypeSelect.value || 'undangan';
            document.getElementById('letterTypeNumberPreview').textContent = nextLetterNumbers[type] || '-';
        }

        function updateGeneratedLetterNumber() {
            const type = document.getElementById('generated_document_type').value || 'undangan';
            const dateValue = document.getElementById('generated_document_date').value || todayDate();
            const date = new Date(`${dateValue}T00:00:00`);
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const year = String(date.getFullYear());
            const sequence = String((nextLetterNumbers[type] || '001').split('/')[0] || '001').padStart(3, '0');
            const code = generatedLetterTypes[type]?.code || 'HM.01';

            document.getElementById('generatedLetterNumber').textContent = `${sequence}/Ma.11.31.02/${code}/${month}/${year}`;
        }

        document.getElementById('generated_document_date').addEventListener('change', updateGeneratedLetterNumber);

        function renderGeneratedFields(type) {
            const template = {
                undangan: `
                    <div class="form-row">
                        <div class="form-group form-group-full">
                            <label>Agenda Undangan <span class="required">*</span></label>
                            <input type="text" name="agenda" class="form-control" required placeholder="Contoh: Rapat wali murid kelas X">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Tanggal Kegiatan <span class="required">*</span></label>
                            <input type="date" name="activity_date" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Waktu Mulai <span class="required">*</span></label>
                            <input type="time" name="activity_time" class="form-control" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Tempat Kegiatan <span class="required">*</span></label>
                        <input type="text" name="activity_place" class="form-control" required placeholder="Contoh: Aula MAN 2 Surakarta">
                    </div>
                `,
                keterangan: `
                    <div class="form-row">
                        <div class="form-group">
                            <label>Nama yang Diterangkan <span class="required">*</span></label>
                            <input type="text" name="described_person" class="form-control" required placeholder="Nama siswa/pegawai/pihak terkait">
                        </div>
                        <div class="form-group">
                            <label>Identitas</label>
                            <input type="text" name="person_identifier" class="form-control" placeholder="Contoh: NISN / NIP / Jabatan">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Isi Keterangan <span class="required">*</span></label>
                        <textarea name="statement" class="form-control" rows="4" required placeholder="Tuliskan keterangan yang akan dimuat dalam surat"></textarea>
                    </div>
                `,
                panggilan: `
                    <div class="form-row">
                        <div class="form-group">
                            <label>Nama yang Dipanggil <span class="required">*</span></label>
                            <input type="text" name="called_person" class="form-control" required placeholder="Nama penerima panggilan">
                        </div>
                        <div class="form-group">
                            <label>Keperluan <span class="required">*</span></label>
                            <input type="text" name="call_reason" class="form-control" required placeholder="Contoh: Klarifikasi administrasi">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Tanggal Hadir <span class="required">*</span></label>
                            <input type="date" name="call_date" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Waktu Hadir <span class="required">*</span></label>
                            <input type="time" name="call_time" class="form-control" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Tempat Hadir <span class="required">*</span></label>
                        <input type="text" name="call_place" class="form-control" required placeholder="Contoh: Ruang Tata Usaha MAN 2 Surakarta">
                    </div>
                `,
                perjanjian: `
                    <div class="form-row">
                        <div class="form-group">
                            <label>Pihak Pertama <span class="required">*</span></label>
                            <input type="text" name="first_party" class="form-control" required placeholder="Contoh: MAN 2 Surakarta">
                        </div>
                        <div class="form-group">
                            <label>Pihak Kedua <span class="required">*</span></label>
                            <input type="text" name="second_party" class="form-control" required placeholder="Nama pihak kedua">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Objek Perjanjian <span class="required">*</span></label>
                        <input type="text" name="agreement_subject" class="form-control" required placeholder="Contoh: Kerja sama kegiatan madrasah">
                    </div>
                    <div class="form-group">
                        <label>Pokok Kesepakatan <span class="required">*</span></label>
                        <textarea name="agreement_points" class="form-control" rows="5" required placeholder="Tulis tiap poin pada baris baru"></textarea>
                    </div>
                `,
                izin: `
                    <div class="form-row">
                        <div class="form-group">
                            <label>Nama yang Diberi Izin <span class="required">*</span></label>
                            <input type="text" name="permitted_person" class="form-control" required placeholder="Nama siswa/pegawai/pihak terkait">
                        </div>
                        <div class="form-group">
                            <label>Kegiatan / Keperluan <span class="required">*</span></label>
                            <input type="text" name="permission_activity" class="form-control" required placeholder="Contoh: Mengikuti lomba tingkat kota">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Tanggal Mulai <span class="required">*</span></label>
                            <input type="date" name="permission_start_date" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Tanggal Selesai <span class="required">*</span></label>
                            <input type="date" name="permission_end_date" class="form-control" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Tempat <span class="required">*</span></label>
                        <input type="text" name="permission_place" class="form-control" required placeholder="Contoh: Gedung Pemuda Surakarta">
                    </div>
                `
            };

            generatedDynamicFields.innerHTML = template[type] || template.undangan;
        }

        function todayDate() {
            return new Date().toISOString().slice(0, 10);
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

        window.addEventListener('click', (event) => {
            if (event.target === modal) {
                closeModal();
            }

            if (event.target === letterTypeModal) {
                closeLetterTypeModal();
            }

            if (event.target === generatedLetterModal) {
                closeGeneratedLetterModal();
            }

            if (event.target === detailModal) {
                closeDetailModal();
            }
        });
    </script>
</body>
</html>
