<x-guest-layout :pageTitle="'Register'">
    <div class="ft-card">
        <div class="text-center mb-4">
            <h2 class="fw-800 mb-1">Buat akun Eltrack</h2>
            <p class="text-muted fs-sm mb-0">Mulai kelola keuangan dengan mudah</p>
        </div>

        <form method="POST" action="{{ route('register') }}">
            @csrf

            <div class="mb-3">
                <label class="form-label">Nama</label>
                <input type="text" class="form-control" name="name" value="{{ old('name') }}" required autofocus autocomplete="name" placeholder="Nama lengkap">
                <x-input-error :messages="$errors->get('name')" class="mt-2" />
            </div>

            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" class="form-control" name="email" value="{{ old('email') }}" required autocomplete="username" placeholder="nama@email.com">
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            <div class="mb-3">
                <label class="form-label">Kata Sandi</label>
                <input type="password" class="form-control" name="password" required autocomplete="new-password" placeholder="Minimal 8 karakter">
                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>

            <div class="mb-4">
                <label class="form-label">Konfirmasi Kata Sandi</label>
                <input type="password" class="form-control" name="password_confirmation" required autocomplete="new-password" placeholder="Ulangi kata sandi">
            </div>

            <button type="submit" class="btn btn-ft w-100">Daftar Gratis</button>
        </form>

        <div class="text-center mt-4 fs-sm text-muted">
            Sudah punya akun?
            <a href="{{ route('login') }}" class="fw-700 text-decoration-none">Masuk</a>
        </div>
    </div>
</x-guest-layout>