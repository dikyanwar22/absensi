@extends('layouts.admin')
@section('title','Edit Absensi')
@section('header','Edit Jam Absen — '.$attendance->user->name.' ('.$attendance->date?->format('d M Y').')')
@section('content')
@if($errors->any())<div class="alert alert-danger"><button type="button" class="close" data-dismiss="alert">&times;</button>@foreach($errors->all() as $e){{ $e }}<br>@endforeach</div>@endif

<div class="card">
    <div class="card-header d-flex justify-content-between">
        <h3 class="card-title"><i class="fas fa-clock"></i> Koreksi HRD — Jam Masuk/Pulang & Status</h3>
        <a href="{{ route('admin.attendances.index',['date'=>$attendance->date->format('Y-m-d')]) }}" class="btn btn-default btn-sm ml-auto"><i class="fas fa-arrow-left"></i> Kembali</a>
    </div>
    <form method="POST" action="{{ route('admin.attendances.update',$attendance) }}">
        @csrf @method('PUT')
        <div class="card-body">
            <div class="alert alert-info small"><i class="fas fa-info-circle"></i> HRD berwenang koreksi jam jika karyawan lupa absen / GPS error / salah shift. Perubahan akan mempengaruhi rekap telat & lembur payroll.</div>

            <div class="row">
                <div class="col-md-4 form-group">
                    <label>Tanggal <span class="text-danger">*</span></label>
                    <input type="date" name="date" class="form-control @error('date') is-invalid @enderror" value="{{ old('date',$attendance->date->format('Y-m-d')) }}" required>
                    @error('date')<span class="invalid-feedback">{{ $message }}</span>@enderror
                </div>
                <div class="col-md-4 form-group">
                    <label>Shift</label>
                    <select name="shift_id" class="form-control">
                        <option value="">-- Pilih Shift --</option>
                        @foreach($shifts as $s)
                            <option value="{{ $s->id }}" {{ old('shift_id',$attendance->shift_id)==$s->id?'selected':'' }}>{{ $s->name }} ({{ $s->start_time }} - {{ $s->end_time }}) @if($s->is_overnight) *Overnight @endif</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 form-group">
                    <label>Status <span class="text-danger">*</span></label>
                    <select name="status" class="form-control" required>
                        <option value="hadir" {{ old('status',$attendance->status)=='hadir'?'selected':'' }}>Hadir</option>
                        <option value="terlambat" {{ old('status',$attendance->status)=='terlambat'?'selected':'' }}>Terlambat</option>
                        <option value="pulang_cepat" {{ old('status',$attendance->status)=='pulang_cepat'?'selected':'' }}>Pulang Cepat</option>
                        <option value="alpha" {{ old('status',$attendance->status)=='alpha'?'selected':'' }}>Alpha</option>
                    </select>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 form-group">
                    <label>Jam Masuk (Check-In)</label>
                    <input type="datetime-local" name="check_in" class="form-control @error('check_in') is-invalid @enderror" value="{{ old('check_in', $attendance->check_in ? \Carbon\Carbon::parse($attendance->check_in)->format('Y-m-d\TH:i') : '') }}">
                    @error('check_in')<span class="invalid-feedback">{{ $message }}</span>@enderror
                    <small class="text-muted">Kosongkan jika tidak ada</small>
                </div>
                <div class="col-md-6 form-group">
                    <label>Jam Pulang (Check-Out)</label>
                    <input type="datetime-local" name="check_out" class="form-control @error('check_out') is-invalid @enderror" value="{{ old('check_out', $attendance->check_out ? \Carbon\Carbon::parse($attendance->check_out)->format('Y-m-d\TH:i') : '') }}">
                    @error('check_out')<span class="invalid-feedback">{{ $message }}</span>@enderror
                    <small class="text-muted">Harus setelah jam masuk</small>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4 form-group">
                    <label>Telat (menit)</label>
                    <input type="number" name="late_minutes" class="form-control" value="{{ old('late_minutes',$attendance->late_minutes) }}" min="0" max="1440">
                    <small class="text-muted">Otomatis hitung jika status terlambat & kosong (pakai toleransi shift)</small>
                </div>
                <div class="col-md-4 form-group">
                    <label>Lembur (jam)</label>
                    <input type="number" step="0.01" name="overtime_hours" class="form-control" value="{{ old('overtime_hours',$attendance->overtime_hours) }}" min="0" max="24">
                    <small class="text-muted">Contoh: 1.5 = 1 jam 30 menit</small>
                </div>
                <div class="col-md-4 form-group">
                    <label>Fake GPS</label>
                    <div class="custom-control custom-checkbox mt-2">
                        <input type="checkbox" class="custom-control-input" id="is_fake_gps" name="is_fake_gps" value="1" {{ old('is_fake_gps',$attendance->is_fake_gps)?'checked':'' }}>
                        <label class="custom-control-label" for="is_fake_gps">Tandai Fake GPS</label>
                    </div>
                </div>
            </div>

            <hr>
            <h6><i class="fas fa-map-marker-alt"></i> Koreksi Lokasi (opsional)</h6>
            <div class="row">
                <div class="col-md-3 form-group">
                    <label>Lat In</label>
                    <input type="text" name="lat_in" class="form-control" value="{{ old('lat_in',$attendance->lat_in) }}" placeholder="-6.20880000">
                </div>
                <div class="col-md-3 form-group">
                    <label>Lng In</label>
                    <input type="text" name="lng_in" class="form-control" value="{{ old('lng_in',$attendance->lng_in) }}" placeholder="106.84560000">
                </div>
                <div class="col-md-3 form-group">
                    <label>Akurasi In (m)</label>
                    <input type="number" name="accuracy_in" class="form-control" value="{{ old('accuracy_in',$attendance->accuracy_in) }}">
                </div>
                <div class="col-md-3 form-group">
                    <label>Foto In</label>
                    @if($attendance->photo_in)
                        <div><a href="{{ asset('uploads/'.$attendance->photo_in) }}" target="_blank"><img src="{{ asset('uploads/'.$attendance->photo_in) }}" width="60" class="rounded"></a> <small class="text-muted d-block">{{ $attendance->photo_in }}</small></div>
                    @else
                        <small class="text-muted">Tidak ada</small>
                    @endif
                </div>
            </div>
            <div class="row">
                <div class="col-md-3 form-group">
                    <label>Lat Out</label>
                    <input type="text" name="lat_out" class="form-control" value="{{ old('lat_out',$attendance->lat_out) }}">
                </div>
                <div class="col-md-3 form-group">
                    <label>Lng Out</label>
                    <input type="text" name="lng_out" class="form-control" value="{{ old('lng_out',$attendance->lng_out) }}">
                </div>
                <div class="col-md-3 form-group">
                    <label>Akurasi Out (m)</label>
                    <input type="number" name="accuracy_out" class="form-control" value="{{ old('accuracy_out',$attendance->accuracy_out) }}">
                </div>
                <div class="col-md-3 form-group">
                    <label>Foto Out</label>
                    @if($attendance->photo_out)
                        <div><a href="{{ asset('uploads/'.$attendance->photo_out) }}" target="_blank"><img src="{{ asset('uploads/'.$attendance->photo_out) }}" width="60" class="rounded"></a></div>
                    @else
                        <small class="text-muted">Tidak ada</small>
                    @endif
                </div>
            </div>
        </div>
        <div class="card-footer d-flex justify-content-between">
            <a href="{{ route('admin.attendances.index',['date'=>$attendance->date->format('Y-m-d')]) }}" class="btn btn-default"><i class="fas fa-times"></i> Batal</a>
            <button class="btn btn-warning"><i class="fas fa-save"></i> Simpan Koreksi HRD</button>
        </div>
    </form>
</div>

<div class="alert alert-secondary small">
    <strong>Audit:</strong> {{ $attendance->user->name }} — IP: {{ $attendance->ip_address ?? '-' }} — UA: {{ \Illuminate\Support\Str::limit($attendance->user_agent ?? '-',60) }}
</div>
@endsection
