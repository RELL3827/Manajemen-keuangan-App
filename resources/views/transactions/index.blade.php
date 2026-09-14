<x-app-layout :pageTitle="'Transaksi'">

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <p class="text-muted fs-sm mb-0">Semua catatan uang masuk & keluar.</p>
    <button class="btn btn-ft" id="btnNewTransaction"><i class="bi bi-plus-lg me-1"></i>Transaksi</button>
</div>

<form method="GET" action="{{ route('transactions.index') }}" id="filterForm" class="ft-filter-bar">
    <input type="search" class="form-control" name="q" value="{{ request('q') }}" placeholder="Cari: makan, pengeluaran bulan ini…" style="flex:1;min-width:220px">
    <select class="form-select" name="type" data-auto-submit>
        <option value="">Semua jenis</option>
        <option value="income" @selected(request('type') === 'income')>Uang Masuk</option>
        <option value="expense" @selected(request('type') === 'expense')>Uang Keluar</option>
    </select>
    <select class="form-select" name="category_id" data-auto-submit>
        <option value="">Semua kategori</option>
        @foreach($categories as $cat)
            <option value="{{ $cat->id }}" @selected((string) request('category_id') === (string) $cat->id)>{{ $cat->name }}</option>
        @endforeach
    </select>
    <select class="form-select" name="account_id" data-auto-submit>
        <option value="">Semua akun</option>
        @foreach($accounts as $acc)
            <option value="{{ $acc->id }}" @selected((string) request('account_id') === (string) $acc->id)>{{ $acc->name }}</option>
        @endforeach
    </select>
    <select class="form-select" name="source" data-auto-submit>
        <option value="">Semua sumber</option>
        <option value="manual" @selected(request('source') === 'manual')>Manual</option>
        <option value="voice" @selected(request('source') === 'voice')>Voice</option>
    </select>
    <input type="date" class="form-control" name="from" value="{{ request('from') }}" data-auto-submit>
    <input type="date" class="form-control" name="to" value="{{ request('to') }}" data-auto-submit>
    <select class="form-select" name="sort" data-auto-submit>
        <option value="date_desc" @selected(request('sort') === 'date_desc')>Tanggal terbaru</option>
        <option value="date_asc" @selected(request('sort') === 'date_asc')>Tanggal terlama</option>
        <option value="amount_desc" @selected(request('sort') === 'amount_desc')>Nominal terbesar</option>
        <option value="amount_asc" @selected(request('sort') === 'amount_asc')>Nominal terkecil</option>
        <option value="newest" @selected(request('sort') === 'newest')>Paling baru dibuat</option>
        <option value="oldest" @selected(request('sort') === 'oldest')>Paling lama dibuat</option>
    </select>
    @if(request()->hasAny(['q', 'type', 'category_id', 'account_id', 'source', 'from', 'to', 'sort']))
        <a href="{{ route('transactions.index') }}" class="btn btn-ghost"><i class="bi bi-arrow-counterclockwise me-1"></i>Reset</a>
    @endif
</form>

