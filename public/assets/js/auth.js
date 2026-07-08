document.addEventListener('DOMContentLoaded', function () {
    const loginForm = document.getElementById('loginForm');
    const loginBtn = document.getElementById('loginBtn');

    if (!loginForm) return;

    loginForm.addEventListener('submit', async function (e) {
        e.preventDefault();

        const formData = new FormData(loginForm);
        const originalButtonText = loginBtn.innerHTML;

        loginBtn.disabled = true;
        loginBtn.innerHTML = `
            <span class="spinner-border spinner-border-sm me-2"></span>
            Signing in...
        `;

        try {
            const response = await fetch(loginForm.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            const data = await response.json();

            if (!data.success) {
                Swal.fire({
                    icon: 'error',
                    title: 'Login Failed',
                    text: data.message || 'Please check your login details.',
                    confirmButtonColor: '#00a651'
                });

                loginBtn.disabled = false;
                loginBtn.innerHTML = originalButtonText;
                return;
            }

            Swal.fire({
                icon: 'success',
                title: 'Welcome',
                text: data.message,
                timer: 1200,
                showConfirmButton: false
            });

            setTimeout(function () {
                window.location.href = data.redirect;
            }, 1200);

        } catch (error) {
            Swal.fire({
                icon: 'error',
                title: 'Connection Error',
                text: 'Could not connect to the system. Please try again.',
                confirmButtonColor: '#00a651'
            });

            loginBtn.disabled = false;
            loginBtn.innerHTML = originalButtonText;
        }
    });
});