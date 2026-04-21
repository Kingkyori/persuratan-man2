// Surat Masuk Page JavaScript

document.addEventListener('DOMContentLoaded', function() {
    // Password visibility toggle (if needed in future)
    initializeEventListeners();
});

function initializeEventListeners() {
    // Register button - show/scroll to form
    const registerBtn = document.querySelector('.register-btn');
    if (registerBtn) {
        registerBtn.addEventListener('click', function() {
            const registerSection = document.querySelector('.register-section');
            if (registerSection) {
                registerSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    }

    // File upload area
    const fileUpload = document.querySelector('.file-upload');
    if (fileUpload) {
        fileUpload.addEventListener('click', function() {
            // Create virtual file input
            const input = document.createElement('input');
            input.type = 'file';
            input.accept = '.pdf,.jpg,.jpeg,.png,.img';
            input.onchange = function(e) {
                const file = e.target.files[0];
                if (file) {
                    const maxSize = 5 * 1024 * 1024; // 5MB
                    if (file.size > maxSize) {
                        alert('File size exceeds 5MB limit');
                    } else {
                        console.log('File selected:', file.name);
                        // Update UI to show selected file
                        fileUpload.innerHTML = `
                            <span>✓</span>
                            <p>${file.name}</p>
                            <small>${(file.size / 1024).toFixed(2)} KB</small>
                        `;
                    }
                }
            };
            input.click();
        });

        // Drag and drop support
        fileUpload.addEventListener('dragover', function(e) {
            e.preventDefault();
            fileUpload.style.borderColor = '#1b5e20';
            fileUpload.style.backgroundColor = '#f5faf5';
        });

        fileUpload.addEventListener('dragleave', function(e) {
            e.preventDefault();
            fileUpload.style.borderColor = '#e0e8e3';
            fileUpload.style.backgroundColor = '#f9f9f8';
        });

        fileUpload.addEventListener('drop', function(e) {
            e.preventDefault();
            fileUpload.style.borderColor = '#e0e8e3';
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                const file = files[0];
                const maxSize = 5 * 1024 * 1024; // 5MB
                if (file.size > maxSize) {
                    alert('File size exceeds 5MB limit');
                } else {
                    console.log('File dropped:', file.name);
                    fileUpload.innerHTML = `
                        <span>✓</span>
                        <p>${file.name}</p>
                        <small>${(file.size / 1024).toFixed(2)} KB</small>
                    `;
                }
            }
        });
    }

    // Form submission
    const registerForm = document.querySelector('.register-form');
    if (registerForm) {
        registerForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Get form values
            const formData = {
                origin: registerForm.querySelector('input[placeholder*="Kemenag"]').value,
                receptionDate: registerForm.querySelector('input[placeholder*="mm"]').value,
                letterNumber: registerForm.querySelector('input[placeholder*="Official"]').value,
                subjectMatter: registerForm.querySelector('input[placeholder*="Short"]').value,
            };

            // Validate form
            if (!formData.origin || !formData.receptionDate || !formData.letterNumber || !formData.subjectMatter) {
                alert('Please fill in all required fields');
                return;
            }

            console.log('Form submitted:', formData);
            alert('Surat Masuk berhasil didaftarkan!');
            
            // Reset form
            registerForm.reset();
            fileUpload.innerHTML = `
                <span>📎</span>
                <p>Drop file to upload</p>
                <small>Maximum size 5MB only</small>
            `;
        });
    }

    // Discard button
    const discardBtn = registerForm?.querySelector('.btn-secondary');
    if (discardBtn) {
        discardBtn.addEventListener('click', function() {
            registerForm.reset();
            fileUpload.innerHTML = `
                <span>📎</span>
                <p>Drop file to upload</p>
                <small>Maximum size 5MB only</small>
            `;
        });
    }

    // Filter functionality
    const filterBtn = document.querySelector('.filter-btn');
    if (filterBtn) {
        filterBtn.addEventListener('click', function() {
            alert('Filter functionality akan segera tersedia');
        });
    }

    // Export functionality
    const exportBtn = document.querySelector('.export-btn');
    if (exportBtn) {
        exportBtn.addEventListener('click', function() {
            console.log('Exporting table data...');
            // Basic table export to CSV
            exportTableToCSV('archive-table.csv');
        });
    }

    // Pagination
    const paginationBtns = document.querySelectorAll('.pagination-btn');
    paginationBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            paginationBtns.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
        });
    });
}

// Export table to CSV
function exportTableToCSV(filename) {
    const table = document.querySelector('.archive-table');
    let csv = [];
    
    // Get headers
    const headers = [];
    document.querySelectorAll('.archive-table th').forEach(th => {
        headers.push(th.textContent.trim());
    });
    csv.push(headers.join(','));

    // Get rows
    document.querySelectorAll('.archive-table tbody tr').forEach(tr => {
        const row = [];
        tr.querySelectorAll('td').forEach(td => {
            row.push('"' + td.textContent.trim() + '"');
        });
        csv.push(row.join(','));
    });

    // Create and download file
    const csvContent = csv.join('\n');
    const blob = new Blob([csvContent], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = filename;
    a.click();
    window.URL.revokeObjectURL(url);
}
