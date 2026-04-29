<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Surat Tugas</title>
    <style>
        @page {
            margin: 24mm 18mm 20mm 22mm;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            line-height: 1.45;
            color: #111111;
        }

        .header-table,
        .content-table,
        .sign-table,
        .assignee-table {
            width: 100%;
            border-collapse: collapse;
        }

        .header-table td,
        .content-table td,
        .sign-table td,
        .assignee-table td {
            vertical-align: top;
        }

        .logo-cell {
            width: 90px;
            padding-top: 4px;
        }

        .logo {
            width: 82px;
            height: auto;
        }

        .header-text {
            text-align: center;
        }

        .header-text .line-1 {
            font-size: 16px;
            font-weight: 700;
            letter-spacing: 0.3px;
        }

        .header-text .line-2 {
            font-size: 12px;
        }

        .header-text .line-3 {
            font-size: 14px;
            font-weight: 700;
        }

        .header-text .line-4,
        .header-text .line-5 {
            font-size: 11px;
        }

        .divider {
            border-top: 1px solid #222222;
            margin-top: 10px;
            margin-bottom: 18px;
        }

        .title {
            text-align: center;
            font-size: 22px;
            font-weight: 700;
            letter-spacing: 0.5px;
            margin-bottom: 2px;
        }

        .number {
            text-align: center;
            margin-bottom: 18px;
        }

        .label {
            width: 94px;
            padding-right: 8px;
        }

        .colon {
            width: 12px;
        }

        .content-cell {
            width: auto;
            text-align: justify;
        }

        .content-table {
            margin-bottom: 12px;
        }

        .content-table td {
            padding-bottom: 10px;
        }

        .section-heading {
            text-align: center;
            font-size: 15px;
            font-weight: 700;
            margin: 8px 0 14px;
        }

        .list-item {
            margin-bottom: 5px;
        }

        .assignee-block {
            margin-bottom: 12px;
        }

        .assignee-table td {
            padding-bottom: 2px;
        }

        .assignee-number {
            width: 18px;
        }

        .assignee-label {
            width: 78px;
        }

        .footer-note {
            width: 56%;
            border: 1px solid #444444;
            padding: 10px 12px;
            font-size: 11px;
        }

        .signature-cell {
            width: 38%;
            text-align: right;
            padding-left: 18px;
        }

        .signature-name {
            margin-top: 54px;
            font-weight: 700;
            text-decoration: underline;
        }

        .muted-space {
            margin-top: 18px;
        }
    </style>
</head>
<body>
    <table class="header-table">
        <tr>
            <td class="logo-cell">
                @if($letter['logo_data_uri'])
                    <img src="{{ $letter['logo_data_uri'] }}" alt="Logo MAN 2 Surakarta" class="logo">
                @endif
            </td>
            <td class="header-text">
                <div class="line-1">KEMENTERIAN AGAMA REPUBLIK INDONESIA</div>
                <div class="line-2">KANTOR KEMENTERIAN AGAMA KOTA SURAKARTA</div>
                <div class="line-3">MADRASAH ALIYAH NEGERI 2</div>
                <div class="line-4">Jalan Slamet Riyadi Nomor 308 Surakarta, Telepon: (0271)716387</div>
                <div class="line-5">Web : www.man2ska.sch.id  Email : man2surakarta@kemenag.go.id</div>
            </td>
        </tr>
    </table>

    <div class="divider"></div>

    <div class="title">SURAT TUGAS</div>
    <div class="number">Nomor: {{ $letter['document_number'] }}</div>

    <table class="content-table">
        <tr>
            <td class="label">Menimbang</td>
            <td class="colon">:</td>
            <td class="content-cell">
                Bahwa sehubungan dengan pelaksanaan tugas dan fungsi organisasi di lingkungan Kementerian Agama,
                dipandang perlu membuat surat tugas dinas pada MAN 2 Surakarta;
            </td>
        </tr>
        <tr>
            <td class="label">Dasar</td>
            <td class="colon">:</td>
            <td class="content-cell">
                @foreach($letter['basis_items'] as $item)
                    <div class="list-item">{{ $item }}</div>
                @endforeach
            </td>
        </tr>
    </table>

    <div class="section-heading">MEMBERI TUGAS</div>

    <table class="content-table">
        <tr>
            <td class="label">Kepada</td>
            <td class="colon">:</td>
            <td class="content-cell">
                @foreach($letter['assignees'] as $index => $assignee)
                    <div class="assignee-block">
                        <table class="assignee-table">
                            <tr>
                                <td class="assignee-number">{{ $index + 1 }}.</td>
                                <td class="assignee-label">Nama</td>
                                <td>: {{ $assignee['name'] }}</td>
                            </tr>
                            <tr>
                                <td></td>
                                <td class="assignee-label">NIP</td>
                                <td>: {{ $assignee['nip'] !== '' ? $assignee['nip'] : '-' }}</td>
                            </tr>
                            <tr>
                                <td></td>
                                <td class="assignee-label">Pangkat</td>
                                <td>: {{ $assignee['rank'] !== '' ? $assignee['rank'] : '-' }}</td>
                            </tr>
                            <tr>
                                <td></td>
                                <td class="assignee-label">Jabatan</td>
                                <td>: {{ $assignee['position'] !== '' ? $assignee['position'] : '-' }}</td>
                            </tr>
                        </table>
                    </div>
                @endforeach
            </td>
        </tr>
        <tr>
            <td class="label">Untuk</td>
            <td class="colon">:</td>
            <td class="content-cell">
                @foreach($letter['instruction_points'] as $index => $point)
                    <div class="list-item">{{ $index + 1 }}. {{ $point }}</div>
                @endforeach
            </td>
        </tr>
    </table>

    <table class="sign-table">
        <tr>
            <td class="footer-note">
                <strong>Keterangan :</strong><br>
                Yang bersangkutan telah melaksanakan tugas dengan baik pada :<br><br>
                Tanggal : ____________________<br><br>
                Mengetahui<br><br><br><br>
                NIP. ____________________
            </td>
            <td class="signature-cell">
                {{ $letter['document_city_and_date'] }}<br>
                <strong>{{ $letter['signer_title'] }}</strong>

                <div class="signature-name">{{ $letter['signer_name'] }}</div>

                @if($letter['signer_nip'] !== '')
                    <div class="muted-space">NIP. {{ $letter['signer_nip'] }}</div>
                @endif
            </td>
        </tr>
    </table>
</body>
</html>
