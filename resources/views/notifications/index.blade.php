<x-app-layout :pageTitle="'Notifikasi'">

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <p class="text-muted fs-sm mb-0 mb-0">Peringatan budget, ringkasan, dan pengingat.</p>
    <button class="btn btn-ft-outline btn-sm" id="btnReadAll"><i class="bi bi-check2-all me-1"></i>Tandai semua dibaca</button>
</div>

@forelse($notifications as $notif)
    <div class="ft-notif-item {{ $notif->read_at ? '' : 'unread' }}" data-notif="{{ $notif->id }}" style="cursor:{{ $notif->read_at ? 'default' : 'pointer' }}">
        <span class="ni-icon" style="
            @if($notif->type === 'budget') background:#fffbeb;color:var(--ft-amber)
            @elseif(in_array($notif->type, ['weekly', 'monthly'])) background:#eff6ff;color:var(--ft-blue)
            @endif
        ">
            <i class="bi {{ match($notif->type) { 'budget' => 'bi-speedometer2', 'weekly' => 'bi-calendar-week', 'monthly' => 'bi-calendar-month', 'reminder' => 'bi-bell', default => 'bi-stars' } }}"></i>
        </span>
        <div class="flex-grow-1">
            <div class="d-flex justify-content-between align-items-start gap-2">
                <div class="fw-700">{{ $notif->title }}</div>
                <span class="fs-xs text-muted text-value">{{ $notif->created_at->translatedFormat('d M Y, H:i') }}</span>
            </div>
            <p class="fs-sm text-muted mb-1">{{ $notif->message }}</p>
            @if(!$notif->read_at)
                <span class="fs-xs pill-green ft-pill">Belum dibaca</span>
            @endif
        </div>
    </div>
@empty
    <div class="ft-card">
        <div class="ft-empty">
            <div class="ft-empty-icon"><i class="bi bi-bell-slash"></i></div>
            <h6>Tidak ada notifikasi</h6>
            <p>Notifikasi budget, ringkasan, dan pengingat akan muncul di sini.</p>
        </div>
    </div>
@endforelse

@if($notifications->hasPages())
    <div class="mt-3">
        {{ $notifications->links() }}
    </div>
@endif

@push('scripts')
<script>
    const NOTIF_CSRF = document.querySelector('meta[name="csrf-token"]').content;
    document.querySelectorAll('[data-notif]').forEach(el => {
        el.addEventListener('click', () => {
            const id = el.dataset.notif;
            fetch(`/notifications/${id}/read`, {
                method: 'POST',
                credentials: 'same-origin',
                headers: { 'X-CSRF-TOKEN': NOTIF_CSRF, 'Accept': 'application/json' },
            }).then(() => {
                el.classList.remove('unread');
                el.style.cursor = 'default';
                const badge = el.querySelector('.ft-pill-green, .pill-green');
                if (badge) badge.remove();
            });
        });
    });
    document.getElementById('btnReadAll').addEventListener('click', () => {
        fetch('{{ route("notifications.read-all") }}', {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'X-CSRF-TOKEN': NOTIF_CSRF, 'Accept': 'application/json' },
        }).then(() => {
            document.querySelectorAll('.ft-notif-item.unread').forEach(el => {
                el.classList.remove('unread');
                const b = el.querySelector('.pill-green');
                if (b) b.remove();
            });
        });
    });
</script>
@endpush

</x-app-layout>