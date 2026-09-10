<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
@page { size: 85.6mm 54mm landscape; margin: 0; }
* { box-sizing: border-box; margin:0; padding:0; }
html, body { margin:0; padding:0; background:#fff; }
body { font-family: 'DejaVu Sans', sans-serif; }

/* Each page = one card */
.page {
    width: 85.6mm;
    height: 54mm;
    overflow: hidden;
    page-break-after: always;
    page-break-inside: avoid;
    background: #fff;
}
.page:last-child { page-break-after: auto; }

/* Front card styling */
.card-front {
    width: 85.6mm;
    height: 54mm;
}
.hdr {
    background: #0d6efd;
    color: #fff;
    padding: 1.8mm 3mm;
}
.hdr-table { width: 100%; border-collapse: collapse; }
.hdr-table td { vertical-align: middle; }
.logo-box {
    width: 8.5mm; height: 8.5mm;
    background: #fff;
    border-radius: 1.4mm;
    text-align: center; overflow: hidden;
}
.logo-box img { width: 8.5mm; height: 8.5mm; object-fit: contain; display: block; }
.logo-text {
    width: 8.5mm; height: 8.5mm; line-height: 8.5mm;
    text-align: center; font-size: 4.8pt; font-weight: 800; color: #0d6efd;
}
.company-name {
    font-size: 6.5pt; font-weight: 800; letter-spacing: 0.35pt;
    text-transform: uppercase; line-height: 1.1; padding-left: 2mm;
}
.company-sub {
    font-size: 3.7pt; opacity: 0.93; padding-left: 2mm; line-height: 1.2;
}
.badge-id {
    background: #facc15; color: #1e293b;
    font-size: 3.7pt; font-weight: 800; letter-spacing: 0.6pt;
    padding: 1mm 2mm; border-radius: 0.9mm; text-align: center; white-space: nowrap;
}
.body { padding: 1.8mm 3mm 1.4mm 3mm; }
.body-table { width: 100%; border-collapse: collapse; }
.body-table td { vertical-align: top; }
.col-photo { width: 18.5mm; }
.photo {
    width: 18.5mm; height: 22mm;
    border-radius: 1.6mm; object-fit: cover;
    border: 0.45mm solid #0d6efd; display: block; background: #eef2ff;
}
.photo-ph {
    width: 18.5mm; height: 22mm; border-radius: 1.6mm;
    background: #eef2ff; border: 0.45mm solid #0d6efd;
    text-align: center; line-height: 22mm; font-size: 11pt; color: #94a3b8;
}
.col-info { width: 39mm; padding-left: 2.5mm; padding-right: 1.2mm; }
.emp-name {
    font-size: 6.9pt; font-weight: 800; color: #0f172a;
    text-transform: uppercase; line-height: 1.15; margin-bottom: 0.3mm;
    word-wrap: break-word; overflow-wrap: break-word;
}
.emp-pos {
    font-size: 4.3pt; font-weight: 700; color: #0d6efd;
    text-transform: uppercase; letter-spacing: 0.3pt; margin-bottom: 1mm; line-height: 1.2;
    word-wrap: break-word; overflow-wrap: break-word;
}
.info-table { width: 100%; border-collapse: collapse; }
.info-table td { padding: 0.45mm 0; vertical-align: top; line-height: 1.25; }
.lbl { font-size: 3.5pt; color: #64748b; font-weight: 700; text-transform: uppercase; letter-spacing: 0.2pt; width: 11mm; }
.val { font-size: 4.2pt; color: #1e293b; font-weight: 600; word-wrap: break-word; overflow-wrap: break-word; }
.nik { font-family: 'DejaVu Sans Mono', monospace; font-size: 4.5pt; letter-spacing: 0.35pt; background: #f1f5f9; border: 0.22mm solid #e2e8f0; padding: 0.4mm 1mm; border-radius: 0.8mm; }
.badge-status { display: inline-block; font-size: 3.4pt; font-weight: 800; letter-spacing: 0.3pt; padding: 0.6mm 1.4mm; border-radius: 0.8mm; text-transform: uppercase; }
.st-tetap { background: #dcfce7; color: #14532d; border: 0.22mm solid #86efac; }
.st-kontrak { background: #fef9c3; color: #713f12; border: 0.22mm solid #fde68a; }
.st-magang { background: #e0e7ff; color: #312e81; border: 0.22mm solid #c7d2fe; }
.st-probation { background: #fce7f3; color: #831843; border: 0.22mm solid #fbcfe8; }
.st-resigned { background: #fee2e2; color: #7f1d1d; border: 0.22mm solid #fecaca; }
.col-qr { width: 16.5mm; text-align: center; }
.qr-box { width: 14.5mm; height: 14.5mm; background: #fff; border: 0.3mm solid #e2e8f0; border-radius: 1.2mm; padding: 0.7mm; margin: 0 auto; }
.qr-box img { width: 100%; height: 100%; display: block; object-fit: contain; }
.qr-cap { font-size: 2.9pt; color: #64748b; font-weight: 700; letter-spacing: 0.35pt; text-transform: uppercase; margin-top: 0.7mm; }
.qr-nik { font-size: 3pt; font-family: monospace; color: #475569; letter-spacing: 0.5pt; margin-top: 0.5mm; }
.foot { background: #f8fafc; border-top: 0.28mm solid #e2e8f0; padding: 1.2mm 3mm; }
.foot-table { width: 100%; border-collapse: collapse; }
.foot-table td { vertical-align: middle; }
.foot-left { font-size: 3.3pt; color: #475569; line-height: 1.3; }
.foot-left strong { color: #0f172a; }
.foot-right { text-align: right; }
.sig-line { width: 19mm; border-top: 0.32mm solid #334155; margin-left: auto; text-align: center; font-size: 3pt; color: #334155; padding-top: 0.6mm; }

/* Back */
.card-back { width: 85.6mm; height: 54mm; }
.back-hdr { background: #0f172a; color: #fff; text-align: center; padding: 2.2mm 3mm 1.8mm 3mm; }
.back-hdr h3 { font-size: 4.7pt; letter-spacing: 0.8pt; text-transform: uppercase; font-weight: 800; }
.back-hdr p { font-size: 3.4pt; opacity: 0.85; margin-top: 0.4mm; }
.back-body { padding: 2mm 3mm; }
.back-body h4 { font-size: 4.1pt; color: #0f172a; font-weight: 800; text-transform: uppercase; letter-spacing: 0.25pt; border-bottom: 0.25mm solid #e2e8f0; padding-bottom: 0.8mm; margin-bottom: 1mm; }
.back-body ul { margin-left: 3.2mm; margin-bottom: 1.4mm; }
.back-body li { font-size: 3.8pt; color: #334155; line-height: 1.4; margin-bottom: 0.35mm; }
.addr-box { background: #f8fafc; border: 0.25mm solid #e2e8f0; border-radius: 1.1mm; padding: 1.3mm 1.8mm; font-size: 3.6pt; color: #334155; line-height: 1.35; }
.back-foot { background: #fef2f2; border-top: 0.25mm solid #fecaca; text-align: center; padding: 1.2mm 3mm; font-size: 3.1pt; color: #991b1b; font-weight: 700; }
</style>
</head>
<body>

<div class="page">
<div class="card-front">
    <div class="hdr">
        <table class="hdr-table">
            <tr>
                <td style="width:8.5mm;"><div class="logo-box">@if(!empty($company) && $company->logoBase64())<img src="{{ $company->logoBase64() }}">@else<div class="logo-text">{{ strtoupper(substr($company->name ?? 'A',0,2)) }}</div>@endif</div></td>
                <td><div class="company-name">{{ $company->name ?? 'PT. ABSENSIKU' }}</div><div class="company-sub">{{ Str::limit($company->address ?? 'Jl. Contoh No.1, Jakarta', 55) }}</div></td>
                <td style="width:15mm; text-align:right;"><div class="badge-id">ID CARD</div></td>
            </tr>
        </table>
    </div>
    <div class="body">
        <table class="body-table">
            <tr>
                <td class="col-photo">@if(!empty($photoBase64))<img src="{{ $photoBase64 }}" class="photo">@else<div class="photo-ph">●</div>@endif</td>
                <td class="col-info">
                    <div class="emp-name">{{ $employee->user->name ?? '-' }}</div>
                    <div class="emp-pos">{{ $employee->position->name ?? ($employee->user->role ?? 'Staff') }}</div>
                    <table class="info-table">
                        <tr><td class="lbl">NIK</td><td class="val"><span class="nik">{{ $employee->user->nik ?? '-' }}</span></td></tr>
                        <tr><td class="lbl">Departemen</td><td class="val">{{ $employee->department->name ?? '-' }}</td></tr>
                        <tr><td class="lbl">Jabatan</td><td class="val">{{ $employee->position->name ?? '-' }}</td></tr>
                    </table>
                </td>
                <td class="col-qr"><div class="qr-box">@if(!empty($qrBase64))<img src="{{ $qrBase64 }}">@endif</div><div class="qr-cap">Scan Verifikasi</div><div class="qr-nik">{{ $employee->user->nik ?? '' }}</div></td>
            </tr>
        </table>
    </div>
    <div class="foot">
        <table class="foot-table">
            <tr>
                <td class="foot-left"><div>Bergabung: <strong>{{ $employee->join_date?->format('d M Y') ?? '-' }}</strong> • ID: {{ str_pad($employee->id, 5, '0', STR_PAD_LEFT) }}</div><div>{{ $employee->officeLocation->name ?? 'Kantor Pusat' }}</div></td>
                <td class="foot-right"><div class="sig-line">HRD Manager</div></td>
            </tr>
        </table>
    </div>
</div>
</div>

<div class="page">
<div class="card-back">
    <div class="back-hdr"><h3>Peraturan Penggunaan</h3><p>Kartu milik {{ $company->name ?? 'Perusahaan' }} • Wajib dibawa saat bekerja</p></div>
    <div class="back-body">
        <h4>Ketentuan</h4>
        <ul>
            <li>Tidak boleh dipinjamkan kepada orang lain</li>
            <li>Wajib dikembalikan jika resign / hilang segera lapor HRD</li>
            <li>Penyalahgunaan menjadi tanggung jawab pemegang kartu</li>
        </ul>
        <h4>Alamat Kantor</h4>
        <div class="addr-box"><strong>{{ $company->name ?? '-' }}</strong><br>{{ $company->address ?? '-' }}<br>@if($company->phone) Tel: {{ $company->phone }} @endif @if($company->email) • {{ $company->email }} @endif @if($company->website)<br>{{ $company->website }}@endif</div>
    </div>
    <div class="back-foot">Jika menemukan kartu harap kembalikan ke alamat di atas • Dicetak {{ now()->format('d/m/Y H:i') }} • Bukan untuk tujuan lain</div>
</div>
</div>

</body>
</html>
