<x-guest-layout :pageTitle="'Reset Kata Sandi'">
    <div class="ft-card">
        <div class="text-center mb-4">
            <h2 class="fw-800 mb-1">Atur kata sandi baru</h2>
        </div>

        <form method="POST" action="{{ route('password.store') }}">
            @csrf
            <input type="hidden" name="token" value="{{ $request->route('token') }}">

            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" class="form-control" name="email" value="{{ old('email', $request->email) }}" required autofocus autocomplete="username">
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            <div class="mb-3">
                <label class="form-label">Kata Sandi Baru</label>
                <input type="password" class="form-control" name="password" required autocomplete="new-password">
                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>

            <div class="mb-4">
                <label class="form-label">Konfirmasi Kata Sandi</label>
                <input type="password" class="form-control" name="password_confirmation" required autocomplete="new-password">
                <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
            </div>

            <button type="submit" class="btn btn-ft w-100">Simpan Kata Sandi</button>
        </form>
    </div>
</x-guest-layout>