@extends('layouts.employee')
@section('title','Riwayat')
@section('content')
<h5 class="mb-3">Riwayat Absensi</h5>
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
