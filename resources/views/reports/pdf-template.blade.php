<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Keuangan Famly - {{ $month }}/{{ $year }}</title>
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; padding: 40px; color: #334155; }
        .header { text-align: center; margin-bottom: 40px; border-bottom: 2px solid #00AA13; padding-bottom: 20px; }
        .logo { color: #00AA13; font-weight: 900; font-size: 24px; }
        .grid { display: flex; gap: 20px; margin-bottom: 30px; }
        .card { flex: 1; padding: 20px; border: 1px solid #F1F5F9; border-radius: 12px; }
        .label { font-size: 10px; font-weight: bold; color: #94A3B8; text-transform: uppercase; }
        .value { font-size: 18px; font-weight: 900; margin-top: 5px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th { text-align: left; padding: 12px; border-bottom: 1px solid #F1F5F9; font-size: 11px; text-transform: uppercase; color: #64748B; }
        td { padding: 12px; border-bottom: 1px solid #F1F5F9; font-size: 12px; }
        .footer { margin-top: 50px; font-size: 10px; text-align: center; color: #94A3B8; }
        @media print {
            .no-print { display: none; }
        }
    </style>
</head>
<body onload="window.print()">
    <div class="header">
        <div class="logo">Famly.</div>
        <div style="font-size: 14px; font-weight: bold; margin-top: 10px;">Laporan Ringkasan Keuangan</div>
        <div style="font-size: 11px; color: #64748B;">Periode: {{ date('F', mktime(0, 0, 0, $month, 10)) }} {{ $year }}</div>
    </div>

    <div style="margin-bottom: 20px;">
        <strong>Informasi Grup/Entitas:</strong><br>
        {{ $user->currentGroup->name ?? 'Pribadi' }}
    </div>

    <div class="grid">
        <div class="card">
            <div class="label">Pemasukan</div>
            <div class="value" style="color: #00AA13;">Rp {{ number_format(\App\Models\Tabungan::whereMonth('created_at', $month)->whereYear('created_at', $year)->whereHas('kategoriJenis', fn($q) => $q->where('jenis', 'Pemasukan'))->sum('nominal')) }}</div>
        </div>
        <div class="card">
            <div class="label">Pengeluaran</div>
            <div class="value" style="color: #F43F5E;">Rp {{ number_format(\App\Models\Tabungan::whereMonth('created_at', $month)->whereYear('created_at', $year)->whereHas('kategoriJenis', fn($q) => $q->where('jenis', 'Pengeluaran'))->sum('nominal')) }}</div>
        </div>
    </div>

    <h3>Rincian Transaksi Terbaru</h3>
    <table>
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>Kategori</th>
                <th>Keterangan</th>
                <th>Nominal</th>
            </tr>
        </thead>
        <tbody>
            @foreach(\App\Models\Tabungan::whereMonth('created_at', $month)->whereYear('created_at', $year)->latest()->take(50)->get() as $trx)
            <tr>
                <td>{{ $trx->created_at->format('d/m/Y') }}</td>
                <td>{{ $trx->kategoriNama->nama ?? '-' }}</td>
                <td>{{ $trx->keterangan ?: '-' }}</td>
                <td style="font-weight: bold; {{ $trx->kategoriJenis->jenis == 'Pemasukan' ? 'color: #00AA13;' : 'color: #F43F5E;' }}">
                    {{ $trx->kategoriJenis->jenis == 'Pemasukan' ? '+' : '-' }} {{ number_format($trx->nominal) }}
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        Dokumen ini dihasilkan secara otomatis oleh sistem Famly pada {{ now()->format('d/m/Y H:i') }}.<br>
        <strong>Orkestrasi Keuangan Berbasis Grup.</strong>
    </div>

    <button onclick="window.history.back()" class="no-print" style="position: fixed; bottom: 20px; right: 20px; padding: 10px 20px; background: #00AA13; color: white; border: none; border-radius: 8px; cursor: pointer;">
        Kembali ke Aplikasi
    </button>
</body>
</html>
