<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Disposisi - MAN 2 Surakarta</title>
    <link rel="stylesheet" href="{{ asset('css/sidebar.css') }}">
    <link rel="stylesheet" href="{{ asset('css/modal.css') }}">
    <link rel="stylesheet" href="{{ asset('css/disposisi.css') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body>
    <div class="main-layout">
        @include('layouts.sidebar')

        <main class="main-content">
            <div class="top-bar">
                <div class="search-container">
                    <input
                        type="text"
                        class="search-input"
                        id="searchInput"
                        placeholder="Cari nama, tujuan surat, perihal, atau nomor surat..."
                    >
                </div>
                <div class="top-bar-note">
                    Perubahan status di halaman ini langsung tersimpan ke data surat asalnya.
                </div>
            </div>

            <section class="page-header">
                <div class="header-content">
                    <span class="eyebrow">LEMBAR DISPOSISI</span>
                    <h1>Disposisi Surat</h1>
                    <p>
                        Kelola disposisi surat masuk, surat keluar, dan SPPD dalam satu halaman.
                        Klik salah satu surat untuk membuka preview arsip dan ubah statusnya sesuai alur persetujuan.
                    </p>
                </div>
                <div class="workflow-panel">
                    <strong>Alur status:</strong>
                    <span>Draft -> Menunggu Persetujuan -> Revisi / Ditolak / Disetujui</span>
                </div>
            </section>

            <section class="disposition-section">
                <div class="section-toolbar">
                    <div class="tab-list" id="tabList">
                        <button class="tab-button active" type="button" data-tab="surat-masuk">
                            Surat Masuk
                            <span>{{ $counts['surat-masuk'] ?? 0 }}</span>
                        </button>
                        <button class="tab-button" type="button" data-tab="surat-keluar">
                            Surat Keluar
                            <span>{{ $counts['surat-keluar'] ?? 0 }}</span>
                        </button>
                        <button class="tab-button" type="button" data-tab="sppd">
                            SPPD
                            <span>{{ $counts['sppd'] ?? 0 }}</span>
                        </button>
                    </div>
                    <div class="toolbar-note">
                        <strong id="resultCount">{{ $counts['surat-masuk'] ?? 0 }}</strong> arsip tampil
                    </div>
                </div>

                <div class="list-head">
                    <span>Nama / Asal</span>
                    <span>Tujuan Surat</span>
                    <span>Tanggal</span>
                    <span>Status</span>
                    <span>Aksi</span>
                </div>

                <div class="disposition-list" id="dispositionList">
                    @forelse($items as $item)
                        <article
                            class="disposition-row"
                            data-key="{{ $item['key'] }}"
                            data-category="{{ $item['category'] }}"
                            data-search="{{ $item['search'] }}"
                        >
                            <div class="row-primary">
                                <span class="type-pill type-{{ $item['category'] }}">{{ $item['type_label'] }}</span>
                                <strong>{{ $item['primary_name'] }}</strong>
                                <small>{{ $item['letter_number'] }}</small>
                                <span class="row-note">{{ $item['notes_excerpt'] }}</span>
                            </div>
                            <div class="row-secondary">
                                <span class="mobile-label">Tujuan</span>
                                <p>{{ $item['destination'] }}</p>
                            </div>
                            <div class="row-date">
                                <span class="mobile-label">Tanggal</span>
                                <p>{{ $item['date_label'] }}</p>
                            </div>
                            <div class="row-status">
                                <span class="mobile-label">Status</span>
                                <span class="badge {{ $item['status_class'] }}">{{ $item['status_label'] }}</span>
                            </div>
                            <div class="row-action">
                                <button class="btn-open" type="button" onclick="openDisposition('{{ $item['key'] }}')">
                                    Buka Disposisi
                                </button>
                            </div>
                        </article>
                    @empty
                        <div class="empty-shell" id="serverEmptyState">
                            Belum ada arsip surat yang bisa didisposisikan.
                        </div>
                    @endforelse
                </div>

                <div class="empty-shell client-empty" id="clientEmptyState" hidden>
                    Tidak ada arsip yang cocok dengan tab atau pencarian saat ini.
                </div>
            </section>
        </main>
    </div>

    <div class="modal" id="dispositionModal">
        <div class="modal-content disposition-modal-content">
            <div class="modal-header">
                <div>
                    <h2 id="modalTitle">Detail Disposisi</h2>
                    <p class="modal-subtitle" id="modalSubtitle">Preview surat dan ubah status disposisi.</p>
                </div>
                <button class="modal-close" type="button" onclick="closeDispositionModal()">&times;</button>
            </div>

            <div class="modal-body disposition-modal-body">
                <div class="preview-panel" id="previewPanel"></div>

                <div class="detail-panel">
                    <div class="detail-summary">
                        <span class="type-pill" id="modalTypeLabel">Surat</span>
                        <span class="badge" id="modalStatusBadge">Draft</span>
                    </div>

                    <div class="detail-grid">
                        <div class="detail-item">
                            <span>Nama / Asal</span>
                            <strong id="modalPrimaryName">-</strong>
                        </div>
                        <div class="detail-item">
                            <span>Tujuan Surat</span>
                            <strong id="modalDestination">-</strong>
                        </div>
                        <div class="detail-item">
                            <span>Tanggal</span>
                            <strong id="modalDate">-</strong>
                        </div>
                        <div class="detail-item">
                            <span>Nomor / Perihal</span>
                            <strong id="modalLetterNumber">-</strong>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="statusSelect">Status Disposisi</label>
                        <select id="statusSelect" class="form-control">
                            @foreach($statusConfig as $statusKey => $statusMeta)
                                <option value="{{ $statusKey }}">{{ $statusMeta['label'] }}</option>
                            @endforeach
                        </select>
                        <small class="helper-text" id="statusDescription"></small>
                    </div>

                    <div class="form-group">
                        <label for="modalNotes">Catatan</label>
                        <textarea
                            id="modalNotes"
                            class="form-control notes-input"
                            rows="5"
                            placeholder="Tulis catatan disposisi untuk surat ini..."
                        ></textarea>
                    </div>

                    <div class="form-actions modal-actions">
                        <a href="#" id="modalFileLink" class="btn-secondary btn-link" target="_blank" rel="noopener">Buka File Asli</a>
                        <button type="button" class="btn-success" id="saveStatusButton" onclick="saveDispositionStatus()">
                            <span id="saveStatusText">Simpan Status</span>
                            <span id="saveStatusLoader" style="display: none;">Menyimpan...</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="{{ asset('js/request-progress.js') }}"></script>
    <script>
        const dispositionItems = @json($items);
        const statusConfig = @json($statusConfig);
        const dispositionMap = Object.fromEntries(dispositionItems.map((item) => [item.key, item]));
        const dispositionModal = document.getElementById('dispositionModal');
        const searchInput = document.getElementById('searchInput');
        const resultCount = document.getElementById('resultCount');
        const clientEmptyState = document.getElementById('clientEmptyState');
        const rowElements = Array.from(document.querySelectorAll('.disposition-row'));
        const statusSelect = document.getElementById('statusSelect');
        const statusDescription = document.getElementById('statusDescription');
        const modalStatusBadge = document.getElementById('modalStatusBadge');
        const modalFileLink = document.getElementById('modalFileLink');
        const saveStatusButton = document.getElementById('saveStatusButton');
        const saveStatusText = document.getElementById('saveStatusText');
        const saveStatusLoader = document.getElementById('saveStatusLoader');

        let activeTab = 'surat-masuk';
        let activeItemKey = null;

        document.querySelectorAll('.tab-button').forEach((button) => {
            button.addEventListener('click', () => {
                document.querySelectorAll('.tab-button').forEach((item) => item.classList.remove('active'));
                button.classList.add('active');
                activeTab = button.dataset.tab;
                filterRows();
            });
        });

        searchInput.addEventListener('input', filterRows);
        statusSelect.addEventListener('change', updateStatusDescription);

        function filterRows() {
            const query = searchInput.value.trim().toLowerCase();
            let visibleCount = 0;

            rowElements.forEach((row) => {
                const matchesTab = row.dataset.category === activeTab;
                const matchesSearch = row.dataset.search.includes(query);
                const isVisible = matchesTab && matchesSearch;

                row.hidden = !isVisible;

                if (isVisible) {
                    visibleCount += 1;
                }
            });

            resultCount.textContent = visibleCount;
            clientEmptyState.hidden = rowElements.length === 0 || visibleCount !== 0;
        }

        function openDisposition(key) {
            const item = dispositionMap[key];

            if (!item) {
                return;
            }

            activeItemKey = key;

            document.getElementById('modalTitle').textContent = item.primary_name;
            document.getElementById('modalSubtitle').textContent = `${item.type_label} - ${item.destination}`;
            document.getElementById('modalTypeLabel').textContent = item.type_label;
            document.getElementById('modalPrimaryName').textContent = item.primary_name;
            document.getElementById('modalDestination').textContent = item.destination;
            document.getElementById('modalDate').textContent = item.date_label;
            document.getElementById('modalLetterNumber').textContent = item.letter_number;
            document.getElementById('modalNotes').value = item.notes === '-' ? '' : item.notes;

            statusSelect.value = item.status;
            applyStatusBadge(item.status);
            updateStatusDescription();

            if (item.file_url) {
                modalFileLink.href = item.file_url;
                modalFileLink.style.display = 'inline-flex';
            } else {
                modalFileLink.removeAttribute('href');
                modalFileLink.style.display = 'none';
            }

            document.getElementById('previewPanel').innerHTML = buildPreviewMarkup(item);
            dispositionModal.style.display = 'flex';
        }

        async function saveDispositionStatus() {
            if (!activeItemKey) {
                return;
            }

            const item = dispositionMap[activeItemKey];
            const newStatus = statusSelect.value;
            const newNotes = document.getElementById('modalNotes').value.trim();

            try {
                setSavingState(true);

                const { response, result } = await RequestProgress.requestJson({
                    url: `/disposisi/${item.category}/${item.id}/status`,
                    method: 'POST',
                    data: { status: newStatus, notes: newNotes },
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    title: 'Menyimpan disposisi',
                    initialMessage: 'Mengirim perubahan status disposisi...',
                    processingMessage: 'Menyimpan status surat ke database...',
                    successMessage: 'Status disposisi berhasil diperbarui.'
                });

                if (!response.ok || !result.success) {
                    throw new Error(result.message || 'Gagal memperbarui status disposisi.');
                }

                item.status = result.data.status;
                item.status_label = result.data.status_label;
                item.status_class = result.data.status_class;
                item.status_description = result.data.status_description;
                item.notes = result.data.notes;
                item.notes_excerpt = truncateText(result.data.notes, 56);

                applyStatusBadge(item.status);
                updateRowContent(item);
                RequestProgress.showNotice(result.message, 'success');
            } catch (error) {
                alert(error.message || 'Terjadi kesalahan saat mengubah status disposisi.');
            } finally {
                setSavingState(false);
            }
        }

        function updateRowContent(item) {
            const row = document.querySelector(`.disposition-row[data-key="${item.key}"]`);

            if (!row) {
                return;
            }

            const badge = row.querySelector('.row-status .badge');
            badge.className = `badge ${item.status_class}`;
            badge.textContent = item.status_label;

            const note = row.querySelector('.row-note');
            if (note) {
                note.textContent = item.notes_excerpt;
            }
        }

        function applyStatusBadge(statusKey) {
            const meta = statusConfig[statusKey] || statusConfig.draft;
            modalStatusBadge.className = `badge ${meta.class}`;
            modalStatusBadge.textContent = meta.label;
        }

        function updateStatusDescription() {
            const meta = statusConfig[statusSelect.value] || statusConfig.draft;
            statusDescription.textContent = meta.description;
            applyStatusBadge(statusSelect.value);
        }

        function buildPreviewMarkup(item) {
            if (item.preview_url) {
                return `
                    <div class="preview-header">
                        <span>Preview Surat</span>
                        <small>${escapeHtml(item.file_name)}</small>
                    </div>
                    <iframe
                        src="${escapeHtml(item.preview_url)}"
                        class="preview-frame"
                        title="Preview ${escapeHtml(item.primary_name)}"
                    ></iframe>
                `;
            }

            return `
                <div class="preview-placeholder">
                    <strong>Preview belum tersedia</strong>
                    <p>File surat belum memiliki tautan preview. Gunakan tombol "Buka File Asli" jika arsip sudah diunggah.</p>
                    <span>${escapeHtml(item.file_name)}</span>
                </div>
            `;
        }

        function setSavingState(isSaving) {
            saveStatusButton.disabled = isSaving;
            saveStatusText.style.display = isSaving ? 'none' : 'inline';
            saveStatusLoader.style.display = isSaving ? 'inline' : 'none';
        }

        function closeDispositionModal() {
            dispositionModal.style.display = 'none';
            activeItemKey = null;
            setSavingState(false);
        }

        function escapeHtml(value) {
            return String(value ?? '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#39;');
        }

        function truncateText(value, maxLength) {
            const text = String(value ?? '').trim() || 'Belum ada catatan disposisi.';
            return text.length > maxLength ? `${text.slice(0, maxLength)}...` : text;
        }

        window.addEventListener('click', (event) => {
            if (event.target === dispositionModal) {
                closeDispositionModal();
            }
        });

        filterRows();
    </script>
</body>
</html>
