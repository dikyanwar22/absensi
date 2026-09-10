<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
body{font-family: DejaVu Sans, sans-serif; font-size:12px; color:#333;}
.header{border-bottom:2px solid #0d6efd; padding-bottom:10px; margin-bottom:15px;}
.header-table{width:100%;}
.header-table td{vertical-align:middle;}
.logo{max-height:60px; max-width:120px; object-fit:contain;}
.company-name{margin:0; color:#0d6efd; font-size:16px; font-weight:bold;}
.company-meta{color:#666; font-size:10px; line-height:1.4;}
.header-title{text-align:center; margin-top:12px; border-top:1px solid #eee; padding-top:8px;}
.header-title h2{margin:0; color:#0d6efd; font-size:14px;}
.header-title small{color:#666;}
.info table{width:100%; margin-bottom:15px;}
.info td{padding:2px 5px;}
.table{width:100%; border-collapse:collapse; margin-bottom:15px;}
.table th,.table td{border:1px solid #ddd; padding:6px; text-align:left;}
.table th{background:#0d6efd; color:#fff;}
.text-right{text-align:right;}
.text-center{text-align:center;}
.total{background:#e3f2fd; font-weight:bold;}
.footer{text-align:center; margin-top:30px; font-size:10px; color:#888;}
</style>
</head>
<body>
{{-- HEADER PERUSAHAAN: Logo + Nama + Alamat --}}
<div class="header">
    <table class="header-table">
        <tr>
            <td width="15%" class="text-center">
                @if(!empty($company) && $company->logoBase64())
                    <img src="{{ $company->logoBase64() }}" class="logo">
                @else
                    <div style="width:60px;height:60px;background:#0d6efd;color:#fff;line-height:60px;text-align:center;border-radius:4px;font-size:10px;">LOGO</div>
                @endif
            </td>
            <td width="85%">
                <div class="company-name">{{ $company->name ?? 'PT. AbsensiKu' }}</div>
                <div class="company-meta">
                    {{ $company->address ?? 'Jl. Contoh No.1, Jakarta' }}
                    @if(!empty($company->phone) || !empty($company->email))
                        <br>{{ $company->phone ?? '' }} {{ !empty($company->phone) && !empty($company->email) ? ' | ' : '' }} {{ $company->email ?? '' }} {{ !empty($company->website) ? ' | '.$company->website : '' }}
                    @endif
                </div>
            </td>
        </tr>
    </table>
    <div class="header-title">
        <h2>SLIP GAJI KARYAWAN</h2>
        <small>Periode {{ $detail->payroll->period }} ({{ $detail->payroll->start_date }} - {{ $detail->payroll->end_date }})</small>
    </div>
</div>

<div class="info">
<table>
<tr><td width="20%">NIK</td><td width="30%">: {{ $detail->user->nik }}</td><td width="20%">Departemen</td><td>: {{ $detail->user->employee->department->name ?? '-' }}</td></tr>
<tr><td>Nama</td><td>: {{ $detail->user->name }}</td><td>Jabatan</td><td>: {{ $detail->user->employee->position->name ?? '-' }}</td></tr>
<tr><td>Status</td><td>: {{ ucfirst($detail->user->employee->employment_status ?? '-') }}</td><td>Rekening</td><td>: {{ $detail->user->employee->bank_name ?? '-' }} {{ $detail->user->employee->bank_account ?? '-' }}</td></tr>
</table>
</div>

@php
    $systemLabels = [
        'terlambat' => 'Terlambat ('.($detail->attendance_summary['late_minutes'] ?? 0).' menit)',
        'alpha' => 'Alpha ('.($detail->attendance_summary['alpha'] ?? 0).' hari)',
        'bpjs_kes' => 'BPJS Kesehatan 1%',
        'bpjs_tk' => 'BPJS TK 2%',
        'lain' => 'Lain-lain',
    ];
    $deductions = $detail->deductions ?? [];
    $systemDeds = [];
    $dynamicDeds = [];
    foreach($deductions as $k=>$v){
        if(array_key_exists($k, $systemLabels)) $systemDeds[$k]=$v;
        else $dynamicDeds[$k]=$v;
    }
    $pendapatanRows = [
        'Gaji Pokok' => $detail->basic_salary,
        'Tunjangan Makan' => $detail->allowances['makan'] ?? 0,
        'Tunjangan Transport' => $detail->allowances['transport'] ?? 0,
        'Tunjangan Jabatan' => $detail->allowances['jabatan'] ?? 0,
        'Lembur ('.($detail->attendance_summary['overtime_hours'] ?? 0).' jam)' => $detail->overtime_pay,
        'Bonus/THR' => ($detail->bonus+$detail->thr),
    ];
    $maxRows = max(count($pendapatanRows), count($systemDeds)+count($dynamicDeds));
@endphp

<table class="table">
<tr><th colspan="2">Pendapatan</th><th>Rp</th><th colspan="2">Potongan</th><th>Rp</th></tr>
@for($i=0;$i<$maxRows;$i++)
@php
    $pKeys = array_keys($pendapatanRows);
    $pVals = array_values($pendapatanRows);
    $allDedKeys = array_merge(array_keys($systemDeds), array_keys($dynamicDeds));
    $allDedVals = array_merge(array_values($systemDeds), array_values($dynamicDeds));
    $pKey = $pKeys[$i] ?? '';
    $pVal = $pVals[$i] ?? null;
    $dKey = $allDedKeys[$i] ?? '';
    $dVal = $allDedVals[$i] ?? null;
    $dLabel = $dKey ? ($systemLabels[$dKey] ?? $dKey) : '';
@endphp
<tr>
    <td colspan="2">{{ $pKey }}</td>
    <td class="text-right">{{ $pVal !== null ? number_format($pVal,0,',','.') : '' }}</td>
    <td colspan="2">{{ $dLabel }}</td>
    <td class="text-right">{{ $dVal !== null ? number_format($dVal,0,',','.') : '' }}</td>
</tr>
@endfor
<tr class="total"><td colspan="2">Total Pendapatan (Gross)</td><td class="text-right">{{ number_format($detail->gross_salary,0,',','.') }}</td><td colspan="2">Total Potongan</td><td class="text-right">{{ number_format($detail->total_deduction,0,',','.') }}</td></tr>
</table>

<table class="table">
<tr class="total"><td width="70%">GAJI BERSIH (NET SALARY)</td><td class="text-right">Rp {{ number_format($detail->net_salary,0,',','.') }}</td></tr>
</table>

@if(!empty($dynamicDeds))
<div class="info">
<small><strong>Rincian Potongan Dinamis (Input HRD):</strong> {{ implode(', ', array_map(fn($k,$v)=>"$k: Rp ".number_format($v,0,',','.'), array_keys($dynamicDeds), $dynamicDeds)) }}</small>
</div>
@endif

<div class="info">
<small><strong>Ringkasan Kehadiran:</strong> Hadir {{ $detail->attendance_summary['hadir'] ?? 0 }} hari, Terlambat {{ $detail->attendance_summary['terlambat'] ?? 0 }} ({{ $detail->attendance_summary['late_minutes'] ?? 0 }} menit), Alpha {{ $detail->attendance_summary['alpha'] ?? 0 }}, Lembur {{ $detail->attendance_summary['overtime_hours'] ?? 0 }} jam</small>
@if($detail->notes)<br><small><strong>Catatan:</strong> {{ $detail->notes }}</small>@endif
</div>

<div class="footer">
    <p>Ini adalah slip gaji resmi {{ $company->name ?? 'Perusahaan' }} yang dicetak sistem. HRD - {{ now()->format('d/m/Y H:i') }}</p>
    <p>AbsensiKu - Laravel 10 + AdminLTE 3</p>
</div>
</body>
</html>
