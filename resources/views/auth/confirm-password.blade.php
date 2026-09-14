<x-guest-layout :pageTitle="'Konfirmasi Kata Sandi'">
    <div class="ft-card">
        <div class="text-center mb-4">
            <div class="d-inline-grid place-items-center rounded-3 bg-light text-secondary mb-3" style="width:52px;height:52px;">
                <i class="bi bi-shield-lock fs-4"></i>
            </div>
            <h2 class="fw-800 mb-1">Konfirmasi kata sandi</h2>
            <p class="text-muted fs-sm mb-0">Ini adalah area aman. Konfirmasi kata sandimu untuk melanjutkan.</p>
        </div>

        <form method="POST" action="{{ route('password.confirm') }}">
            @csrf

            <div class="mb-4">
                <label class="form-label">Kata Sandi</label>
                <input type="password" class="form-control" name="password" required autocomplete="current-password" placeholder="••••••••">
                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>

            <button type="submit" class="btn btn-ft w-100">Konfirmasi</button>
        </form>
    </div>
</x-guest-layout>