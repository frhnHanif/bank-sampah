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
        
        /* CSS Header Baru menggunakan Table */
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

        .item-list { margin: 4px 0 0; padding: 0; list-style: none; }
        .item-list li { padding: 1px 0; }
    </style>
</head>
<body>

    <table class="header-table">
        <tr>
            <td width="25%" style="text-align: left;">
                <img src="{{ public_path('img/logo-kiri.png') }}" alt="Logo Bank Sampah" style="height: 45px;">
            </td>
            
            <td width="50%" style="text-align: center;">
                <h1 class="header-title">LAPORAN RIWAYAT </h1>
                <p class="header-subtitle">MUTASI TABUNGAN NASABAH</p>
            </td>
            
            <td width="25%" style="text-align: right;">
                <img src="{{ public_path('img/desktop_icon.png') }}" alt="Logo Sponsor" style="height: 40px;">
            </td>
        </tr>
    </table>

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
            @php
                $tgl = \Carbon\Carbon::parse($m->tanggal);
                $bulanSingkat = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
                $tglFormat = $tgl->day . ' ' . $bulanSingkat[$tgl->month - 1] . ' ' . $tgl->year;
                $isKredit = $m->jenis == 'kredit';
                $items = $isKredit && $m->transaksiSetor ? $m->transaksiSetor->items : null;
            @endphp
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>{{ $tglFormat }}</td>
                <td>
                    {{ $m->keterangan }}
                    @if($isKredit && $items && $items->count())
                    <ul class="item-list">
                        @foreach($items as $item)
                        <li>{{ $item->kategori ? $item->kategori->nama : '—' }} — {{ number_format($item->berat_kg, 2, ',', '.') }} kg &times; Rp {{ number_format($item->nilai, 0, ',', '.') }}</li>
                        @endforeach
                    </ul>
                    @endif
                </td>
                <td class="text-right cr">{{ $isKredit ? 'Rp ' . number_format($m->jumlah, 0, ',', '.') : '-' }}</td>
                <td class="text-right db">{{ !$isKredit ? 'Rp ' . number_format($m->jumlah, 0, ',', '.') : '-' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="text-center">Belum ada riwayat transaksi.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <p style="text-align: right; font-size: 10px; color: #999; margin-top: 30px;">
        *Dokumen ini dicetak otomatis dari sistem pada {{ date('d/m/Y H:i') }} WIB
    </p>

</body>
</html>