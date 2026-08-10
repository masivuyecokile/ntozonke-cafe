document.addEventListener('DOMContentLoaded', function () {
    /**
     * Global helpers
     */
    function getBaseUrl() {
        return window.NTOZONKE && window.NTOZONKE.baseUrl ? window.NTOZONKE.baseUrl : '';
    }

    function getCsrfToken() {
        return window.NTOZONKE && window.NTOZONKE.csrfToken ? window.NTOZONKE.csrfToken : '';
    }

    async function parseJsonResponse(response, logLabel = 'SERVER RESPONSE') {
        const rawText = await response.text();

        console.log(logLabel + ':', rawText);

        /**
         * Removes accidental UTF-8 BOM/invisible characters before JSON.
         * This protects the frontend while we also fix PHP files as UTF-8 without BOM.
         */
        const cleanedText = rawText.replace(/^\uFEFF+/, '').trim();

        try {
            return JSON.parse(cleanedText);
        } catch (error) {
            console.error('JSON parse failed:', error);
            console.error('Raw response:', rawText);

            throw new Error('Server did not return valid JSON.');
        }
    }

    function showServerJsonError(button = null, originalText = null) {
        Swal.fire({
            icon: 'error',
            title: 'Server Error',
            html: 'The server did not return valid JSON.<br>Please check the browser console or PHP error log.',
            confirmButtonColor: '#00a651'
        });

        if (button && originalText !== null) {
            button.disabled = false;
            button.innerHTML = originalText;
        }
    }

    function formatRemainingTime(totalSeconds) {
        totalSeconds = Math.max(0, totalSeconds);

        const hours = Math.floor(totalSeconds / 3600);
        const minutes = Math.floor((totalSeconds % 3600) / 60);
        const seconds = totalSeconds % 60;

        if (hours > 0) {
            return `${hours}h ${minutes}m ${seconds}s`;
        }

        if (minutes > 0) {
            return `${minutes}m ${seconds}s`;
        }

        return `${seconds}s`;
    }

    /**
     * Start Session
     */
    const startButtons = document.querySelectorAll('.js-start-session');
    const startSessionModalEl = document.getElementById('startSessionModal');
    const startSessionForm = document.getElementById('startSessionForm');
    const startSessionBtn = document.getElementById('startSessionBtn');

    const sessionPcId = document.getElementById('sessionPcId');
    const sessionPcName = document.getElementById('sessionPcName');

    const packageSelect = document.getElementById('packageSelect');
    const packageSummary = document.getElementById('packageSummary');
    const summaryMinutes = document.getElementById('summaryMinutes');
    const summaryPrice = document.getElementById('summaryPrice');

    if (startSessionModalEl && startSessionForm && startSessionBtn) {
        const startSessionModal = new bootstrap.Modal(startSessionModalEl);

        startButtons.forEach(function (button) {
            button.addEventListener('click', function () {
                const pcId = this.dataset.pcId;
                const pcName = this.dataset.pcName;

                sessionPcId.value = pcId;
                sessionPcName.textContent = pcName;

                startSessionForm.reset();

                if (packageSummary) {
                    packageSummary.classList.add('d-none');
                }

                startSessionModal.show();
            });
        });

        if (packageSelect) {
            packageSelect.addEventListener('change', function () {
                const selectedOption = this.options[this.selectedIndex];

                if (!selectedOption.value) {
                    packageSummary.classList.add('d-none');
                    summaryMinutes.textContent = '0 minutes';
                    summaryPrice.textContent = 'R0.00';
                    return;
                }

                const minutes = selectedOption.dataset.minutes;
                const price = selectedOption.dataset.price;

                summaryMinutes.textContent = `${minutes} minutes`;
                summaryPrice.textContent = `R${parseFloat(price).toFixed(2)}`;

                packageSummary.classList.remove('d-none');
            });
        }

        startSessionForm.addEventListener('submit', async function (e) {
            e.preventDefault();

            const originalBtnText = startSessionBtn.innerHTML;
            const formData = new FormData(startSessionForm);

            startSessionBtn.disabled = true;
            startSessionBtn.innerHTML = `
                <span class="spinner-border spinner-border-sm me-2"></span>
                Starting...
            `;

            try {
                const response = await fetch(startSessionForm.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                let data;

                try {
                    data = await parseJsonResponse(response, 'START SESSION RESPONSE');
                } catch (error) {
                    showServerJsonError(startSessionBtn, originalBtnText);
                    return;
                }

                if (!data.success) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Could Not Start Session',
                        text: data.message || 'Something went wrong.',
                        confirmButtonColor: '#00a651'
                    });

                    startSessionBtn.disabled = false;
                    startSessionBtn.innerHTML = originalBtnText;
                    return;
                }

                startSessionModal.hide();

                Swal.fire({
                    icon: 'success',
                    title: 'Session Started',
                    text: data.message,
                    timer: 1200,
                    showConfirmButton: false
                });

                setTimeout(function () {
                    window.location.reload();
                }, 1200);

            } catch (error) {
                console.error('Start session fetch error:', error);

                Swal.fire({
                    icon: 'error',
                    title: 'Connection Error',
                    text: 'Could not connect to the local server.',
                    confirmButtonColor: '#00a651'
                });

                startSessionBtn.disabled = false;
                startSessionBtn.innerHTML = originalBtnText;
            }
        });
    }

    /**
     * End Session
     */
    document.querySelectorAll('.js-end-session').forEach(function (button) {
        button.addEventListener('click', function () {
            const sessionId = this.dataset.sessionId;
            const pcName = this.dataset.pcName;

            Swal.fire({
                icon: 'warning',
                title: 'End Session?',
                text: `This will end the session and lock ${pcName}.`,
                showCancelButton: true,
                confirmButtonText: 'Yes, End Session',
                cancelButtonText: 'Cancel',
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d'
            }).then(async function (result) {
                if (!result.isConfirmed) return;

                const formData = new FormData();
                formData.append('csrf_token', getCsrfToken());
                formData.append('session_id', sessionId);

                try {
                    const response = await fetch(`${getBaseUrl()}/index.php?route=sessions.end`, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });

                    let data;

                    try {
                        data = await parseJsonResponse(response, 'END SESSION RESPONSE');
                    } catch (error) {
                        showServerJsonError();
                        return;
                    }

                    if (!data.success) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Could Not End Session',
                            text: data.message || 'Something went wrong.',
                            confirmButtonColor: '#00a651'
                        });
                        return;
                    }

                    Swal.fire({
                        icon: 'success',
                        title: 'Session Ended',
                        text: data.message,
                        timer: 1200,
                        showConfirmButton: false
                    });

                    setTimeout(function () {
                        window.location.reload();
                    }, 1200);

                } catch (error) {
                    console.error('End session error:', error);

                    Swal.fire({
                        icon: 'error',
                        title: 'Connection Error',
                        text: 'Could not connect to the local server.',
                        confirmButtonColor: '#00a651'
                    });
                }
            });
        });
    });

    /**
     * Live Countdown
     */
    function updateSessionCountdowns() {
        const countdowns = document.querySelectorAll('.js-session-countdown');

        countdowns.forEach(function (countdown) {
            const endTimestamp = parseInt(countdown.dataset.endTimestamp, 10);

            if (!endTimestamp) return;

            const nowTimestamp = Math.floor(Date.now() / 1000);
            const remainingSeconds = endTimestamp - nowTimestamp;

            countdown.textContent = formatRemainingTime(remainingSeconds);

            if (remainingSeconds <= 300 && remainingSeconds > 60) {
                countdown.classList.add('text-warning');
                countdown.classList.remove('text-danger');
            }

            if (remainingSeconds <= 60) {
                countdown.classList.add('text-danger');
                countdown.classList.remove('text-warning');
            }

            if (remainingSeconds <= 0) {
                countdown.textContent = 'Expired';
                countdown.classList.add('text-danger');
            }
        });
    }

    updateSessionCountdowns();

    if (document.querySelectorAll('.js-session-countdown').length > 0) {
        setInterval(updateSessionCountdowns, 1000);
    }

    /**
     * Extend Session
     */
    const extendButtons = document.querySelectorAll('.js-extend-session');
    const extendSessionModalEl = document.getElementById('extendSessionModal');
    const extendSessionForm = document.getElementById('extendSessionForm');
    const extendSessionBtn = document.getElementById('extendSessionBtn');

    const extendSessionId = document.getElementById('extendSessionId');
    const extendPcName = document.getElementById('extendPcName');

    const extendPackageSelect = document.getElementById('extendPackageSelect');
    const extendPackageSummary = document.getElementById('extendPackageSummary');
    const extendSummaryMinutes = document.getElementById('extendSummaryMinutes');
    const extendSummaryPrice = document.getElementById('extendSummaryPrice');

    if (extendSessionModalEl && extendSessionForm && extendSessionBtn) {
        const extendSessionModal = new bootstrap.Modal(extendSessionModalEl);

        extendButtons.forEach(function (button) {
            button.addEventListener('click', function () {
                extendSessionId.value = this.dataset.sessionId;
                extendPcName.textContent = this.dataset.pcName;

                extendSessionForm.reset();

                if (extendPackageSummary) {
                    extendPackageSummary.classList.add('d-none');
                }

                extendSessionModal.show();
            });
        });

        if (extendPackageSelect) {
            extendPackageSelect.addEventListener('change', function () {
                const selectedOption = this.options[this.selectedIndex];

                if (!selectedOption.value) {
                    extendPackageSummary.classList.add('d-none');
                    extendSummaryMinutes.textContent = '0 minutes';
                    extendSummaryPrice.textContent = 'R0.00';
                    return;
                }

                const minutes = selectedOption.dataset.minutes;
                const price = selectedOption.dataset.price;

                extendSummaryMinutes.textContent = `+${minutes} minutes`;
                extendSummaryPrice.textContent = `R${parseFloat(price).toFixed(2)}`;

                extendPackageSummary.classList.remove('d-none');
            });
        }

        extendSessionForm.addEventListener('submit', async function (e) {
            e.preventDefault();

            const originalBtnText = extendSessionBtn.innerHTML;
            const formData = new FormData(extendSessionForm);

            extendSessionBtn.disabled = true;
            extendSessionBtn.innerHTML = `
                <span class="spinner-border spinner-border-sm me-2"></span>
                Extending...
            `;

            try {
                const response = await fetch(extendSessionForm.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                let data;

                try {
                    data = await parseJsonResponse(response, 'EXTEND SESSION RESPONSE');
                } catch (error) {
                    showServerJsonError(extendSessionBtn, originalBtnText);
                    return;
                }

                if (!data.success) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Could Not Extend Session',
                        text: data.message || 'Something went wrong.',
                        confirmButtonColor: '#00a651'
                    });

                    extendSessionBtn.disabled = false;
                    extendSessionBtn.innerHTML = originalBtnText;
                    return;
                }

                extendSessionModal.hide();

                Swal.fire({
                    icon: 'success',
                    title: 'Session Extended',
                    text: data.message,
                    timer: 1200,
                    showConfirmButton: false
                });

                setTimeout(function () {
                    window.location.reload();
                }, 1200);

            } catch (error) {
                console.error('Extend session error:', error);

                Swal.fire({
                    icon: 'error',
                    title: 'Connection Error',
                    text: 'Could not connect to the local server.',
                    confirmButtonColor: '#00a651'
                });

                extendSessionBtn.disabled = false;
                extendSessionBtn.innerHTML = originalBtnText;
            }
        });
    }

    /**
     * Auto-expire active sessions
     */
    let expiryCheckRunning = false;

    async function checkExpiredSessions() {
        if (expiryCheckRunning || !getBaseUrl() || !getCsrfToken()) return;

        expiryCheckRunning = true;

        const formData = new FormData();
        formData.append('csrf_token', getCsrfToken());

        try {
            const response = await fetch(`${getBaseUrl()}/index.php?route=sessions.expire`, {
                method: 'POST',
                body: formData,
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            let data;

            try {
                data = await parseJsonResponse(response, 'EXPIRE SESSION RESPONSE');
            } catch (error) {
                console.error('Expired session JSON error:', error);
                expiryCheckRunning = false;
                return;
            }

            if (data.success && parseInt(data.expired_count || 0, 10) > 0) {
                Swal.fire({
                    icon: 'info',
                    title: 'Session Expired',
                    text: `${data.expired_count} session(s) ended and PC(s) locked.`,
                    timer: 1200,
                    showConfirmButton: false
                });

                setTimeout(function () {
                    window.location.reload();
                }, 1200);
            }

        } catch (error) {
            console.error('Expired session check failed:', error);
        }

        expiryCheckRunning = false;
    }

    if (document.querySelectorAll('.js-session-countdown').length > 0) {
        setInterval(checkExpiredSessions, 10000);
    }

    /**
     * Pending Print Job Polling
     */
    const pendingPrintBadge = document.getElementById('pendingPrintBadge');

    let lastPendingJobId = localStorage.getItem('lastPendingJobId') || null;

    async function checkPendingPrintJobs() {
        if (!getBaseUrl()) return;

        try {
            const response = await fetch(`${getBaseUrl()}/index.php?route=print-jobs.pending-summary`, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            let data;

            try {
                data = await parseJsonResponse(response, 'PENDING PRINT RESPONSE');
            } catch (error) {
                console.error('Pending print check failed:', error);
                return;
            }

            if (!data.success) return;

            const pendingCount = parseInt(data.pending_count || 0, 10);

            if (pendingPrintBadge) {
                pendingPrintBadge.textContent = pendingCount;

                if (pendingCount > 0) {
                    pendingPrintBadge.classList.remove('d-none');
                } else {
                    pendingPrintBadge.classList.add('d-none');
                }
            }

            if (data.latest_job && String(data.latest_job.id) !== String(lastPendingJobId)) {
                lastPendingJobId = data.latest_job.id;
                localStorage.setItem('lastPendingJobId', lastPendingJobId);

                Swal.fire({
                    icon: 'info',
                    title: 'New Print Job',
                    html: `
                        <strong>${data.latest_job.pc_name}</strong><br>
                        ${data.latest_job.document_name}<br>
                        Amount: <strong>R${parseFloat(data.latest_job.amount).toFixed(2)}</strong>
                    `,
                    confirmButtonText: 'View Print Jobs',
                    showCancelButton: true,
                    cancelButtonText: 'Later',
                    confirmButtonColor: '#00a651',
                    cancelButtonColor: '#6c757d'
                }).then(function (result) {
                    if (result.isConfirmed) {
                        window.location.href = `${getBaseUrl()}/index.php?route=print-jobs`;
                    }
                });
            }

        } catch (error) {
            console.error('Pending print check failed:', error);
        }
    }

    if (getBaseUrl()) {
        checkPendingPrintJobs();
        setInterval(checkPendingPrintJobs, 7000);
    }

    /**
     * Admin Direct Print
     */
    const adminDirectPrintForm = document.getElementById('adminDirectPrintForm');
    const adminDirectPrintBtn = document.getElementById('adminDirectPrintBtn');
    const adminDirectPrintModalEl = document.getElementById('adminDirectPrintModal');

    if (adminDirectPrintForm && adminDirectPrintBtn && adminDirectPrintModalEl) {
        const adminDirectPrintModal = new bootstrap.Modal(adminDirectPrintModalEl);

        adminDirectPrintForm.addEventListener('submit', async function (e) {
            e.preventDefault();

            const originalText = adminDirectPrintBtn.innerHTML;
            const formData = new FormData(adminDirectPrintForm);

            adminDirectPrintBtn.disabled = true;
            adminDirectPrintBtn.innerHTML = `
                <span class="spinner-border spinner-border-sm me-2"></span>
                Recording...
            `;

            try {
                const response = await fetch(adminDirectPrintForm.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                let data;

                try {
                    data = await parseJsonResponse(response, 'ADMIN DIRECT PRINT RESPONSE');
                } catch (error) {
                    showServerJsonError(adminDirectPrintBtn, originalText);
                    return;
                }

                if (!data.success) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Could Not Record Print',
                        text: data.message || 'Something went wrong.',
                        confirmButtonColor: '#00a651'
                    });

                    adminDirectPrintBtn.disabled = false;
                    adminDirectPrintBtn.innerHTML = originalText;
                    return;
                }

                adminDirectPrintModal.hide();

                Swal.fire({
                    icon: 'success',
                    title: 'Print Recorded',
                    text: data.message,
                    timer: 1200,
                    showConfirmButton: false
                });

                setTimeout(function () {
                    window.location.reload();
                }, 1200);

            } catch (error) {
                console.error('Admin direct print error:', error);

                Swal.fire({
                    icon: 'error',
                    title: 'Connection Error',
                    text: 'Could not connect to the local server.',
                    confirmButtonColor: '#00a651'
                });

                adminDirectPrintBtn.disabled = false;
                adminDirectPrintBtn.innerHTML = originalText;
            }
        });
    }

    /**
     * Internet Packages - Create / Update
     */
    const packageForm = document.getElementById('packageForm');
    const packageModalEl = document.getElementById('packageModal');
    const addPackageBtn = document.getElementById('addPackageBtn');
    const savePackageBtn = document.getElementById('savePackageBtn');

    if (packageForm && packageModalEl && savePackageBtn) {
        const packageModal = new bootstrap.Modal(packageModalEl);

        const packageId = document.getElementById('packageId');
        const packageName = document.getElementById('packageName');
        const packageMinutes = document.getElementById('packageMinutes');
        const packagePrice = document.getElementById('packagePrice');
        const packageStatus = document.getElementById('packageStatus');
        const packageSortOrder = document.getElementById('packageSortOrder');
        const packageModalTitle = document.getElementById('packageModalTitle');

        if (addPackageBtn) {
            addPackageBtn.addEventListener('click', function () {
                packageForm.reset();
                packageId.value = '';
                packageForm.action = `${getBaseUrl()}/index.php?route=packages.store`;
                packageModalTitle.textContent = 'Add Internet Package';
            });
        }

        document.querySelectorAll('.js-edit-package').forEach(function (button) {
            button.addEventListener('click', function () {
                packageId.value = this.dataset.packageId;
                packageName.value = this.dataset.packageName;
                packageMinutes.value = this.dataset.minutes;
                packagePrice.value = this.dataset.price;
                packageStatus.value = this.dataset.status;
                packageSortOrder.value = this.dataset.sortOrder;

                packageForm.action = `${getBaseUrl()}/index.php?route=packages.update`;
                packageModalTitle.textContent = 'Edit Internet Package';

                packageModal.show();
            });
        });

        packageForm.addEventListener('submit', async function (e) {
            e.preventDefault();

            const originalText = savePackageBtn.innerHTML;
            const formData = new FormData(packageForm);

            savePackageBtn.disabled = true;
            savePackageBtn.innerHTML = `
                <span class="spinner-border spinner-border-sm me-2"></span>
                Saving...
            `;

            try {
                const response = await fetch(packageForm.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                let data;

                try {
                    data = await parseJsonResponse(response, 'PACKAGE SAVE RESPONSE');
                } catch (error) {
                    showServerJsonError(savePackageBtn, originalText);
                    return;
                }

                if (!data.success) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Could Not Save Package',
                        text: data.message || 'Something went wrong.',
                        confirmButtonColor: '#00a651'
                    });

                    savePackageBtn.disabled = false;
                    savePackageBtn.innerHTML = originalText;
                    return;
                }

                packageModal.hide();

                Swal.fire({
                    icon: 'success',
                    title: 'Package Saved',
                    text: data.message,
                    timer: 1200,
                    showConfirmButton: false
                });

                setTimeout(function () {
                    window.location.reload();
                }, 1200);

            } catch (error) {
                console.error('Package save error:', error);

                Swal.fire({
                    icon: 'error',
                    title: 'Connection Error',
                    text: 'Could not connect to the local server.',
                    confirmButtonColor: '#00a651'
                });

                savePackageBtn.disabled = false;
                savePackageBtn.innerHTML = originalText;
            }
        });
    }

    /**
     * Internet Packages - Toggle Status
     */
    document.querySelectorAll('.js-toggle-package').forEach(function (button) {
        button.addEventListener('click', function () {
            const packageId = this.dataset.packageId;
            const packageName = this.dataset.packageName;

            Swal.fire({
                icon: 'warning',
                title: 'Change Package Status?',
                text: `Update status for "${packageName}"?`,
                showCancelButton: true,
                confirmButtonText: 'Yes, update',
                cancelButtonText: 'Cancel',
                confirmButtonColor: '#00a651',
                cancelButtonColor: '#6c757d'
            }).then(async function (result) {
                if (!result.isConfirmed) return;

                const formData = new FormData();
                formData.append('csrf_token', getCsrfToken());
                formData.append('package_id', packageId);

                try {
                    const response = await fetch(`${getBaseUrl()}/index.php?route=packages.toggle`, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });

                    let data;

                    try {
                        data = await parseJsonResponse(response, 'PACKAGE TOGGLE RESPONSE');
                    } catch (error) {
                        showServerJsonError();
                        return;
                    }

                    if (!data.success) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Could Not Update Package',
                            text: data.message || 'Something went wrong.',
                            confirmButtonColor: '#00a651'
                        });
                        return;
                    }

                    Swal.fire({
                        icon: 'success',
                        title: 'Package Updated',
                        text: data.message,
                        timer: 1200,
                        showConfirmButton: false
                    });

                    setTimeout(function () {
                        window.location.reload();
                    }, 1200);

                } catch (error) {
                    console.error('Package toggle error:', error);

                    Swal.fire({
                        icon: 'error',
                        title: 'Connection Error',
                        text: 'Could not connect to the local server.',
                        confirmButtonColor: '#00a651'
                    });
                }
            });
        });
    });

    /**
 * Services Pricing
 */
