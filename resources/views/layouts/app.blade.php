@props(['pageTitle' => null])

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#0f172a">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="Eltrack">
    <link rel="manifest" href="/manifest.json">
    <link rel="icon" type="image/png" href="/icons/icon-192.png">
    <link rel="apple-touch-icon" href="/icons/icon-192.png">
    <title>{{ config('app.name', 'Eltrack') }}{{ $pageTitle ? ' — ' . $pageTitle : '' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-vh-100">

<div class="offcanvas-backdrop fade d-none" id="sidebarBackdrop"></div>

<aside class="ft-sidebar" id="ftSidebar">
    <a href="{{ route('dashboard') }}" class="brand">
        <span class="logo-mark"><i class="bi bi-graph-up-arrow"></i></span>
        <span>Eltrack</span>
    </a>

    <nav class="ft-nav">
        <div class="group-label">Utama</div>
        <a href="{{ route('dashboard') }}" class="ft-nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <i class="bi bi-grid-1x2"></i> Dashboard
        </a>
        <a href="{{ route('transactions.index') }}" class="ft-nav-item {{ request()->routeIs('transactions.*') ? 'active' : '' }}">
            <i class="bi bi-list-ul"></i> Transaksi
        </a>
        <a href="{{ route('voice.index') }}" class="ft-nav-item {{ request()->routeIs('voice.*') ? 'active' : '' }}">
            <i class="bi bi-mic"></i> Catat dengan Suara
        </a>

        <div class="group-label">Kelola</div>
        <a href="{{ route('accounts.index') }}" class="ft-nav-item {{ request()->routeIs('accounts.*') ? 'active' : '' }}">
            <i class="bi bi-wallet2"></i> Dompet
        </a>
        <a href="{{ route('transfers.index') }}" class="ft-nav-item {{ request()->routeIs('transfers.*') ? 'active' : '' }}">
            <i class="bi bi-arrow-left-right"></i> Transfer
        </a>
        <a href="{{ route('categories.index') }}" class="ft-nav-item {{ request()->routeIs('categories.*') ? 'active' : '' }}">
            <i class="bi bi-tags"></i> Kategori
        </a>
        <a href="{{ route('budgets.index') }}" class="ft-nav-item {{ request()->routeIs('budgets.*') ? 'active' : '' }}">
            <i class="bi bi-speedometer2"></i> Budget
        </a>

        <div class="group-label">Analisis</div>
        <a href="{{ route('reports.index') }}" class="ft-nav-item {{ request()->routeIs('reports.*') ? 'active' : '' }}">
            <i class="bi bi-bar-chart"></i> Laporan
        </a>
        <a href="{{ route('calendar.index') }}" class="ft-nav-item {{ request()->routeIs('calendar.*') ? 'active' : '' }}">
            <i class="bi bi-calendar3"></i> Kalender
        </a>
        <a href="{{ route('notifications.index') }}" class="ft-nav-item {{ request()->routeIs('notifications.*') ? 'active' : '' }}">
            <i class="bi bi-bell"></i> Notifikasi
            @php $uCount = auth()->user()?->unread_notifications_count ?? 0; @endphp
            @if($uCount > 0)
                <span class="badge-ft">{{ $uCount }}</span>
            @endif
        </a>

        <div class="group-label">Akun</div>
        <a href="{{ route('settings') }}" class="ft-nav-item {{ request()->routeIs('settings') || request()->routeIs('profile.*') ? 'active' : '' }}">
            <i class="bi bi-gear"></i> Pengaturan
        </a>
    </nav>

    <div class="sidebar-foot">
        <img src="{{ auth()->user()->avatar ? asset('storage/' . auth()->user()->avatar) : 'https://ui-avatars.com/api/?name=' . urlencode(auth()->user()->name) . '&background=0f172a&color=fff' }}" onerror="this.onerror=null;this.src='https://ui-avatars.com/api/?name={{ urlencode(auth()->user()->name) }}&background=0f172a&color=fff';" class="ft-avatar" alt="">
        <div class="flex-grow-1 text-truncate">
            <div class="user-name text-truncate">{{ auth()->user()->name }}</div>
            <div class="user-email text-truncate">{{ auth()->user()->email }}</div>
        </div>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button class="btn btn-link p-0 text-muted" title="Keluar"><i class="bi bi-box-arrow-right fs-5"></i></button>
        </form>
    </div>
</aside>

<div class="ft-main">
    <header class="ft-topbar">
        <button class="btn btn-ghost d-lg-none px-2" id="sidebarToggle" aria-label="Menu"><i class="bi bi-list fs-4"></i></button>
        <h1 class="page-title flex-grow-1">{{ $pageTitle ?? 'Dashboard' }}</h1>

        <a href="{{ route('voice.index') }}" class="btn btn-ft btn-sm d-none d-lg-inline-flex align-items-center gap-2">
            <i class="bi bi-mic"></i> Catat Suara
        </a>
        <a href="{{ route('transactions.index') }}" class="btn btn-ft-outline btn-sm d-none d-lg-inline-flex align-items-center gap-2">
            <i class="bi bi-plus-lg"></i> Transaksi
        </a>

        <a href="{{ route('notifications.index') }}" class="position-relative text-muted d-none d-lg-inline-block" aria-label="Notifikasi">
            <i class="bi bi-bell fs-5"></i>
            @if($uCount > 0)
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size:.6rem">{{ $uCount }}</span>
            @endif
        </a>

        <a href="{{ route('settings') }}" class="d-none d-lg-inline-block">
            <img src="{{ auth()->user()->avatar ? asset('storage/' . auth()->user()->avatar) : 'https://ui-avatars.com/api/?name=' . urlencode(auth()->user()->name) . '&background=0f172a&color=fff' }}" onerror="this.onerror=null;this.src='https://ui-avatars.com/api/?name={{ urlencode(auth()->user()->name) }}&background=0f172a&color=fff';" class="ft-avatar" alt="">
        </a>
    </header>

    <main class="ft-content">
        {{ $slot }}
    </main>
</div>

<nav class="ft-bottom-nav">
    <a href="{{ route('dashboard') }}" class="bn-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
        <i class="bi bi-grid-1x2"></i> Beranda
    </a>
    <a href="{{ route('transactions.index') }}" class="bn-item {{ request()->routeIs('transactions.*') ? 'active' : '' }}">
        <i class="bi bi-list-ul"></i> Transaksi
    </a>
    <a href="{{ route('reports.index') }}" class="bn-item {{ request()->routeIs('reports.*') ? 'active' : '' }}">
        <i class="bi bi-bar-chart"></i> Laporan
    </a>
    <a href="{{ route('calendar.index') }}" class="bn-item {{ request()->routeIs('calendar.*') ? 'active' : '' }}">
        <i class="bi bi-calendar3"></i> Kalender
    </a>
    <a href="{{ route('settings') }}" class="bn-item {{ request()->routeIs('settings') || request()->routeIs('profile.*') ? 'active' : '' }}">
        <i class="bi bi-gear"></i> Atur
    </a>
</nav>

<button type="button" class="ft-fab" data-bs-toggle="modal" data-bs-target="#voiceModal" aria-label="Catat transaksi">
    <i class="bi bi-mic-fill"></i>
</button>

@include('partials.voice-modal')

@if(session('success'))
    <div class="toast-ft">
        <div class="toast" role="alert">
            <div class="toast-body d-flex align-items-center gap-2">
                <i class="bi bi-check-circle-fill text-success fs-5"></i>
                <span class="flex-grow-1">{{ session('success') }}</span>
                <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
            </div>
        </div>
    </div>
@endif

@if(session('error'))
    <div class="toast-ft">
        <div class="toast" role="alert">
            <div class="toast-body d-flex align-items-center gap-2">
                <i class="bi bi-x-circle-fill text-danger fs-5"></i>
                <span class="flex-grow-1">{{ session('error') }}</span>
                <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
            </div>
        </div>
    </div>
@endif

<script>
    window.Eltrack = window.Eltrack || {};
    window.Eltrack.routes = {
        'voice.parse': @json(route('voice.parse')),
        'voice.store': @json(route('voice.store')),
        'voice.upload-audio': @json(route('voice.upload-audio')),
    };
</script>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const toggle = document.getElementById('sidebarToggle');
        const sidebar = document.getElementById('ftSidebar');
        const backdrop = document.getElementById('sidebarBackdrop');
        if (toggle && sidebar) {
            const close = () => {
                sidebar.classList.remove('open');
                backdrop.classList.add('d-none');
            };
            toggle.addEventListener('click', () => {
                sidebar.classList.toggle('open');
                backdrop.classList.toggle('d-none', !sidebar.classList.contains('open'));
            });
            backdrop.addEventListener('click', close);
        }
    });

    if ('serviceWorker' in navigator) {
        window.addEventListener('load', () => {
            navigator.serviceWorker.register('/sw.js').catch(() => {});
        });
    }
</script>

@stack('scripts')

</body>
</html>