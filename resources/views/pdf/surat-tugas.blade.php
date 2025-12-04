<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Surat Tugas</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: DejaVu Sans, Arial, Helvetica, sans-serif; font-size: 12px; color: #111; }
        h1 { font-size: 18px; text-align: center; margin: 0 0 4px; text-transform: uppercase; }
        h2 { font-size: 14px; text-align: center; margin: 0 0 18px; }
        .small { font-size: 11px; }
        .muted { color: #555; }
        .mb-1 { margin-bottom: 6px; }
        .mb-2 { margin-bottom: 12px; }
        .mb-3 { margin-bottom: 18px; }
        .section { margin-top: 10px; }
        table { width: 100%; border-collapse: collapse; }
        td { padding: 6px 8px; vertical-align: top; }
        .table-bordered td { border: 1px solid #000; }
        .right { text-align: right; }
        .center { text-align: center; }
    </style>
    @php
        use Illuminate\Support\Carbon;
        $fmtDate = function ($d) { return $d ? Carbon::parse($d)->format('d/m/Y') : '-'; };
        $client = $client ?? optional(optional($project ?? null)->client);
    @endphp
    </head>
<body>
    <h1>Surat Tugas</h1>
    <h2 class="muted">Nomor: {{ $workOrder->no_wo ?? '—' }}</h2>

    <div class="section mb-2">
        <table>
            <tr>
                <td style="width: 160px;">Proyek</td>
                <td>: {{ $project->nama ?? '—' }}</td>
            </tr>
            <tr>
                <td>Client</td>
                <td>: {{ $client->nama ?? '—' }}</td>
            </tr>
            <tr>
                <td>Lokasi</td>
                <td>: {{ $project->lokasi_nama ?? '—' }}</td>
            </tr>
        </table>
    </div>

    <div class="section mb-2">
        <table class="table-bordered">
            <tr>
                <td style="width: 160px;">Nama Operator</td>
                <td>{{ $operator->nama ?? '—' }}</td>
            </tr>
            <tr>
                <td>Peralatan</td>
                <td>{{ $heavyEquipment->nama ?? '—' }} @if(!empty($heavyEquipment?->nopol)) ({{ $heavyEquipment->nopol }}) @endif</td>
            </tr>
            <tr>
                <td>Tanggal Mulai</td>
                <td>{{ $fmtDate($assignment['tgl_mulai'] ?? null) }}</td>
            </tr>
            <tr>
                <td>Tanggal Selesai</td>
                <td>{{ $fmtDate($assignment['tgl_selesai'] ?? null) }}</td>
            </tr>
            <tr>
                <td>Status</td>
                <td>{{ $assignment['status'] ?? ($assignment['status'] ?? 'AKTIF') }}</td>
            </tr>
        </table>
    </div>

    <p class="mb-3">
        Dengan ini menugaskan operator tersebut untuk melaksanakan pekerjaan sesuai Surat Perintah Tugas di atas
        pada proyek terkait, dengan memperhatikan prosedur keselamatan dan ketentuan yang berlaku.
    </p>

    <table>
        <tr>
            <td style="width: 40%"></td>
            <td style="width: 60%" class="center">
                <div>Surabaya, {{ now()->format('d/m/Y') }}</div>
                <div class="mb-3">Mengetahui,</div>
                <div style="height: 64px"></div>
                <div><strong>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;__________________________</strong></div>
                <div class="small muted">(Nama & Jabatan)</div>
            </td>
        </tr>
    </table>
</body>
</html>