const pricingForm = document.getElementById('pricingForm');
const savePricingBtn = document.getElementById('savePricingBtn');

if (pricingForm && savePricingBtn) {
    pricingForm.addEventListener('submit', async function (e) {
        e.preventDefault();

        const originalText = savePricingBtn.innerHTML;
        const formData = new FormData(pricingForm);

        savePricingBtn.disabled = true;
        savePricingBtn.innerHTML = `
            <span class="spinner-border spinner-border-sm me-2"></span>
            Saving...
        `;

        try {
            const response = await fetch(pricingForm.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            let data;

            try {
                data = await parseJsonResponse(response, 'PRICING UPDATE RESPONSE');
            } catch (error) {
                showServerJsonError(savePricingBtn, originalText);
                return;
            }

            if (!data.success) {
                Swal.fire({
                    icon: 'error',
                    title: 'Could Not Save Pricing',
                    text: data.message || 'Something went wrong.',
                    confirmButtonColor: '#00a651'
                });

                savePricingBtn.disabled = false;
                savePricingBtn.innerHTML = originalText;
                return;
            }

            Swal.fire({
                icon: 'success',
                title: 'Pricing Updated',
                text: data.message,
                timer: 1200,
                showConfirmButton: false
            });

            setTimeout(function () {
                window.location.reload();
            }, 1200);

        } catch (error) {
            console.error('Pricing update error:', error);

            Swal.fire({
                icon: 'error',
                title: 'Connection Error',
                text: 'Could not connect to the local server.',
                confirmButtonColor: '#00a651'
            });

            savePricingBtn.disabled = false;
            savePricingBtn.innerHTML = originalText;
        }
    });
}

/**
 * Manual Services Sales
 */
const serviceSaleForm = document.getElementById('serviceSaleForm');
const saveServiceSaleBtn = document.getElementById('saveServiceSaleBtn');
const serviceType = document.getElementById('serviceType');
const serviceQuantity = document.getElementById('serviceQuantity');
const servicePaymentMethod = document.getElementById('servicePaymentMethod');
const serviceTotalAmount = document.getElementById('serviceTotalAmount');

function updateServiceTotal() {
    if (!serviceType || !serviceQuantity || !serviceTotalAmount) return;

    const selectedOption = serviceType.options[serviceType.selectedIndex];
    const rate = selectedOption && selectedOption.dataset.rate
        ? parseFloat(selectedOption.dataset.rate)
        : 0;

    const quantity = parseInt(serviceQuantity.value || 0, 10);
    const paymentMethod = servicePaymentMethod ? servicePaymentMethod.value : 'cash';

    const total = paymentMethod === 'free' ? 0 : rate * quantity;

    serviceTotalAmount.textContent = `R${total.toFixed(2)}`;
}

if (serviceType) {
    serviceType.addEventListener('change', updateServiceTotal);
}

if (serviceQuantity) {
    serviceQuantity.addEventListener('input', updateServiceTotal);
}

if (servicePaymentMethod) {
    servicePaymentMethod.addEventListener('change', updateServiceTotal);
}

updateServiceTotal();

if (serviceSaleForm && saveServiceSaleBtn) {
    serviceSaleForm.addEventListener('submit', async function (e) {
        e.preventDefault();

        const originalText = saveServiceSaleBtn.innerHTML;
        const formData = new FormData(serviceSaleForm);

        saveServiceSaleBtn.disabled = true;
        saveServiceSaleBtn.innerHTML = `
            <span class="spinner-border spinner-border-sm me-2"></span>
            Recording...
        `;

        try {
            const response = await fetch(serviceSaleForm.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            let data;

            try {
                data = await parseJsonResponse(response, 'SERVICE SALE RESPONSE');
            } catch (error) {
                showServerJsonError(saveServiceSaleBtn, originalText);
                return;
            }

            if (!data.success) {
                Swal.fire({
                    icon: 'error',
                    title: 'Could Not Record Sale',
                    text: data.message || 'Something went wrong.',
                    confirmButtonColor: '#00a651'
                });

                saveServiceSaleBtn.disabled = false;
                saveServiceSaleBtn.innerHTML = originalText;
                return;
            }

            Swal.fire({
                icon: 'success',
                title: 'Service Sale Recorded',
                text: data.message,
                timer: 1200,
                showConfirmButton: false
            });

            setTimeout(function () {
                window.location.reload();
            }, 1200);

        } catch (error) {
            console.error('Service sale error:', error);

            Swal.fire({
                icon: 'error',
                title: 'Connection Error',
                text: 'Could not connect to the local server.',
                confirmButtonColor: '#00a651'
            });

            saveServiceSaleBtn.disabled = false;
            saveServiceSaleBtn.innerHTML = originalText;
        }
    });
}

/**
 * Expenses
 */
const expenseForm = document.getElementById('expenseForm');
const saveExpenseBtn = document.getElementById('saveExpenseBtn');

if (expenseForm && saveExpenseBtn) {
    expenseForm.addEventListener('submit', async function (e) {
        e.preventDefault();

        const originalText = saveExpenseBtn.innerHTML;
        const formData = new FormData(expenseForm);

        saveExpenseBtn.disabled = true;
        saveExpenseBtn.innerHTML = `
            <span class="spinner-border spinner-border-sm me-2"></span>
            Saving...
        `;

        try {
            const response = await fetch(expenseForm.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            let data;

            try {
                data = await parseJsonResponse(response, 'EXPENSE SAVE RESPONSE');
            } catch (error) {
                showServerJsonError(saveExpenseBtn, originalText);
                return;
            }

            if (!data.success) {
                Swal.fire({
                    icon: 'error',
                    title: 'Could Not Save Expense',
                    text: data.message || 'Something went wrong.',
                    confirmButtonColor: '#00a651'
                });

                saveExpenseBtn.disabled = false;
                saveExpenseBtn.innerHTML = originalText;
                return;
            }

            Swal.fire({
                icon: 'success',
                title: 'Expense Saved',
                text: data.message,
                timer: 1200,
                showConfirmButton: false
            });

            setTimeout(function () {
                window.location.reload();
            }, 1200);

        } catch (error) {
            console.error('Expense save error:', error);

            Swal.fire({
                icon: 'error',
                title: 'Connection Error',
                text: 'Could not connect to the local server.',
                confirmButtonColor: '#00a651'
            });

            saveExpenseBtn.disabled = false;
            saveExpenseBtn.innerHTML = originalText;
        }
    });
}

/**
 * Reports Filter
 */
const reportPeriod = document.getElementById('reportPeriod');
const reportStartDate = document.getElementById('reportStartDate');
const reportEndDate = document.getElementById('reportEndDate');

function toggleReportCustomDates() {
    if (!reportPeriod || !reportStartDate || !reportEndDate) return;

    const isCustom = reportPeriod.value === 'custom';

    reportStartDate.style.display = isCustom ? 'block' : 'none';
    reportEndDate.style.display = isCustom ? 'block' : 'none';
}

if (reportPeriod) {
    reportPeriod.addEventListener('change', toggleReportCustomDates);
    toggleReportCustomDates();
}

/**
 * Expenses Filter
 */
const expensePeriod = document.getElementById('expensePeriod');
const expenseStartDate = document.getElementById('expenseStartDate');
const expenseEndDate = document.getElementById('expenseEndDate');

function toggleExpenseCustomDates() {
    if (!expensePeriod || !expenseStartDate || !expenseEndDate) return;

    const isCustom = expensePeriod.value === 'custom';

    expenseStartDate.style.display = isCustom ? 'block' : 'none';
    expenseEndDate.style.display = isCustom ? 'block' : 'none';
}

if (expensePeriod) {
    expensePeriod.addEventListener('change', toggleExpenseCustomDates);
    toggleExpenseCustomDates();
}
});