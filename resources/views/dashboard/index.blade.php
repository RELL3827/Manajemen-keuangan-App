<x-app-layout :pageTitle="'Dashboard'">

<div class="ft-action-3 mb-4">
    <a href="{{ route('transactions.index', ['type' => 'income']) }}" class="ft-action-card income">
        <span class="ac-icon"><i class="bi bi-plus-lg"></i></span>
        Uang Masuk
    </a>
    <a href="{{ route('transactions.index', ['type' => 'expense']) }}" class="ft-action-card expense">
        <span class="ac-icon"><i class="bi bi-dash-lg"></i></span>
        Uang Keluar
    </a>
    <button type="button" class="ft-action-card voice" data-bs-toggle="modal" data-bs-target="#voiceModal">
        <span class="ac-icon"><i class="bi bi-mic-fill"></i></span>
        Catat dengan Suara
    </button>
</div>

<div class="ft-filter-bar">
    <div class="ft-period-group" id="periodGroup">
        <button class="per-btn" data-period="today">Hari ini</button>
        <button class="per-btn" data-period="week">7 hari</button>
        <button class="per-btn active" data-period="month">Bulan ini</button>
        <button class="per-btn" data-period="quarter">3 bulan</button>
        <button class="per-btn" data-period="year">Tahun ini</button>
        <button class="per-btn" data-period="custom" id="customPeriodBtn">Custom</button>
    </div>
    <div class="d-none gap-2" id="customRange">
        <input type="date" class="form-control form-control-sm" id="dateFrom">
        <input type="date" class="form-control form-control-sm" id="dateTo">
        <button class="btn btn-ft btn-sm" id="applyCustom">Terapkan</button>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-6 col-xl-3">
        <div class="ft-stat primary">
            <div class="stat-label">Total Saldo</div>
            <div class="stat-value text-value" data-stat="balance">{{ $summary['format']['balance'] }}</div>
            <div class="stat-footer"><a href="{{ route('accounts.index') }}" class="text-decoration-none">Semua dompet</a></div>
            <div class="stat-icon"><i class="bi bi-wallet2"></i></div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="ft-stat income">
            <div class="stat-label">Uang Masuk</div>
            <div class="stat-value text-value" data-stat="income">{{ $summary['format']['income'] }}</div>
            <div class="stat-footer" data-stat="income-count">periode berjalan</div>
            <div class="stat-icon"><i class="bi bi-arrow-down-circle"></i></div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="ft-stat expense">
            <div class="stat-label">Uang Keluar</div>
            <div class="stat-value text-value" data-stat="expense">{{ $summary['format']['expense'] }}</div>
            <div class="stat-footer" data-stat="expense-count">periode berjalan</div>
            <div class="stat-icon"><i class="bi bi-arrow-up-circle"></i></div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="ft-stat neutral">
            <div class="stat-label">Selisih Periode</div>
            <div class="stat-value text-value" data-stat="net">{{ $summary['format']['net'] }}</div>
            <div class="stat-footer">masuk − keluar · <span data-stat="count">{{ $summary['count'] }}</span> transaksi</div>
            <div class="stat-icon"><i class="bi bi-graph-up"></i></div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-lg-8">
        <div class="ft-card h-100">
            <div class="ft-card-title">Masuk vs Keluar <span class="sub" data-chart-title>Bulan ini</span></div>
            <div class="chart-wrap" style="height:280px">
                <canvas id="chartTrend"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="ft-card h-100">
            <div class="ft-card-title">Pengeluaran per Kategori</div>
            <div class="chart-wrap" style="height:240px">
                <canvas id="chartCategory"></canvas>
            </div>
            <div id="categoryLegend" class="mt-2 fs-xs d-flex flex-column gap-1"></div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-lg-7">
        <div class="ft-card h-100">
            <div class="ft-card-title">Perkembangan Saldo</div>
            <div class="chart-wrap" style="height:260px">
                <canvas id="chartBalance"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="ft-card h-100">
            <div class="ft-card-title">7 Hari Terakhir</div>
            <div class="chart-wrap" style="height:260px">
                <canvas id="chart7"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-5">
        <div class="ft-card no-pad h-100">
            <div class="p-3 border-bottom d-flex justify-content-between align-items-center">
                <h6 class="fw-750 mb-0">Transaksi Terbaru</h6>
                <a href="{{ route('transactions.index') }}" class="fs-xs fw-700">Lihat semua</a>
            </div>
            <div class="p-3 pt-2">
                @forelse($recent as $tx)
                    <div class="ft-tx-row">
                        <span class="tx-icon" style="background:{{ $tx->category->color }}1a;color:{{ $tx->category->color }}">
                            <i class="bi {{ $tx->category->icon }}"></i>
                        </span>
                        <div class="tx-main">
                            <div class="tx-title">{{ $tx->category->name }}{{ $tx->description ? ' · ' . $tx->description : '' }}</div>
                            <div class="tx-sub">{{ $tx->transaction_date->translatedFormat('d M') }} · {{ $tx->account->name }}@if($tx->source === 'voice') · <i class="bi bi-mic"></i>@endif</div>
                        </div>
                        <div class="fs-sm num text-value {{ $tx->type === 'income' ? 'amount-income' : 'amount-expense' }}">
                            {{ $tx->type === 'income' ? '+' : '-' }}@money($tx->amount)
                        </div>
                    </div>
                @empty
                    <div class="ft-empty py-4">
                        <div class="ft-empty-icon"><i class="bi bi-inbox"></i></div>
                        <h6>Belum ada transaksi</h6>
                        <p>Mulai catat transaksi pertamamu sekarang.</p>
                        <button class="btn btn-ft btn-sm" data-bs-toggle="modal" data-bs-target="#voiceModal"><i class="bi bi-mic me-1"></i>Catat dengan Suara</button>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <div class="col-lg-7">
        <div class="ft-card no-pad h-100">
            <div class="p-3 border-bottom d-flex justify-content-between align-items-center">
                <h6 class="fw-750 mb-0">Budget Bulan Ini</h6>
                <a href="{{ route('budgets.index') }}" class="fs-xs fw-700">Kelola budget</a>
            </div>
            <div class="p-3">
                @forelse($budgets as $budget)
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <div class="d-flex align-items-center gap-2">
                                <span class="d-inline-grid place-items-center rounded-2" style="width:28px;height:28px;background:{{ $budget->category->color }}1a;color:{{ $budget->category->color }}"><i class="bi {{ $budget->category->icon }} fs-xs"></i></span>
                                <span class="fw-650 fs-sm">{{ $budget->category->name }}</span>
                            </div>
                            <span class="fs-xs text-muted num text-value">@money($budget->spentAmount()) / @money($budget->amount)</span>
                        </div>
                        @php
                            $p = $budget->progress;
                            $cls = $p >= 100 ? 'over' : ($p >= 90 ? 'warn' : '');
                        @endphp
                        <div class="ft-progress">
                            <div class="bar {{ $cls }}" style="width:{{ $p }}%"></div>
                        </div>
                        @if($p >= 100)
                            <div class="fs-xs text-danger fw-650 mt-1"><i class="bi bi-exclamation-circle me-1"></i>Budget {{ $budget->category->name }} telah terlampaui.</div>
                        @elseif($p >= 90)
                            <div class="fs-xs text-warning fw-650 mt-1"><i class="bi bi-exclamation-triangle me-1"></i>Pengeluaran {{ $budget->category->name }} sudah mencapai {{ $p }}% dari budget.</div>
                        @endif
                    </div>
                @empty
                    <div class="text-center text-muted fs-sm py-4">Belum ada budget. <a href="{{ route('budgets.index') }}" class="fw-700">Buat sekarang</a></div>
                @endforelse
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const csrf = document.querySelector('meta[name="csrf-token"]').content;

        let charts = {};

        const els = {
            balance: document.querySelector('[data-stat="balance"]'),
            income: document.querySelector('[data-stat="income"]'),
            incomeCount: document.querySelector('[data-stat="income-count"]'),
            expense: document.querySelector('[data-stat="expense"]'),
            expenseCount: document.querySelector('[data-stat="expense-count"]'),
            net: document.querySelector('[data-stat="net"]'),
            count: document.querySelector('[data-stat="count"]'),
            chartTitle: document.querySelector('[data-chart-title]'),
            legend: document.getElementById('categoryLegend'),
            customRange: document.getElementById('customRange'),
            dateFrom: document.getElementById('dateFrom'),
            dateTo: document.getElementById('dateTo'),
        };

        const sheet = {
            today: 'Hari ini',
            week: '7 hari terakhir',
            month: 'Bulan ini',
            quarter: '3 bulan terakhir',
            year: 'Tahun ini',
            custom: 'Periode custom',
        };

        async function load(period) {
            const params = new URLSearchParams({ period });
            if (period === 'custom') {
                if (!els.dateFrom.value) els.dateFrom.value = new Date(new Date().getFullYear(), new Date().getMonth(), 1).toISOString().slice(0, 10);
                if (!els.dateTo.value) els.dateTo.value = new Date().toISOString().slice(0, 10);
                params.set('from', els.dateFrom.value);
                params.set('to', els.dateTo.value);
            }

            params.set('_t', Date.now());

            const res = await fetch(`/dashboard/data?${params}`, {
                cache: 'no-store',
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            });
            const data = await res.json();

            els.balance.textContent = data.summary.format.balance;
            els.income.textContent = data.summary.format.income;
            els.expense.textContent = data.summary.format.expense;
            els.net.textContent = data.summary.format.net;
            els.count.textContent = data.summary.count;
            els.chartTitle.textContent = sheet[period] || '';

            renderTrend(data.trend);
            renderCategory(data.categoryExpense);
            renderBalance(data.balanceTrend.labels, data.balanceTrend.values);
            render7(data.last7);
        }

        function makeChart(id, config) {
            if (charts[id]) charts[id].destroy();
            charts[id] = new Chart(document.getElementById(id), config);
        }

        function renderTrend(rows) {
            makeChart('chartTrend', {
                type: 'bar',
                data: {
                    labels: rows.map(r => r.label),
                    datasets: [
                        { label: 'Uang Masuk', data: rows.map(r => r.income), backgroundColor: '#16a34a', borderRadius: 6 },
                        { label: 'Uang Keluar', data: rows.map(r => r.expense), backgroundColor: '#dc2626', borderRadius: 6 },
                    ],
                },
                options: chartBaseOptions('masuk vs keluar'),
            });
        }

        function renderCategory(rows) {
            makeChart('chartCategory', {
                type: 'doughnut',
                data: {
                    labels: rows.length ? rows.map(r => r.name) : ['Belum ada pengeluaran'],
                    datasets: [{
                        data: rows.length ? rows.map(r => r.total) : [1],
                        backgroundColor: rows.length ? rows.map(r => r.color) : ['#e2e8f0'],
                        borderWidth: 2,
                        borderColor: '#fff'
                    }],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            enabled: rows.length > 0
                        }
                    },
                },
            });
            if (rows.length === 0) {
                els.legend.innerHTML = '<div class="text-center text-muted py-2">Belum ada pengeluaran di periode ini</div>';
            } else {
                els.legend.innerHTML = rows.slice(0, 5).map(r =>
                    `<div class="d-flex justify-content-between align-items-center gap-2">
                        <span><span class="d-inline-block rounded-circle me-1" style="width:8px;height:8px;background:${r.color}"></span>${r.name}</span>
                        <span class="fw-650 num text-value">${r.total_format}</span>
                    </div>`
                ).join('');
            }
        }

        function renderBalance(labels, values) {
            makeChart('chartBalance', {
                type: 'line',
                data: {
                    labels,
                    datasets: [{ label: 'Saldo', data: values, borderColor: '#0f172a', backgroundColor: 'rgba(15,23,42,.06)', fill: true, tension: .35, pointRadius: 3 }],
                },
                options: chartBaseOptions('Perkembangan saldo (Rp)'),
            });
        }

        function render7(d) {
            makeChart('chart7', {
                type: 'bar',
                data: {
                    labels: d.labels,
                    datasets: [
                        { label: 'Masuk', data: d.income, backgroundColor: '#16a34a', borderRadius: 5 },
                        { label: 'Keluar', data: d.expense, backgroundColor: '#dc2626', borderRadius: 5 },
                    ],
                },
                options: chartBaseOptions('7 hari terakhir'),
            });
        }

        function chartBaseOptions(title) {
            const fmt = (v) => 'Rp' + new Intl.NumberFormat('id-ID').format(v);
            return {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'top', labels: { usePointStyle: true, boxWidth: 8, font: { size: 11 } } },
                    tooltip: {
                        callbacks: {
                            label: (c) => ` ${c.dataset.label}: ${fmt(c.parsed.y ?? c.parsed)}`,
                            title: (items) => items.length ? items[0].label : '',
                        },
                    },
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { callback: (v) => fmt(v), font: { size: 10 } },
                        grid: { color: 'rgba(15,23,42,.06)' },
                    },
                    x: { grid: { display: false }, ticks: { font: { size: 10 } } },
                },
            };
        }

        document.querySelectorAll('#periodGroup .per-btn').forEach((btn) => {
            btn.addEventListener('click', () => {
                document.querySelectorAll('#periodGroup .per-btn').forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                if (btn.dataset.period === 'custom') {
                    els.customRange.classList.remove('d-none');
                    els.customRange.classList.add('d-flex');
                    load('custom');
                } else {
                    els.customRange.classList.add('d-none');
                    els.customRange.classList.remove('d-flex');
                    load(btn.dataset.period);
                }
            });
        });

        document.getElementById('applyCustom').addEventListener('click', () => load('custom'));

        load('month');
    });
</script>
@endpush

</x-app-layout>