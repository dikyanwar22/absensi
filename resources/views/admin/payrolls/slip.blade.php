<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
body{font-family: DejaVu Sans, sans-serif; font-size:12px; color:#333;}
.header{text-align:center; border-bottom:2px solid #0d6efd; padding-bottom:10px; margin-bottom:15px;}
.header h2{margin:0; color:#0d6efd;}
.header small{color:#666;}
.info table{width:100%; margin-bottom:15px;}
.info td{padding:2px 5px;}
.table{width:100%; border-collapse:collapse; margin-bottom:15px;}
.table th,.table td{border:1px solid #ddd; padding:6px; text-align:left;}
.table th{background:#0d6efd; color:#fff;}
.text-right{text-align:right;}
.total{background:#e3f2fd; font-weight:bold;}
.footer{text-align:center; margin-top:30px; font-size:10px; color:#888;}
</style>
</head>
<body>
<div class="header">
    <h2>SLIP GAJI KARYAWAN</h2>
    <small>Periode {{ $detail->payroll->period }} ({{ $detail->payroll->start_date }} - {{ $detail->payroll->end_date }})</small>
</div>

<div class="info">
<table>
<tr><td width="20%">NIK</td><td width="30%">: {{ $detail->user->nik }}</td><td width="20%">Departemen</td><td>: {{ $detail->user->employee->department->name ?? '-' }}</td></tr>
<tr><td>Nama</td><td>: {{ $detail->user->name }}</td><td>Jabatan</td><td>: {{ $detail->user->employee->position->name ?? '-' }}</td></tr>
<tr><td>Status</td><td>: {{ ucfirst($detail->user->employee->employment_status ?? '-') }}</td><td>Rekening</td><td>: {{ $detail->user->employee->bank_name ?? '-' }} {{ $detail->user->employee->bank_account ?? '-' }}</td></tr>
</table>
</div>

<table class="table">
<tr><th colspan="2">Pendapatan</th><th>Rp</th><th colspan="2">Potongan</th><th>Rp</th></tr>
<tr><td colspan="2">Gaji Pokok</td><td class="text-right">{{ number_format($detail->basic_salary,0,',','.') }}</td><td colspan="2">Terlambat ({{ $detail->attendance_summary['late_minutes'] ?? 0 }} menit)</td><td class="text-right">{{ number_format($detail->deductions['terlambat'] ?? 0,0,',','.') }}</td></tr>
<tr><td colspan="2">Tunjangan Makan</td><td class="text-right">{{ number_format($detail->allowances['makan'] ?? 0,0,',','.') }}</td><td colspan="2">Alpha ({{ $detail->attendance_summary['alpha'] ?? 0 }} hari)</td><td class="text-right">{{ number_format($detail->deductions['alpha'] ?? 0,0,',','.') }}</td></tr>
<tr><td colspan="2">Tunjangan Transport</td><td class="text-right">{{ number_format($detail->allowances['transport'] ?? 0,0,',','.') }}</td><td colspan="2">BPJS Kesehatan 1%</td><td class="text-right">{{ number_format($detail->deductions['bpjs_kes'] ?? 0,0,',','.') }}</td></tr>
<tr><td colspan="2">Tunjangan Jabatan</td><td class="text-right">{{ number_format($detail->allowances['jabatan'] ?? 0,0,',','.') }}</td><td colspan="2">BPJS TK 2%</td><td class="text-right">{{ number_format($detail->deductions['bpjs_tk'] ?? 0,0,',','.') }}</td></tr>
<tr><td colspan="2">Lembur ({{ $detail->attendance_summary['overtime_hours'] ?? 0 }} jam)</td><td class="text-right">{{ number_format($detail->overtime_pay,0,',','.') }}</td><td colspan="2">Lain-lain</td><td class="text-right">0</td></tr>
<tr><td colspan="2">Bonus/THR</td><td class="text-right">{{ number_format(($detail->bonus+$detail->thr),0,',','.') }}</td><td colspan="2"></td><td></td></tr>
<tr class="total"><td colspan="2">Total Pendapatan (Gross)</td><td class="text-right">{{ number_format($detail->gross_salary,0,',','.') }}</td><td colspan="2">Total Potongan</td><td class="text-right">{{ number_format($detail->total_deduction,0,',','.') }}</td></tr>
</table>

<table class="table">
<tr class="total"><td width="70%">GAJI BERSIH (NET SALARY)</td><td class="text-right">Rp {{ number_format($detail->net_salary,0,',','.') }}</td></tr>
</table>

<div class="info">
<small><strong>Ringkasan Kehadiran:</strong> Hadir {{ $detail->attendance_summary['hadir'] ?? 0 }} hari, Terlambat {{ $detail->attendance_summary['terlambat'] ?? 0 }} ({{ $detail->attendance_summary['late_minutes'] ?? 0 }} menit), Alpha {{ $detail->attendance_summary['alpha'] ?? 0 }}, Lembur {{ $detail->attendance_summary['overtime_hours'] ?? 0 }} jam</small>
</div>

<div class="footer">
    <p>Ini adalah slip gaji resmi yang dicetak sistem. HRD - {{ now()->format('d/m/Y H:i') }}</p>
    <p>AbsensiKu - Laravel 10 + AdminLTE 3</p>
</div>
</body>
</html>
