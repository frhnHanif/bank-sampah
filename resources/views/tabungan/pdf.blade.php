<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Buku Tabungan - {{ $nasabah->nama }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            color: #333;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #10b981;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .header h1 {
            margin: 0;
            color: #065f46;
            font-size: 20px;
        }
        .header p {
            margin: 5px 0 0;
            color: #666;
        }
        .info-table {
            width: 100%;
            margin-bottom: 20px;
        }
        .info-table td {
            padding: 4px 0;
            vertical-align: top;
        }
        .info-table td:first-child {
            width: 120px;
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

    <div class="header">
        <h1>BANK SAMPAH SIDO MAKMUR</h1>
        <p>Laporan Riwayat Mutasi Tabungan Nasabah</p>
    </div>

    <table class="info-table">
        <tr>
            <td>Kode Nasabah</td>
            <td>: {{ $nasabah->kode }}</td>
            <td rowspan="4">
                <div class="saldo-box text-right">
                    <h3>TOTAL SALDO AKTIF</h3>
                    <p>Rp {{ number_format($nasabah->tabungan ? $nasabah->tabungan->saldo_saat_ini : 0, 0, ',', '.') }}</p>
                </div>
            </td>
        </tr>
        <tr>
            <td>Nama Lengkap</td>
            <td>: {{ $nasabah->nama }}</td>
        </tr>
        <tr>
            <td>Alamat (RT/RW)</td>
            <td>: RT {{ $nasabah->rt }} / RW {{ $nasabah->rw }}</td>
        </tr>
        <tr>
            <td>No. HP</td>
            <td>: {{ $nasabah->no_hp ?? '-' }}</td>
        </tr>
    </table>

    <table class="mutasi-table">
        <thead>
            <tr>
                <th width="5%" class="text-center">No</th>
                <th width="15%">Tanggal</th>
                <th width="30%">Keterangan Transaksi</th>
                <th width="20%" class="text-right">Masuk (Cr)</th>
                <th width="20%" class="text-right">Keluar (Db)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($mutasi as $index => $m)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>{{ \Carbon\Carbon::parse($m->tanggal)->format('d/m/Y') }}</td>
                <td>{{ $m->keterangan }}</td>
                <td class="text-right cr">{{ $m->jenis == 'kredit' ? 'Rp ' . number_format($m->jumlah, 0, ',', '.') : '-' }}</td>
                <td class="text-right db">{{ $m->jenis == 'debit' ? 'Rp ' . number_format($m->jumlah, 0, ',', '.') : '-' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="text-center">Belum ada riwayat transaksi.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <p style="text-align: right; font-size: 10px; color: #999; margin-top: 30px;">
        *Dokumen ini dicetak otomatis dari sistem pada {{ date('d/m/Y H:i') }}
    </p>

</body>
</html>