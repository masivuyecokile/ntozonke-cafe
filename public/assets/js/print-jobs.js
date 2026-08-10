document.addEventListener('DOMContentLoaded', function () {
    function getBaseUrl() {
        return window.NTOZONKE && window.NTOZONKE.baseUrl ? window.NTOZONKE.baseUrl : '';
    }

    function getCsrfToken() {
        return window.NTOZONKE && window.NTOZONKE.csrfToken ? window.NTOZONKE.csrfToken : '';
    }

    async function parseJsonResponse(response, logLabel = 'SERVER RESPONSE') {
        const rawText = await response.text();

        console.log(logLabel + ':', rawText);

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
            html: 'The request was completed, but the server did not return valid JSON.<br>Check Console for the raw response.',
            confirmButtonColor: '#00a651'
        });

        if (button && originalText !== null) {
            button.disabled = false;
            button.innerHTML = originalText;
        }
    }

    /**
     * Test Incoming Print Job
     */
    const testPrintJobForm = document.getElementById('testPrintJobForm');
    const testPrintJobBtn = document.getElementById('testPrintJobBtn');
    const testPrintJobModalEl = document.getElementById('testPrintJobModal');

    if (testPrintJobForm && testPrintJobBtn && testPrintJobModalEl) {
        const testPrintJobModal = new bootstrap.Modal(testPrintJobModalEl);

        testPrintJobForm.addEventListener('submit', async function (e) {
            e.preventDefault();

            const originalText = testPrintJobBtn.innerHTML;
            const formData = new FormData(testPrintJobForm);

            testPrintJobBtn.disabled = true;
            testPrintJobBtn.innerHTML = `
                <span class="spinner-border spinner-border-sm me-2"></span>
                Creating...
            `;

            try {
                const response = await fetch(testPrintJobForm.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                let data;

                try {
                    data = await parseJsonResponse(response, 'TEST PRINT JOB RESPONSE');
                } catch (error) {
                    showServerJsonError(testPrintJobBtn, originalText);
                    return;
                }

                if (!data.success) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Could Not Create Print Job',
                        text: data.message || 'Something went wrong.',
                        confirmButtonColor: '#00a651'
                    });

                    testPrintJobBtn.disabled = false;
                    testPrintJobBtn.innerHTML = originalText;
                    return;
                }

                testPrintJobModal.hide();

                Swal.fire({
                    icon: 'success',
                    title: 'Print Job Created',
                    text: data.message,
                    timer: 1200,
                    showConfirmButton: false
                });

                setTimeout(function () {
                    window.location.reload();
                }, 1200);

            } catch (error) {
                console.error('Test print job error:', error);

                Swal.fire({
                    icon: 'error',
                    title: 'Connection Error',
                    text: 'Could not connect to the local server.',
                    confirmButtonColor: '#00a651'
                });

                testPrintJobBtn.disabled = false;
                testPrintJobBtn.innerHTML = originalText;
            }
        });
    }

    /**
     * Approve / Print Job
     */
    document.querySelectorAll('.js-approve-print').forEach(function (button) {
        button.addEventListener('click', function () {
            const printJobId = this.dataset.printJobId || this.dataset.jobId;

            Swal.fire({
                icon: 'question',
                title: 'Approve Print Job?',
                text: 'This will mark the job as printed and record the sale.',
                showCancelButton: true,
                confirmButtonText: 'Yes, Approve',
                cancelButtonText: 'Cancel',
                confirmButtonColor: '#00a651',
                cancelButtonColor: '#6c757d'
            }).then(async function (result) {
                if (!result.isConfirmed) return;

                const formData = new FormData();
                formData.append('csrf_token', getCsrfToken());
                formData.append('print_job_id', printJobId);

                try {
                    const response = await fetch(`${getBaseUrl()}/index.php?route=print-jobs.approve`, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });

                    let data;

                    try {
                        data = await parseJsonResponse(response, 'APPROVE PRINT JOB RESPONSE');
                    } catch (error) {
                        showServerJsonError();
                        return;
                    }

                    if (!data.success) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Could Not Approve Print Job',
                            text: data.message || 'Something went wrong.',
                            confirmButtonColor: '#00a651'
                        });
                        return;
                    }

                    Swal.fire({
                        icon: 'success',
                        title: 'Print Job Approved',
                        text: data.message,
                        timer: 1200,
                        showConfirmButton: false
                    });

                    setTimeout(function () {
                        window.location.reload();
                    }, 1200);

                } catch (error) {
                    console.error('Approve print job error:', error);

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
     * Reject Print Job
     */
    document.querySelectorAll('.js-reject-print').forEach(function (button) {
        button.addEventListener('click', function () {
            const printJobId = this.dataset.printJobId || this.dataset.jobId;

            Swal.fire({
                icon: 'warning',
                title: 'Reject Print Job?',
                input: 'text',
                inputLabel: 'Reason',
                inputPlaceholder: 'Example: Customer cancelled',
                showCancelButton: true,
                confirmButtonText: 'Reject Job',
                cancelButtonText: 'Cancel',
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                inputValidator: function (value) {
                    if (!value) {
                        return 'Please enter a rejection reason.';
                    }
                }
            }).then(async function (result) {
                if (!result.isConfirmed) return;

                const formData = new FormData();
                formData.append('csrf_token', getCsrfToken());
                formData.append('print_job_id', printJobId);
                formData.append('reason', result.value);

                try {
                    const response = await fetch(`${getBaseUrl()}/index.php?route=print-jobs.reject`, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });

                    let data;

                    try {
                        data = await parseJsonResponse(response, 'REJECT PRINT JOB RESPONSE');
                    } catch (error) {
                        showServerJsonError();
                        return;
                    }

                    if (!data.success) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Could Not Reject Print Job',
                            text: data.message || 'Something went wrong.',
                            confirmButtonColor: '#00a651'
                        });
                        return;
                    }

                    Swal.fire({
                        icon: 'success',
                        title: 'Print Job Rejected',
                        text: data.message,
                        timer: 1200,
                        showConfirmButton: false
                    });

                    setTimeout(function () {
                        window.location.reload();
                    }, 1200);

                } catch (error) {
                    console.error('Reject print job error:', error);

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
});