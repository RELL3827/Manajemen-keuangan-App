<x-app-layout :pageTitle="'Budget'">

<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3">
        <div class="ft-stat neutral">
            <div class="stat-label">Total Budget</div>
            <div class="stat-value text-value">@money($totalBudget)</div>
            <div class="stat-footer">{{ $budgets->count() }} budget aktif</div>
            <div class="stat-icon"><i class="bi bi-speedometer2"></i></div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="ft-stat primary">
            <div class="stat-label">Terpakai</div>
            <div class="stat-value text-value">@money($budgets->sum('spent'))</div>
            <div class="stat-footer"><span class="text-value">{{ $budgets->sum('progress') / max(1, $budgets->count()) }}%</span> rata-rata</div>
            <div class="stat-icon"><i class="bi bi-pie-chart"></i></div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="ft-stat income">
            <div class="stat-label">Sisa Budget</div>
            <div class="stat-value text-value">@money($budgets->sum('remaining'))</div>
            <div class="stat-footer">dalam periode</div>
            <div class="stat-icon"><i class="bi bi-piggy-bank"></i></div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="ft-stat expense">
            <div class="stat-label">Kelebihan</div>
            <div class="stat-value text-value">@money($budgets->sum(fn($b) => max(0, $b['spent'] - $b['amount'])))</div>
            <div class="stat-footer">budget terlampaui</div>
            <div class="stat-icon"><i class="bi bi-exclamation-triangle"></i></div>
        </div>
    </div>
</div>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <p class="text-muted fs-sm mb-0">Batas pengeluaran per kategori setiap periode.</p>
    <button class="btn btn-ft" data-bs-toggle="modal" data-bs-target="#budgetModal"><i class="bi bi-plus-lg me-1"></i>Buat Budget</button>
</div>

