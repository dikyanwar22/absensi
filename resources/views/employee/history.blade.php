@extends('layouts.employee')
@section('title','Riwayat')
@section('content')
<h5 class="mb-3">Riwayat Absensi</h5>

{{-- Filter Tanggal 01-31 bulan ini (default) --}}
<div class="card card-rounded p-3 mb-3">
    <form method="GET" action="{{ route('employee.history') }}" class="row g-2 align-items-end">
        <div class="col-5">
            <label class="form-label small mb-1">Dari</label>
            <input type="date" name="start_date" value="{{ $start }}" class="form-control form-control-sm" required>
        </div>
        <div class="col-5">
            <label class="form-label small mb-1">Sampai</label>
            <input type="date" name="end_date" value="{{ $end }}" class="form-control form-control-sm" required>
        </div>
        <div class="col-2 d-grid">
            <button class="btn btn-primary btn-sm"><i class="bi bi-search"></i></button>
        </div>
        <div class="col-12 d-flex gap-2 mt-2 flex-wrap">
            <a href="{{ route('employee.history', ['start_date'=>\Carbon\Carbon::now()->startOfMonth()->toDateString(), 'end_date'=>\Carbon\Carbon::now()->endOfMonth()->toDateString()]) }}" class="btn btn-sm {{ request('start_date')==\Carbon\Carbon::now()->startOfMonth()->toDateString() && request('end_date')==\Carbon\Carbon::now()->endOfMonth()->toDateString() ? 'btn-primary' : 'btn-outline-primary' }}">Bulan Ini</a>
            <a href="{{ route('employee.history', ['start_date'=>\Carbon\Carbon::now()->startOfWeek()->toDateString(), 'end_date'=>\Carbon\Carbon::now()->endOfWeek()->toDateString()]) }}" class="btn btn-sm btn-outline-secondary">Minggu Ini</a>
            <a href="{{ route('employee.history', ['start_date'=>\Carbon\Carbon::today()->toDateString(), 'end_date'=>\Carbon\Carbon::today()->toDateString()]) }}" class="btn btn-sm btn-outline-secondary">Hari Ini</a>
            <a href="{{ route('employee.history') }}" class="btn btn-sm btn-outline-dark ms-auto">Reset</a>
        </div>
    </form>
    <small class="text-muted d-block mt-2">Menampilkan {{ \Carbon\Carbon::parse($start)->translatedFormat('d M Y') }} s/d {{ \Carbon\Carbon::parse($end)->translatedFormat('d M Y') }} — {{ $attendances->total() }} data (pagination 20/hal)</small>
</div>
@forelse($attendances as $a)
<div class="card card-rounded p-3 mb-2">
    <div class="d-flex justify-content-between">
        <div>
            <div class="fw-semibold">{{ \Carbon\Carbon::parse($a->date)->translatedFormat('d M Y') }}</div>
            <small class="text-muted">Masuk: {{ $a->check_in ? \Carbon\Carbon::parse($a->check_in)->format('H:i') : '-' }} | Pulang: {{ $a->check_out ? \Carbon\Carbon::parse($a->check_out)->format('H:i') : '-' }}</small>
            @if($a->lat_in)<br><small><a href="https://www.google.com/maps?q={{ $a->lat_in }},{{ $a->lng_in }}" target="_blank">📍 {{ $a->lat_in }}, {{ $a->lng_in }}</a> ({{ $a->distance_in_meter }}m)</small>@endif
        </div>
        <div class="text-end">
            @if($a->status=='hadir')<span class="badge bg-success">Hadir</span>
            @elseif($a->status=='terlambat')<span class="badge bg-warning">Terlambat {{ $a->late_minutes }}m</span>
            @else<span class="badge bg-secondary">{{ $a->status }}</span>@endif
        </div>
    </div>
</div>
@empty
<div class="card card-rounded p-4 text-center text-muted">Belum ada riwayat</div>
@endforelse
<div class="mt-3">{{ $attendances->links() }}</div>
@endsection
