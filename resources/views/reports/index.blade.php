<x-app-layout :pageTitle="'Laporan'">

<form method="GET" action="{{ route('reports.index') }}" class="ft-filter-bar">
    <div class="ft-period-group">
        <button class="per-btn {{ $p['period'] === 'day' ? 'active' : '' }}" data-period="day" name="period" value="day" formaction="{{ route('reports.index', ['period' => 'day']) }}">Harian</button>
        <button class="per-btn {{ $p['period'] === 'week' ? 'active' : '' }}" data-period="week" name="period" value="week" formaction="{{ route('reports.index', ['period' => 'week']) }}">Mingguan</button>
        <button class="per-btn {{ $p['period'] === 'month' ? 'active' : '' }}" data-period="month" name="period" value="month" formaction="{{ route('reports.index', ['period' => 'month']) }}">Bulanan</button>
        <button class="per-btn {{ $p['period'] === 'year' ? 'active' : '' }}" data-period="year" name="period" value="year" formaction="{{ route('reports.index', ['period' => 'year']) }}">Tahunan</button>
    </div>
    @if($p['period'] === 'custom')
        <input type="hidden" name="period" value="custom">
        <input type="date" class="form-control" name="from" value="{{ old('from', $p['from']) }}">
        <input type="date" class="form-control" name="to" value="{{ old('to', $p['to']) }}">
    @endif
    <button type="submit" class="btn btn-ghost btn-sm" id="reportApplyBtn"><i class="bi bi-arrow-repeat me-1"></i>Terapkan</button>
</form>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <p class="text-muted fs-sm mb-0">
        Periode: <strong class="text-dark">{{ Carbon\Carbon::parse($p['from'])->translatedFormat('d M Y') }} — {{ Carbon\Carbon::parse($p['to'])->translatedFormat('d M Y') }}</strong>
    </p>
    <div class="d-flex gap-2">
        <a href="{{ route('reports.export-pdf', ['period' => $p['period'], 'from' => $p['from'], 'to' => $p['to']]) }}" class="btn btn-ft-outline btn-sm"><i class="bi bi-file-earmark-pdf me-1"></i>Export PDF</a>
        <a href="{{ route('reports.export-excel', ['period' => $p['period'], 'from' => $p['from'], 'to' => $p['to']]) }}" class="btn btn-ft-outline btn-sm"><i class="bi bi-file-earmark-excel me-1"></i>Export Excel</a>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-6 col-xl-3">
        <div class="ft-stat primary">
            <div class="stat-label">Saldo</div>
            <div class="stat-value text-value" style="font-size:1.15rem">@money($data['balance'])</div>
            <div class="stat-icon"><i class="bi bi-wallet2"></i></div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="ft-stat income">
            <div class="stat-label">Uang Masuk</div>
            <div class="stat-value text-value" style="font-size:1.15rem">@money($data['income'])</div>
            <div class="stat-icon"><i class="bi bi-arrow-down-circle"></i></div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="ft-stat expense">
            <div class="stat-label">Uang Keluar</div>
            <div class="stat-value text-value" style="font-size:1.15rem">@money($data['expense'])</div>
            <div class="stat-icon"><i class="bi bi-arrow-up-circle"></i></div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="ft-stat neutral">
            <div class="stat-label">Net (Masuk−Keluar)</div>
            <div class="stat-value text-value" style="font-size:1.15rem">{{ $data['net'] >= 0 ? '+' : '-' }}@money(abs($data['net']))</div>
            <div class="stat-footer"><span class="text-value">{{ $data['count'] }}</span> transaksi</div>
            <div class="stat-icon"><i class="bi bi-graph-up"></i></div>
        </div>
    </div>
</div>

