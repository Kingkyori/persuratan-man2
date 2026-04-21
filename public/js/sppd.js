// SPPD Reporting & Archives Page JavaScript

document.addEventListener('DOMContentLoaded', function() {
    initializeEventListeners();
    initializeFilters();
});

function initializeEventListeners() {
    // Report type buttons
    const reportTypeBtns = document.querySelectorAll('.report-type-btn');
    reportTypeBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            reportTypeBtns.forEach(b => b.style.borderColor = '#e0e8e3');
            this.style.borderColor = '#1b5e20';
            console.log('Report type selected:', this.textContent.trim());
        });
    });

    // Generate report button
    const generateBtn = document.querySelector('.btn-generate-report');
    if (generateBtn) {
        generateBtn.addEventListener('click', function() {
            generateReport();
        });
    }

    // View toggle buttons
    const viewBtns = document.querySelectorAll('.view-btn');
    viewBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            viewBtns.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            const view = this.textContent.trim() === '☰' ? 'list' : 'grid';
            console.log('View changed to:', view);
        });
    });

    // Action buttons
    const actionBtns = document.querySelectorAll('.action-btn');
    actionBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const action = this.textContent.includes('PDF') ? 'PDF' : 'Email';
            const reportName = this.closest('.report-row')?.querySelector('h4')?.textContent || 'Report';
            console.log(action + ' download for:', reportName);
            alert(`${action} download initiated for: ${reportName}`);
        });
    });

    // Pagination buttons
    const paginationBtns = document.querySelectorAll('.pagination-btn');
    paginationBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            if (this.textContent === '→' || this.textContent === '1' || this.textContent === '2' || this.textContent === '3') {
                paginationBtns.forEach(b => b.classList.remove('active'));
                if (this.textContent !== '→' && this.textContent !== '←') {
                    this.classList.add('active');
                    console.log('Go to page:', this.textContent);
                }
            }
        });
    });

    // Secure archive checkbox
    const archiveCheckbox = document.getElementById('reference-search');
    if (archiveCheckbox) {
        archiveCheckbox.addEventListener('change', function() {
            console.log('Archive search enabled:', this.checked);
        });
    }

    // Archive action buttons
    const archiveButtons = document.querySelectorAll('.option-group .btn-secondary');
    archiveButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            console.log('Archive action:', this.textContent.trim());
            alert('Archive feature: ' + this.textContent.trim());
        });
    });
}

function initializeFilters() {
    const fromDate = document.querySelector('.from-date');
    const toDate = document.querySelector('.to-date');
    const departmentSelect = document.querySelector('.department-select');

    // Date pickers
    [fromDate, toDate].forEach(input => {
        if (input) {
            input.addEventListener('focus', function() {
                if (this.type === 'text') {
                    this.type = 'date';
                }
            });

            input.addEventListener('blur', function() {
                if (!this.value) {
                    this.type = 'text';
                }
            });

            input.addEventListener('change', function() {
                console.log('Date filter changed');
            });
        }
    });

    // Department filter
    if (departmentSelect) {
        departmentSelect.addEventListener('change', function() {
            console.log('Department filter:', this.value);
        });
    }
}

function generateReport() {
    const fromDate = document.querySelector('.from-date').value || 'No date';
    const toDate = document.querySelector('.to-date').value || 'No date';
    const department = document.querySelector('.department-select').value;
    const selectedReportType = document.querySelector('.report-type-btn[style*="border-color: rgb(27, 94, 32)"]');
    const reportType = selectedReportType?.textContent.trim() || 'All Types';

    const reportData = {
        type: reportType,
        from_date: fromDate,
        to_date: toDate,
        department: department,
        timestamp: new Date().toISOString()
    };

    console.log('Generating report:', reportData);

    alert(`✓ Report generation started\n\nType: ${reportType}\nPeriod: ${fromDate} to ${toDate}\nDepartment: ${department}\n\nPlease wait while we process your request...`);

    // Simulate report generation
    setTimeout(() => {
        alert('✓ Comprehensive export has been prepared!\n\nThe report will be available in your secure archive.');
    }, 2000);
}

// Export chart data functionality
function exportReportData(format = 'csv') {
    const rows = document.querySelectorAll('.report-row');
    let data = [];

    if (format === 'csv') {
        let csv = 'Report Name,Type,Date Generated,Status\n';

        rows.forEach(row => {
            const name = row.querySelector('h4')?.textContent || '';
            const type = row.querySelector('.badge')?.textContent?.trim() || '';
            const date = row.querySelectorAll('td')[2]?.textContent?.trim() || '';

            csv += `"${name}","${type}","${date}"\n`;
        });

        const blob = new Blob([csv], { type: 'text/csv' });
        const url = URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.href = url;
        link.download = `sppd_reports_${new Date().getTime()}.csv`;
        link.click();
        URL.revokeObjectURL(url);

        console.log('CSV exported');
    }
}

// Archive security status check
function checkArchiveStatus() {
    const encryptionStatus = {
        method: 'SSL 256',
        type: 'Military-Grade Encryption',
        status: 'Protected',
        last_verified: new Date().toLocaleString()
    };

    console.log('Archive Security Status:', encryptionStatus);
    return encryptionStatus;
}

// Initialize archive status on page load
document.addEventListener('DOMContentLoaded', function() {
    checkArchiveStatus();
});

// Real-time report updates simulation
function startReportUpdates() {
    setInterval(() => {
        // In production, this would fetch new reports from the server
        console.log('Checking for new reports...');
    }, 60000); // Check every minute
}

startReportUpdates();
