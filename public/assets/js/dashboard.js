document.addEventListener('DOMContentLoaded', function () {
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

    if (!startSessionModalEl || !startSessionForm) {
        console.warn('Start session modal/form not found.');
        return;
    }

    const startSessionModal = new bootstrap.Modal(startSessionModalEl);

    startButtons.forEach(function (button) {
        button.addEventListener('click', function () {
            const pcId = this.dataset.pcId;
            const pcName = this.dataset.pcName;

            sessionPcId.value = pcId;
            sessionPcName.textContent = pcName;

            startSessionForm.reset();
            packageSummary.classList.add('d-none');

            startSessionModal.show();
        });
    });

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

            const rawText = await response.text();
            console.log('START SESSION RESPONSE:', rawText);

            let data;

            try {
                data = JSON.parse(rawText);
            } catch (error) {
                Swal.fire({
                    icon: 'error',
                    title: 'Server Error',
                    text: 'Server did not return valid JSON. Check console for details.',
                    confirmButtonColor: '#00a651'
                });

                startSessionBtn.disabled = false;
                startSessionBtn.innerHTML = originalBtnText;
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

    const endButtons = document.querySelectorAll('.js-end-session');

    endButtons.forEach(function (button) {
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
                formData.append('csrf_token', window.NTOZONKE.csrfToken);
                formData.append('session_id', sessionId);

                try {
                    const response = await fetch(`${window.NTOZONKE.baseUrl}/index.php?route=sessions.end`, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                const rawText = await response.text();
                console.log('END SESSION RESPONSE:', rawText);

                let data;

                try {
                    data = JSON.parse(rawText);
                } catch (error) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Server Error',
                        text: 'Server did not return valid JSON.',
                        confirmButtonColor: '#00a651'
                    });
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
setInterval(updateSessionCountdowns, 1000);

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

if (extendSessionModalEl && extendSessionForm) {
    const extendSessionModal = new bootstrap.Modal(extendSessionModalEl);

    extendButtons.forEach(function (button) {
        button.addEventListener('click', function () {
            extendSessionId.value = this.dataset.sessionId;
            extendPcName.textContent = this.dataset.pcName;

            extendSessionForm.reset();
            extendPackageSummary.classList.add('d-none');

            extendSessionModal.show();
        });
    });

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

            const rawText = await response.text();
            console.log('EXTEND SESSION RESPONSE:', rawText);

            let data;

            try {
                data = JSON.parse(rawText);
            } catch (error) {
                Swal.fire({
                    icon: 'error',
                    title: 'Server Error',
                    text: 'Server did not return valid JSON.',
                    confirmButtonColor: '#00a651'
                });

                extendSessionBtn.disabled = false;
                extendSessionBtn.innerHTML = originalBtnText;
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

let expiryCheckRunning = false;

async function checkExpiredSessions() {
    if (expiryCheckRunning) return;

    expiryCheckRunning = true;

    const formData = new FormData();
    formData.append('csrf_token', window.NTOZONKE.csrfToken);

    try {
        const response = await fetch(`${window.NTOZONKE.baseUrl}/index.php?route=sessions.expire`, {
            method: 'POST',
            body: formData,
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });

        const data = await response.json();

        if (data.success && parseInt(data.expired_count || 0) > 0) {
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

setInterval(checkExpiredSessions, 10000);
});