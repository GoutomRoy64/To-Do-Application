document.addEventListener('DOMContentLoaded', function() {
    const loginForm = document.getElementById('login-form');
    const registerForm = document.getElementById('register-form');
    const loginCard = document.getElementById('login-card');
    const registerCard = document.getElementById('register-card');
    const showRegisterLink = document.getElementById('show-register');
    const showLoginLink = document.getElementById('show-login');
    const messageArea = document.getElementById('message-area');

    const showRegisterForm = () => {
        loginCard.style.display = 'none';
        registerCard.style.display = 'block';
        messageArea.innerHTML = '';
    };

    const showLoginForm = () => {
        registerCard.style.display = 'none';
        loginCard.style.display = 'block';
        messageArea.innerHTML = '';
    };

    if (window.location.hash === '#register') {
        showRegisterForm();
    }

    showRegisterLink.addEventListener('click', (e) => {
        e.preventDefault();
        showRegisterForm();
    });

    showLoginLink.addEventListener('click', (e) => {
        e.preventDefault();
        showLoginForm();
    });
    
    const showMessage = (message, type = 'danger') => {
        messageArea.innerHTML = `<div class="alert alert-${type}">${message}</div>`;
    };

    // --- Handle Login Form Submission ---
    loginForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        // --- UPDATED: Get email from the form ---
        const email = document.getElementById('login-email').value;
        const password = document.getElementById('login-password').value;
        
        const formData = new FormData();
        formData.append('action', 'login');
        // --- UPDATED: Send email instead of username ---
        formData.append('email', email);
        formData.append('password', password);

        try {
            const response = await fetch('auth.php', { method: 'POST', body: formData });
            const result = await response.json();

            if (result.status === 'success') {
                window.location.href = 'app.php';
            } else {
                showMessage(result.message);
            }
        } catch (error) {
            showMessage('An error occurred. Please try again.');
        }
    });

    // --- Handle Register Form Submission ---
    registerForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const username = document.getElementById('register-username').value;
        const email = document.getElementById('register-email').value;
        const password = document.getElementById('register-password').value;

        const formData = new FormData();
        formData.append('action', 'register');
        formData.append('username', username);
        formData.append('email', email);
        formData.append('password', password);

        try {
            const response = await fetch('auth.php', { method: 'POST', body: formData });
            const result = await response.json();

            if (result.status === 'success') {
                showMessage(result.message, 'success');
                setTimeout(() => {
                    showLoginForm();
                    loginForm.reset();
                    registerForm.reset();
                }, 2000);
            } else {
                showMessage(result.message);
            }
        } catch (error) {
            showMessage('An error occurred. Please try again.');
        }
    });
});
