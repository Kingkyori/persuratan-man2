(function () {
    const overlayId = 'appRequestProgressOverlay';
    const noticeContainerId = 'appRequestNoticeContainer';
    let progressValue = 0;
    let fakeTimer = null;

    function ensureOverlay() {
        let overlay = document.getElementById(overlayId);

        if (overlay) {
            return overlay;
        }

        overlay = document.createElement('div');
        overlay.id = overlayId;
        overlay.className = 'request-progress-overlay';
        overlay.innerHTML = `
            <div class="request-progress-card">
                <div class="request-progress-top">
                    <strong id="requestProgressTitle">Memproses data</strong>
                    <span id="requestProgressPercent">0%</span>
                </div>
                <p id="requestProgressMessage">Mohon tunggu sebentar...</p>
                <div class="request-progress-track">
                    <div id="requestProgressFill" class="request-progress-fill"></div>
                </div>
            </div>
        `;

        document.body.appendChild(overlay);

        return overlay;
    }

    function showProgress(title, message) {
        const overlay = ensureOverlay();
        overlay.classList.add('is-visible');
        progressValue = 0;

        document.getElementById('requestProgressTitle').textContent = title || 'Memproses data';
        document.getElementById('requestProgressMessage').textContent = message || 'Mohon tunggu sebentar...';
        setProgress(0);
    }

    function ensureNoticeContainer() {
        let container = document.getElementById(noticeContainerId);

        if (container) {
            return container;
        }

        container = document.createElement('div');
        container.id = noticeContainerId;
        container.className = 'request-notice-container';
        document.body.appendChild(container);

        return container;
    }

    function showNotice(message, tone = 'success') {
        const container = ensureNoticeContainer();
        const notice = document.createElement('div');

        notice.className = `request-notice request-notice-${tone}`;
        notice.textContent = message;
        container.appendChild(notice);

        window.setTimeout(() => {
            notice.classList.add('is-visible');
        }, 10);

        window.setTimeout(() => {
            notice.classList.remove('is-visible');
            window.setTimeout(() => notice.remove(), 220);
        }, 3800);
    }

    function hideProgress() {
        const overlay = ensureOverlay();
        overlay.classList.remove('is-visible');
        stopFakeProgress();
    }

    function setProgress(percent, message) {
        progressValue = Math.max(progressValue, Math.min(percent, 100));

        const fill = document.getElementById('requestProgressFill');
        const percentLabel = document.getElementById('requestProgressPercent');
        const messageLabel = document.getElementById('requestProgressMessage');

        fill.style.width = `${progressValue}%`;
        percentLabel.textContent = `${Math.round(progressValue)}%`;

        if (message) {
            messageLabel.textContent = message;
        }
    }

    function stopFakeProgress() {
        if (fakeTimer) {
            window.clearInterval(fakeTimer);
            fakeTimer = null;
        }
    }

    function startFakeProgress(startAt, maxPercent, message) {
        stopFakeProgress();
        setProgress(startAt, message);

        fakeTimer = window.setInterval(() => {
            if (progressValue >= maxPercent) {
                stopFakeProgress();
                return;
            }

            const nextStep = progressValue + Math.max(1, Math.round((maxPercent - progressValue) / 6));
            setProgress(nextStep, message);
        }, 350);
    }

    function parseHeaders(rawHeaders) {
        return rawHeaders
            .trim()
            .split(/[\r\n]+/)
            .filter(Boolean)
            .reduce((headers, line) => {
                const parts = line.split(': ');
                const header = parts.shift();

                if (!header) {
                    return headers;
                }

                headers[header.toLowerCase()] = parts.join(': ');
                return headers;
            }, {});
    }

    function completeProgress(message) {
        setProgress(100, message || 'Proses selesai.');

        return new Promise((resolve) => {
            window.setTimeout(() => {
                hideProgress();
                resolve();
            }, 280);
        });
    }

    function failProgress() {
        hideProgress();
    }

    function request(options) {
        const {
            url,
            method = 'GET',
            data = null,
            headers = {},
            title = 'Memproses data',
            initialMessage = 'Menyiapkan permintaan...',
            uploadMessage = 'Mengunggah file...',
            processingMessage = 'Server sedang menyimpan data...',
            successMessage = 'Proses selesai.',
            timeout = 600000,
        } = options || {};

        return new Promise((resolve, reject) => {
            const xhr = new XMLHttpRequest();
            const normalizedMethod = String(method || 'GET').toUpperCase();
            const isFormData = typeof FormData !== 'undefined' && data instanceof FormData;
            const requestHeaders = { ...headers };

            showProgress(title, initialMessage);

            if (!isFormData) {
                startFakeProgress(12, 88, processingMessage);
            } else {
                setProgress(8, initialMessage);
            }

            xhr.open(normalizedMethod, url, true);
            xhr.responseType = 'text';
            xhr.timeout = timeout;

            if (isFormData && xhr.upload) {
                xhr.upload.addEventListener('loadstart', () => {
                    setProgress(8, uploadMessage);
                });

                xhr.upload.addEventListener('progress', (event) => {
                    if (!event.lengthComputable) {
                        startFakeProgress(12, 78, uploadMessage);
                        return;
                    }

                    stopFakeProgress();
                    const rawPercent = Math.round((event.loaded / event.total) * 100);
                    const visiblePercent = Math.min(Math.max(rawPercent, 8), 92);
                    setProgress(visiblePercent, `${uploadMessage} ${rawPercent}%`);
                });

                xhr.upload.addEventListener('load', () => {
                    setProgress(94, processingMessage);
                });
            }

            xhr.onreadystatechange = () => {
                if (xhr.readyState >= 2) {
                    setProgress(96, processingMessage);
                }
            };

            xhr.onload = () => {
                stopFakeProgress();

                const response = {
                    ok: xhr.status >= 200 && xhr.status < 300,
                    status: xhr.status,
                    text: xhr.responseText,
                    headers: parseHeaders(xhr.getAllResponseHeaders()),
                };

                resolve(response);
            };

            xhr.onerror = () => {
                failProgress();
                reject(new Error('Terjadi kesalahan jaringan saat mengirim data.'));
            };

            xhr.onabort = () => {
                failProgress();
                reject(new Error('Permintaan dibatalkan sebelum selesai.'));
            };

            xhr.ontimeout = () => {
                failProgress();
                reject(new Error('Proses terlalu lama. Silakan coba lagi.'));
            };

            Object.entries(requestHeaders).forEach(([key, value]) => {
                xhr.setRequestHeader(key, value);
            });

            if (data === null || typeof data === 'undefined') {
                xhr.send();
                return;
            }

            if (isFormData) {
                xhr.send(data);
                return;
            }

            const contentType = String(requestHeaders['Content-Type'] || requestHeaders['content-type'] || '').toLowerCase();

            if (contentType.includes('application/json') && typeof data !== 'string') {
                xhr.send(JSON.stringify(data));
                return;
            }

            xhr.send(data);
        });
    }

    async function requestJson(options) {
        const response = await request(options);
        const contentType = response.headers['content-type'] || '';
        let result = null;

        if (contentType.includes('application/json')) {
            try {
                result = JSON.parse(response.text || '{}');
            } catch (error) {
                result = {
                    success: false,
                    message: 'Respons server tidak dapat dibaca sebagai JSON.',
                };
            }
        } else {
            result = {
                success: response.ok,
                message: response.text || 'Permintaan selesai.',
            };
        }

        const isSuccess = response.ok && result && result.success !== false;

        if (isSuccess) {
            await completeProgress(options?.successMessage || 'Proses selesai.');
        } else {
            failProgress();
        }

        return { response, result };
    }

    window.RequestProgress = {
        request,
        requestJson,
        showNotice,
    };
})();
