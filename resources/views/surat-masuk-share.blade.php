<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Konfirmasi Surat Masuk - MAN 2 Surakarta</title>
    <link rel="stylesheet" href="{{ asset('css/surat-masuk-share.css') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body>
    <main class="share-shell">
        <section class="share-hero">
            <span class="eyebrow">PORTAL DEPARTEMEN</span>
            <h1>Konfirmasi Penerimaan Surat</h1>
            <p>Halaman ini dipakai departemen tujuan untuk melihat rincian surat masuk, membuka file asli, mengunduh arsip, lalu memperbarui status penerimaan beserta catatan untuk admin.</p>
        </section>

        <section class="share-card">
            <div class="share-card-head">
                <div>
                    <h2>{{ $surat->origin }}</h2>
                    <p>Tujuan departemen: {{ $surat->department_destination ?: '-' }}</p>
                </div>
                <div class="status-group">
                    <span class="status-caption">Status Kepala Sekolah</span>
                    <span class="badge {{ $statusMeta['class'] }}">{{ $statusMeta['label'] }}</span>
                </div>
            </div>

            <div class="share-layout">
                <div class="preview-panel">
                    @if($previewUrl)
                        <div class="preview-head">
                            <span>Preview Arsip</span>
                            <small>{{ $surat->file_name ?: 'File surat' }}</small>
                        </div>
                        <iframe src="{{ $previewUrl }}" class="preview-frame" title="Preview surat"></iframe>
                    @else
                        <div class="preview-placeholder">
                            <strong>Preview belum tersedia</strong>
                            <p>File belum memiliki tautan preview. Silakan gunakan tombol buka atau download di sisi kanan.</p>
                        </div>
                    @endif
                </div>

                <div class="detail-panel">
                    <div class="department-current-status">
                        <span>Status Departemen Saat Ini</span>
                        <strong id="currentDepartmentStatusBadge" class="badge {{ $departmentStatusMeta['class'] }}">{{ $departmentStatusMeta['label'] }}</strong>
                    </div>

                    <div class="detail-grid">
                        <div class="detail-item">
                            <span>Asal Surat</span>
                            <strong>{{ $surat->origin }}</strong>
                        </div>
                        <div class="detail-item">
                            <span>Departemen Tujuan</span>
                            <strong>{{ $surat->department_destination ?: '-' }}</strong>
                        </div>
                        <div class="detail-item">
                            <span>Tanggal Terima</span>
                            <strong>{{ optional($surat->reception_date)->format('d M Y') }}</strong>
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
                            <span>Nomor Referensi</span>
                            <strong>{{ $surat->reference_number ?: '-' }}</strong>
                        </div>
                    </div>

                    <form id="departmentReceiptForm" class="receipt-form">
                        <div class="form-group">
                            <label for="department_status">Status Departemen</label>
                            <select id="department_status" name="department_status" class="form-control">
                                @foreach($departmentStatusConfig as $statusKey => $meta)
                                    <option value="{{ $statusKey }}" {{ \App\Support\DepartmentReceiptStatus::normalize($surat->department_status) === $statusKey ? 'selected' : '' }}>
                                        {{ $meta['label'] }}
                                    </option>
                                @endforeach
                            </select>
                            <small id="departmentStatusDescription" class="helper-text">{{ $departmentStatusMeta['description'] }}</small>
                        </div>

                        <div class="form-group">
                            <label for="department_notes">Catatan untuk Admin</label>
                            <textarea
                                id="department_notes"
                                name="department_notes"
                                class="form-control notes-input"
                                rows="6"
                                placeholder="Tulis catatan penerimaan, tindak lanjut, atau hal yang perlu disampaikan ke admin..."
                            >{{ $surat->department_notes }}</textarea>
                        </div>

                        <div class="form-actions">
                            @if($openUrl)
                                <a href="{{ $openUrl }}" target="_blank" rel="noopener" class="btn-secondary">Buka File Asli</a>
                            @endif
                            @if($downloadUrl)
                                <a href="{{ $downloadUrl }}" target="_blank" rel="noopener" class="btn-secondary">Download File</a>
                            @endif
                            <button type="submit" class="btn-success" id="saveButton">
                                <span id="saveButtonText">Simpan Konfirmasi</span>
                                <span id="saveButtonLoader" style="display: none;">Menyimpan...</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </section>
    </main>

    <script src="{{ asset('js/request-progress.js') }}"></script>
    <script>
        const departmentStatusConfig = @json($departmentStatusConfig);
        const form = document.getElementById('departmentReceiptForm');
        const statusSelect = document.getElementById('department_status');
        const statusDescription = document.getElementById('departmentStatusDescription');
        const saveButton = document.getElementById('saveButton');
        const saveButtonText = document.getElementById('saveButtonText');
        const saveButtonLoader = document.getElementById('saveButtonLoader');

        statusSelect.addEventListener('change', () => {
            const meta = departmentStatusConfig[statusSelect.value] || departmentStatusConfig.pending;
            statusDescription.textContent = meta.description;
        });

        form.addEventListener('submit', async (event) => {
            event.preventDefault();

            try {
                setSavingState(true);

                const { response, result } = await RequestProgress.requestJson({
                    url: '{{ route("surat-masuk.share.update", $surat->share_token) }}',
                    method: 'POST',
                    data: {
                        department_status: statusSelect.value,
                        department_notes: document.getElementById('department_notes').value.trim(),
                    },
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    title: 'Menyimpan konfirmasi departemen',
                    initialMessage: 'Menyiapkan perubahan status departemen...',
                    processingMessage: 'Menyimpan status penerimaan dan catatan ke database...',
                    successMessage: 'Konfirmasi departemen berhasil disimpan.'
                });

                if (!response.ok || !result.success) {
                    throw new Error(result.message || 'Gagal menyimpan konfirmasi departemen.');
                }

                const departmentMeta = departmentStatusConfig[result.data.department_status] || departmentStatusConfig.pending;
                document.querySelector('.receipt-form .helper-text').textContent = departmentMeta.description;
                const currentBadge = document.getElementById('currentDepartmentStatusBadge');
                currentBadge.className = `badge ${departmentMeta.class}`;
                currentBadge.textContent = departmentMeta.label;

                RequestProgress.showNotice(result.message, 'success');
            } catch (error) {
                alert(error.message || 'Terjadi kesalahan saat menyimpan data.');
            } finally {
                setSavingState(false);
            }
        });

        function setSavingState(isSaving) {
            saveButton.disabled = isSaving;
            saveButtonText.style.display = isSaving ? 'none' : 'inline';
            saveButtonLoader.style.display = isSaving ? 'inline' : 'none';
        }
    </script>
</body>
</html>
