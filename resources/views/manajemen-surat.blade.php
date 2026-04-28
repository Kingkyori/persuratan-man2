<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Surat - MAN 2 Surakarta</title>
    <link rel="stylesheet" href="{{ asset('css/sidebar.css') }}">
    <link rel="stylesheet" href="{{ asset('css/modal.css') }}">
    <link rel="stylesheet" href="{{ asset('css/manajemen-surat.css') }}">
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
                        placeholder="Cari nama, asal, tujuan, perihal, nomor surat, atau catatan..."
                    >
                </div>
                <div class="top-bar-note">
                    Halaman ini dipakai untuk memantau semua arsip surat dan SPPD dalam satu tempat, lengkap dengan filter status dan tanggal.
                </div>
            </div>

            <section class="page-header">
                <div class="header-content">
                    <span class="eyebrow">PUSAT KONTROL ARSIP</span>
                    <h1>Manajemen Surat</h1>
                    <p>
                        Lihat seluruh surat masuk, surat keluar, dan SPPD dalam satu halaman. Gunakan filter tanggal,
                        status kepala sekolah, status departemen, dan urutan data untuk menemukan dokumen lebih cepat.
                    </p>
                </div>
                <div class="workflow-panel">
                    <strong>Filter utama:</strong>
                    <span>Tanggal, status kepsek, status departemen, urutan data, dan pencarian cepat.</span>
                </div>
            </section>

            <section class="stats-strip">
                <article class="mini-stat">
                    <span class="mini-label">Total Arsip</span>
                    <strong>{{ $stats['total'] ?? 0 }}</strong>
                </article>
                <article class="mini-stat">
                    <span class="mini-label">Disetujui</span>
                    <strong>{{ $stats['approved'] ?? 0 }}</strong>
                </article>
                <article class="mini-stat">
                    <span class="mini-label">Menunggu</span>
                    <strong>{{ $stats['pending'] ?? 0 }}</strong>
                </article>
                <article class="mini-stat">
                    <span class="mini-label">Dept. Terima</span>
                    <strong>{{ $stats['department_received'] ?? 0 }}</strong>
                </article>
            </section>

            <section class="management-section">
                <div class="section-toolbar">
                    <div class="tab-list" id="tabList">
                        <button class="tab-button active" type="button" data-tab="all">
                            Semua
                            <span>{{ $counts['all'] ?? 0 }}</span>
                        </button>
                        <button class="tab-button" type="button" data-tab="surat-masuk">
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
                        <strong id="resultCount">{{ $counts['all'] ?? 0 }}</strong> dokumen tampil
                    </div>
                </div>

                <div class="filter-panel">
                    <div class="filter-grid">
                        <div class="filter-group">
                            <label for="dateFrom">Tanggal Dari</label>
                            <input type="date" id="dateFrom" class="filter-control">
                        </div>
                        <div class="filter-group">
                            <label for="dateTo">Tanggal Sampai</label>
                            <input type="date" id="dateTo" class="filter-control">
                        </div>
                        <div class="filter-group">
                            <label for="headmasterStatusFilter">Status Kepsek</label>
                            <select id="headmasterStatusFilter" class="filter-control">
                                <option value="all">Semua Status</option>
                                @foreach($statusConfig as $statusKey => $statusMeta)
                                    <option value="{{ $statusKey }}">{{ $statusMeta['label'] }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="filter-group">
                            <label for="departmentStatusFilter">Status Departemen</label>
                            <select id="departmentStatusFilter" class="filter-control">
                                <option value="all">Semua Status</option>
                                <option value="pending">Belum Diterima</option>
                                <option value="received">Sudah Diterima</option>
                                <option value="not_applicable">Tidak Berlaku</option>
                            </select>
                        </div>
                        <div class="filter-group">
                            <label for="sortFilter">Urutkan</label>
                            <select id="sortFilter" class="filter-control">
                                <option value="updated_desc">Terakhir Diperbarui</option>
                                <option value="date_desc">Tanggal Terbaru</option>
                                <option value="date_asc">Tanggal Terlama</option>
                                <option value="updated_asc">Paling Lama Diperbarui</option>
                                <option value="alpha_asc">A-Z</option>
                                <option value="alpha_desc">Z-A</option>
                            </select>
                        </div>
                    </div>
                    <button type="button" class="filter-reset" id="resetFilters">Reset Filter</button>
                </div>

                <div class="list-head">
                    <span>Dokumen</span>
                    <span>Tujuan / Dept</span>
                    <span>Tanggal</span>
                    <span>Status Kepsek</span>
                    <span>Status Dept</span>
                    <span>Aksi</span>
                </div>

                <div class="management-list" id="managementList">
                    @forelse($items as $item)
                        <article
                            class="management-row"
                            data-key="{{ $item['key'] }}"
                            data-category="{{ $item['category'] }}"
                            data-search="{{ $item['search'] }}"
                            data-date-sort="{{ $item['date_sort'] }}"
                            data-updated-sort="{{ $item['updated_sort'] }}"
                            data-alpha="{{ strtolower($item['primary_name']) }}"
                            data-headmaster-status="{{ $item['headmaster_status'] }}"
                            data-department-status="{{ $item['department_status'] }}"
                        >
                            <div class="row-primary">
                                <span class="type-pill type-{{ $item['category'] }}">{{ $item['type_label'] }}</span>
                                <strong>{{ $item['primary_name'] }}</strong>
                                <small>{{ $item['number'] }}</small>
                                <span class="row-note">{{ $item['secondary_name'] }}</span>
                            </div>
                            <div class="row-secondary">
                                <span class="mobile-label">Tujuan / Dept</span>
                                <p>{{ $item['destination'] }}</p>
                                <small>{{ $item['updated_label'] }}</small>
                            </div>
                            <div class="row-date">
                                <span class="mobile-label">Tanggal</span>
                                <p>{{ $item['date_label'] }}</p>
                            </div>
                            <div class="row-status">
                                <span class="mobile-label">Status Kepsek</span>
                                <span class="badge {{ $item['headmaster_status_class'] }}">{{ $item['headmaster_status_label'] }}</span>
                            </div>
                            <div class="row-status">
                                <span class="mobile-label">Status Dept</span>
                                <span class="badge {{ $item['department_status_class'] }}">{{ $item['department_status_label'] }}</span>
                            </div>
                            <div class="row-action">
                                <button class="btn-open" type="button" onclick="openDetail('{{ $item['key'] }}')">
                                    Lihat Detail
                                </button>
                            </div>
                        </article>
                    @empty
                        <div class="empty-shell" id="serverEmptyState">
                            Belum ada dokumen yang bisa ditampilkan.
                        </div>
                    @endforelse
                </div>

                <div class="empty-shell client-empty" id="clientEmptyState" hidden>
                    Tidak ada dokumen yang cocok dengan filter saat ini.
                </div>
            </section>
        </main>
    </div>

    <div class="modal" id="detailModal">
        <div class="modal-content detail-modal-content">
            <div class="modal-header">
                <div>
                    <h2 id="modalTitle">Detail Dokumen</h2>
                    <p class="modal-subtitle" id="modalSubtitle">Ringkasan dokumen.</p>
                </div>
                <button class="modal-close" type="button" onclick="closeDetailModal()">&times;</button>
            </div>

            <div class="modal-body detail-modal-body">
                <div class="preview-panel" id="previewPanel"></div>

                <div class="detail-panel">
                    <div class="detail-summary">
                        <span class="type-pill" id="modalTypeLabel">Dokumen</span>
                        <span class="badge" id="modalHeadmasterBadge">Draft</span>
                    </div>

                    <div class="detail-grid">
                        <div class="detail-item">
                            <span>Nama / Asal / Tujuan</span>
                            <strong id="modalPrimaryName">-</strong>
                        </div>
                        <div class="detail-item">
                            <span>Perihal / Ringkasan</span>
                            <strong id="modalSecondaryName">-</strong>
                        </div>
                        <div class="detail-item">
                            <span>Tujuan / Dept</span>
                            <strong id="modalDestination">-</strong>
                        </div>
                        <div class="detail-item">
                            <span>Tanggal</span>
                            <strong id="modalDate">-</strong>
                        </div>
                        <div class="detail-item">
                            <span>Nomor Dokumen</span>
                            <strong id="modalNumber">-</strong>
                        </div>
                        <div class="detail-item">
                            <span>Status Departemen</span>
                            <strong id="modalDepartmentStatus">-</strong>
                        </div>
                    </div>

                    <div class="notes-box">
                        <span>Catatan Kepala Sekolah / Disposisi</span>
                        <p id="modalNotes">-</p>
                    </div>

                    <div class="notes-box">
                        <span>Catatan Departemen</span>
                        <p id="modalDepartmentNotes">-</p>
                    </div>

                    <div class="form-actions modal-actions">
                        <a href="#" id="modalSourceLink" class="btn-secondary btn-link">Buka Halaman Asal</a>
                        <a href="#" id="modalFileLink" class="btn-secondary btn-link" target="_blank" rel="noopener">Buka File Asli</a>
                        <a href="#" id="modalShareLink" class="btn-secondary btn-link" target="_blank" rel="noopener" hidden>Portal Departemen</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        const items = @json($items);
        const statusConfig = @json($statusConfig);
        const itemMap = Object.fromEntries(items.map((item) => [item.key, item]));
        const listElement = document.getElementById('managementList');
        const rowElements = Array.from(document.querySelectorAll('.management-row'));
        const clientEmptyState = document.getElementById('clientEmptyState');
        const resultCount = document.getElementById('resultCount');
        const detailModal = document.getElementById('detailModal');
        const searchInput = document.getElementById('searchInput');
        const dateFromInput = document.getElementById('dateFrom');
        const dateToInput = document.getElementById('dateTo');
        const headmasterStatusFilter = document.getElementById('headmasterStatusFilter');
        const departmentStatusFilter = document.getElementById('departmentStatusFilter');
        const sortFilter = document.getElementById('sortFilter');
        const resetFiltersButton = document.getElementById('resetFilters');

        let activeTab = 'all';

        document.querySelectorAll('.tab-button').forEach((button) => {
            button.addEventListener('click', () => {
                document.querySelectorAll('.tab-button').forEach((item) => item.classList.remove('active'));
                button.classList.add('active');
                activeTab = button.dataset.tab;
                filterRows();
            });
        });

        [searchInput, dateFromInput, dateToInput, headmasterStatusFilter, departmentStatusFilter, sortFilter]
            .forEach((element) => element.addEventListener('input', filterRows));

        resetFiltersButton.addEventListener('click', () => {
            searchInput.value = '';
            dateFromInput.value = '';
            dateToInput.value = '';
            headmasterStatusFilter.value = 'all';
            departmentStatusFilter.value = 'all';
            sortFilter.value = 'updated_desc';
            activeTab = 'all';
            document.querySelectorAll('.tab-button').forEach((button) => {
                button.classList.toggle('active', button.dataset.tab === 'all');
            });
            filterRows();
        });

        function filterRows() {
            const query = searchInput.value.trim().toLowerCase();
            const dateFrom = dateFromInput.value;
            const dateTo = dateToInput.value;
            const headmasterStatus = headmasterStatusFilter.value;
            const departmentStatus = departmentStatusFilter.value;
            const sortBy = sortFilter.value;

            const rowsWithMeta = rowElements.map((row) => {
                const matchesTab = activeTab === 'all' || row.dataset.category === activeTab;
                const matchesSearch = row.dataset.search.includes(query);
                const matchesHeadmaster = headmasterStatus === 'all' || row.dataset.headmasterStatus === headmasterStatus;
                const matchesDepartment = departmentStatus === 'all' || row.dataset.departmentStatus === departmentStatus;
                const matchesDateFrom = !dateFrom || row.dataset.dateSort >= dateFrom;
                const matchesDateTo = !dateTo || row.dataset.dateSort <= dateTo;
                const isVisible = matchesTab && matchesSearch && matchesHeadmaster && matchesDepartment && matchesDateFrom && matchesDateTo;

                row.hidden = !isVisible;

                return {
                    row,
                    visible: isVisible,
                    dateSort: row.dataset.dateSort,
                    updatedSort: Number(row.dataset.updatedSort || 0),
                    alpha: row.dataset.alpha || '',
                };
            });

            sortVisibleRows(rowsWithMeta.filter((item) => item.visible), sortBy);

            const visibleCount = rowsWithMeta.filter((item) => item.visible).length;
            resultCount.textContent = visibleCount;
            clientEmptyState.hidden = rowElements.length === 0 || visibleCount !== 0;
        }

        function sortVisibleRows(visibleRows, sortBy) {
            const compare = {
                updated_desc: (a, b) => b.updatedSort - a.updatedSort,
                updated_asc: (a, b) => a.updatedSort - b.updatedSort,
                date_desc: (a, b) => String(b.dateSort).localeCompare(String(a.dateSort)),
                date_asc: (a, b) => String(a.dateSort).localeCompare(String(b.dateSort)),
                alpha_asc: (a, b) => String(a.alpha).localeCompare(String(b.alpha)),
                alpha_desc: (a, b) => String(b.alpha).localeCompare(String(a.alpha)),
            }[sortBy] || ((a, b) => b.updatedSort - a.updatedSort);

            visibleRows
                .sort(compare)
                .forEach(({ row }) => listElement.appendChild(row));
        }

        function openDetail(key) {
            const item = itemMap[key];

            if (!item) {
                return;
            }

            document.getElementById('modalTitle').textContent = item.primary_name;
            document.getElementById('modalSubtitle').textContent = `${item.type_label} - ${item.secondary_name}`;
            document.getElementById('modalTypeLabel').textContent = item.type_label;
            document.getElementById('modalTypeLabel').className = `type-pill type-${item.category}`;
            document.getElementById('modalPrimaryName').textContent = item.primary_name;
            document.getElementById('modalSecondaryName').textContent = item.secondary_name || '-';
            document.getElementById('modalDestination').textContent = item.destination || '-';
            document.getElementById('modalDate').textContent = item.date_label || '-';
            document.getElementById('modalNumber').textContent = item.number || '-';
            document.getElementById('modalDepartmentStatus').textContent = item.department_status_label || '-';
            document.getElementById('modalNotes').textContent = item.notes || '-';
            document.getElementById('modalDepartmentNotes').textContent = item.department_notes || '-';

            const headmasterMeta = statusConfig[item.headmaster_status] || statusConfig.draft;
            const headmasterBadge = document.getElementById('modalHeadmasterBadge');
            headmasterBadge.className = `badge ${headmasterMeta.class}`;
            headmasterBadge.textContent = headmasterMeta.label;

            const modalSourceLink = document.getElementById('modalSourceLink');
            modalSourceLink.href = item.source_url || '#';
            modalSourceLink.textContent = item.source_label || 'Buka Halaman Asal';

            const modalFileLink = document.getElementById('modalFileLink');
            if (item.file_url) {
                modalFileLink.href = item.file_url;
                modalFileLink.style.display = 'inline-flex';
            } else {
                modalFileLink.removeAttribute('href');
                modalFileLink.style.display = 'none';
            }

            const modalShareLink = document.getElementById('modalShareLink');
            if (item.share_url) {
                modalShareLink.href = item.share_url;
                modalShareLink.hidden = false;
                modalShareLink.style.display = 'inline-flex';
            } else {
                modalShareLink.hidden = true;
                modalShareLink.style.display = 'none';
                modalShareLink.removeAttribute('href');
            }

            document.getElementById('previewPanel').innerHTML = buildPreviewMarkup(item);
            detailModal.style.display = 'flex';
        }

        function buildPreviewMarkup(item) {
            if (item.preview_url) {
                return `
                    <div class="preview-header">
                        <span>Preview Dokumen</span>
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
                    <p>File belum memiliki tautan preview. Gunakan tombol buka file asli untuk melihat dokumen.</p>
                    <span>${escapeHtml(item.file_name)}</span>
                </div>
            `;
        }

        function closeDetailModal() {
            detailModal.style.display = 'none';
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
            if (event.target === detailModal) {
                closeDetailModal();
            }
        });

        filterRows();
    </script>
</body>
</html>
