<x-app-layout :pageTitle="'Catat dengan Suara'">

<div class="row g-4">
    <div class="col-lg-6">
        <div class="ft-card h-100">
            <div class="ft-card-title">Perekam Suara</div>
            <div class="bg-light rounded-4 p-4">
                <x-voice-capture />
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="ft-card mb-4">
            <div class="ft-card-title">Panduan & Contoh</div>
            <p class="fs-sm text-muted mb-3">Tekan tombol mikrofon, ucapkan transaksi dengan bahasa sehari-hari. Sistem akan mengenali nominal, jenis, dan kategorinya.</p>
            <div class="d-flex flex-column gap-2 fs-sm">
                <div class="border rounded-3 p-2 px-3 bg-light"><i class="bi bi-mic text-value me-2"></i>"Tadi saya makan siang 25 ribu"</div>
                <div class="border rounded-3 p-2 px-3 bg-light"><i class="bi bi-mic text-value me-2"></i>"Bayar listrik 150 ribu"</div>
                <div class="border rounded-3 p-2 px-3 bg-light"><i class="bi bi-mic text-value me-2"></i>"Saya menerima gaji 2 juta"</div>
                <div class="border rounded-3 p-2 px-3 bg-light"><i class="bi bi-mic text-value me-2"></i>"Beli bensin 50 ribu"</div>
                <div class="border rounded-3 p-2 px-3 bg-light"><i class="bi bi-mic text-value me-2"></i>"Beli makanan"</div>
            </div>
        </div>

        <div class="ft-card no-pad">
            <div class="p-3 border-bottom"><h6 class="fw-750 mb-0">Riwayat Transaksi Suara</h6></div>
            <div class="p-3 pt-2">
                @forelse($recentVoice as $tx)
                    <div class="ft-tx-row">
                        <span class="tx-icon" style="background:{{ $tx->category->color }}1a;color:{{ $tx->category->color }}">
                            <i class="bi {{ $tx->category->icon }}"></i>
                        </span>
                        <div class="tx-main">
                            <div class="tx-title">{{ $tx->category->name }}{{ $tx->description ? ' · ' . $tx->description : '' }}</div>
                            <div class="tx-sub">{{ $tx->transaction_date->translatedFormat('d M Y, H:i') }} · {{ $tx->account->name }}</div>
                        </div>
                        <div class="fs-sm num {{ $tx->type === 'income' ? 'amount-income' : 'amount-expense' }}">
                            {{ $tx->type === 'income' ? '+' : '-' }}<span class="text-value">@money($tx->amount)</span>
                        </div>
                    </div>
                @empty
                    <div class="text-center text-muted fs-sm py-4">Belum ada transaksi yang dicatat lewat suara.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>

</x-app-layout>