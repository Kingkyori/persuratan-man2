// Surat Keluar Page JavaScript

document.addEventListener('DOMContentLoaded', function() {
    initializeEventListeners();
    initializeDatePicker();
});

function initializeEventListeners() {
    // Tab switching
    const tabButtons = document.querySelectorAll('.tab-btn');
    tabButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            tabButtons.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            console.log('Switched to tab:', this.dataset.tab);
        });
    });

    // Create New Draft button
    const createDraftBtn = document.querySelector('.create-draft-btn');
    if (createDraftBtn) {
        createDraftBtn.addEventListener('click', function() {
            const draftForm = document.querySelector('.draft-form');
            if (draftForm) {
                draftForm.reset();
                draftForm.scrollIntoView({ behavior: 'smooth', block: 'start' });
                draftForm.querySelector('input').focus();
            }
        });
    }

    // Form submission
    const draftForm = document.querySelector('.draft-form');
    if (draftForm) {
        draftForm.addEventListener('submit', function(e) {
            e.preventDefault();
            submitDraft('approval');
        });
    }

    // Save as Draft button
    const saveDraftBtn = document.querySelector('.btn-draft');
    if (saveDraftBtn) {
        saveDraftBtn.addEventListener('click', function() {
            submitDraft('draft');
        });
    }

    // Edit button
    const editBtn = document.querySelector('.edit-btn');
    if (editBtn) {
        editBtn.addEventListener('click', function() {
            alert('Edit functionality akan segera tersedia');
        });
    }

    // Export button
    const exportBtn = document.querySelector('.export-btn');
    if (exportBtn) {
        exportBtn.addEventListener('click', function() {
            exportCorrespondenceList();
        });
    }

    // Correspondence item click
    const correspondenceItems = document.querySelectorAll('.correspondence-item');
    correspondenceItems.forEach(item => {
        item.addEventListener('click', function() {
            console.log('Clicked correspondence item');
            // Could open detail view or edit mode
        });
    });

    // Pagination
    const paginationBtns = document.querySelectorAll('.pagination-btn');
    paginationBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            if (this.textContent === '←' || this.textContent === '→') {
                console.log('Navigate to:', this.textContent);
            } else {
                paginationBtns.forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                console.log('Go to page:', this.textContent);
            }
        });
    });
}

function initializeDatePicker() {
    const dateInput = document.querySelector('.date-input');
    if (dateInput) {
        dateInput.addEventListener('focus', function() {
            this.type = 'date';
        });

        dateInput.addEventListener('blur', function() {
            if (!this.value) {
                this.type = 'text';
            }
        });
    }
}

function submitDraft(mode) {
    const form = document.querySelector('.draft-form');
    const recipient = form.querySelector('input[placeholder*="Recipient"]').value;
    const classification = form.querySelector('select').value;
    const date = form.querySelector('.date-input').value;
    const content = form.querySelector('.textarea-control').value;

    // Validation
    if (!recipient || !date || !content) {
        alert('Silakan isi semua field yang wajib: Recipient, Date, dan Content');
        return;
    }

    const draftData = {
        recipient: recipient,
        classification: classification,
        date: date,
        content: content,
        mode: mode
    };

    console.log('Submitting draft:', draftData);

    if (mode === 'draft') {
        alert(`✓ Surat berhasil disimpan sebagai DRAFT\n\nPenerima: ${recipient}\nTanggal: ${date}`);
    } else {
        alert(`✓ Surat berhasil diajukan untuk APPROVAL\n\nPenerima: ${recipient}\nTanggal: ${date}\n\nWaiting for approval...`);
    }

    // Reset form
    form.reset();
}

function exportCorrespondenceList() {
    console.log('Exporting correspondence list...');

    const items = document.querySelectorAll('.correspondence-item');
    let csv = 'No,Subject,Recipient,Date,Status\n';
    
    items.forEach((item, index) => {
        const subject = item.querySelector('h4').textContent;
        const recipient = item.querySelector('p').textContent;
        const date = item.querySelector('.date').textContent;
        const badge = item.querySelector('.badge');
        const status = badge ? badge.textContent.trim() : 'Unknown';

        csv += `${index + 1},"${subject}","${recipient}","${date}","${status}"\n`;
    });

    // Create and download
    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    const url = URL.createObjectURL(blob);
    
    link.setAttribute('href', url);
    link.setAttribute('download', `surat_keluar_${new Date().getTime()}.csv`);
    link.style.visibility = 'hidden';
    
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);

    console.log('Export completed');
}

// Auto-save draft every 2 minutes if content exists
let autoSaveInterval;
document.addEventListener('DOMContentLoaded', function() {
    const textarea = document.querySelector('.textarea-control');
    if (textarea) {
        textarea.addEventListener('input', function() {
            clearTimeout(autoSaveInterval);
            autoSaveInterval = setTimeout(() => {
                if (this.value.length > 10) {
                    console.log('Auto-saving draft...');
                    // Could implement auto-save here
                }
            }, 120000); // 2 minutes
        });
    }
});
