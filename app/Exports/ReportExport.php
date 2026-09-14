<?php

namespace App\Exports;

use App\Support\Money;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ReportExport implements FromCollection, WithHeadings, WithTitle, ShouldAutoSize, WithStyles
{
    private array $data;
    private array $p;

    public function __construct(array $data, array $p)
    {
        $this->data = $data;
        $this->p = $p;
    }

    public function title(): string
    {
        return 'Laporan Keuangan';
    }

    public function headings(): array
    {
        return [
            ['Eltrack — Laporan Keuangan'],
            ['Periode: ' . $this->p['from'] . ' s.d. ' . $this->p['to']],
            ['Saldo', Money::format($this->data['balance'])],
            ['Total Pemasukan', Money::format($this->data['income'])],
            ['Total Pengeluaran', Money::format($this->data['expense'])],
            ['Net Cash Flow', Money::format($this->data['net'])],
            [],
            ['Tanggal', 'Jenis', 'Kategori', 'Deskripsi', 'Metode', 'Akun', 'Nominal'],
        ];
    }

    public function collection()
    {
        return collect($this->data['transactions'])->map(function ($tx) {
            return [
                $tx->transaction_date->toDateString(),
                $tx->type === 'income' ? 'Uang Masuk' : 'Uang Keluar',
                $tx->category->name ?? '-',
                $tx->description ?? '-',
                $tx->payment_method,
                $tx->account->name ?? '-',
                ($tx->type === 'income' ? '+' : '-') . number_format((float) $tx->amount, 0, ',', '.'),
            ];
        });
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 16]],
            8 => ['font' => ['bold' => true], 'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'E7EAF0']]],
        ];
    }
}