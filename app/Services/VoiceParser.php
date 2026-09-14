<?php

namespace App\Services;

class VoiceParser
{
    private const DIGITS = [
        'se' => 1, 'nol' => 0, 'satu' => 1, 'dua' => 2, 'tiga' => 3,
        'empat' => 4, 'lima' => 5, 'enam' => 6, 'tujuh' => 7,
        'delapan' => 8, 'sembilan' => 9, 'sepuluh' => 10, 'sebelas' => 11,
    ];

    private const INCOME_WORDS = [
        'terima', 'menerima', 'diterima', 'masuk', 'dapat', 'dapatkan',
        'gaji', 'honor', 'bonus', 'thr', 'omset', 'jualan', 'kasih',
        'transferan', 'pemasukan', 'uangmasuk', 'topup', 'top up', 'cair',
    ];

    private const EXPENSE_WORDS = [
        'keluar', 'bayar', 'beli', 'belanja', 'makan', 'ngopi', 'minum',
        'nongkrong', 'habis', 'jajan', 'pengeluaran', 'uangkeluar', 'isi',
        'buat', 'ambil', 'selesaikan',
    ];

    private const CATEGORY_KEYWORDS = [
        'Gaji' => [
            'gaji', 'honor', 'thr', 'gajian', 'upah',
        ],
        'Bonus' => [
            'bonus', 'insentif', 'premi',
        ],
        'Bisnis' => [
            'bisnis', 'dagang', 'usaha', 'jualan', 'omset', 'warung', 'toko',
        ],
        'Freelance' => [
            'freelance', 'project', 'proyek', 'design', 'desain', 'client',
        ],
        'Investasi' => [
            'saham', 'reksadana', 'reksa dana', 'dividen', 'crypto', 'kripto', 'emas',
        ],
        'Hadiah' => [
            'hadiah', 'kado', 'doorprize',
        ],
        'Makanan' => [
            'makan', 'kopi', 'ngopi', 'sarapan', 'makan siang', 'makan malam',
            'lauk', 'nasi', 'bakso', 'sate', 'mie', 'ayam', 'gorengan', 'snack',
            'jajan', 'minum', 'es teh', 'boba', 'restoran', 'cafe', 'kafe', 'sop',
            'fast food', 'pizza', 'burger',
        ],
        'Transportasi' => [
            'bensin', 'ojek', 'gojek', 'grab', 'taxi', 'tol', 'parkir',
            'transport', 'transportasi', 'angkot', 'bus', 'kereta', 'pajak kendaraan',
            'spbu', 'bahan bakar', 'bbm',
        ],
        'Belanja' => [
            'belanja', 'baju', 'sepatu', 'tas', 'skincare', 'kebutuhan',
            'vitamin', 'minimarket', 'alpro', 'swalayan', 'matahari', 'baju anak',
        ],
        'Tagihan' => [
            'listrik', 'air', 'internet', 'pulsa', 'tagihan', 'bpjs', 'telepon',
            'wifi', 'token', 'spp', 'streaming', 'netflix', 'spotify',
        ],
        'Hiburan' => [
            'nonton', 'film', 'bioskop', 'hangout', 'game', 'liburan', 'tiket',
            'konser', 'wisata', 'main', 'game online',
        ],
        'Pendidikan' => [
            'kursus', 'buku', 'sekolah', 'kuliah', 'les', 'belajar', 'kelas',
        ],
        'Kesehatan' => [
            'obat', 'dokter', 'klinik', 'rumah sakit', 'rs', 'vitamin', 'periksa',
            'konsultasi', 'cektensi', 'cek tensi', 'lab',
        ],
        'Tabungan' => [
            'tabungan', 'nabung', 'menabung', 'deposit',
        ],
        'Kebutuhan Rumah' => [
            'rumah', 'dapur', 'sapu', 'sabun', 'deterjen', 'perlengkapan',
            'belanja bulanan', 'sembako', 'beras', 'minyak goreng', 'gas',
        ],
    ];

