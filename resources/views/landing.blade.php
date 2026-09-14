<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Eltrack') }} — Kelola Keuanganmu, Semudah Berbicara</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="ft-landing">

<nav class="ft-nav-landing">
    <div class="container d-flex justify-content-between align-items-center py-2">
        <a href="#" class="d-flex align-items-center gap-2 fw-800 fs-5">
            <span class="logo-mark d-inline-grid place-items-center" style="width:34px;height:34px;border-radius:10px;background:var(--ft-primary);color:#fff"><i class="bi bi-graph-up-arrow"></i></span>
            <span>Eltrack</span>
        </a>
        <div class="d-flex align-items-center gap-2">
            @auth
                <a href="{{ route('dashboard') }}" class="btn btn-ft btn-sm">Dashboard</a>
            @else
                <a href="{{ route('login') }}" class="btn btn-ghost btn-sm d-none d-sm-inline-block">Login</a>
                <a href="{{ route('register') }}" class="btn btn-ft btn-sm">Mulai Gratis</a>
            @endauth
        </div>
    </div>
</nav>

<header class="ft-hero">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-6">
                <span class="ft-pill pill-green mb-3"><i class="bi bi-mic-fill"></i> Input suara Bahasa Indonesia</span>
                <h1>Kelola Keuanganmu,<br>Semudah <span class="text-decoration-underline text-decoration-color-success">Berbicara.</span></h1>
                <p class="lead mt-3 mb-4">Catat uang masuk dan keluar dalam hitungan detik dengan Eltrack. Tanpa mengetik — cukup tekan mikrofon dan bicara.</p>
                <div class="d-flex flex-wrap gap-3">
                    <a href="{{ route('register') }}" class="btn btn-ft btn-lg px-4">Mulai Gratis</a>
                    @auth
                        <a href="{{ route('dashboard') }}" class="btn btn-ghost btn-lg px-4">Login</a>
                    @else
                        <a href="{{ route('login') }}" class="btn btn-ghost btn-lg px-4">Login</a>
                    @endauth
                </div>
                <div class="d-flex gap-4 mt-4 text-muted fs-sm">
                    <span><i class="bi bi-check-circle-fill text-success me-1"></i> Gratis</span>
                    <span><i class="bi bi-check-circle-fill text-success me-1"></i> Tanpa kartu kredit</span>
                    <span><i class="bi bi-check-circle-fill text-success me-1"></i> Data privat</span>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="ft-mic-demo">
                    <div class="d-flex align-items-center gap-3 mb-4">
                        <span class="d-inline-grid place-items-center rounded-circle" style="width:56px;height:56px;background:rgba(255,255,255,.14);font-size:1.6rem"><i class="bi bi-mic-fill"></i></span>
                        <div>
                            <div class="fw-750">Catat dengan Suara</div>
                            <div class="fs-xs" style="opacity:.75">Tekan, bicara, selesai.</div>
                        </div>
                    </div>
                    <div class="bg-white rounded-4 p-3 text-dark">
                        <div class="fs-xs text-uppercase fw-700 text-muted mb-2">Hasil Voice Input</div>
                        <div class="fw-650 mb-3">"Tadi saya makan siang 25 ribu"</div>
                        <div class="d-flex flex-wrap gap-2 mb-3">
                            <span class="pill pill-red">Uang Keluar</span>
                            <span class="pill pill-gray">Rp25.000</span>
                            <span class="pill pill-amber">Makanan</span>
                        </div>
                        <div class="row g-2 text-center">
                            <div class="col-4">
                                <div class="bg-light rounded-3 p-2">
                                    <div class="fs-xs text-muted">Nominal</div>
                                    <div class="fw-700">Rp25rb</div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="bg-light rounded-3 p-2">
                                    <div class="fs-xs text-muted">Kategori</div>
                                    <div class="fw-700">Makanan</div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="bg-light rounded-3 p-2">
                                    <div class="fs-xs text-muted">Jenis</div>
                                    <div class="fw-700 text-danger">Keluar</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>

