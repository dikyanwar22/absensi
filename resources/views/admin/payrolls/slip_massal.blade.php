<!DOCTYPE html>
<html>
<head><meta charset="utf-8"><style>body{font-family:DejaVu Sans,sans-serif;font-size:10px;} .header{border-bottom:2px solid #0d6efd;padding-bottom:8px;margin-bottom:12px;} .header-table{width:100%;} .header-table td{vertical-align:middle;} .logo{max-height:50px;max-width:100px;object-fit:contain;} .company-name{margin:0;color:#0d6efd;font-size:14px;font-weight:bold;} .company-meta{color:#666;font-size:9px;} table{width:100%;border-collapse:collapse;} th,td{border:1px solid #ddd;padding:4px;} th{background:#0d6efd;color:#fff;} h2{text-align:center;color:#0d6efd;margin:8px 0 0 0;font-size:13px;}</style></head>
<body>
<div class="header">
    <table class="header-table">
        <tr>
            <td width="12%" align="center">
                @if(!empty($company) && $company->logoBase64())
                    <img src="{{ $company->logoBase64() }}" class="logo">
                @endif
            </td>
            <td width="88%">
                <div class="company-name">{{ $company->name ?? 'PT. AbsensiKu' }}</div>
                <div class="company-meta">{{ $company->address ?? '' }} @if(!empty($company->phone)) | {{ $company->phone }} @endif @if(!empty($company->email)) | {{ $company->email }} @endif</div>
            </td>
        </tr>
    </table>
    <h2>Rekap Gaji Massal - Periode {{ $payroll->period }}</h2>
    <div style="text-align:center;color:#666;font-size:9px;">{{ $payroll->start_date }} s/d {{ $payroll->end_date }} | Dicetak {{ now()->format('d/m/Y H:i') }}</div>
</div>
<table>
<thead><tr><th>NIK</th><th>Nama</th><th>Dept</th><th>Pokok</th><th>Tunjangan</th><th>Lembur</th><th>Potongan</th><th>Bersih</th></tr></thead>
<tbody>
@foreach($payroll->details as $d)
<tr><td>{{ $d->user->nik }}</td><td>{{ $d->user->name }}</td><td>{{ $d->user->employee->department->name ?? '-' }}</td><td align="right">{{ number_format($d->basic_salary,0,',','.') }}</td><td align="right">{{ number_format(array_sum($d->allowances??[]),0,',','.') }}</td><td align="right">{{ number_format($d->overtime_pay,0,',','.') }}</td><td align="right">{{ number_format($d->total_deduction,0,',','.') }}</td><td align="right"><strong>{{ number_format($d->net_salary,0,',','.') }}</strong></td></tr>
@endforeach
</tbody>
<tfoot><tr><th colspan="7" align="right">Total</th><th align="right">Rp {{ number_format($payroll->total_amount,0,',','.') }}</th></tr></tfoot>
</table>
<p style="text-align:center;font-size:9px;color:#888;margin-top:10px;">Dokumen resmi {{ $company->name ?? '' }} - HRD</p>
</body>
</html>