    public function parse(string $transcript): array
    {
        $original = trim($transcript);
        $text = mb_strtolower($original);
        $text = str_replace(['...', '?', '!', '"', "'"], '', $text);
        $text = preg_replace('/\s+/', ' ', $text) ?? $text;

        $amount = $this->extractAmount($text);
        $type = $this->detectType($text);
        $category = $this->detectCategory($text, $type);
        $description = $this->buildDescription($original, $text, $amount);

        $missing = [];
        if ($amount === null) {
            $missing[] = 'Nominal transaksi belum ditemukan. Silakan masukkan nominal.';
        }
        if ($category === null) {
            $missing[] = 'Kategori belum dapat ditentukan. Silakan pilih kategori.';
        }

        return [
            'type' => $type,
            'amount' => $amount,
            'category' => $category,
            'description' => $description,
            'transcript' => $original,
            'warnings' => $missing,
        ];
    }

    private function detectType(string $text): string
    {
        $countIncome = count(array_filter(self::INCOME_WORDS, fn ($w) => str_contains($text, $w)));
        $countExpense = count(array_filter(self::EXPENSE_WORDS, fn ($w) => str_contains($text, $w)));

        if ($countIncome > $countExpense) {
            return 'income';
        }

        if ($countExpense > 0) {
            return 'expense';
        }

        if (str_contains($text, '+') && str_contains($text, 'masuk')) {
            return 'income';
        }

        return 'expense';
    }

    private function detectCategory(string $text, string $type): ?string
    {
        $best = null;
        $bestScore = 0;

        foreach (self::CATEGORY_KEYWORDS as $category => $keywords) {
            $score = 0;
            foreach ($keywords as $keyword) {
                if (str_contains($text, $keyword)) {
                    $score += strlen($keyword);
                }
            }
            if ($score > $bestScore) {
                $bestScore = $score;
                $best = $category;
            }
        }

        if ($best === null || $bestScore === 0) {
            return 'Lainnya';
        }

        if (in_array($best, ['Gaji', 'Bonus', 'Bisnis', 'Freelance', 'Investasi', 'Hadiah'], true) && $type === 'expense') {
            return 'Lainnya';
        }

        if (in_array($best, ['Makanan', 'Transportasi', 'Belanja', 'Tagihan', 'Hiburan', 'Pendidikan', 'Kesehatan', 'Tabungan', 'Kebutuhan Rumah'], true) && $type === 'income') {
            if ($type === 'income') {
                return $best;
            }
        }

        return $best;
    }

    private function extractAmount(string $text): ?int
    {
        if (preg_match('/rp\s?([0-9][\d.]*(?:[,.][\d]+)?)/', $text, $m)) {
            $value = $this->numericFromClean($m[1]);
            if ($value !== null) {
                return $value;
            }
        }

        if (preg_match_all('/\b(\d{1,3}(?:[.,]\d{3})+|\d{3,}|[0-9]+(?:[.,][0-9]{1,2})?)\s*(ribu|rb|rbu|juta|jt|k)?\b/', $text, $matches, PREG_SET_ORDER)) {
            $candidates = [];
            foreach ($matches as $match) {
                $num = $this->numericFromClean($match[1]);
                if ($num === null || $num <= 0) {
                    continue;
                }
                $unit = strtolower($match[2] ?? '');
                if ($unit === 'ribu' || $unit === 'k' || $unit === 'rb' || $unit === 'rbu') {
                    $num *= 1000;
                } elseif ($unit === 'juta' || $unit === 'jt') {
                    $num *= 1000000;
                }
                $candidates[] = $num;
            }
            if ($candidates) {
                return (int) max($candidates);
            }
        }

        return $this->extractAmountWords($text);
    }