<section class="py-5">
    <div class="container">
        <h2 class="text-center fw-800 mb-2">Semua yang kamu butuhkan</h2>
        <p class="text-center text-muted mb-5">Satu aplikasi untuk mencatat, memantau, dan memahami keuanganmu.</p>
        <div class="row g-4">
            <div class="col-md-6 col-lg-4">
                <div class="ft-feature">
                    <div class="ff-icon"><i class="bi bi-mic"></i></div>
                    <h6 class="fw-750 mb-2">Voice Transaction</h6>
                    <p class="fs-sm text-muted mb-0">Catat transaksi cukup dengan berbicara. Sistem memahami nominal, kategori, dan jenis transaksi secara otomatis.</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="ft-feature">
                    <div class="ff-icon"><i class="bi bi-cash-coin"></i></div>
                    <h6 class="fw-750 mb-2">Pencatatan Keuangan</h6>
                    <p class="fs-sm text-muted mb-0">Uang masuk, uang keluar, transfer antar dompet, dan saldo otomatis diperbarui di setiap akunmu.</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="ft-feature">
                    <div class="ff-icon"><i class="bi bi-bar-chart"></i></div>
                    <h6 class="fw-750 mb-2">Laporan Keuangan</h6>
                    <p class="fs-sm text-muted mb-0">Grafik interaktif, laporan bulanan, ekspor PDF & Excel, serta insight dari pola pengeluaranmu.</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="ft-feature">
                    <div class="ff-icon"><i class="bi bi-speedometer2"></i></div>
                    <h6 class="fw-750 mb-2">Budget</h6>
                    <p class="fs-sm text-muted mb-0">Tentukan anggaran per kategori dan dapatkan peringatan saat mendekati atau melebihi batas.</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="ft-feature">
                    <div class="ff-icon"><i class="bi bi-phone"></i></div>
                    <h6 class="fw-750 mb-2">Mobile Friendly</h6>
                    <p class="fs-sm text-muted mb-0">Dioptimalkan untuk HP dan desktop. Tombol mikrofon selalu dalam jangkauan jempolmu.</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="ft-feature">
                    <div class="ff-icon"><i class="bi bi-shield-lock"></i></div>
                    <h6 class="fw-750 mb-2">Aman & Privat</h6>
                    <p class="fs-sm text-muted mb-0">Setiap user hanya mengakses datanya sendiri. File rekaman suara disimpan privat dan terlindungi.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="py-5">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-6">
                <h2 class="fw-800 mb-3">Cara kerjanya sederhana</h2>
                <div class="d-flex gap-3 mb-4">
                    <span class="d-inline-grid place-items-center rounded-3 fw-800" style="width:40px;height:40px;background:var(--ft-primary);color:#fff">1</span>
                    <div>
                        <h6 class="fw-750 mb-1">Tekan mikrofon</h6>
                        <p class="text-muted fs-sm mb-0">Tekan tombol besar di layar utama. Browser akan mendengarkan.</p>
                    </div>
                </div>
                <div class="d-flex gap-3 mb-4">
                    <span class="d-inline-grid place-items-center rounded-3 fw-800" style="width:40px;height:40px;background:var(--ft-primary);color:#fff">2</span>
                    <div>
                        <h6 class="fw-750 mb-1">Bicara</h6>
                        <p class="text-muted fs-sm mb-0">Contoh: <em>"Bayar listrik 150 ribu"</em>. Eltrack memahami nominal, kategori, dan jenisnya.</p>
                    </div>
                </div>
                <div class="d-flex gap-3 mb-4">
                    <span class="d-inline-grid place-items-center rounded-3 fw-800" style="width:40px;height:40px;background:var(--ft-primary);color:#fff">3</span>
                    <div>
                        <h6 class="fw-750 mb-1">Konfirmasi & simpan</h6>
                        <p class="text-muted fs-sm mb-0">Cek hasilnya, perbaiki bila perlu, lalu simpan. Saldo dan dashboard langsung terbarui.</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="ft-card">
                    <p class="text-muted fs-sm mb-3">Coba contoh kalimat yang bisa dipahami:</p>
                    <div class="d-flex flex-column gap-2">
                        <div class="border rounded-3 p-2 px-3 fs-sm bg-light"><i class="bi bi-mic text-value me-2"></i>"Tadi saya makan siang 25 ribu" <span class="text-success fw-700 d-block fs-xs mt-1">→ Uang Keluar · Rp25.000 · Makanan</span></div>
                        <div class="border rounded-3 p-2 px-3 fs-sm bg-light"><i class="bi bi-mic text-value me-2"></i>"Saya menerima gaji 2 juta" <span class="text-success fw-700 d-block fs-xs mt-1">→ Uang Masuk · Rp2.000.000 · Gaji</span></div>
                        <div class="border rounded-3 p-2 px-3 fs-sm bg-light"><i class="bi bi-mic text-value me-2"></i>"Beli bensin 50 ribu" <span class="text-success fw-700 d-block fs-xs mt-1">→ Uang Keluar · Rp50.000 · Transportasi</span></div>
                        <div class="border rounded-3 p-2 px-3 fs-sm bg-light"><i class="bi bi-mic text-value me-2"></i>"Barusan beli kopi 20k" <span class="text-success fw-700 d-block fs-xs mt-1">→ Uang Keluar · Rp20.000 · Makanan</span></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="py-5">
    <div class="container text-center">
        <h2 class="fw-800 mb-2">Siap mengendalikan keuanganmu?</h2>
        <p class="text-muted mb-4">Gratis mulai sekarang. Tidak perlu kartu kredit.</p>
        <a href="{{ route('register') }}" class="btn btn-ft btn-lg px-5">Mulai Gratis</a>
    </div>
</section>

<footer class="border-top py-4 mt-4">
    <div class="container d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div class="fw-800"><i class="bi bi-graph-up-arrow me-1"></i>Eltrack</div>
        <div class="text-muted fs-xs">© {{ date('Y') }} Eltrack. Dibuat untuk kehidupan yang lebih teratur secara finansial.</div>
        @auth
            <a href="{{ route('dashboard') }}" class="fs-sm fw-600 text-decoration-none">Dashboard</a>
        @else
            <a href="{{ route('login') }}" class="fs-sm fw-600 text-decoration-none">Login</a>
        @endauth
    </div>
</footer>

</body>
</html>