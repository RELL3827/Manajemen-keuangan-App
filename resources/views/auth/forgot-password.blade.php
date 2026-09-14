<x-guest-layout :pageTitle="'Lupa Kata Sandi'">
    <div class="ft-card">
        <div class="text-center mb-4">
            <div class="d-inline-grid place-items-center rounded-3 bg-light text-secondary mb-3" style="width:52px;height:52px;">
                <i class="bi bi-key fs-4"></i>
            </div>
            <h2 class="fw-800 mb-1">Lupa kata sandi?</h2>
            <p class="text-muted fs-sm mb-0">Masukkan emailmu dan kami kirim link reset.</p>
        </div>

        <x-auth-session-status class="mb-3" :status="session('status')" />

        <form method="POST" action="{{ route('password.email') }}">
            @csrf

            <div class="mb-4">
                <label class="form-label">Email</label>
                <input type="email" class="form-control" name="email" value="{{ old('email') }}" required autofocus placeholder="nama@email.com">
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            <button type="submit" class="btn btn-ft w-100">Kirim Link Reset</button>
        </form>

        <div class="text-center mt-4 fs-sm">
            <a href="{{ route('login') }}" class="fw-600 text-decoration-none"><i class="bi bi-arrow-left me-1"></i>Kembali ke login</a>
        </div>
    </div>
</x-guest-layout>