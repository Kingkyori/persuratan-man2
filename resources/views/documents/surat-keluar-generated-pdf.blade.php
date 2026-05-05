<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <style>
        @page {
            margin: 28px 44px 36px;
        }

        body {
            font-family: "Times New Roman", DejaVu Serif, serif;
            color: #111827;
            font-size: 12px;
            line-height: 1.45;
        }

        .letterhead {
            display: table;
            width: 100%;
            border-bottom: 3px solid #111827;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }

        .logo-cell,
        .title-cell {
            display: table-cell;
            vertical-align: middle;
        }

        .logo-cell {
            width: 88px;
            text-align: center;
        }

        .logo-cell img {
            width: 72px;
            height: 72px;
            object-fit: contain;
        }

        .title-cell {
            text-align: center;
        }

        .title-cell h1,
        .title-cell h2,
        .title-cell h3,
        .title-cell p {
            margin: 0;
        }

        .title-cell h1 {
            font-size: 15px;
        }

        .title-cell h2,
        .title-cell h3 {
            font-size: 14px;
        }

        .date-row {
            text-align: right;
            margin-bottom: 12px;
        }

        .meta-table,
        .detail-table {
            width: 100%;
            border-collapse: collapse;
        }

        .meta-table {
            margin-bottom: 20px;
        }

        .meta-table td,
        .detail-table td {
            vertical-align: top;
            padding: 1px 0;
        }

        .label {
            width: 88px;
        }

        .colon {
            width: 14px;
        }

        .recipient {
            margin-bottom: 18px;
        }

        .paragraph {
            text-align: justify;
            margin: 0 0 12px;
        }

        .signature-table {
            width: 100%;
            margin-top: 28px;
            border-collapse: collapse;
        }

        .signature-spacer {
            width: 58%;
        }

        .signature-cell {
            width: 42%;
            vertical-align: top;
        }

        .signature-name {
            margin-top: 72px;
            font-weight: bold;
            text-decoration: underline;
        }
    </style>
</head>
<body>
    @php
        $logoDataUri = null;
        if (extension_loaded('gd') && !empty($letter['logo_path']) && is_file($letter['logo_path'])) {
            $logoDataUri = 'data:image/png;base64,' . base64_encode(file_get_contents($letter['logo_path']));
        }
    @endphp

    <div class="letterhead">
        <div class="logo-cell">
            @if($logoDataUri)
                <img src="{{ $logoDataUri }}" alt="Logo MAN 2 Surakarta">
            @endif
        </div>
        <div class="title-cell">
            <h1>KEMENTERIAN AGAMA REPUBLIK INDONESIA</h1>
            <h2>KANTOR KEMENTERIAN AGAMA KOTA SURAKARTA</h2>
            <h3>MADRASAH ALIYAH NEGERI 2 SURAKARTA</h3>
            <p>Jl. Slamet Riyadi No. 308 Surakarta</p>
            <p>Telepon (0271) 717510</p>
        </div>
    </div>

    <div class="date-row">{{ $letter['document_city_and_date'] }}</div>

    <table class="meta-table">
        <tr>
            <td class="label">Nomor</td>
            <td class="colon">:</td>
            <td>{{ $letter['document_number'] }}</td>
        </tr>
        <tr>
            <td class="label">Lamp</td>
            <td class="colon">:</td>
            <td>-</td>
        </tr>
        <tr>
            <td class="label">Hal</td>
            <td class="colon">:</td>
            <td>{{ $letter['subject'] }}</td>
        </tr>
    </table>

    <div class="recipient">
        <div>Kepada</div>
        <div>{{ $letter['recipient_name'] }}</div>
        <div>Di</div>
        <div>{{ $letter['recipient_address'] ?: 'Tempat' }}</div>
    </div>

    <p class="paragraph">{{ $letter['opening'] }}</p>

    <table class="detail-table">
        @foreach($letter['details'] as $label => $value)
            <tr>
                <td class="label">{{ $label }}</td>
                <td class="colon">:</td>
                <td>{!! nl2br(e($value)) !!}</td>
            </tr>
        @endforeach
    </table>

    <p class="paragraph" style="margin-top: 14px;">{{ $letter['closing'] }}</p>

    <table class="signature-table">
        <tr>
            <td class="signature-spacer"></td>
            <td class="signature-cell">
                <div>{{ $letter['signer_title'] }}</div>
                <div class="signature-name">{{ $letter['signer_name'] }}</div>
                @if($letter['signer_nip'])
                    <div>NIP. {{ $letter['signer_nip'] }}</div>
                @endif
            </td>
        </tr>
    </table>
</body>
</html>
