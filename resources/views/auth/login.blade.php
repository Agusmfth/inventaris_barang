<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#0B2F35">
    <title>Masuk - {{ $schoolSetting->display_name }}</title>
    @vite(['resources/css/app.css', 'resources/css/school-identity.css', 'resources/css/auth-motion.css', 'resources/js/app.js', 'resources/js/login.js'])
</head>
<body class="auth-page">
<main class="auth-layout">
    <section class="auth-identity" @if(file_exists(public_path('images/sekolah.jpg'))) style="--school-image: url('{{ asset('images/sekolah.jpg') }}')" @endif aria-label="Identitas {{ $schoolSetting->display_name }}">
        <div class="auth-overlay"></div>
        <div class="identity-content">
            <div class="identity-header">
                @if($schoolSetting->logo_url)
                    <img src="{{ $schoolSetting->logo_url }}" alt="Logo {{ $schoolSetting->display_name }}" class="school-logo">
                @else
                    <div class="school-logo-fallback" aria-label="Logo sementara {{ $schoolSetting->display_name }}">SD</div>
                @endif
                <div class="identity-title">
                    <span>SISTEM INVENTARIS & ASET</span>
                    <h1>{{ $schoolSetting->display_name }}</h1>
                </div>
            </div>
            <div class="identity-divider"></div>
            <p>Pusat pengelolaan barang dan aset operasional koperasi yang tertib, aman, dan mudah dipantau.</p>
        </div>
        <footer class="identity-footer">
            <i data-lucide="shield-check" aria-hidden="true"></i>
            <div><span>Sistem aman dan terjaga kerahasiaannya</span><small>© {{ date('Y') }} {{ $schoolSetting->display_name }}</small></div>
        </footer>
    </section>

    <section class="auth-form-section">
        <div class="mobile-identity">
            @if($schoolSetting->logo_url)
                <img src="{{ $schoolSetting->logo_url }}" alt="Logo {{ $schoolSetting->display_name }}">
            @else
                <div class="mobile-logo-fallback">SD</div>
            @endif
            <div><strong>{{ $schoolSetting->display_name }}</strong><span>Sistem Inventaris & Aset</span></div>
        </div>

        <div class="auth-form-wrap">
            <header class="auth-form-header">
                <div class="auth-icon"><i data-lucide="lock-keyhole"></i></div>
                <h2>Selamat Datang Kembali</h2>
                <p>Silakan masuk untuk melanjutkan</p>
            </header>

            @if(session('success'))
                <div class="auth-inline-alert success" role="status"><i data-lucide="circle-check"></i><span>{{ session('success') }}</span></div>
            @endif
            @if($errors->has('login'))
                <div class="auth-inline-alert error" role="alert"><i data-lucide="circle-alert"></i><span>{{ $errors->first('login') }}</span></div>
            @endif

            <form method="POST" action="{{ route('login.store') }}" id="loginForm" novalidate>
                @csrf
                <div class="auth-field">
                    <label for="username">Username</label>
                    <div class="auth-input {{ $errors->has('username') || $errors->has('login') ? 'invalid' : '' }}">
                        <i data-lucide="user"></i>
                        <input id="username" name="username" value="{{ old('username') }}" autocomplete="username" autofocus placeholder="Masukkan username">
                    </div>
                    @error('username')<small class="auth-field-error">{{ $message }}</small>@enderror
                </div>

                <div class="auth-field">
                    <label for="password">Password</label>
                    <div class="auth-input {{ $errors->has('password') || $errors->has('login') ? 'invalid' : '' }}">
                        <i data-lucide="lock-keyhole"></i>
                        <input type="password" id="password" name="password" autocomplete="current-password" placeholder="Masukkan password">
                        <button type="button" class="password-toggle" id="togglePassword" aria-label="Tampilkan password" aria-pressed="false"><i data-lucide="eye"></i></button>
                    </div>
                    @error('password')<small class="auth-field-error">{{ $message }}</small>@enderror
                </div>

                <label class="auth-remember" for="remember">
                    <input type="checkbox" name="remember" value="1" id="remember" {{ old('remember') ? 'checked' : '' }}>
                    <span>Ingat saya</span>
                </label>

                <button class="auth-submit" id="loginSubmit" type="submit">
                    <span class="submit-default">Masuk</span>
                    <span class="submit-loading"><span class="auth-spinner" aria-hidden="true"></span>Memproses...</span>
                </button>
            </form>
            <p class="auth-support">Hubungi administrator koperasi apabila Anda mengalami kendala saat masuk.</p>
        </div>
    </section>
</main>
<div class="login-result-overlay" id="loginResult" aria-live="assertive" aria-hidden="true"><div class="login-result-card"><div class="login-result-icon" aria-hidden="true"><svg viewBox="0 0 64 64" fill="none"><circle cx="32" cy="32" r="27"></circle><path d="M20 32.5 28.5 41 45 23.5"></path></svg></div><h2>Login Berhasil</h2><p id="loginResultMessage">Selamat datang.</p></div></div>
</body>
</html>