@if(count($insights))
    <div class="ft-card mb-4">
        <div class="ft-card-title"><i class="bi bi-stars me-2 text-value"></i>Insight Keuangan</div>
        <div class="row g-3">
            @foreach($insights as $insight)
                <div class="col-md-6 col-lg-4">
                    <div class="d-flex gap-2">
                        <span class="d-inline-grid place-items-center rounded-3 flex-shrink-0" style="width:34px;height:34px;background:{{ $insight['color'] }}18;color:{{ $insight['color'] }}"><i class="bi {{ $insight['icon'] }}"></i></span>
                        <p class="fs-sm text-muted mb-0">{{ $insight['text'] }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endif

<div class="row g-3 mb-4">
    <div class="col-lg-7">
        <div class="ft-card h-100">
            <div class="ft-card-title">Arus Kas per Bulan</div>
            <div class="chart-wrap" style="height:280px"><canvas id="chartStream"></canvas></div>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="ft-card h-100">
            <div class="ft-card-title">Pengeluaran per Kategori</div>
            @forelse($data['category_expense'] as $row)
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="d-flex align-items-center gap-2 fs-sm">
                        <span class="d-inline-block rounded-circle" style="width:10px;height:10px;background:{{ $row['color'] }}"></span>
                        {{ $row['name'] }}
                    </span>
                    <span class="num fs-sm text-value fw-650">{{ $row['total_format'] }}</span>
                </div>
            @empty
                <div class="text-muted fs-sm py-3 text-center">Tidak ada pengeluaran di periode ini.</div>
            @endforelse
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-5">
        <div class="ft-card no-pad overflow-hidden">
            <div class="p-3 border-bottom"><h6 class="fw-750 mb-0">Pemasukan per Kategori</h6></div>
            <div class="p-3">
                @forelse($data['category_income'] as $row)
                    <div class="d-flex justify-content-between align-items-center border-bottom py-2 fs-sm">
                        <span class="d-flex align-items-center gap-2">
                            <span class="d-inline-block rounded-circle" style="width:10px;height:10px;background:{{ $row['color'] }}"></span>
                            {{ $row['name'] }}
                        </span>
                        <span class="num text-value fw-650">{{ $row['total_format'] }}</span>
                    </div>
                @empty
                    <div class="text-muted fs-sm py-3 text-center">Tidak ada pemasukan di periode ini.</div>
                @endforelse
            </div>
        </div>
    </div>
    <div class="col-lg-7">
        <div class="ft-card no-pad overflow-hidden">
            <div class="p-3 border-bottom"><h6 class="fw-750 mb-0">Rincian Transaksi</h6></div>
            <div class="ft-scroll-area">
                <table class="ft-table">
                    <thead>
                        <tr><th>Tanggal</th><th>Kategori</th><th>Akun</th><th class="text-end">Nominal</th></tr>
                    </thead>
                    <tbody>
                        @forelse($data['transactions'] as $tx)
                            <tr>
                                <td class="num">{{ $tx->transaction_date->translatedFormat('d M') }}</td>
                                <td class="fs-sm">{{ $tx->category->name }}{{ $tx->description ? ' — ' . $tx->description : '' }}</td>
                                <td class="fs-sm text-muted">{{ $tx->account->name }}</td>
                                <td class="text-end num {{ $tx->type === 'income' ? 'amount-income' : 'amount-expense' }}"><span class="text-value">@money($tx->amount)</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="4"><div class="text-center text-muted fs-sm py-3">Tidak ada transaksi.</div></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const stream = @json($data['monthly_stream']);
        const moneyFmt = (v) => 'Rp' + new Intl.NumberFormat('id-ID').format(v);
        new Chart(document.getElementById('chartStream'), {
            type: 'bar',
            data: {
                labels: stream.labels,
                datasets: [
                    { label: 'Uang Masuk', data: stream.income, backgroundColor: '#16a34a', borderRadius: 6 },
                    { label: 'Uang Keluar', data: stream.expense, backgroundColor: '#dc2626', borderRadius: 6 },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'top', labels: { usePointStyle: true, boxWidth: 8, font: { size: 11 } } },
                    tooltip: { callbacks: { label: (c) => ` ${c.dataset.label}: ${moneyFmt(c.parsed.y)}` } },
                },
                scales: {
                    y: { beginAtZero: true, ticks: { callback: (v) => moneyFmt(v), font: { size: 10 } }, grid: { color: 'rgba(15,23,42,.06)' } },
                    x: { grid: { display: false }, ticks: { font: { size: 10 } } },
                },
            },
        });

        document.querySelectorAll('.ft-period-group .per-btn').forEach((btn) => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                const url = new URL(btn.formAction, window.location.origin);
                document.querySelectorAll('.ft-period-group .per-btn').forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                window.location.href = url;
            });
        });
    });
</script>
@endpush

</x-app-layout>