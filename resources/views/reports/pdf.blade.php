<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: DejaVu Sans, sans-serif; color: #0f172a; font-size: 11px; margin: 0; padding: 20px; }
        .header { border-bottom: 2px solid #0f172a; padding-bottom: 12px; margin-bottom: 16px; }
        .logo { font-size: 18px; font-weight: 800; }
        .title { margin-top: 6px; font-size: 13px; font-weight: 700; }
        .meta { margin-top: 4px; color: #64748b; font-size: 10px; }
        table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        th, td { padding: 5px 6px; text-align: left; border-bottom: 1px solid #e7eaf0; }
        th { background: #f1f5f9; font-size: 9px; text-transform: uppercase; letter-spacing: .03em; }
        .sum { display: inline-block; padding: 3px 8px; border-radius: 6px; font-size: 10px; margin-bottom: 4px; }
        .g { background: #ecfdf3; color: #15803d; }
        .r { background: #fef2f2; color: #b91c1c; }
        .m { background: #f1f5f9; color: #334155; }
        td.num, th.num { text-align: right; }
        .section { margin-top: 18px; font-weight: 700; font-size: 12px; border-left: 3px solid #0f172a; padding-left: 8px; }
        .grid { width: 100%; margin-top: 6px; }
        .grid td { border: none; }
        .box { border: 1px solid #e7eaf0; border-radius: 8px; padding: 8px 10px; }
        .box .l { font-size: 9px; color: #64748b; }
        .box .v { font-size: 13px; font-weight: 800; }
        .footer { position: fixed; bottom: -18px; left: 0; right: 0; text-align: center; color: #94a3b8; font-size: 9px; border-top: 1px solid #e7eaf0; padding-top: 6px; }
    </style>
</head>
<body>
    <div class="header">
        <div class="logo">Eltrack</div>
        <div class="title">{{ $title }}</div>
        <div class="meta">{{ $user->name }} · {{ $user->email }} · Dicetak {{ now()->translatedFormat('d M Y H:i') }}</div>
    </div>

    <div class="grid">
        <table>
            <tr>
                <td style="width:25%"><div class="box"><div class="l">Saldo</div><div class="v">@money($data['balance'])</div></div></td>
                <td style="width:25%"><div class="box"><div class="l">Uang Masuk</div><div class="v">@money($data['income'])</div></div></td>
                <td style="width:25%"><div class="box"><div class="l">Uang Keluar</div><div class="v">@money($data['expense'])</div></div></td>
                <td style="width:25%"><div class="box"><div class="l">Net</div><div class="v">{{ $data['net'] >= 0 ? '+' : '-' }}@money(abs($data['net']))</div></div></td>
            </tr>
        </table>
    </div>

    <div class="section">Ringkasan</div>
    <table>
        <tr><th>Uang Masuk</th><th>Uang Keluar</th><th>Net</th><th class="num">Jumlah Transaksi</th></tr>
        <tr>
            <td><span class="sum g">@money($data['income'])</span></td>
            <td><span class="sum r">@money($data['expense'])</span></td>
            <td><span class="sum m">{{ $data['net'] >= 0 ? '+' : '-' }}@money(abs($data['net']))</span></td>
            <td class="num">{{ $data['count'] }}</td>
        </tr>
    </table>

    <div class="section">Pengeluaran per Kategori</div>
    <table>
        <tr><th>Kategori</th><th class="num">Total</th></tr>
        @forelse($data['category_expense'] as $row)
            <tr><td>{{ $row['name'] }}</td><td class="num">@money($row['total'])</td></tr>
        @empty
            <tr><td colspan="2">Tidak ada pengeluaran.</td></tr>
        @endforelse
    </table>

    <div class="section">Pemasukan per Kategori</div>
    <table>
        <tr><th>Kategori</th><th class="num">Total</th></tr>
        @forelse($data['category_income'] as $row)
            <tr><td>{{ $row['name'] }}</td><td class="num">@money($row['total'])</td></tr>
        @empty
            <tr><td colspan="2">Tidak ada pemasukan.</td></tr>
        @endforelse
    </table>

    <div class="section">Rincian Transaksi</div>
    <table>
        <thead>
            <tr><th>Tanggal</th><th>Kategori</th><th>Akun</th><th>Deskripsi</th><th class="num">Nominal</th></tr>
        </thead>
        <tbody>
            @forelse($data['transactions'] as $tx)
                <tr>
                    <td>{{ $tx->transaction_date->translatedFormat('d M Y') }}</td>
                    <td>{{ $tx->category->name }}</td>
                    <td>{{ $tx->account->name }}</td>
                    <td>{{ $tx->description ?: '-' }}</td>
                    <td class="num">{{ $tx->type === 'income' ? '+' : '-' }}@money($tx->amount)</td>
                </tr>
            @empty
                <tr><td colspan="5">Tidak ada transaksi pada periode ini.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">Laporan dihasilkan otomatis oleh Eltrack · {{ $p['from'] }} s.d. {{ $p['to'] }}</div>
</body>
</html>