<div class="ft-card no-pad mb-3 mb-lg-4 overflow-hidden">
    <div class="table-responsive">
        <table class="ft-table">
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Kategori</th>
                    <th>Deskripsi</th>
                    <th class="d-none d-md-table-cell">Akun</th>
                    <th class="d-none d-lg-table-cell">Metode</th>
                    <th class="text-end">Nominal</th>
                    <th class="text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($transactions as $tx)
                    <tr class="cursor-pointer" data-detail="{{ $tx->id }}" style="cursor:pointer">
                        <td class="num" data-detail="{{ $tx->id }}">{{ $tx->transaction_date->translatedFormat('d M Y') }}</td>
                        <td data-detail="{{ $tx->id }}">
                            <span class="ft-cat-chip">
                                <span class="cc-dot" style="background:{{ $tx->category->color }}1a;color:{{ $tx->category->color }}"><i class="bi {{ $tx->category->icon }}"></i></span>
                                <span class="fs-sm">{{ $tx->category->name }}</span>
                            </span>
                        </td>
                        <td data-detail="{{ $tx->id }}">
                            <div class="d-flex flex-column">
                                <span>{{ $tx->description ?: '—' }}</span>
                                <span class="fs-xs text-muted d-flex align-items-center gap-1">
                                    @if($tx->source === 'voice')<i class="bi bi-mic"></i>@endif
                                    {{ $tx->type === 'income' ? 'Uang Masuk' : 'Uang Keluar' }}
                                </span>
                            </div>
                        </td>
                        <td class="d-none d-md-table-cell" data-detail="{{ $tx->id }}">{{ $tx->account->name }}</td>
                        <td class="d-none d-lg-table-cell" data-detail="{{ $tx->id }}">{{ $tx->payment_method }}</td>
                        <td class="text-end num {{ $tx->type === 'income' ? 'amount-income' : 'amount-expense' }}" data-detail="{{ $tx->id }}">
                            {{ $tx->type === 'income' ? '+' : '-' }}<span class="text-value">@money($tx->amount)</span>
                        </td>
                        <td class="text-end">
                            <div class="btn-group btn-group-sm">
                                <button type="button" class="btn btn-ghost btn-tx-detail" data-id="{{ $tx->id }}" title="Detail"><i class="bi bi-eye"></i></button>
                                <button type="button" class="btn btn-ghost btn-tx-edit" data-id="{{ $tx->id }}" title="Edit"><i class="bi bi-pencil"></i></button>
                                <button type="button" class="btn btn-ghost btn-tx-delete" data-id="{{ $tx->id }}" data-type="{{ $tx->type }}" title="Hapus"><i class="bi bi-trash"></i></button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7">
                            <div class="ft-empty py-4">
                                <div class="ft-empty-icon"><i class="bi bi-inbox"></i></div>
                                <h6>Tidak ada transaksi</h6>
                                <p>Ubah filter atau tambahkan transaksi baru.</p>
                                <button class="btn btn-ft btn-sm" id="btnNewTransaction2"><i class="bi bi-plus-lg me-1"></i>Tambah Transaksi</button>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($transactions->hasPages())
        <div class="p-3 border-top">
            {{ $transactions->links() }}
        </div>
    @endif
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
    @php $ftCategories = $categories->map(fn($c) => ['id' => $c->id, 'name' => $c->name, 'type' => $c->type, 'icon' => $c->icon, 'color' => $c->color])->values(); @endphp
    window.FT_DATA = { categories: @json($ftCategories) };

    const CSRF = document.querySelector('meta[name="csrf-token"]').content;
    const txModal = new bootstrap.Modal('#transactionModal');

    document.querySelectorAll('[data-auto-submit]').forEach((el) => {
        el.addEventListener('change', () => document.getElementById('filterForm').submit());
    });

    function newTransaction(type) {
        document.getElementById('txMethod').value = 'POST';
        document.getElementById('txFormError').textContent = '';
        document.getElementById('txModalTitle').textContent = 'Tambah Transaksi';
        document.getElementById('transactionForm').reset();
        document.getElementById('txDate').value = new Date().toISOString().slice(0, 10);
        document.getElementById('txType').value = type || 'expense';
        setTypeUI(type || 'expense');
        fillCategoryOptions();
        txModal.show();
    }

    document.getElementById('btnNewTransaction')?.addEventListener('click', () => newTransaction({!! json_encode(request('type')) !!}));
    document.getElementById('btnNewTransaction2')?.addEventListener('click', () => newTransaction('expense'));

    function setTypeUI(type) {
        document.querySelectorAll('#txTypeGroup .btn').forEach((b) => {
            const active = b.dataset.type === type;
            b.classList.toggle('active', active);
        });
        fillCategoryOptions();
    }

    document.querySelectorAll('#txTypeGroup .btn').forEach((btn) => {
        btn.addEventListener('click', () => {
            document.getElementById('txType').value = btn.dataset.type;
            setTypeUI(btn.dataset.type);
        });
    });

    function fillCategoryOptions() {
        const type = document.getElementById('txType').value;
        const sel = document.getElementById('txCategory');
        const options = window.FT_DATA.categories.filter(c => c.type === type);
        sel.innerHTML = options.map(c => `<option value="${c.id}">${c.name}</option>`).join('');
    }

    document.getElementById('transactionForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const box = document.getElementById('txFormError');
        box.textContent = '';

        const form = e.target;
        const isEdit = document.getElementById('txMethod').value === 'PUT';
        const id = form.dataset.editId;
        const url = isEdit ? `/transactions/${id}` : '{{ route("transactions.store") }}';

        try {
            const res = await fetch(url, {
                method: 'POST',
                credentials: 'same-origin',
                headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
                body: new FormData(form),
            });
            const data = await res.json();
            if (!res.ok) {
                const msg = data.errors ? Object.values(data.errors).flat().join(' ') : (data.message || 'Terjadi kesalahan.');
                box.textContent = msg;
                return;
            }
            location.reload();
        } catch (err) {
            box.textContent = 'Gagal menyimpan transaksi.';
        }
    });

    async function openDetail(id) {
        try {
            const res = await fetch(`/transactions/${id}`, { headers: { 'Accept': 'application/json' } });
            if (!res.ok) {
                alert('Gagal memuat detail transaksi.');
                return;
            }
            const { transaction } = await res.json();
            const color = transaction.category?.color || '#64748b';
            document.getElementById('dtIcon').className = 'bi ' + (transaction.category?.icon || 'bi-tag');
            document.getElementById('dtIcon').style.background = color + '1a';
            document.getElementById('dtIcon').style.color = color;
            document.getElementById('dtAmount').textContent = (transaction.type === 'income' ? '+' : '-') + transaction.amount_format;
            document.getElementById('dtAmount').className = transaction.type === 'income' ? 'amount-income py-1 d-block' : 'amount-expense py-1 d-block';
            document.getElementById('dtTitle').textContent = transaction.description || transaction.category?.name || 'Transaksi';
            document.getElementById('dtCategory').textContent = transaction.category?.name || '—';
            document.getElementById('dtType').textContent = transaction.type_label;
            document.getElementById('dtAmount2').textContent = transaction.amount_format;
            document.getElementById('dtDate').textContent = transaction.date_format;
            document.getElementById('dtAccount').textContent = transaction.account?.name || '—';
            document.getElementById('dtPayment').textContent = transaction.payment_method || '—';
            document.getElementById('dtNotes').textContent = transaction.notes || 'Tidak ada catatan.';
            document.getElementById('dtSource').textContent = transaction.source_label;
            document.getElementById('dtAudioWrap').classList.add('d-none');
            if (transaction.voice_note) {
                document.getElementById('dtAudioWrap').classList.remove('d-none');
                document.getElementById('dtTranscript').textContent = transaction.voice_note.transcription || '—';
                const audio = document.getElementById('dtAudio');
                if (transaction.voice_note.audio_path) {
                    audio.src = `/voice/audio/${transaction.voice_note.id}`;
                    audio.classList.remove('d-none');
                } else {
                    audio.removeAttribute('src');
                    audio.classList.add('d-none');
                }
            }

            const dtEdit = document.getElementById('dtEditBtn');
            if (dtEdit) {
                dtEdit.onclick = () => {
                    const detailModal = bootstrap.Modal.getInstance(document.getElementById('detailModal'));
                    if (detailModal) detailModal.hide();
                    openEdit(id);
                };
            }

            bootstrap.Modal.getOrCreateInstance('#detailModal').show();
        } catch (err) {
            alert('Terjadi kesalahan memuat detail.');
        }
    }

    async function openEdit(id) {
        try {
            const res = await fetch(`/transactions/${id}`, { headers: { 'Accept': 'application/json' } });
            if (!res.ok) {
                alert('Gagal mengambil data transaksi untuk diedit.');
                return;
            }
            const { transaction } = await res.json();

            document.getElementById('txMethod').value = 'PUT';
            document.getElementById('txFormError').textContent = '';
            document.getElementById('txModalTitle').textContent = 'Edit Transaksi';
            const form = document.getElementById('transactionForm');
            form.dataset.editId = id;

            document.getElementById('txType').value = transaction.type;
            setTypeUI(transaction.type);
            fillCategoryOptions();
            document.getElementById('txAmount').value = Number(transaction.amount).toLocaleString('id-ID');
            document.getElementById('txDate').value = (transaction.transaction_date || '').slice(0, 10);
            document.getElementById('txAccount').value = transaction.account_id;
            document.getElementById('txPayment').value = transaction.payment_method;
            document.getElementById('txCategory').value = transaction.category_id;
            document.getElementById('txDescription').value = transaction.description || '';
            document.getElementById('txNotes').value = transaction.notes || '';
            bootstrap.Modal.getOrCreateInstance('#transactionModal').show();
        } catch (err) {
            alert('Terjadi kesalahan membuka form edit.');
        }
    }

    document.addEventListener('click', async (e) => {
        const detailBtn = e.target.closest('.btn-tx-detail');
        if (detailBtn) {
            e.preventDefault();
            e.stopPropagation();
            openDetail(detailBtn.dataset.id);
            return;
        }

        const editBtn = e.target.closest('.btn-tx-edit');
        if (editBtn) {
            e.preventDefault();
            e.stopPropagation();
            openEdit(editBtn.dataset.id);
            return;
        }

        const delBtn = e.target.closest('.btn-tx-delete');
        if (delBtn) {
            e.preventDefault();
            e.stopPropagation();
            if (!window.confirm('Hapus transaksi ini? Saldo akun akan dikembalikan.')) return;
            try {
                const res = await fetch(`/transactions/${delBtn.dataset.id}`, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
                    body: new URLSearchParams({ _method: 'DELETE' }),
                });
                if (res.ok) {
                    location.reload();
                } else {
                    const d = await res.json().catch(() => ({}));
                    alert(d.message || 'Gagal menghapus transaksi.');
                }
            } catch (err) {
                alert('Terjadi kesalahan saat menghapus transaksi.');
            }
            return;
        }

        if (e.target.closest('.btn-group') || e.target.closest('td.text-end')) return;

        const row = e.target.closest('[data-detail]');
        if (!row || row.dataset.detail === 'undefined') return;
        openDetail(row.dataset.detail);
    });
    });
