@props(['pageTitle' => null])

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Eltrack') }}{{ $pageTitle ? ' — ' . $pageTitle : '' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-vh-100 d-flex flex-column">

<header class="py-3 px-4 d-flex justify-content-between align-items-center">
    <a href="{{ route('landing') }}" class="d-flex align-items-center gap-2 fw-800 fs-5">
        <span class="logo-mark d-inline-grid place-items-center" style="width:34px;height:34px;border-radius:10px;background:var(--ft-primary);color:#fff"><i class="bi bi-graph-up-arrow"></i></span>
        <span>Eltrack</span>
    </a>
    @auth
        <a href="{{ route('dashboard') }}" class="btn btn-ft btn-sm">Ke Dashboard</a>
    @else
        <a href="{{ route('login') }}" class="btn btn-ghost btn-sm">Masuk</a>
    @endauth
</header>

<main class="flex-grow-1 d-flex align-items-center justify-content-center p-3">
    <div class="w-100" style="max-width: 440px;">
        {{ $slot }}
    </div>
</main>

<footer class="text-center text-muted fs-xs py-3">
    © {{ date('Y') }} Eltrack — Kelola keuanganmu, semudah berbicara.
</footer>

</body>
</html>