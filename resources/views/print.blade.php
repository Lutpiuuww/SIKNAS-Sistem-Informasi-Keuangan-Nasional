<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Cetak Dokumen SIKNAS</title>
    <style>
        body { font-family: 'Times New Roman', Times, serif; background-color: white; margin: 0; padding: 0; }
        .watermark { position: fixed; top: 30%; left: 10%; transform: rotate(-45deg); font-size: 100px; color: rgba(200, 200, 200, 0.2); z-index: -1; font-weight: bold; }
        table.header-table { width: 100%; border-bottom: 4px double #000; padding-bottom: 10px; margin-bottom: 20px; }
        table.header-table td { border: none; padding: 0; }
        .text-center { text-align: center; }
        .font-bold { font-weight: bold; }
        .title { font-size: 20px; text-transform: uppercase; margin: 0; }
        .subtitle { font-size: 18px; font-weight: bold; margin: 5px 0; }
        .address { font-size: 12px; margin: 0; }
        .qr-code { width: 80px; height: 80px; }
        table.data-table { width: 100%; border-collapse: collapse; margin-top: 20px; font-size: 12px; }
        table.data-table th, table.data-table td { border: 1px solid #000; padding: 6px; text-align: left; }
        table.data-table th { background-color: #f3f4f6; }
        .text-right { text-align: right; }
        .text-red { color: #dc2626; }
    </style>
</head>
<body>
    <div class="watermark">RAHASIA NEGARA</div>
    <table class="header-table">
        <tr>
            <td style="width: 15%; text-align: left; font-size: 50px;">🦅</td>
            <td class="text-center" style="width: 70%;">
                <p class="title">Kementerian Keuangan Republik Indonesia</p>
                <p class="subtitle">Direktorat Jenderal Perbendaharaan Negara</p>
                <p class="address">Gedung Prijadi Praptosuhardjo I, Jl. Lapangan Banteng Timur No.2-4, Jakarta 10710</p>
            </td>
            <td style="width: 15%; text-align: right;">
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=80x80&data=SIKNAS" alt="QR Code" class="qr-code">
            </td>
        </tr>
    </table>

    <div class="text-center" style="margin-bottom: 20px;">
        <h3 class="font-bold" style="text-decoration: underline; margin:0;">LAPORAN MUTASI KEUANGAN NEGARA</h3>
        <p style="font-size:12px; margin: 5px 0;">Nomor: SIKNAS-{{ now()->format('Y/m/d/Hi') }}</p>
    </div>

    <p style="font-size: 12px; text-indent: 30px; text-align: justify; margin-bottom: 15px;">
        Dengan hormat, berdasarkan data yang terekam pada Sistem Informasi Keuangan Nasional (SIKNAS) per tanggal <strong>{{ now()->translatedFormat('d F Y H:i:s') }}</strong>, berikut ini disampaikan lampiran log transaksi arus kas negara yang ditarik oleh <strong>{{ $user->name }} ({{ strtoupper($user->role) }})</strong> untuk keperluan audit dan pengawasan.
    </p>

    <table class="data-table">
        <thead>
            <tr>
                <th class="text-center">No</th>
                <th>Nomor Transaksi</th>
                <th>Waktu (UTC)</th>
                <th>Kategori Transaksi</th>
                <th class="text-right">Nominal (Rp)</th>
                <th class="text-center">Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($transactions as $index => $trx)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td style="font-family: monospace;">{{ $trx->transaction_number }}</td>
                <td>{{ $trx->created_at }}</td>
                <td>{{ $trx->category }}</td>
                <td class="text-right">{{ number_format($trx->amount / 1e9, 2, ',', '.') }} M</td>
                <td class="text-center font-bold {{ $trx->status == 'Anomaly' ? 'text-red' : '' }}">
                    {{ $trx->status == 'Success' ? 'SAH' : ($trx->status == 'Anomaly' ? 'ANOMALI' : strtoupper($trx->status)) }}
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <table style="width: 100%; margin-top: 50px;">
        <tr>
            <td style="width: 60%;"></td>
            <td class="text-center" style="width: 40%;">
                <p style="font-size: 12px; margin-bottom: 60px;">Jakarta, {{ now()->translatedFormat('d F Y') }}</p>
                <p class="font-bold" style="text-decoration: underline; margin: 0;">Menteri Keuangan RI</p>
                <p style="font-size: 10px; margin: 5px 0;">Otorisasi Sistem Digital SIKNAS</p>
            </td>
        </tr>
    </table>
    
    <p style="margin-top: 30px; font-size: 10px; color: #666; font-style: italic; border-top: 1px solid #ccc; padding-top: 10px;">
        * Dokumen ini digenerasi secara otomatis oleh sistem SIKNAS Enterprise menggunakan DOMPDF. Validitas dokumen dapat diperiksa melalui pemindaian QR Code di sudut kanan atas.
    </p>
</body>
</html>
