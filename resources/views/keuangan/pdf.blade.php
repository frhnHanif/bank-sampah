<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Kas Induk Bank Sampah</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            color: #333;
        }
        
        /* CSS Header */
        .header-table {
            width: 100%;
            border-bottom: 2px solid #10b981;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        .header-table td {
            vertical-align: middle;
        }
        .header-title {
            margin: 0;
            color: #065f46;
            font-size: 18px;
            font-weight: bold;
        }
        .header-subtitle {
            margin: 5px 0 0;
            color: #666;
            font-size: 11px;
        }

        /* CSS Tabel Info & Mutasi */
        .info-table {
            width: 100%;
            margin-bottom: 20px;
        }
        .info-table td {
            padding: 4px 0;
            vertical-align: top;
        }
        .info-table td:first-child {
            width: 130px;
            font-weight: bold;
        }
        .mutasi-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .mutasi-table th, .mutasi-table td {
            border: 1px solid #ddd;
            padding: 8px;
        }
        .mutasi-table th {
            background-color: #ecfdf5;
            color: #065f46;
            text-align: left;
        }
        
        /* Utility Classes */
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .cr { color: #059669; font-weight: bold; }
        .db { color: #dc2626; font-weight: bold; }
        
        .saldo-box {
            float: right;
            border: 2px solid #10b981;
            padding: 15px;
            border-radius: 8px;
            background: #ecfdf5;
            width: 200px;
        }
        .saldo-box h3 { margin: 0 0 5px 0; font-size: 12px; color: #065f46; }
        .saldo-box p { margin: 0; font-size: 20px; font-weight: bold; color: #065f46; }
    </style>
</head>
<body>

    <table class="header-table">
        <tr>
            <td width="25%" style="text-align: left;">
                <img src="{{ public_path('img/logo-kiri.png') }}" alt="Logo Bank Sampah" style="height: 45px;">
            </td>
            <td width="50%" style="text-align: center;">
                <h1 class="header-title">LAPORAN ARUS KAS</h1>
                <p class="header-subtitle">KEUANGAN INTERNAL BANK SAMPAH</p>
            </td>
            <td width="25%" style="text-align: right;">
                <img src="{{ public_path('img/desktop_icon.png') }}" alt="Logo Sponsor" style="height: 40px;">
            </td>
        </tr>
    </table>

    <table class="info-table">
        <tr>
            <td>Periode Cetak</td>
            <td>: Keseluruhan</td>
            <td rowspan="3">
                <div class="saldo-box text-right">
                    <h3>TOTAL SALDO KAS</h3>
                    <p>Rp {{ number_format($saldoKas, 0, ',', '.') }}</p>
                </div>
            </td>
        </tr>
        <tr>
            <td>Total Pemasukan</td>
            <td>: Rp {{ number_format($totalPemasukan, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td>Total Pengeluaran</td>
            <td>: Rp {{ number_format($totalPengeluaran, 0, ',', '.') }}</td>
        </tr>
    </table>

    <table class="mutasi-table">
        <thead>
            <tr>
                <th width="5%" class="text-center">No</th>
                <th width="15%">Tanggal</th>
                <th width="15%">Kategori</th>
                <th width="25%">Keterangan</th>
                <th width="20%" class="text-right">Pemasukan</th>
                <th width="20%" class="text-right">Pengeluaran</th>
            </tr>
        </thead>
        <tbody>
            @forelse($mutasiKas as $index => $m)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>{{ \Carbon\Carbon::parse($m->tanggal)->format('d/m/Y') }}</td>
                <td>{{ $m->kategori }}</td>
                <td>{{ $m->keterangan }}</td>
                <td class="text-right cr">{{ $m->tipe == 'pemasukan' ? 'Rp ' . number_format($m->nominal, 0, ',', '.') : '-' }}</td>
                <td class="text-right db">{{ $m->tipe == 'pengeluaran' ? 'Rp ' . number_format($m->nominal, 0, ',', '.') : '-' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="text-center">Belum ada riwayat transaksi kas.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <p style="text-align: right; font-size: 10px; color: #999; margin-top: 30px;">
        *Dokumen ini dicetak otomatis dari sistem pada {{ date('d/m/Y H:i') }} WIB
    </p>

</body>
</html>