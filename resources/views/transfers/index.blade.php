<x-app-layout :pageTitle="'Transfer'">

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <p class="text-muted fs-sm mb-0">Pindahkan uang antar dompet. Tidak memengaruhi laporan masuk/keluar.</p>
    <button class="btn btn-ft" data-bs-toggle="modal" data-bs-target="#transferModal"><i class="bi bi-arrow-left-right me-1"></i>Transfer</button>
</div>

<div class="ft-card no-pad overflow-hidden">
    <div class="table-responsive">
        <table class="ft-table">
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Dari</th>
                    <th>Ke</th>
                    <th>Deskripsi</th>
                    <th class="text-end">Nominal</th>
                    <th class="text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($transfers as $transfer)
                    <tr>
                        <td class="num">{{ $transfer->transfer_date->translatedFormat('d M Y') }}</td>
                        <td>
                            <span class="ft-cat-chip">
                                <span class="cc-dot" style="background:{{ $transfer->fromAccount->color }}1a;color:{{ $transfer->fromAccount->color }}"><i class="bi {{ $transfer->fromAccount->icon ?: 'bi-wallet2' }}"></i></span>
                                <span class="fs-sm">{{ $transfer->fromAccount->name }}</span>
                            </span>
                        </td>
                        <td>
                            <span class="ft-cat-chip">
                                <span class="cc-dot" style="background:{{ $transfer->toAccount->color }}1a;color:{{ $transfer->toAccount->color }}"><i class="bi {{ $transfer->toAccount->icon ?: 'bi-wallet2' }}"></i></span>
                                <span class="fs-sm">{{ $transfer->toAccount->name }}</span>
                            </span>
                        </td>
                        <td class="fs-sm">{{ $transfer->description ?: '—' }}</td>
                        <td class="text-end num"><span class="text-value fw-700">@money($transfer->amount)</span></td>
                        <td class="text-end">
                            <form method="POST" action="{{ route('transfers.destroy', $transfer) }}" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-ghost btn-sm" data-confirm="Batalkan transfer ini? Saldo akan dikembalikan masing-masing." title="Batalkan"><i class="bi bi-x-circle"></i></button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6">
                            <div class="ft-empty py-4">
                                <div class="ft-empty-icon"><i class="bi bi-arrow-left-right"></i></div>
                                <h6>Belum ada transfer</h6>
                                <p>Pindahkan uang antar dompet untuk saldo yang akurat per akun.</p>
                                <button class="btn btn-ft btn-sm" data-bs-toggle="modal" data-bs-target="#transferModal">Buat Transfer</button>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($transfers->hasPages())
        <div class="p-3 border-top">
            {{ $transfers->links() }}
        </div>
    @endif
</div>

<div class="modal fade" id="transferModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="transferForm" method="POST" action="{{ route('transfers.store') }}" novalidate>
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-arrow-left-right me-2"></i>Transfer Antar Dompet</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Dari Akun</label>
                        <select class="form-select" name="from_account_id" id="trFrom" required>
                            @foreach($accounts as $acc)
                                <option value="{{ $acc->id }}">{{ $acc->name }} (saldo <span class="text-value">@money($acc->balance)</span>)</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Ke Akun</label>
                        <select class="form-select" name="to_account_id" id="trTo" required>
                            @foreach($accounts as $acc)
                                <option value="{{ $acc->id }}">{{ $acc->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nominal (Rp)</label>
                        <input type="text" class="form-control" name="amount" data-format-money inputmode="numeric" placeholder="0" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tanggal</label>
                        <input type="date" class="form-control" name="transfer_date" value="{{ now()->toDateString() }}" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Deskripsi <span class="text-muted fw-normal">(opsional)</span></label>
                        <input type="text" class="form-control" name="description" maxlength="255" placeholder="cth: pinjaman, belanja bareng">
                    </div>
                    <span class="fs-xs text-danger" id="trFormErr"></span>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-ghost" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-ft">Transfer</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    (() => {
        const form = document.getElementById('transferForm');
        const errBox = document.getElementById('trFormErr');
        const from = document.getElementById('trFrom');
        const to = document.getElementById('trTo');

        document.getElementById('transferModal').addEventListener('show.bs.modal', () => {
            errBox.textContent = '';
            form.reset();
            form.querySelector('[name="transfer_date"]').value = new Date().toISOString().slice(0, 10);
        });

        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            errBox.textContent = '';
            if (from.value === to.value) { errBox.textContent = 'Akun sumber dan tujuan harus berbeda.'; return; }
            const res = await fetch(form.action, {
                method: 'POST',
                credentials: 'same-origin',
                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' },
                body: new FormData(form),
            });
            const data = await res.json();
            if (!res.ok) {
                errBox.textContent = data.message || 'Terjadi kesalahan.';
                return;
            }
            location.reload();
        });
    })();
</script>
@endpush

</x-app-layout>