    private function extractAmountWords(string $text): ?int
    {
        $tokens = preg_split('/\s+/', trim($text)) ?: [];
        $numberTokens = array_merge(array_keys(self::DIGITS), ['puluh', 'belas', 'ratus', 'ribu', 'juta', 'miliar']);

        $results = [];
        $buffer = [];

        foreach ($tokens as $token) {
            $token = preg_replace('/[^a-z]/', '', $token);
            if (in_array($token, $numberTokens, true)) {
                $buffer[] = $token;
            } else {
                if ($buffer) {
                    $results[] = $this->wordsToNumber($buffer);
                    $buffer = [];
                }
            }
        }
        if ($buffer) {
            $results[] = $this->wordsToNumber($buffer);
        }

        $max = null;
        foreach ($results as $value) {
            if ($value > 0 && ($max === null || $value > $max)) {
                $max = $value;
            }
        }

        return $max;
    }

    private function wordsToNumber(array $words): int
    {
        $result = 0;
        $current = 0;

        foreach ($words as $word) {
            if (isset(self::DIGITS[$word])) {
                $current = self::DIGITS[$word];
            } elseif ($word === 'puluh') {
                $current = ($current ?: 0) * 10;
            } elseif ($word === 'belas') {
                $current = ($current ?: 1) + 10;
            } elseif ($word === 'ratus') {
                $current = ($current ?: 1) * 100;
            } elseif ($word === 'ribu') {
                $result += ($current ?: 1) * 1000;
                $current = 0;
            } elseif ($word === 'juta') {
                $result += ($current ?: 1) * 1000000;
                $current = 0;
            } elseif ($word === 'miliar') {
                $result += ($current ?: 1) * 1000000000;
                $current = 0;
            }
        }

        $result += $current;

        return $result;
    }

    private function numericFromClean(string $value): ?float
    {
        $value = trim($value);

        if (preg_match('/^(\d{1,3})([.,])(\d{3})$/', $value, $m)) {
            return (float) ($m[1] . $m[3]);
        }

        if (preg_match('/^(\d+)[.,](\d{1,2})$/', $value, $m)) {
            return (float) ($m[1] . '.' . $m[2]);
        }

        $clean = preg_replace('/\./', '', $value);

        return is_numeric($clean) ? (float) $clean : null;
    }

    private function buildDescription(string $original, string $text, ?int $amount): string
    {
        $raw = mb_strtolower($original);
        $raw = str_replace(['...', '?', '!', '"', "'"], '', $raw);

        $raw = preg_replace('/[0-9][\d.]*(?:[,.][\d]+)?/', ' ', $raw) ?? $raw;

        $unitWords = ['rb', 'k', 'ribu', 'rbu', 'juta', 'jt', 'rupiah', 'perak'];
        foreach ($unitWords as $unit) {
            $raw = preg_replace('/\b'.preg_quote($unit, '/').'\b/', ' ', $raw);
        }

        $numberWords = array_keys(self::DIGITS);
        $numberWords = array_merge($numberWords, ['puluh', 'belas', 'ratus', 'ribu', 'juta', 'miliar']);
        usort($numberWords, fn ($a, $b) => strlen($b) - strlen($a));
        foreach ($numberWords as $word) {
            $raw = preg_replace('/\b'.preg_quote($word, '/').'\b/', ' ', $raw);
        }

        $filler = [
            'saya', 'aku', 'gue', 'gw', 'gua', 'tadi', 'barusan', 'td', 'ya',
            'hari', 'ini', 'kemarin', 'besok', 'sudah', 'udah', 'mau', 'nih',
            'yang', 'ny', 'nya', 'aku', 'sih', 'dong', 'deh', 'lah', 'dan',
            'sama', 'dengan', 'buat', 'kali', 'tiap', 'untuk', 'habis', 'pake',
            'pakai', 'ke', 'di', 'itu', 'juga', 'terus', 'lalu', 'sekarang',
            'barusan', 'setelah', 'pagi', 'tuh',
        ];

        $words = array_filter(
            preg_split('/\s+/', trim($raw)) ?: [],
            fn ($w) => strlen($w) > 1 && ! in_array($w, $filler, true)
        );

        $description = implode(' ', array_slice(array_values($words), 0, 8));

        if ($description === '') {
            $description = $this->detectType($text) === 'income' ? 'Pemasukan' : 'Pengeluaran';
        }

        return ucfirst($description);
    }
}