@extends('layouts.admin')
@section('title','Shift')
@section('header','Master Shift (Pagi/Siang/Malam)')
@section('content')
@if(session('success'))<div class="alert alert-success alert-dismissible"><button type="button" class="close" data-dismiss="alert">&times;</button>{{ session('success') }}</div>@endif
@if($errors->any())<div class="alert alert-danger"><button type="button" class="close" data-dismiss="alert">&times;</button>@foreach($errors->all() as $e){{ $e }}<br>@endforeach</div>@endif

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title">Daftar Shift ({{ $shifts->count() }})</h3>
        <a href="{{ route('admin.shifts.create') }}" class="btn btn-primary btn-sm ml-auto"><i class="fas fa-plus"></i> Tambah Shift</a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-bordered mb-0">
                <thead class="thead-light"><tr><th>#</th><th>Nama</th><th>Jam</th><th>Toleransi</th><th>Overnight</th><th>Warna</th><th class="text-center">Karyawan</th><th style="width:160px;">Aksi</th></tr></thead>
                <tbody>
                @forelse($shifts as $i=>$s)
                    <tr>
                        <td>{{ $i+1 }}</td>
                        <td><span class="badge" style="background: {{ $s->color }}; color:#fff;">{{ $s->name }}</span></td>
                        <td><strong>{{ \Carbon\Carbon::parse($s->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($s->end_time)->format('H:i') }}</strong>
                            @if($s->is_overnight) <span class="badge bg-dark">Cross Day</span> @endif
                        </td>
                        <td>{{ $s->tolerance_late }} menit</td>
                        <td>@if($s->is_overnight)<span class="badge bg-warning">Ya (22:00-06:00)</span>@else<span class="badge bg-secondary">Tidak</span>@endif</td>
                        <td><span class="badge" style="background:{{ $s->color }}">{{ $s->color }}</span></td>
                        <td class="text-center"><span class="badge bg-success">{{ $s->employees_count }}</span></td>
                        <td>
                            <a href="{{ route('admin.shifts.edit',$s) }}" class="btn btn-xs btn-warning"><i class="fas fa-edit"></i> Edit</a>
                            <form method="POST" action="{{ route('admin.shifts.destroy',$s) }}" class="d-inline" onsubmit="return confirm('Hapus shift {{ $s->name }}?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-xs btn-danger"><i class="fas fa-trash"></i> Hapus</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center text-muted py-4">Belum ada shift. <a href="{{ route('admin.shifts.create') }}">Tambah Pagi/Siang/Malam</a></td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer">
        <small class="text-muted"><i class="fas fa-info-circle"></i> Pagi: 07:00-15:00, Siang: 14:00-22:00, Malam: 22:00-06:00 (is_overnight=1, cross day dihitung benar di AttendanceController)</small>
    </div>
</div>
@endsection
