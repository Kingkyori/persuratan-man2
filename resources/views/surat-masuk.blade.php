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
    <div class="main-layout">
        @include('layouts.sidebar')

        <main class="main-content">
            <div class="top-bar">
                <div class="search-container">
                    <input type="text" class="search-input" id="searchInput" placeholder="Search entries...">
                </div>
                <div class="top-bar-actions">
                    <button class="icon-btn">🔔</button>
                    <button class="icon-btn">⚙️</button>
                    <div class="user-profile">
                        <span class="profile-avatar">👤</span>
                    </div>
                </div>
            </div>

            <div class="page-header">
                <div class="header-content">
                    <h1>Incoming Correspondence</h1>
                    <p>Sistem manajemen surat masuk terpusat MAN 2 Surakarta.</p>
                </div>
                <button class="btn-primary register-btn" id="btnOpenModal">
                    <span>✚</span> Register New Surat Masuk
                </button>
            </div>

            <div class="stats-container">
                <div class="stat-card">
                    <div class="stat-label">TOTAL RECEIVED</div>
                    <div class="stat-value">{{ $stats['total_received'] ?? 0 }}</div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">DISPOSED</div>
                    <div class="stat-value">{{ $stats['disposed'] ?? 0 }}</div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">PENDING</div>
                    <div class="stat-value">{{ $stats['pending'] ?? 0 }}</div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">GROWTH</div>
                    <div class="stat-value">+12%</div>
                </div>
            </div>

            <div class="archive-section">
                <div class="section-header">
                    <h2>Archive Ledger</h2>
                    <div class="section-actions">
                        <button class="btn-secondary">🔽 Filter</button>
                        <button class="btn-secondary">📤 Export</button>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="archive-table">
                        <thead>
                            <tr>
                                <th>Date Received</th>
                                <th>From / Origin</th>
                                <th>Subject</th>
                                <th>Reference No.</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="tableBody">
                            @forelse($surat_masuk as $surat)
                                <tr data-id="{{ $surat->id }}">
                                    <td>
                                        <div class="date-cell">{{ $surat->reception_date->format('d M Y') }}</div>
                                        <small>{{ $surat->reception_date->format('H:i') }}</small>
                                    </td>
                                    <td>
                                        <strong>{{ $surat->origin }}</strong>
                                    </td>
                                    <td>{{ Str::limit($surat->subject, 40) }}</td>
                                    <td>{{ $surat->reference_number ?? $surat->letter_number }}</td>
                                    <td>
                                        <select class="status-select" onchange="updateStatus({{ $surat->id }}, this.value)">
                                            <option value="pending" {{ $surat->status === 'pending' ? 'selected' : '' }}>⏳ Pending</option>
                                            <option value="done" {{ $surat->status === 'done' ? 'selected' : '' }}>✓ Done</option>
                                            <option value="disposed" {{ $surat->status === 'disposed' ? 'selected' : '' }}>✗ Disposed</option>
                                        </select>
                                    </td>
                                    <td>
                                        @if($surat->google_drive_link)
                                            <a href="{{ $surat->google_drive_link }}" target="_blank" class="btn-small" title="Buka di Drive">🔗</a>
                                        @endif
                                        <button class="btn-small" onclick="viewDetail({{ $surat->id }})">👁️</button>
                                        <button class="btn-small btn-danger" onclick="deleteSurat({{ $surat->id }})">🗑️</button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" style="text-align: center; padding: 30px;">Data surat belum tersedia.</td>
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
                <h2>Register New Surat Masuk</h2>
                <button class="modal-close" id="btnCloseModal">&times;</button>
            </div>

            <form id="formRegisterSurat" enctype="multipart/form-data">
                @csrf
                <div class="form-row">
                    <div class="form-group">
                        <label>ORIGIN / SENDER <span class="required">*</span></label>
                        <input type="text" name="origin" class="form-control" required placeholder="Contoh: Kemenag Solo">
                    </div>
                    <div class="form-group">
                        <label>RECEPTION DATE <span class="required">*</span></label>
                        <input type="date" name="reception_date" class="form-control" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>LETTER NUMBER <span class="required">*</span></label>
                        <input type="text" name="letter_number" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>SUBJECT MATTER <span class="required">*</span></label>
                        <input type="text" name="subject" class="form-control" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>REFERENCE NUMBER</label>
                        <input type="text" name="reference_number" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>STATUS</label>
                        <select name="status" class="form-control">
                            <option value="pending">⏳ Pending</option>
                            <option value="done">✓ Done</option>
                            <option value="disposed">✗ Disposed</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label>NOTES</label>
                    <textarea name="notes" class="form-control" rows="2"></textarea>
                </div>

                <div class="form-group">
                    <label>LETTER SCAN (PDF/JPG/PNG) <span class="required">*</span></label>
                    <div class="file-upload" id="fileUploadArea">
                        <span id="icon-upload">📎</span>
                        <p id="file-name-display">Click to upload file (Max 10MB)</p>
                        <small>Format yang didukung: PDF, JPG, JPEG, PNG</small>
                        <input type="file" name="letter_scan" id="letter_scan_input" accept=".pdf,.jpg,.jpeg,.png,application/pdf,image/jpeg,image/png" hidden required>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="button" class="btn-secondary" onclick="closeModal()">Discard</button>
                    <button type="submit" class="btn-success" id="btnSubmit">
                        <span id="submitText">Submit Registration</span>
                        <span id="submitLoader" style="display: none;">⏳ Mengunggah ke Drive...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal" id="modalDetail">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Detail Surat Masuk</h2>
                <button class="modal-close" onclick="closeDetailModal()">&times;</button>
            </div>
            <div class="modal-body" id="detailContent" style="padding: 20px;">
                </div>
        </div>
    </div>

    <script>
        const modal = document.getElementById('modalRegister');
        const form = document.getElementById('formRegisterSurat');
        const fileInput = document.getElementById('letter_scan_input');
        const fileArea = document.getElementById('fileUploadArea');
        const fileNameDisplay = document.getElementById('file-name-display');

        // OPEN & CLOSE MODAL
        document.getElementById('btnOpenModal').onclick = () => modal.style.display = 'flex';
        document.getElementById('btnCloseModal').onclick = () => closeModal();
        
        function closeModal() {
            modal.style.display = 'none';
            form.reset();
            fileNameDisplay.innerText = "Click to upload file (Max 10MB)";
            fileArea.style.borderColor = "";
        }

        // FILE SELECTION UI
        fileArea.onclick = () => fileInput.click();
        fileInput.onchange = () => {
            if (fileInput.files.length > 0) {
                fileNameDisplay.innerText = "File: " + fileInput.files[0].name;
                fileArea.style.borderColor = "#2ecc71";
            }
        };

        // SUBMIT FORM AJAX
        form.onsubmit = async (e) => {
            e.preventDefault();
            
            const submitBtn = document.getElementById('btnSubmit');
            const loader = document.getElementById('submitLoader');
            const btnText = document.getElementById('submitText');

            if (!fileInput.files[0]) {
                alert("Harap pilih file scan surat!");
                return;
            }

            const formData = new FormData(form);
            
            try {
                submitBtn.disabled = true;
                loader.style.display = 'inline';
                btnText.style.display = 'none';

                const response = await fetch('{{ route("surat-masuk.store") }}', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });

                const contentType = response.headers.get('content-type') || '';
                const result = contentType.includes('application/json')
                    ? await response.json()
                    : {
                        success: false,
                        message: await response.text()
                    };

                if (response.ok && result.success) {
                    alert('Berhasil: ' + result.message);
                    location.reload();
                } else {
                    alert('Gagal: ' + (result.message || 'Upload surat gagal.'));
                }
            } catch (err) {
                console.error(err);
                alert('Terjadi kesalahan koneksi/server.');
            } finally {
                submitBtn.disabled = false;
                loader.style.display = 'none';
                btnText.style.display = 'inline';
            }
        };

        // UPDATE STATUS
        async function updateStatus(id, newStatus) {
            try {
                const response = await fetch(`/surat-masuk/${id}/status`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ status: newStatus })
                });
                const res = await response.json();
                if (res.success) alert(res.message);
            } catch (e) {
                alert('Gagal mengubah status.');
            }
        }

        // DELETE SURAT
        async function deleteSurat(id) {
            if (!confirm('Hapus surat ini?')) return;
            try {
                const response = await fetch(`/surat-masuk/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });
                const res = await response.json();
                if (res.success) location.reload();
            } catch (e) {
                alert('Gagal menghapus data.');
            }
        }

        // VIEW DETAIL
        function viewDetail(id) {
            const row = document.querySelector(`tr[data-id="${id}"]`);
            const cells = row.querySelectorAll('td');
            const html = `
                <div style="line-height: 1.8;">
                    <p><strong>Tanggal:</strong> ${cells[0].innerText}</p>
                    <p><strong>Pengirim:</strong> ${cells[1].innerText}</p>
                    <p><strong>Perihal:</strong> ${cells[2].innerText}</p>
                    <p><strong>No. Referensi:</strong> ${cells[3].innerText}</p>
                </div>
            `;
            document.getElementById('detailContent').innerHTML = html;
            document.getElementById('modalDetail').style.display = 'flex';
        }

        function closeDetailModal() {
            document.getElementById('modalDetail').style.display = 'none';
        }

        // Close modal if click outside
        window.onclick = (e) => {
            if (e.target == modal) closeModal();
            if (e.target == document.getElementById('modalDetail')) closeDetailModal();
        };
    </script>
</body>
</html>
