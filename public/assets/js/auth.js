document.addEventListener('DOMContentLoaded', function () {
    const loginForm = document.getElementById('loginForm');
    const loginBtn = document.getElementById('loginBtn');

    if (!loginForm || !loginBtn) {
        return;
    }

    async function parseJsonResponse(response) {
        const rawText = await response.text();

        console.log('LOGIN RESPONSE:', rawText);

        // Remove BOM / invisible UTF-8 characters before JSON
        const cleanedText = rawText.replace(/^\uFEFF+/, '').trim();

        try {
            return JSON.parse(cleanedText);
        } catch (error) {
            console.error('Login JSON parse failed:', error);
            console.error('Raw login response:', rawText);
            throw new Error('Server did not return valid JSON.');
        }
    }

    loginForm.addEventListener('submit', async function (e) {
        e.preventDefault();

        const originalText = loginBtn.innerHTML;
        const formData = new FormData(loginForm);

        loginBtn.disabled = true;
        loginBtn.innerHTML = `
            <span class="spinner-border spinner-border-sm me-2"></span>
            Logging in...
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

            let data;

            try {
                data = await parseJsonResponse(response);
            } catch (error) {
                Swal.fire({
                    icon: 'error',
                    title: 'Server Response Error',
                    html: 'Login was sent, but the server returned invalid JSON.<br>Check Console for the raw response.',
                    confirmButtonColor: '#00a651'
                });

                loginBtn.disabled = false;
                loginBtn.innerHTML = originalText;
                return;
            }

            if (!data.success) {
                let debugHtml = '';

                if (data.debug) {
                    debugHtml = `
                        <hr>
                        <div style="text-align:left;font-size:12px;">
                            <strong>File:</strong> ${data.debug.file || 'Unknown'}<br>
                            <strong>Line:</strong> ${data.debug.line || 'Unknown'}
                        </div>
                    `;
                }

                Swal.fire({
                    icon: 'error',
                    title: 'Login Failed',
                    html: `
                        ${data.message || 'Something went wrong.'}
                        ${debugHtml}
                    `,
                    confirmButtonColor: '#00a651'
                });

                loginBtn.disabled = false;
                loginBtn.innerHTML = originalText;
                return;
            }

            Swal.fire({
                icon: 'success',
                title: 'Login Successful',
                text: data.message || 'Redirecting...',
                timer: 900,
                showConfirmButton: false
            });

            setTimeout(function () {
                window.location.href = data.redirect;
            }, 900);

        } catch (error) {
            console.error('Login fetch error:', error);

            Swal.fire({
                icon: 'error',
                title: 'Connection Error',
                text: 'Could not connect to the system. Please try again.',
                confirmButtonColor: '#00a651'
            });

            loginBtn.disabled = false;
            loginBtn.innerHTML = originalText;
        }
    });
});