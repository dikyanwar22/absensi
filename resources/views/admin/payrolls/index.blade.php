@extends('layouts.admin')
@section('title','Payroll')
@section('header','Periode Gaji - HRD Full Access')
@section('content')
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif

<div class="card">
    <div class="card-header d-flex justify-content-between">
        <h3 class="card-title">Daftar Periode</h3>
        <a href="{{ route('admin.payrolls.create') }}" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Buat Periode Baru</a>
    </div>
    <div class="card-body p-0">
        <table class="table table-sm table-bordered">
            <thead><tr><th>Periode</th><th>Tanggal</th><th>Status</th><th>Jml Karyawan</th><th>Total Rp</th><th>Aksi</th></tr></thead>
            <tbody>
            @forelse($payrolls as $p)
            <tr>
                <td><strong>{{ $p->period }}</strong></td>
                <td><small>{{ $p->start_date }} - {{ $p->end_date }}</small></td>
                <td>
                    @if($p->status=='draft')<span class="badge bg-warning">Draft</span>
                    @elseif($p->status=='locked')<span class="badge bg-success">Locked</span>
                    @else<span class="badge bg-primary">Paid</span>@endif
                </td>
                <td>{{ $p->total_employees }}</td>
                <td>Rp {{ number_format($p->total_amount,0,',','.') }}</td>
                <td>
                    <a href="{{ route('admin.payrolls.show', $p) }}" class="btn btn-xs btn-info btn-sm">Detail & Slip</a>
                </td>
            </tr>
            @empty
            <tr><td colspan="6" class="text-center text-muted">Belum ada payroll. Buat periode baru untuk generate otomatis dari absensi.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer">{{ $payrolls->links() }}</div>
</div>
@endsection
