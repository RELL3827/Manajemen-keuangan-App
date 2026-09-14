<x-app-layout :pageTitle="'Kalender'">

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <div class="d-flex align-items-center gap-2">
        <a href="{{ route('calendar.index', ['month' => $prevMonth]) }}" class="btn btn-ghost btn-sm" title="Bulan sebelumnya"><i class="bi bi-chevron-left"></i></a>
        <h5 class="fw-800 mb-0 text-capitalize text-value">{{ $month->translatedFormat('F Y') }}</h5>
        <a href="{{ route('calendar.index', ['month' => $nextMonth]) }}" class="btn btn-ghost btn-sm" title="Bulan berikutnya"><i class="bi bi-chevron-right"></i></a>
    </div>
    <div class="d-flex align-items-center gap-3 fs-sm">
        <span class="pill-green ft-pill"><i class="bi bi-arrow-down-circle me-1"></i>@money($monthIncome)</span>
        <span class="pill-red ft-pill"><i class="bi bi-arrow-up-circle me-1"></i>@money($monthExpense)</span>
        <span class="pill-gray ft-pill">Net: <span class="text-value fw-700">{{ $monthNet >= 0 ? '+' : '-' }}@money(abs($monthNet))</span></span>
    </div>
</div>

<div class="ft-card mb-4">
    <div class="ft-cal">
        @foreach(['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'] as $d)
            <div class="cal-head">{{ $d }}</div>
        @endforeach

        @foreach($cells as $cell)
            <div class="cal-day {{ $cell['is_today'] ? 'today' : '' }} {{ $cell['is_outside'] ? 'outside' : '' }}">
                <div class="cal-num">{{ $cell['day'] }}</div>
                @if($cell['count'] > 0)
                    <div class="cal-flow">
                        @if($cell['income'] > 0)<span class="in text-value"><i class="bi bi-arrow-down-circle"></i> {{ $cell['income_format'] }}</span>@endif
                        @if($cell['expense'] > 0)<span class="out text-value"><i class="bi bi-arrow-up-circle"></i> {{ $cell['expense_format'] }}</span>@endif
                        <span class="net text-value" title="{{ $cell['net'] >= 0 ? '+' : '-' }}{{ $cell['net_format'] }}">{{ $cell['net'] >= 0 ? '+' : '-' }}<span class="text-value">{{ $cell['net_format'] }}</span></span>
                    </div>
                @else
                    <div class="cal-flow"></div>
                @endif
            </div>
        @endforeach
    </div>
</div>

</x-app-layout>