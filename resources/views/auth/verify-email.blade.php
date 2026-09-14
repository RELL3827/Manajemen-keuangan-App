<x-guest-layout :pageTitle="'Verifikasi Email'">
    <div class="ft-card">
        <div class="text-center mb-4">
            <div class="d-inline-grid place-items-center rounded-3 bg-light text-secondary mb-3" style="width:52px;height:52px;">
                <i class="bi bi-envelope-check fs-4"></i>
            </div>
            <h2 class="fw-800 mb-1">Verifikasi email kamu</h2>
            <p class="text-muted fs-sm mb-0">Kami mengirim link verifikasi ke emailmu. Klik link tersebut untuk melanjutkan.</p>
        </div>

        @if(session('status') == 'verification-link-sent')
            <div class="alert alert-success py-2 fs-sm"><i class="bi bi-check-circle-fill me-1"></i>Link verifikasi baru telah dikirim ke emailmu.</div>
        @endif

        <div class="d-flex flex-column gap-2">
            <form method="POST" action="{{ route('verification.send') }}">
                @csrf
                <button type="submit" class="btn btn-ft w-100">Kirim Ulang Email Verifikasi</button>
            </form>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn btn-ghost w-100">Keluar</button>
            </form>
        </div>
    </div>
</x-guest-layout>