<div class="row g-4">
    @forelse($budgets as $budget)
        <div class="col-md-6 col-xl-4">
            <div class="ft-card h-100">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <span class="ft-cat-chip">
                        <span class="cc-dot" style="background:{{ $budget['category']->color }}1a;color:{{ $budget['category']->color }}"><i class="bi {{ $budget['category']->icon }}"></i></span>
                        <span class="fw-650 fs-sm">{{ $budget['category']->name }}</span>
                    </span>
                    @if($budget['category']->is_default)
                        <div class="btn-group btn-group-sm">
                            <button class="btn btn-ghost py-0 bd-edit" data-id="{{ $budget['id'] }}" title="Edit"><i class="bi bi-pencil"></i></button>
                            <button class="btn btn-ghost py-0 bd-delete" data-id="{{ $budget['id'] }}" title="Hapus"><i class="bi bi-trash"></i></button>
                        </div>
                    @else
                        <div class="btn-group btn-group-sm">
                            <button class="btn btn-ghost py-0 bd-edit" data-id="{{ $budget['id'] }}" title="Edit"><i class="bi bi-pencil"></i></button>
                            <button class="btn btn-ghost py-0 bd-delete" data-id="{{ $budget['id'] }}" title="Hapus"><i class="bi bi-trash"></i></button>
                        </div>
                    @endif
                </div>
                <div class="d-flex justify-content-between fs-xs text-muted mb-1">
                    <span>Terpakai</span>
                    <span class="num text-value text-dark fw-650">{{ $budget['spent_format'] }} / {{ $budget['amount_format'] }}</span>
                </div>
                @php
                    $p = (int) round($budget['status'] * 100);
                    $cls = $p >= 100 ? 'over' : ($p >= 90 ? 'warn' : '');
                @endphp
                <div class="ft-progress mb-1">
                    <div class="bar {{ $cls }}" style="width:{{ min(100, $p) }}%"></div>
                </div>
                <div class="d-flex justify-content-between fs-xs mt-1">
                    <span class="{{ $p >= 100 ? 'text-danger fw-650' : ($p >= 90 ? 'text-warning fw-650' : 'text-muted') }}">
                        {{ $p }}% terpakai
                    </span>
                    <span class="num text-value text-muted">
                        @if($p >= 100)
                            <i class="bi bi-exclamation-circle me-1"></i>Terlampaui
                        @else
                            Sisa {{ $budget['remaining_format'] }}
                        @endif
                    </span>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12">
            <div class="ft-card">
                <div class="ft-empty">
                    <div class="ft-empty-icon"><i class="bi bi-speedometer2"></i></div>
                    <h6>Belum ada budget</h6>
                    <p>Buat anggaran per kategori untuk memantau pengeluaran dan mencegah pengeluaran berlebih.</p>
                    <button class="btn btn-ft btn-sm" data-bs-toggle="modal" data-bs-target="#budgetModal">Buat Budget</button>
                </div>
            </div>
        </div>
    @endforelse
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
    const BD_CSRF = document.querySelector('meta[name="csrf-token"]').content;
    const bdModal = new bootstrap.Modal('#budgetModal');

    function resetBudgetForm() {
        const form = document.getElementById('budgetForm');
        form.reset();
        document.getElementById('bdMethod').value = 'POST';
        document.getElementById('bdFormErr').textContent = '';
        document.getElementById('bdModalTitle').textContent = 'Buat Budget';
    }

    document.getElementById('budgetModal').addEventListener('show.bs.modal', resetBudgetForm);

    document.getElementById('budgetForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const form = e.target;
        const isEdit = document.getElementById('bdMethod').value === 'PUT';
        const id = form.dataset.editId;
        const url = isEdit ? `/budgets/${id}` : '{{ route("budgets.store") }}';
        const fd = new FormData(form);
        if (isEdit) fd.append('_method', 'PUT');
        const res = await fetch(url, {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'X-CSRF-TOKEN': BD_CSRF, 'Accept': 'application/json' },
            body: fd,
        });
        const data = await res.json();
        if (!res.ok) {
            document.getElementById('bdFormErr').textContent = data.message || (data.errors ? Object.values(data.errors).flat().join(' ') : 'Terjadi kesalahan.');
            return;
        }
        location.reload();
    });

    document.addEventListener('click', (e) => {
        const editBtn = e.target.closest('.bd-edit');
        if (editBtn) {
            fetch(`/budgets/${editBtn.dataset.id}/edit-info`).then(r => r.json()).then(d => {
                const b = d.budget;
                document.getElementById('bdMethod').value = 'PUT';
                document.getElementById('bdModalTitle').textContent = 'Edit Budget';
                document.getElementById('bdCategory').value = b.category_id;
                document.getElementById('bdAmount').value = Number(b.amount).toLocaleString('id-ID');
                document.getElementById('bdPeriod').value = b.period;
                document.getElementById('budgetForm').dataset.editId = b.id;
                document.getElementById('bdFormErr').textContent = '';
                bdModal.show();
            });
            return;
        }
        const delBtn = e.target.closest('.bd-delete');
        if (delBtn) {
            if (!window.confirm('Hapus budget ini?')) return;
            fetch(`/budgets/${delBtn.dataset.id}`, {
                method: 'POST',
                credentials: 'same-origin',
                headers: { 'X-CSRF-TOKEN': BD_CSRF, 'Accept': 'application/json' },
                body: new URLSearchParams({ _method: 'DELETE' }),
            }).then(() => location.reload());
        }
    });
    });
</script>
@endpush

<div class="modal fade" id="budgetModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="budgetForm" novalidate>
                @csrf
                <input type="hidden" name="_method" value="POST" id="bdMethod">
                <div class="modal-header">
                    <h5 class="modal-title" id="bdModalTitle">Buat Budget</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Kategori</label>
                        <select class="form-select" name="category_id" id="bdCategory" required>
                            <option value="">Pilih kategori</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nominal Budget (Rp)</label>
                        <input type="text" class="form-control" name="amount" id="bdAmount" data-format-money inputmode="numeric" placeholder="0" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Periode</label>
                        <select class="form-select" name="period" id="bdPeriod" required>
                            <option value="monthly">Bulanan</option>
                            <option value="weekly">Mingguan</option>
                        </select>
                    </div>
                    <span class="fs-xs text-danger" id="bdFormErr"></span>
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