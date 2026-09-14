<x-app-layout :pageTitle="'Dompet'">

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <p class="text-muted fs-sm mb-0">Kelola akun, saldo, dan dompet mu.</p>
    <button class="btn btn-ft" data-bs-toggle="modal" data-bs-target="#accountModal"><i class="bi bi-plus-lg me-1"></i>Tambahkan Dompet</button>
</div>

<div class="row g-4">
    @foreach($accounts as $account)
        <div class="col-sm-6 col-xl-4">
            <div class="ft-card account-card h-100 position-relative overflow-hidden" data-account="{{ $account->id }}">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <span class="d-inline-grid place-items-center rounded-3" style="width:48px;height:48px;background:{{ $account->color }}1a;color:{{ $account->color }}">
                        <i class="bi {{ $account->icon ?: 'bi-wallet2' }}" style="font-size:1.3rem"></i>
                    </span>
                    <div class="d-flex align-items-center gap-2">
                        @if($account->is_default)
                            <span class="ft-pill pill-green">Default</span>
                        @endif
                        <div class="btn-group btn-group-sm">
                            <button class="btn btn-ghost acct-edit" data-id="{{ $account->id }}" title="Edit"><i class="bi bi-pencil"></i></button>
                            <button class="btn btn-ghost acct-delete" data-id="{{ $account->id }}" title="Hapus"><i class="bi bi-trash"></i></button>
                        </div>
                    </div>
                </div>
                <div class="fs-xs fw-700 text-uppercase text-muted">{{ $account->type_label }}</div>
                <h3 class="fw-800 text-value mt-1 mb-2">@money($account->balance)</h3>
                <div class="fs-sm text-muted mb-3">{{ $account->name }}</div>
                <div class="d-flex justify-content-between fs-xs text-muted">
                    <span>{{ $account->transactions_count }} transaksi</span>
                    <span><span class="text-value">@money($account->initial_balance)</span> saldo awal</span>
                </div>
            </div>
        </div>
    @endforeach

    <div class="col-sm-6 col-xl-4">
        <button class="ft-card w-100 h-100 text-center d-flex flex-column align-items-center justify-content-center gap-2 text-muted" style="border-style:dashed;box-shadow:none" data-bs-toggle="modal" data-bs-target="#accountModal">
            <span class="d-inline-grid place-items-center rounded-circle" style="width:52px;height:52px;background:var(--ft-surface-2)"><i class="bi bi-plus-lg fs-4"></i></span>
            <span class="fw-650 fs-sm">Tambah dompet baru</span>
        </button>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
    const ACCT_CSRF = document.querySelector('meta[name="csrf-token"]').content;
    const acctModal = new bootstrap.Modal('#accountModal');

    function resetAcctForm() {
        const form = document.getElementById('accountForm');
        form.reset();
        document.getElementById('acctFormMethod').value = 'POST';
        document.getElementById('acctFormErr').textContent = '';
        document.getElementById('acctModalTitle').textContent = 'Tambah Dompet';
        document.getElementById('acctColor').value = '#0f172a';
    }

    document.getElementById('accountModal').addEventListener('show.bs.modal', resetAcctForm);

    document.getElementById('accountForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const form = e.target;
        const isEdit = document.getElementById('acctFormMethod').value === 'PUT';
        const id = form.dataset.editId;
        const url = isEdit ? `/accounts/${id}` : '{{ route("accounts.store") }}';
        const res = await fetch(url, {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'X-CSRF-TOKEN': ACCT_CSRF, 'Accept': 'application/json' },
            body: new FormData(form),
        });
        const data = await res.json();
        if (!res.ok) {
            document.getElementById('acctFormErr').textContent = data.errors ? Object.values(data.errors).flat().join(' ') : (data.message || 'Terjadi kesalahan.');
            return;
        }
        location.reload();
    });

    document.addEventListener('click', (e) => {
        const editBtn = e.target.closest('.acct-edit');
        if (editBtn) {
            const id = editBtn.dataset.id;
            fetch(`/accounts/${id}/edit-info`)
                .then(r => r.json())
                .then(d => {
                    const a = d.account;
                    document.getElementById('acctFormMethod').value = 'PUT';
                    document.getElementById('acctModalTitle').textContent = 'Edit Dompet: ' + a.name;
                    document.getElementById('acctName').value = a.name;
                    document.getElementById('acctType').value = a.type;
                    document.getElementById('acctInitial').value = Number(a.initial_balance).toLocaleString('id-ID');
                    document.getElementById('acctColor').value = a.color || '#0f172a';
                    document.getElementById('acctDefault').checked = !!a.is_default;
                    document.getElementById('accountForm').dataset.editId = id;
                    document.getElementById('acctFormErr').textContent = '';
                    acctModal.show();
                });
            return;
        }
        const delBtn = e.target.closest('.acct-delete');
        if (delBtn) {
            if (!window.confirm('Hapus dompet ini?')) return;
            fetch(`/accounts/${delBtn.dataset.id}`, {
                method: 'POST',
                credentials: 'same-origin',
                headers: { 'X-CSRF-TOKEN': ACCT_CSRF, 'Accept': 'application/json' },
                body: new URLSearchParams({ _method: 'DELETE' }),
            }).then(res => {
                if (res.status === 422) return res.json().then(d => window.alert(d.message));
                location.reload();
            });
        }
    });
    });
</script>
@endpush

<div class="modal fade" id="accountModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="accountForm" novalidate>
                @csrf
                <input type="hidden" name="_method" value="POST" id="acctFormMethod">
                <div class="modal-header">
                    <h5 class="modal-title" id="acctModalTitle">Tambah Dompet</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nama Dompet</label>
                        <input type="text" class="form-control" name="name" id="acctName" maxlength="60" placeholder="cth: Tunai, Mandiri, GoPay" required>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Tipe</label>
                            <select class="form-select" name="type" id="acctType" required>
                                <option value="cash">Cash</option>
                                <option value="bank">Bank</option>
                                <option value="ewallet">E-Wallet</option>
                                <option value="other">Lainnya</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Saldo Awal (Rp)</label>
                            <input type="text" class="form-control" name="initial_balance" id="acctInitial" data-format-money inputmode="numeric" value="0" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label">Warna</label>
                            <input type="color" class="form-control form-control-color" name="color" id="acctColor" value="#0f172a" title="Pilih warna">
                        </div>
                        <div class="col-6 d-flex align-items-end">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" name="is_default" id="acctDefault" value="1">
                                <label class="form-check-label fs-sm" for="acctDefault">Jadikan dompet default</label>
                            </div>
                        </div>
                    </div>
                    <span class="fs-xs text-danger" id="acctFormErr"></span>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-ghost" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-ft">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

</x-app-layout>