<x-app-layout :pageTitle="'Kategori'">

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <p class="text-muted fs-sm mb-0">Kelompokkan transaksi ke dalam kategori.</p>
    <button class="btn btn-ft" data-bs-toggle="modal" data-bs-target="#categoryModal"><i class="bi bi-plus-lg me-1"></i>Kategori</button>
</div>

@php
    $cats = ['expense' => ['label' => 'Uang Keluar', 'data' => $expense, 'noun' => 'Kategori Pengeluaran'], 'income' => ['label' => 'Uang Masuk', 'data' => $income, 'noun' => 'Kategori Pemasukan']];
@endphp

@foreach($cats as $type => $group)
    <div class="ft-card mb-4">
        <div class="ft-card-title">
            {{ $group['label'] }}
            <span class="ft-pill pill-gray">{{ $group['data']->count() }} kategori</span>
        </div>
        <div class="row g-3 row-cols-2 row-cols-sm-3 row-cols-lg-4">
            @forelse($group['data'] as $cat)
                <div class="col">
                    <div class="cat-item border rounded-3 p-3 position-relative" style="background:var(--ft-surface-2)">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <span class="d-inline-grid place-items-center rounded-3" style="width:36px;height:36px;background:{{ $cat->color }}1a;color:{{ $cat->color }}"><i class="bi {{ $cat->icon ?: 'bi-tag' }}"></i></span>
                            <span class="fw-650 fs-sm flex-grow-1">{{ $cat->name }}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="fs-xs text-muted">{{ $cat->transactions_count }} transaksi</span>
                            @if(!$cat->is_default)
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-ghost py-0 cat-edit" data-id="{{ $cat->id }}" title="Edit"><i class="bi bi-pencil"></i></button>
                                    <button class="btn btn-ghost py-0 cat-delete" data-id="{{ $cat->id }}" title="Hapus"><i class="bi bi-trash"></i></button>
                                </div>
                            @else
                                <span class="ft-pill pill-green fs-xs">Default</span>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12 text-center text-muted fs-sm py-3">Belum ada kategori {{ $group['noun'] }}.</div>
            @endforelse
        </div>
    </div>
@endforeach

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
    const CAT_CSRF = document.querySelector('meta[name="csrf-token"]').content;
    const catModal = new bootstrap.Modal('#categoryModal');
    const ICONS = ["bi-tag","bi-cart","bi-egg-fried","bi-cup-hot","bi-cup-straw","bi-house-door","bi-lightning","bi-droplet","bi-fire","bi-truck","bi-fuel-pump","bi-bus-front","bi-train-front","bi-heart","bi-bandaid","bi-mortarboard","bi-book","bi-joystick","bi-film","bi-music-note","bi-dice","bi-star","bi-gift","bi-person-heart","bi-paw","bi-watch","bi-phone","bi-laptop","bi-wifi","bi-tv","bi-cash-coin","bi-credit-card","bi-wallet2","bi-piggy-bank","bi-bank","bi-graph-up-arrow","bi-check-circle","bi-x-circle","bi-tools","bi-brush","bi-flower1","bi-suit-heart"];
    const ICON_OPTIONS = ICONS.map(i => `<option value="${i}">${i.replace('bi-','')}</option>`).join('');

    function resetCatForm() {
        const form = document.getElementById('categoryForm');
        form.reset();
        document.getElementById('catFormMethod').value = 'POST';
        document.getElementById('catFormErr').textContent = '';
        document.getElementById('catModalTitle').textContent = 'Tambah Kategori';
        document.getElementById('catIcon').innerHTML = ICON_OPTIONS;
        document.getElementById('catColor').value = '#0f172a';
    }

    document.getElementById('categoryModal').addEventListener('show.bs.modal', resetCatForm);

    document.getElementById('categoryForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const form = e.target;
        const isEdit = document.getElementById('catFormMethod').value === 'PUT';
        const id = form.dataset.editId;
        const url = isEdit ? `/categories/${id}` : '{{ route("categories.store") }}';
        const formData = new FormData(form);
        if (isEdit) formData.append('_method', 'PUT');
        const res = await fetch(url, {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'X-CSRF-TOKEN': CAT_CSRF, 'Accept': 'application/json' },
            body: formData,
        });
        const data = await res.json();
        if (!res.ok) {
            document.getElementById('catFormErr').textContent = data.errors ? Object.values(data.errors).flat().join(' ') : (data.message || 'Terjadi kesalahan.');
            return;
        }
        location.reload();
    });

    document.addEventListener('click', (e) => {
        const editBtn = e.target.closest('.cat-edit');
        if (editBtn) {
            const id = editBtn.dataset.id;
            fetch(`/categories/${id}/edit-info`).then(r => r.json()).then(d => {
                const c = d.category;
                document.getElementById('catFormMethod').value = 'PUT';
                document.getElementById('catModalTitle').textContent = 'Edit Kategori: ' + c.name;
                document.getElementById('catName').value = c.name;
                document.getElementById('catType').value = c.type;
                document.getElementById('catType')?.setAttribute('data-fixed', c.type);
                document.getElementById('catIcon').innerHTML = ICON_OPTIONS;
                document.getElementById('catIcon').value = c.icon || 'bi-tag';
                document.getElementById('catColor').value = c.color || '#0f172a';
                document.getElementById('categoryForm').dataset.editId = id;
                document.getElementById('catFormErr').textContent = '';
                catModal.show();
            });
            return;
        }
        const delBtn = e.target.closest('.cat-delete');
        if (delBtn) {
            if (!window.confirm('Hapus kategori ini?')) return;
            fetch(`/categories/${delBtn.dataset.id}`, {
                method: 'POST',
                credentials: 'same-origin',
                headers: { 'X-CSRF-TOKEN': CAT_CSRF, 'Accept': 'application/json' },
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

<div class="modal fade" id="categoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="categoryForm" novalidate>
                @csrf
                <input type="hidden" name="_method" value="POST" id="catFormMethod">
                <div class="modal-header">
                    <h5 class="modal-title" id="catModalTitle">Tambah Kategori</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nama Kategori</label>
                        <input type="text" class="form-control" name="name" id="catName" maxlength="60" placeholder="cth: Makanan, Gaji, Transportasi" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Jenis</label>
                        <select class="form-select" name="type" id="catType" required>
                            <option value="expense">Uang Keluar</option>
                            <option value="income">Uang Masuk</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Ikon</label>
                        <select class="form-select" name="icon" id="catIcon" required></select>
                        <div class="d-flex flex-wrap gap-2 mt-2" id="catIconPreview"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Warna</label>
                        <input type="color" class="form-control form-control-color" name="color" id="catColor" value="#0f172a" title="Pilih warna">
                    </div>
                    <span class="fs-xs text-danger" id="catFormErr"></span>
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