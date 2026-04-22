<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Surat Masuk - MAN 2 Surakarta</title>
    <link rel="stylesheet" href="{{ asset('css/sidebar.css') }}">
    <link rel="stylesheet" href="{{ asset('css/surat-masuk.css') }}">
    <link rel="stylesheet" href="{{ asset('css/modal.css') }}">
</head>
<body>
    <div class="main-layout">
        <!-- Sidebar -->
        @include('layouts.sidebar')

        <!-- Main Content -->
        <main class="main-content">
            <!-- Top Bar -->
            <div class="top-bar">
                <div class="search-container">
                    <input type="text" class="search-input" id="searchInput" placeholder="Search entries...">
                </div>
                <div class="top-bar-actions">
                    <button class="icon-btn notification-btn">
                        <span>🔔</span>
                    </button>
                    <button class="icon-btn settings-btn">
                        <span>⚙️</span>
                    </button>
                    <div class="user-profile">
                        <span class="profile-avatar">👤</span>
                    </div>
                </div>
            </div>

            <!-- Page Header -->
            <div class="page-header">
                <div class="header-content">
                    <h1>Incoming Correspondence</h1>
                    <p>Centralized management system for all institutional communications. Efficiently track, archive, and dispose official letters received by the secretariat.</p>
                </div>
                <button class="btn-primary register-btn" id="btnRegisterNew">
                    <span>✚</span>
                    Register New Surat Masuk
                </button>
            </div>

            <!-- Stats Cards -->
            <div class="stats-container">
                <div class="stat-card">
                    <div class="stat-label">TOTAL RECEIVED</div>
                    <div class="stat-value" id="statTotal">{{ $stats['total_received'] ?? 0 }}</div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">DISPOSED</div>
                    <div class="stat-value" id="statDisposed">{{ $stats['disposed'] ?? 0 }}</div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">PENDING</div>
                    <div class="stat-value" id="statPending">{{ $stats['pending'] ?? 0 }}</div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">MONTH-OVER-MONTH</div>
                    <div class="stat-value">+12%</div>
                </div>
            </div>

            <!-- Archive Ledger -->
            <div class="archive-section">
                <div class="section-header">
                    <h2>Archive Ledger</h2>
                    <div class="section-actions">
                        <button class="btn-secondary filter-btn" id="btnFilter">🔽 Filter</button>
                        <button class="btn-secondary export-btn">📤 Export</button>
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
                            <!-- Data akan diisi oleh JavaScript -->
                            @forelse($surat_masuk as $surat)
                                <tr data-id="{{ $surat->id }}">
                                    <td>
                                        <div class="date-cell">{{ $surat->reception_date->format('M d, Y') }}</div>
                                        <small>{{ $surat->reception_date->format('h:i A') }}</small>
                                    </td>
                                    <td>
                                        <div>{{ $surat->origin }}</div>
                                        <small>Official Letter</small>
                                    </td>
                                    <td>{{ Str::limit($surat->subject, 50) }}</td>
                                    <td>{{ $surat->reference_number ?? $surat->letter_number }}</td>
                                    <td>
                                        <select class="status-select" onchange="updateStatus({{ $surat->id }}, this.value)">
                                            <option value="pending" {{ $surat->status === 'pending' ? 'selected' : '' }}>⏳ Pending</option>
                                            <option value="done" {{ $surat->status === 'done' ? 'selected' : '' }}>✓ Done</option>
                                            <option value="disposed" {{ $surat->status === 'disposed' ? 'selected' : '' }}>✗ Disposed</option>
                                        </select>
                                    </td>
                                    <td>
                                        <button class="btn-small" onclick="viewDetail({{ $surat->id }})" title="View">👁️</button>
                                        <button class="btn-small" onclick="editSurat({{ $surat->id }})" title="Edit">✏️</button>
                                        <button class="btn-small btn-danger" onclick="deleteSurat({{ $surat->id }})" title="Delete">🗑️</button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" style="text-align: center; padding: 20px;">Belum ada data surat masuk</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="pagination" id="paginationContainer">
                    <!-- Pagination akan diisi oleh JavaScript -->
                </div>
            </div>

            <!-- Right Sidebar -->
            <aside class="right-sidebar">
                <!-- Administrative Guidelines -->
                <div class="info-card guidelines-card">
                    <div class="info-header">
                        <span class="info-icon">ℹ️</span>
                        <h3>Administrative Guidelines</h3>
                    </div>
                    <ul class="info-list">
                        <li>
                            <span class="check-mark">✓</span>
                            <span>Ensure all incoming mail is registered within 1 business day of reception to maintain institutional efficiency.</span>
                        </li>
                        <li>
                            <span class="check-mark">✓</span>
                            <span>Attach scanned documents and direct to the relevant office. Files will be stored in Google Drive.</span>
                        </li>
                        <li>
                            <span class="check-mark">✓</span>
                            <span>Update status sesuai dengan kondisi surat (Pending, Done, or Disposed).</span>
                        </li>
                    </ul>
                </div>

                <!-- Recent Activity -->
                <div class="info-card activity-card">
                    <h3>Recent Activity</h3>
                    <div class="activity-item">
                        <div class="activity-title">Mail Received</div>
                        <small>15 mins ago</small>
                    </div>
                    <div class="activity-item">
                        <div class="activity-title">New Mail Logged</div>
                        <small>2 hours ago</small>
                    </div>
                </div>
            </aside>
        </main>
    </div>

    <!-- MODAL FORM -->
    <div class="modal" id="modalRegister">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Register New Surat Masuk</h2>
                <button class="modal-close" onclick="closeModal()">&times;</button>
            </div>

            <form id="formRegisterSurat" class="register-form" enctype="multipart/form-data">
                @csrf

                <div class="form-row">
                    <div class="form-group">
                        <label>ORIGIN / SENDER <span class="required">*</span></label>
                        <input type="text" name="origin" placeholder="e.g. Kemenag Kota Surakarta" class="form-control" required>
                        <small class="error-message" id="error-origin"></small>
                    </div>
                    <div class="form-group">
                        <label>RECEPTION DATE <span class="required">*</span></label>
                        <input type="date" name="reception_date" class="form-control" required>
                        <small class="error-message" id="error-reception_date"></small>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>LETTER NUMBER <span class="required">*</span></label>
                        <input type="text" name="letter_number" placeholder="Official Reference Number" class="form-control" required>
                        <small class="error-message" id="error-letter_number"></small>
                    </div>
                    <div class="form-group">
                        <label>SUBJECT MATTER <span class="required">*</span></label>
                        <input type="text" name="subject" placeholder="Short description of content" class="form-control" required>
                        <small class="error-message" id="error-subject"></small>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>REFERENCE NUMBER</label>
                        <input type="text" name="reference_number" placeholder="Optional reference number" class="form-control">
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
                    <textarea name="notes" placeholder="Additional notes..." class="form-control" rows="3"></textarea>
                </div>

                <div class="form-group">
                    <label>LETTER SCAN / UPLOAD (PDF/JPG/PNG) <span class="required">*</span></label>
                    <div class="file-upload" id="fileUploadArea">
                        <span>📎</span>
                        <p>Drop file or click to upload</p>
                        <small>Maximum size 10MB - File will be uploaded to Google Drive</small>
                        <input type="file" name="letter_scan" id="fileInput" accept=".pdf,.jpg,.jpeg,.png,.img" style="display: none;">
                    </div>
                    <div class="file-preview" id="filePreview" style="display: none;">
                        <span>✓</span>
                        <span id="fileName"></span>
                    </div>
                    <small class="error-message" id="error-letter_scan"></small>
                </div>

                <div class="form-actions">
                    <button type="button" class="btn-secondary" onclick="closeModal()">Discard</button>
                    <button type="submit" class="btn-success" id="btnSubmit">
                        <span id="submitText">Submit Registration</span>
                        <span id="submitLoader" style="display: none;">⏳ Processing...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- DETAIL MODAL -->
    <div class="modal" id="modalDetail">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Detail Surat Masuk</h2>
                <button class="modal-close" onclick="closeDetailModal()">&times;</button>
            </div>
            <div class="modal-body" id="detailContent">
                <!-- Content will be filled by JavaScript -->
            </div>
        </div>
    </div>

    <script>
        const registerForm = document.getElementById('formRegisterSurat');
        const fileUploadArea = document.getElementById('fileUploadArea');
        const fileInput = document.getElementById('fileInput');
        const filePreview = document.getElementById('filePreview');
        const modal = document.getElementById('modalRegister');
        const btnRegisterNew = document.getElementById('btnRegisterNew');

        // Open Modal
        btnRegisterNew.addEventListener('click', () => {
            modal.style.display = 'flex';
            registerForm.reset();
            filePreview.style.display = 'none';
        });

        // Close Modal
        function closeModal() {
            modal.style.display = 'none';
            registerForm.reset();
            filePreview.style.display = 'none';
            clearErrors();
        }

        // File Upload Handler
        fileUploadArea.addEventListener('click', () => fileInput.click());
        fileUploadArea.addEventListener('dragover', (e) => {
            e.preventDefault();
            fileUploadArea.style.borderColor = '#4CAF50';
            fileUploadArea.style.backgroundColor = '#f0f8f0';
        });
        fileUploadArea.addEventListener('dragleave', () => {
            fileUploadArea.style.borderColor = '#ddd';
            fileUploadArea.style.backgroundColor = 'transparent';
        });
        fileUploadArea.addEventListener('drop', (e) => {
            e.preventDefault();
            fileUploadArea.style.borderColor = '#ddd';
            fileUploadArea.style.backgroundColor = 'transparent';
            if (e.dataTransfer.files.length) {
                fileInput.files = e.dataTransfer.files;
                updateFilePreview();
            }
        });

        fileInput.addEventListener('change', updateFilePreview);

        function updateFilePreview() {
            const file = fileInput.files[0];
            if (file) {
                document.getElementById('fileName').textContent = `BLUE PRINT ${file.name.toUpperCase()}`;
                filePreview.style.display = 'block';
                fileUploadArea.style.display = 'none';
            }
        }

        // Form Submit Handler
        registerForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            clearErrors();

            const formData = new FormData(registerForm);
            const submitBtn = document.getElementById('btnSubmit');
            const submitText = document.getElementById('submitText');
            const submitLoader = document.getElementById('submitLoader');

            try {
                submitBtn.disabled = true;
                submitText.style.display = 'none';
                submitLoader.style.display = 'inline';

                const response = await fetch('{{ route("surat-masuk.store") }}', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('[name="_token"]').value
                    }
                });

                const data = await response.json();

                if (!response.ok) {
                    throw new Error(data.message || 'Gagal menyimpan data');
                }

                if (data.success) {
                    alert('✓ Surat masuk berhasil ditambahkan!');
                    closeModal();
                    location.reload(); // Reload halaman untuk update data
                } else {
                    alert('Error: ' + (data.message || 'Terjadi kesalahan'));
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Error: ' + error.message);
            } finally {
                submitBtn.disabled = false;
                submitText.style.display = 'inline';
                submitLoader.style.display = 'none';
            }
        });

        function clearErrors() {
            document.querySelectorAll('.error-message').forEach(el => el.textContent = '');
        }

        // Update Status
        async function updateStatus(id, status) {
            if (!confirm('Apakah Anda yakin ingin mengubah status?')) return;

            try {
                const response = await fetch(`/surat-masuk/${id}/status`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('[name="_token"]').value
                    },
                    body: JSON.stringify({ status: status })
                });

                const data = await response.json();
                if (data.success) {
                    alert('✓ Status berhasil diperbarui!');
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            } catch (error) {
                alert('Error: ' + error.message);
            }
        }

        // Delete Surat
        async function deleteSurat(id) {
            if (!confirm('Apakah Anda yakin ingin menghapus surat ini?')) return;

            try {
                const response = await fetch(`/surat-masuk/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('[name="_token"]').value
                    }
                });

                const data = await response.json();
                if (data.success) {
                    alert('✓ Surat berhasil dihapus!');
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            } catch (error) {
                alert('Error: ' + error.message);
            }
        }

        // View Detail
        async function viewDetail(id) {
            // Ambil data dari tabel
            const row = document.querySelector(`tr[data-id="${id}"]`);
            const cells = row.querySelectorAll('td');
            
            const html = `
                <div class="detail-info">
                    <p><strong>Tanggal Penerimaan:</strong> ${cells[0].textContent}</p>
                    <p><strong>Asal/Pengirim:</strong> ${cells[1].textContent}</p>
                    <p><strong>Perihal:</strong> ${cells[2].textContent}</p>
                    <p><strong>Nomor Referensi:</strong> ${cells[3].textContent}</p>
                    <p><strong>Status:</strong> ${cells[4].innerHTML}</p>
                </div>
            `;
            
            document.getElementById('detailContent').innerHTML = html;
            document.getElementById('modalDetail').style.display = 'flex';
        }

        function closeDetailModal() {
            document.getElementById('modalDetail').style.display = 'none';
        }

        // Close modal when clicking outside
        window.addEventListener('click', (e) => {
            if (e.target === modal) closeModal();
            if (e.target === document.getElementById('modalDetail')) closeDetailModal();
        });
    </script>

    <script src="{{ asset('js/surat-masuk.js') }}"></script>
</body>
</html>
