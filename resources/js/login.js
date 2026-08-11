const initLogin = () => {
    const form = document.getElementById('loginForm');
    if (!form || form.dataset.initialized === '1') return;
    form.dataset.initialized = '1';

    const submit = document.getElementById('loginSubmit');
    const password = document.getElementById('password');
    const toggle = document.getElementById('togglePassword');
    const error = document.querySelector('.auth-inline-alert.error');
    const success = document.querySelector('.auth-inline-alert.success');

    if (error) {
        document.querySelector('.auth-form-wrap')?.classList.add('login-has-error');
        window.setTimeout(() => document.getElementById('username')?.focus(), 250);
    }
    if (success) document.querySelector('.auth-form-wrap')?.classList.add('login-has-success');

    toggle?.addEventListener('click', () => {
        const willShow = password.type === 'password';
        password.type = willShow ? 'text' : 'password';
        toggle.setAttribute('aria-label', willShow ? 'Sembunyikan password' : 'Tampilkan password');
        toggle.setAttribute('aria-pressed', String(willShow));
        toggle.querySelector('svg')?.setAttribute('data-lucide', willShow ? 'eye-off' : 'eye');
        window.refreshLucideIcons?.();
    });

    form?.addEventListener('submit', async event => {
        event.preventDefault();
        if (form.dataset.submitting === 'true') {
            return;
        }
        form.dataset.submitting = 'true';
        submit.disabled = true;
        submit.classList.add('is-loading');
        submit.setAttribute('aria-busy', 'true');

        try {
            const response = await fetch(form.action, { method: 'POST', body: new FormData(form), headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
            const data = await response.json();
            if (!response.ok) throw new Error(data.message || Object.values(data.errors || {})[0]?.[0] || 'Login gagal. Silakan coba kembali.');
            const overlay = document.getElementById('loginResult');
            const message = document.getElementById('loginResultMessage');
            if (message) message.textContent = data.message || 'Login berhasil. Menyiapkan dashboard Anda...';
            overlay?.classList.add('show'); overlay?.setAttribute('aria-hidden', 'false');
            window.setTimeout(() => { window.location.replace(data.redirect); }, 900);
        } catch (requestError) {
            let alert = document.querySelector('.auth-inline-alert.error');
            if (!alert) { alert = document.createElement('div'); alert.className = 'auth-inline-alert error'; alert.setAttribute('role', 'alert'); alert.innerHTML = '<i data-lucide="circle-alert"></i><span></span>'; form.before(alert); }
            alert.querySelector('span').textContent = requestError.message;
            alert.classList.remove('login-error-replay'); void alert.offsetWidth; alert.classList.add('login-error-replay');
            document.querySelector('.auth-form-wrap')?.classList.add('login-has-error');
            password.value = ''; document.getElementById('username')?.focus(); window.refreshLucideIcons?.();
            form.dataset.submitting = 'false'; submit.disabled = false; submit.classList.remove('is-loading'); submit.removeAttribute('aria-busy');
        }
    });

    const resetLoginFeedback = () => {
        const overlay = document.getElementById('loginResult');
        overlay?.classList.remove('show'); overlay?.setAttribute('aria-hidden', 'true');
        if (form) form.dataset.submitting = 'false';
        if (submit) { submit.disabled = false; submit.classList.remove('is-loading'); submit.removeAttribute('aria-busy'); }
    };
    window.addEventListener('pagehide', resetLoginFeedback);
    window.addEventListener('pageshow', event => { resetLoginFeedback(); if (event.persisted) window.location.reload(); });
};

initLogin();
document.addEventListener('turbo:load', initLogin);
