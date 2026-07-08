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
});