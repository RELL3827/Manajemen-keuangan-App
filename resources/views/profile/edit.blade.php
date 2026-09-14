<x-app-layout :pageTitle="'Pengaturan'">

<div class="row g-4">
    <div class="col-lg-4">
        <div class="ft-card text-center">
            <img id="profileAvatarPreview" src="{{ $user->avatar ? asset('storage/' . $user->avatar) : 'https://ui-avatars.com/api/?name=' . urlencode($user->name) . '&background=0f172a&color=fff' }}" onerror="this.onerror=null;this.src='https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&background=0f172a&color=fff';" class="rounded-circle mx-auto mb-3" style="width:88px;height:88px;object-fit:cover" alt="">
            <h5 class="fw-800 mb-0">{{ $user->name }}</h5>
            <p class="text-muted fs-sm mb-3">{{ $user->email }}</p>
            <div class="d-grid gap-2 fs-sm text-start">
                <div class="d-flex justify-content-between border-bottom pb-2"><span class="text-muted">Transaksi</span><strong class="text-value">{{ $user->transactions_count }}</strong></div>
                <div class="d-flex justify-content-between border-bottom pb-2"><span class="text-muted">Dompet</span><strong class="text-value">{{ $user->accounts_count }}</strong></div>
                <div class="d-flex justify-content-between border-bottom pb-2"><span class="text-muted">Kategori</span><strong class="text-value">{{ $user->categories_count }}</strong></div>
                <div class="d-flex justify-content-between"><span class="text-muted">Kurs</span><strong>{{ $user->currency ?? 'IDR' }}</strong></div>
            </div>
        </div>
    </div>

    <div class="col-lg-8 d-flex flex-column gap-4">
        @if(session('status') === 'profile-updated')
            <div class="alert alert-success d-flex align-items-center gap-2 py-2 mb-0"><i class="bi bi-check-circle-fill"></i> Profil berhasil diperbarui.</div>
        @endif
        @if(session('status') === 'password-updated')
            <div class="alert alert-success d-flex align-items-center gap-2 py-2 mb-0"><i class="bi bi-check-circle-fill"></i> Kata sandi berhasil diubah.</div>
        @endif

        <form class="ft-card" method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">
            @csrf
            @method('PATCH')
            <h6 class="fw-750 mb-3"><i class="bi bi-person me-2"></i>Informasi Profil</h6>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Nama</label>
                    <input type="text" class="form-control" name="name" value="{{ old('name', $user->name) }}" required autocomplete="name">
                    <x-input-error :messages="$errors->get('name')" class="mt-2" />
                </div>
                <div class="col-md-6">
                    <label class="form-label">Email</label>
                    <input type="email" class="form-control" name="email" value="{{ old('email', $user->email) }}" required autocomplete="username">
                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                </div>
                <div class="col-md-6">
                    <label class="form-label">Mata Uang</label>
                    <select class="form-select" name="currency">
                        <option value="IDR" @selected(($user->currency ?? 'IDR') === 'IDR')>IDR — Rupiah</option>
                        <option value="USD" @selected($user->currency === 'USD')>USD — US Dollar</option>
                        <option value="SGD" @selected($user->currency === 'SGD')>SGD — Dolar Singapura</option>
                        <option value="MYR" @selected($user->currency === 'MYR')>MYR — Ringgit Malaysia</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Foto Profil</label>
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <img id="formAvatarPreview" src="{{ $user->avatar ? asset('storage/' . $user->avatar) : 'https://ui-avatars.com/api/?name=' . urlencode($user->name) . '&background=0f172a&color=fff' }}" onerror="this.onerror=null;this.src='https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&background=0f172a&color=fff';" class="rounded-circle border flex-shrink-0" style="width:42px;height:42px;object-fit:cover" alt="">
                        <input type="file" class="form-control" name="avatar" id="avatarInput" accept="image/*">
                    </div>
                    <div class="form-text fs-xs">Format: JPG, PNG, WEBP (Maksimal 2MB).</div>
                    @if($user->avatar)
                        <div class="form-check mt-1">
                            <input class="form-check-input" type="checkbox" name="remove_avatar" value="1" id="removeAvatarCheck">
                            <label class="form-check-label fs-xs text-danger" for="removeAvatarCheck">
                                <i class="bi bi-trash me-1"></i>Hapus foto profil saat ini
                            </label>
                        </div>
                    @endif
                    <x-input-error :messages="$errors->get('avatar')" class="mt-2" />
                </div>
            </div>
            <button type="submit" class="btn btn-ft mt-3">Simpan Profil</button>
        </form>

        <form class="ft-card" method="POST" action="{{ route('password.update') }}">
            @csrf
            @method('PUT')
            <h6 class="fw-750 mb-3"><i class="bi bi-shield-lock me-2"></i>Ubah Kata Sandi</h6>
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Kata Sandi Lama</label>
                    <input type="password" class="form-control" name="current_password" required autocomplete="current-password">
                    <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-2" />
                </div>
                <div class="col-md-4">
                    <label class="form-label">Kata Sandi Baru</label>
                    <input type="password" class="form-control" name="password" required autocomplete="new-password">
                    <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-2" />
                </div>
                <div class="col-md-4">
                    <label class="form-label">Konfirmasi</label>
                    <input type="password" class="form-control" name="password_confirmation" required autocomplete="new-password">
                </div>
            </div>
            <button type="submit" class="btn btn-ft mt-3">Ubah Kata Sandi</button>
        </form>

        <form class="ft-card" method="POST" action="{{ route('profile.update') }}">
            @csrf
            @method('PATCH')
            <h6 class="fw-750 mb-3"><i class="bi bi-bell me-2"></i>Preferensi Notifikasi</h6>
            <div class="d-flex flex-column gap-3">
                <div class="form-check form-switch d-flex align-items-center gap-3">
                    <input class="form-check-input" type="checkbox" name="notif_budget" value="1" id="n1" @checked($user->notif_budget)>
                    <label class="form-check-label" for="n1"><strong>Peringatan Budget</strong><span class="d-block fs-xs text-muted">Beri tahu saat pengeluaran mencapai 90% atau melebihi budget.</span></label>
                </div>
                <div class="form-check form-switch d-flex align-items-center gap-3">
                    <input class="form-check-input" type="checkbox" name="notif_reminder" value="1" id="n2" @checked($user->notif_reminder)>
                    <label class="form-check-label" for="n2"><strong>Pengingat</strong><span class="d-block fs-xs text-muted">Pengingat untuk mencatat transaksi bila belum ada hari ini.</span></label>
                </div>
                <div class="form-check form-switch d-flex align-items-center gap-3">
                    <input class="form-check-input" type="checkbox" name="notif_weekly" value="1" id="n3" @checked($user->notif_weekly)>
                    <label class="form-check-label" for="n3"><strong>Ringkasan Mingguan</strong><span class="d-block fs-xs text-muted">Ringkasan pemasukan & pengeluaran tiap minggu.</span></label>
                </div>
                <div class="form-check form-switch d-flex align-items-center gap-3">
                    <input class="form-check-input" type="checkbox" name="notif_monthly" value="1" id="n4" @checked($user->notif_monthly)>
                    <label class="form-check-label" for="n4"><strong>Ringkasan Bulanan</strong><span class="d-block fs-xs text-muted">Ringkasan tren keuangan tiap awal bulan.</span></label>
                </div>
            </div>
            <button type="submit" class="btn btn-ft mt-3">Simpan Preferensi</button>
        </form>

        <div class="ft-card border-danger">
            <h6 class="fw-750 text-danger mb-2"><i class="bi bi-exclamation-octagon me-2"></i>Zona Berbahaya</h6>
            <p class="fs-sm text-muted mb-3">Hapus akun dan semua data keuanganmu secara permanen. Tindakan ini tidak dapat dibatalkan.</p>
            <form method="POST" action="{{ route('profile.destroy') }}" onsubmit="return confirm('Hapus akun secara permanen? Seluruh data akan hilang.');">
                @csrf
                @method('DELETE')
                <div class="row g-2 align-items-center">
                    <div class="col-md-6">
                        <input type="password" class="form-control" name="password" placeholder="Kata sandi untuk konfirmasi" required autocomplete="current-password">
                        <x-input-error :messages="$errors->userDeletion->get('password')" class="mt-2" />
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-ft-red">Hapus Akun</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const input = document.getElementById('avatarInput');
        const preview1 = document.getElementById('profileAvatarPreview');
        const preview2 = document.getElementById('formAvatarPreview');
        if (input) {
            input.addEventListener('change', function(e) {
                const file = e.target.files && e.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(ev) {
                        if (preview1) preview1.src = ev.target.result;
                        if (preview2) preview2.src = ev.target.result;
                    };
                    reader.readAsDataURL(file);
                }
            });
        }
    });
</script>
@endpush

</x-app-layout>