<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
@page { size: 85.6mm 54mm; margin: 0; }
* { box-sizing: border-box; margin:0; padding:0; }
body { font-family: 'DejaVu Sans', sans-serif; margin:0; padding:0; background:#fff; }

/* ===== FRONT ===== */
.card {
    width: 85.6mm;
    height: 54mm;
    background: #ffffff;
    border: 0.35mm solid #dbeafe;
    overflow: hidden;
}
/* Header solid blue - DomPDF safe (no gradient) */
.hdr {
    background: #0d6efd;
    color: #fff;
    height: 12.5mm;
    padding: 2mm 3mm;
}
.hdr-table { width: 100%; border-collapse: collapse; }
.hdr-table td { vertical-align: middle; }
.logo-box {
    width: 9mm; height: 9mm;
    background: #fff;
    border-radius: 1.5mm;
    text-align: center;
    vertical-align: middle;
    overflow: hidden;
}
.logo-box img { width: 9mm; height: 9mm; object-fit: contain; display: block; }
.logo-text {
    width: 9mm; height: 9mm; line-height: 9mm;
    text-align: center; font-size: 5pt; font-weight: 800; color: #0d6efd;
}
.company-name {
    font-size: 6.8pt; font-weight: 800; letter-spacing: 0.4pt;
    text-transform: uppercase; line-height: 1.1;
    padding-left: 2.2mm;
}
.company-sub {
    font-size: 4pt; opacity: 0.92; padding-left: 2.2mm; line-height: 1.2; font-weight: 400;
}
.badge-id {
    background: #facc15; color: #1e293b;
    font-size: 4pt; font-weight: 800; letter-spacing: 0.7pt;
    padding: 1.2mm 2.2mm; border-radius: 1mm;
    text-align: center; white-space: nowrap;
}

/* Body */
.body { padding: 2.2mm 3mm 1.8mm 3mm; height: 34.5mm; }
.body-table { width: 100%; border-collapse: collapse; }
.body-table td { vertical-align: top; }
.col-photo { width: 19mm; }
.photo {
    width: 19mm; height: 23mm;
    border-radius: 1.8mm;
    object-fit: cover;
    border: 0.5mm solid #0d6efd;
    display: block; background: #eef2ff;
}
.photo-ph {
    width: 19mm; height: 23mm;
    border-radius: 1.8mm;
    background: #eef2ff; border: 0.5mm solid #0d6efd;
    text-align: center; line-height: 23mm;
    font-size: 12pt; color: #94a3b8;
}
.col-info { width: 40mm; padding-left: 2.8mm; padding-right: 1.5mm; }
.emp-name {
    font-size: 7.2pt; font-weight: 800; color: #0f172a;
    text-transform: uppercase; line-height: 1.15;
    margin-bottom: 0.4mm;
    word-wrap: break-word;
}
.emp-pos {
    font-size: 4.6pt; font-weight: 700; color: #0d6efd;
    text-transform: uppercase; letter-spacing: 0.3pt;
    margin-bottom: 1.2mm; line-height: 1.2;
}
.info-table { width: 100%; border-collapse: collapse; }
.info-table td { padding: 0.55mm 0; vertical-align: top; line-height: 1.25; }
.lbl {
    font-size: 3.7pt; color: #64748b; font-weight: 700;
    text-transform: uppercase; letter-spacing: 0.25pt;
    width: 11.5mm;
}
.val {
    font-size: 4.4pt; color: #1e293b; font-weight: 600;
    word-wrap: break-word;
}
.nik {
    font-family: 'DejaVu Sans Mono', monospace;
    font-size: 4.8pt; letter-spacing: 0.4pt;
    background: #f1f5f9; border: 0.25mm solid #e2e8f0;
    padding: 0.5mm 1.2mm; border-radius: 0.9mm;
}
.badge-status {
    display: inline-block;
    font-size: 3.6pt; font-weight: 800; letter-spacing: 0.35pt;
    padding: 0.7mm 1.6mm; border-radius: 0.9mm;
    text-transform: uppercase; line-height: 1;
}
.st-tetap { background: #dcfce7; color: #14532d; border: 0.25mm solid #86efac; }
.st-kontrak { background: #fef9c3; color: #713f12; border: 0.25mm solid #fde68a; }
.st-magang { background: #e0e7ff; color: #312e81; border: 0.25mm solid #c7d2fe; }
.st-probation { background: #fce7f3; color: #831843; border: 0.25mm solid #fbcfe8; }
.st-resigned { background: #fee2e2; color: #7f1d1d; border: 0.25mm solid #fecaca; }

.col-qr { width: 17mm; text-align: center; }
.qr-box {
    width: 15.5mm; height: 15.5mm;
    background: #fff; border: 0.35mm solid #e2e8f0;
    border-radius: 1.4mm; padding: 1mm;
    margin: 0 auto;
}
.qr-box img { width: 100%; height: 100%; display: block; object-fit: contain; }
.qr-cap {
    font-size: 3.1pt; color: #64748b; font-weight: 700;
    letter-spacing: 0.4pt; text-transform: uppercase;
    margin-top: 0.9mm; line-height: 1;
}
.qr-nik {
    font-size: 3.2pt; font-family: monospace; color: #475569;
    letter-spacing: 0.6pt; margin-top: 0.7mm;
}

/* Footer */
.foot {
    height: 7mm;
    background: #f8fafc; border-top: 0.3mm solid #e2e8f0;
    padding: 1.4mm 3mm;
}
.foot-table { width: 100%; border-collapse: collapse; }
.foot-table td { vertical-align: middle; }
.foot-left { font-size: 3.5pt; color: #475569; line-height: 1.35; }
.foot-left strong { color: #0f172a; font-weight: 700; }
.foot-right { text-align: right; }
.sig-line {
    width: 20mm; border-top: 0.35mm solid #334155;
    margin-left: auto; text-align: center;
    font-size: 3.2pt; color: #334155; padding-top: 0.7mm;
    letter-spacing: 0.25pt;
}

/* ===== BACK ===== */
.page-break { page-break-after: always; }
.back {
    width: 85.6mm; height: 54mm;
    background: #fff; border: 0.35mm solid #dbeafe;
    overflow: hidden;
}
.back-hdr {
    background: #0f172a; color: #fff;
    text-align: center; padding: 2.8mm 3mm 2.2mm 3mm;
}
.back-hdr h3 { font-size: 5pt; letter-spacing: 0.9pt; text-transform: uppercase; font-weight: 800; }
.back-hdr p { font-size: 3.6pt; opacity: 0.85; margin-top: 0.6mm; }
.back-body { padding: 2.5mm 3mm; }
.back-body h4 {
    font-size: 4.4pt; color: #0f172a; font-weight: 800;
    text-transform: uppercase; letter-spacing: 0.3pt;
    border-bottom: 0.3mm solid #e2e8f0; padding-bottom: 1mm; margin-bottom: 1.2mm;
}
.back-body ul { margin-left: 3.5mm; margin-bottom: 1.8mm; }
.back-body li { font-size: 4pt; color: #334155; line-height: 1.45; margin-bottom: 0.5mm; }
.addr-box {
    background: #f8fafc; border: 0.3mm solid #e2e8f0;
    border-radius: 1.3mm; padding: 1.6mm 2mm;
    font-size: 3.8pt; color: #334155; line-height: 1.4;
}
.back-foot {
    background: #fef2f2; border-top: 0.3mm solid #fecaca;
    text-align: center; padding: 1.4mm 3mm;
    font-size: 3.3pt; color: #991b1b; font-weight: 700;
}
</style>
</head>
<body>

{{-- FRONT --}}
<div class="card">
    <div class="hdr">
        <table class="hdr-table">
            <tr>
                <td style="width:9mm;">
                    <div class="logo-box">
                        @if(!empty($company) && $company->logoBase64())
                            <img src="{{ $company->logoBase64() }}">
                        @else
                            <div class="logo-text">{{ strtoupper(substr($company->name ?? 'A',0,2)) }}</div>
                        @endif
                    </div>
                </td>
                <td>
                    <div class="company-name">{{ $company->name ?? 'PT. ABSENSIKU' }}</div>
                    <div class="company-sub">{{ Str::limit($company->address ?? 'Jl. Contoh No.1, Jakarta', 58) }}</div>
                </td>
                <td style="width:16mm; text-align:right;">
                    <div class="badge-id">ID CARD</div>
                </td>
            </tr>
        </table>
    </div>

    <div class="body">
        <table class="body-table">
            <tr>
                <td class="col-photo">
                    @if(!empty($photoBase64))
                        <img src="{{ $photoBase64 }}" class="photo">
                    @else
                        <div class="photo-ph">●</div>
                    @endif
                </td>
                <td class="col-info">
                    <div class="emp-name">{{ $employee->user->name ?? '-' }}</div>
                    <div class="emp-pos">{{ $employee->position->name ?? ($employee->user->role ?? 'Staff') }}</div>
                    <table class="info-table">
                        <tr><td class="lbl">NIK</td><td class="val"><span class="nik">{{ $employee->user->nik ?? '-' }}</span></td></tr>
                        <tr><td class="lbl">Departemen</td><td class="val">{{ $employee->department->name ?? '-' }}</td></tr>
                        <tr><td class="lbl">Jabatan</td><td class="val">{{ Str::limit($employee->position->name ?? '-', 18) }}</td></tr>
                        <tr><td class="lbl">Status</td>
                            <td class="val">
                                @php $st = strtolower($employee->employment_status ?? 'kontrak'); @endphp
                                <span class="badge-status st-{{ $st }}">{{ ucfirst($st) }}</span>
                            </td>
                        </tr>
                    </table>
                </td>
                <td class="col-qr">
                    <div class="qr-box">
                        @if(!empty($qrBase64))
                            <img src="{{ $qrBase64 }}">
                        @endif
                    </div>
                    <div class="qr-cap">Scan Verifikasi</div>
                    <div class="qr-nik">{{ $employee->user->nik ?? '' }}</div>
                </td>
            </tr>
        </table>
    </div>

    <div class="foot">
        <table class="foot-table">
            <tr>
                <td class="foot-left">
                    <div>Bergabung: <strong>{{ $employee->join_date?->format('d M Y') ?? '-' }}</strong> • ID: {{ str_pad($employee->id, 5, '0', STR_PAD_LEFT) }}</div>
                    @if($employee->contract_end_date)
                        <div>Berlaku s/d: <strong>{{ $employee->contract_end_date->format('d M Y') }}</strong></div>
                    @else
                        <div>{{ $employee->officeLocation->name ?? 'Kantor Pusat' }} @if($employee->shift) • Shift {{ $employee->shift->name }} @endif</div>
                    @endif
                </td>
                <td class="foot-right">
                    <div class="sig-line">HRD Manager</div>
                </td>
            </tr>
        </table>
    </div>
</div>

{{-- BACK --}}
<div class="page-break"></div>
<div class="back">
    <div class="back-hdr">
        <h3>Peraturan Penggunaan</h3>
        <p>Kartu milik {{ $company->name ?? 'Perusahaan' }} • Wajib dibawa saat bekerja</p>
    </div>
    <div class="back-body">
        <h4>Ketentuan</h4>
        <ul>
            <li>Tidak boleh dipinjamkan kepada orang lain</li>
            <li>Wajib dikembalikan jika resign / hilang segera lapor HRD</li>
            <li>Penyalahgunaan menjadi tanggung jawab pemegang kartu</li>
        </ul>
        <h4>Alamat Kantor</h4>
        <div class="addr-box">
            <strong>{{ $company->name ?? '-' }}</strong><br>
            {{ $company->address ?? '-' }}<br>
            @if($company->phone) Tel: {{ $company->phone }} @endif
            @if($company->email) • {{ $company->email }} @endif
            @if($company->website) <br>{{ $company->website }} @endif
        </div>
    </div>
    <div class="back-foot">
        Jika menemukan kartu ini harap kembalikan ke alamat di atas • Dicetak {{ now()->format('d/m/Y H:i') }} • Bukan untuk tujuan lain
    </div>
</div>

</body>
</html>
