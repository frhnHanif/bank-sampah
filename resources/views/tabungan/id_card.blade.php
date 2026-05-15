<!DOCTYPE html>
<html>
<head>
    <title>ID Card - {{ $nasabah->nama }}</title>

    <style>
        @page {
            margin: 0;
            size: 105mm 148mm;
        }

        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #e8f4fb;
        }

        .card-container {
            width: 105mm;
            height: 148mm;
            position: relative;
            overflow: hidden;
            background: #e8f4fb;
        }

        /* Header Decoration */
        .header-bg {
            background-color: #e8f4fb;
            height: 3mm;
            width: 100%;
            position: absolute;
            top: 0;
            border-radius: 0 0 50% 50% / 0 0 20% 20%;
        }

        .content {
            position: relative;
            z-index: 10;
            text-align: center;
            padding-top: 5mm;
        }

        .logo {
            height: 12mm;
            margin-bottom: 10mm;
        }

        .title {
            color: black;
            font-weight: bolder;
            font-size: 16pt;
            margin: 0;
            letter-spacing: 1px;
        }

        .subtitle {
            color: black;
            font-size: 8pt;
            margin: 0;
            text-transform: uppercase;
        }

        .qr-section {
            margin-top: 15mm;
            background: white;
            display: inline-block;
            padding: 10px;
            border-radius: 15px;
            border: 2px solid #e8f4fb;
        }

        .info-section {
            margin-top: 10mm;
            padding: 0 10mm;
        }

        .name {
            font-size: 14pt;
            font-weight: bold;
            color: #1e293b;
            margin: 0;
            line-height: 1.2;
        }

        .id-code {
            font-size: 10pt;
            font-weight: bold;
            color: #10b981;
            margin-top: 2mm;
            background: white;
            display: inline-block;
            padding: 2px 10px;
            border-radius: 20px;
        }

        .footer {
            position: absolute;
            bottom: 0;
            width: 100%;
            height: 15mm;
            background: #e8f4fb;

            text-align: center;
        }

        .logo-footer {
            height: 8mm;

            position: absolute;
            left: 50%;
            top: 50%;

            transform: translate(-50%, -50%);
        }
    </style>
</head>

<body>

    <div class="card-container">

        <div class="header-bg"></div>

        <div class="content">

            <img src="{{ public_path('img/logo-kiri.png') }}" class="logo">

            <h1 class="title">KARTU ANGGOTA</h1>

            <!-- <p class="subtitle">NGUDIA WILUJENG</p> -->

            <div class="qr-section">
                <img src="data:image/svg+xml;base64,{{ $qrcode }}" width="130">
            </div>

            <div class="info-section">

                <p class="name">
                    {{ strtoupper($nasabah->nama) }}
                </p>

                <p class="id-code">
                    ID: {{ $nasabah->kode }}
                </p>

                <div style="margin-top: 0mm; font-size: 8pt; color: #64748b; line-height: 1.4;">
                    RT {{ $nasabah->rt }} / RW {{ $nasabah->rw }}
                    <br>
                    {{ $nasabah->no_hp ?? '-' }}
                </div>

            </div>
        </div>

        <div class="footer">
            <img src="{{ public_path('img/desktop_icon.png') }}" class="logo-footer">
        </div>

    </div>

</body>
</html>