</script>
@endpush

@include('partials.transaction-modal')

<div class="modal fade" id="detailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="dtTitle">Detail Transaksi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <span class="d-inline-grid place-items-center rounded-3" id="dtIcon" style="width:52px;height:52px;font-size:1.3rem"></span>
                    <div>
                        <div id="dtAmount" class="fw-800" style="font-size:1.25rem"></div>
                        <div class="fs-xs text-muted" id="dtCategory"></div>
                    </div>
                </div>
                <div class="d-flex flex-column gap-2 fs-sm">
                    <div class="d-flex justify-content-between"><span class="text-muted">Jenis</span><span id="dtType"></span></div>
                    <div class="d-flex justify-content-between"><span class="text-muted">Nominal</span><span id="dtAmount2" class="text-value"></span></div>
                    <div class="d-flex justify-content-between"><span class="text-muted">Tanggal</span><span id="dtDate"></span></div>
                    <div class="d-flex justify-content-between"><span class="text-muted">Akun</span><span id="dtAccount"></span></div>
                    <div class="d-flex justify-content-between"><span class="text-muted">Metode</span><span id="dtPayment"></span></div>
                    <div class="d-flex justify-content-between"><span class="text-muted">Sumber</span><span id="dtSource"></span></div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Catatan</span>
                        <span class="text-end ms-3" id="dtNotes"></span>
                    </div>
                </div>
                <div id="dtAudioWrap" class="d-none mt-3 border rounded-3 p-3 bg-light">
                    <div class="d-flex align-items-center gap-2 mb-2 fs-sm fw-650"><i class="bi bi-mic"></i> Rekaman Suara</div>
                    <audio controls class="w-100" id="dtAudio" preload="metadata"></audio>
                    <div class="fs-xs text-muted mt-2 text-value"><i class="bi bi-chat-quote me-1"></i><span id="dtTranscript"></span></div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-ghost" data-bs-dismiss="modal">Tutup</button>
                <button class="btn btn-ft" id="dtEditBtn">Edit</button>
            </div>
        </div>
    </div>
</div>

</x-app-layout>