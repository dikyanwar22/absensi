<!DOCTYPE html>
<html>
<head><meta charset="utf-8"><style>body{font-family:DejaVu Sans,sans-serif;font-size:10px;} table{width:100%;border-collapse:collapse;} th,td{border:1px solid #ddd;padding:4px;} th{background:#0d6efd;color:#fff;} h2{text-align:center;color:#0d6efd;}</style></head>
<body>
<h2>Rekap Gaji Massal - Periode {{ $payroll->period }}</h2>
<table>
<thead><tr><th>NIK</th><th>Nama</th><th>Dept</th><th>Pokok</th><th>Tunjangan</th><th>Lembur</th><th>Potongan</th><th>Bersih</th></tr></thead>
<tbody>
@foreach($payroll->details as $d)
<tr><td>{{ $d->user->nik }}</td><td>{{ $d->user->name }}</td><td>{{ $d->user->employee->department->name ?? '-' }}</td><td align="right">{{ number_format($d->basic_salary,0,',','.') }}</td><td align="right">{{ number_format(array_sum($d->allowances??[]),0,',','.') }}</td><td align="right">{{ number_format($d->overtime_pay,0,',','.') }}</td><td align="right">{{ number_format($d->total_deduction,0,',','.') }}</td><td align="right"><strong>{{ number_format($d->net_salary,0,',','.') }}</strong></td></tr>
@endforeach
</tbody>
<tfoot><tr><th colspan="7" align="right">Total</th><th align="right">Rp {{ number_format($payroll->total_amount,0,',','.') }}</th></tr></tfoot>
</table>
<p style="text-align:center;font-size:9px;color:#888;">Dicetak {{ now()->format('d/m/Y H:i') }} - HRD</p>
</body>
</html>
