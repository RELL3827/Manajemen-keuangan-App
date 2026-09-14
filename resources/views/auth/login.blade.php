<x-guest-layout :pageTitle="'Login'">
    <div class="ft-card">
        <div class="text-center mb-4">
            <h2 class="fw-800 mb-1">Selamat datang kembali</h2>
            <p class="text-muted fs-sm mb-0">Masuk untuk mengelola keuanganmu</p>
        </div>

        <x-auth-session-status class="mb-3" :status="session('status')" />

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" class="form-control" name="email" value="{{ old('email') }}" required autofocus autocomplete="username" placeholder="nama@email.com">
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            <div class="mb-3">
                <label class="form-label">Kata Sandi</label>
                <input type="password" class="form-control" name="password" required autocomplete="current-password" placeholder="••••••••">
                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>

            <div class="d-flex justify-content-between align-items-center mb-4">
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" name="remember" id="remember">
                    <label class="form-check-label fs-sm" for="remember">Ingat saya</label>
                </div>
                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" class="fs-sm fw-600 text-decoration-none">Lupa kata sandi?</a>
                @endif
            </div>

            <button type="submit" class="btn btn-ft w-100">Masuk</button>
        </form>

        <div class="text-center mt-4 fs-sm text-muted">
            Belum punya akun?
            <a href="{{ route('register') }}" class="fw-700 text-decoration-none">Daftar gratis</a>
        </div>
    </div>
</x-guest-layout>