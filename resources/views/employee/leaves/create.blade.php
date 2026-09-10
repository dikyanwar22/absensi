@extends('layouts.employee')
@section('title','Ajukan Cuti')
@section('content')
<h6 class="mb-3 fw-semibold"><i class="bi bi-plus-circle me-1"></i> Ajukan Cuti/Izin/Sakit</h6>

{{-- Sisa Jatah Cuti Tahunan --}}
@if($cutiType)
<div class="card card-rounded p-3 mb-3 {{ $remaining <= 0 ? 'border-start border-4 border-danger' : 'border-start border-4 border-success' }}" style="border-left-width:4px !important;">
    <div class="d-flex justify-content-between align-items-start gap-2">
        <div style="flex:1; min-width:0;">
            <div class="fw-semibold small"><i class="bi bi-calendar-check text-primary"></i> Sisa Cuti Tahunan {{ $year }}</div>
            <div class="h5 mb-0 {{ $remaining <= 0 ? 'text-danger' : 'text-success' }}">{{ $remaining }} <small class="fs-6 fw-normal">hari</small></div>
            <small class="text-muted d-block" style="font-size:11px;">Terpakai {{ $used }} / {{ $cutiType->quota_days }} hari — hanya <b>Disetujui HRD</b> yang mengurangi</small>
        </div>
        <span class="badge rounded-pill {{ $remaining <= 0 ? 'bg-danger' : ($remaining <= 3 ? 'bg-warning text-dark' : 'bg-success') }}" style="font-size:11px; white-space:nowrap;">{{ $remaining <= 0 ? 'Habis' : 'Sisa '.$remaining }}</span>
    </div>
    @if($remaining <= 0)
    <div class="alert alert-danger py-2 px-2 mt-2 mb-0 d-flex gap-2" style="font-size:11px;"><i class="bi bi-exclamation-triangle-fill mt-1"></i><div>Jatah cuti {{ $year }} <b>habis</b> ({{ $used }}/{{ $cutiType->quota_days }}). Tidak bisa ajukan <b>Cuti Tahunan</b> lagi. Sakit/Izin tetap bisa.</div></div>
    @elseif($remaining <= 3)
    <div class="alert alert-warning py-1 px-2 mt-2 mb-0" style="font-size:11px;"><i class="bi bi-info-circle"></i> Sisa menipis! Tinggal {{ $remaining }} hari.</div>
    @endif
</div>
@endif

@if($errors->any())
<div class="alert alert-danger py-2" style="font-size:12px;">
    @foreach($errors->all() as $e)<div>• {{ $e }}</div>@endforeach
</div>
@endif

<form method="POST" action="{{ route('employee.leaves.store') }}" enctype="multipart/form-data">
    @csrf
    <div class="card card-rounded p-3 mb-3">
        {{-- Jenis --}}
        <div class="mb-3">
            <label class="form-label small fw-semibold mb-1">Jenis <span class="text-danger">*</span></label>
            <select name="leave_type_id" id="leave_type_id" class="form-select" required>
                @foreach($types as $t)
                <option value="{{ $t->id }}" data-name="{{ $t->name }}" data-quota="{{ $t->quota_days }}" {{ old('leave_type_id')==$t->id?'selected':'' }}>
                    {{ $t->name }}@if($t->quota_days) ({{ $t->quota_days }}hr)@endif @if($t->requires_document) • Wajib dokter @endif @if($t->name==='Cuti Tahunan') • Sisa {{ $remaining }}hr @endif
                </option>
                @endforeach
            </select>
            <small id="quota-info" class="d-block mt-1" style="font-size:11px;"></small>
        </div>

        {{-- Atasan --}}
        <div class="mb-3">
            <label class="form-label small fw-semibold mb-1">Atasan (Supervisor) <span class="text-danger">*</span></label>
            <select name="supervisor_id" class="form-select" required>
                <option value="">-- Pilih Atasan --</option>
                @foreach($supervisors as $s)
                <option value="{{ $s->id }}" {{ old('supervisor_id')==$s->id?'selected':'' }}>{{ $s->name }} • {{ $s->display_role }}@if($s->employee?->department) • {{ $s->employee->department->name }}@endif</option>
                @endforeach
            </select>
            <small class="text-muted d-block mt-1" style="font-size:11px;">Hanya 1 departemen yang sama.</small>
            @if($supervisors->isEmpty())
            <div class="alert alert-warning py-1 px-2 mt-2 mb-0" style="font-size:11px;">Tidak ada atasan di departemen Anda. Hubungi HRD.</div>
            @endif
        </div>

        {{-- Backup --}}
        <div class="mb-3">
            <label class="form-label small fw-semibold mb-1">Backup oleh</label>
            <select name="backup_user_id" class="form-select">
                <option value="">-- Tidak ada / Pilih Backup --</option>
                @foreach($backups as $b)
                <option value="{{ $b->id }}" {{ old('backup_user_id')==$b->id?'selected':'' }}>{{ $b->name }} • {{ $b->display_role }}@if($b->employee?->department) • {{ $b->employee->department->name }}@endif</option>
                @endforeach
            </select>
            <small class="text-muted d-block mt-1" style="font-size:11px;">Rekan 1 departemen yang backup tugas.</small>
            @if($backups->isEmpty())
            <div class="alert alert-warning py-1 px-2 mt-1 mb-0" style="font-size:11px;">Tidak ada rekan di departemen Anda yang bisa jadi backup.</div>
            @endif
        </div>

        {{-- Tanggal --}}
        <div class="row g-2">
            <div class="col-6">
                <label class="form-label small fw-semibold mb-1">Mulai <span class="text-danger">*</span></label>
                <input type="date" name="start_date" id="start_date" class="form-control" value="{{ old('start_date') }}" required>
            </div>
            <div class="col-6">
                <label class="form-label small fw-semibold mb-1">Selesai <span class="text-danger">*</span></label>
                <input type="date" name="end_date" id="end_date" class="form-control" value="{{ old('end_date') }}" required>
            </div>
        </div>
        <div id="quota-warning" class="alert d-none py-2 px-2 mt-2" style="font-size:11px;"></div>

        {{-- Alasan --}}
        <div class="mt-3">
            <label class="form-label small fw-semibold mb-1">Alasan <span class="text-danger">*</span></label>
            <textarea name="reason" class="form-control" rows="3" placeholder="Contoh: Keperluan keluarga..." required>{{ old('reason') }}</textarea>
        </div>

        {{-- Dokumen --}}
        <div class="mt-3">
            <label class="form-label small fw-semibold mb-1">Dokumen <small class="text-muted fw-normal">(Sakit wajib)</small></label>
            <input type="file" name="document" class="form-control" accept=".jpg,.jpeg,.png,.pdf">
            <small class="text-muted" style="font-size:11px;">JPG/PNG/PDF max 2MB</small>
        </div>

        <button id="btn-submit" class="btn btn-primary w-100 mt-3 py-2 fw-semibold">Kirim Pengajuan ke Atasan</button>
        <small class="text-muted d-block text-center mt-2" style="font-size:10px;">Dilihat supervisor di <code>Admin → Pengajuan Cuti</code> • Berkurang setelah <b>HRD Approve</b></small>
    </div>
</form>
<a href="{{ route('employee.leaves.index') }}" class="btn btn-outline-secondary w-100 py-2"><i class="bi bi-arrow-left"></i> Kembali</a>

@push('js')
<script>
const remaining = {{ $remaining ?? 12 }};
const cutiTypeId = {{ $cutiType?->id ?? 'null' }};
const quotaDays = {{ $cutiType?->quota_days ?? 12 }};

function calcDays(s, e){
    if(!s || !e) return 0;
    const ds = new Date(s), de = new Date(e);
    const diff = Math.ceil((de - ds) / (1000*60*60*24)) + 1;
    return diff > 0 ? diff : 0;
}
function updateQuotaInfo(){
    const sel = document.getElementById('leave_type_id');
    const opt = sel.options[sel.selectedIndex];
    const name = opt?.dataset?.name || opt?.text || '';
    const isCuti = name.includes('Cuti Tahunan');
    const info = document.getElementById('quota-info');
    const warn = document.getElementById('quota-warning');
    const btn = document.getElementById('btn-submit');
    const s = document.getElementById('start_date').value;
    const e = document.getElementById('end_date').value;
    const days = calcDays(s,e);

    if(isCuti){
        info.textContent = `Sisa: ${remaining} dari ${quotaDays} hari. ${days ? `Pengajuan ${days} hari.` : 'Pilih tanggal.'}`;
        info.className = remaining <= 0 ? 'd-block mt-1 text-danger' : (remaining <=3 ? 'd-block mt-1 text-warning' : 'd-block mt-1 text-success');
        if(remaining <= 0){
            warn.className = 'alert alert-danger py-2 px-2 mt-2';
            warn.textContent = `Jatah habis! Tidak bisa ajukan Cuti Tahunan lagi (${remaining} tersisa). Pilih Sakit/Izin.`;
            warn.classList.remove('d-none');
            btn.disabled = true;
            btn.textContent = 'Jatah Cuti Habis';
            btn.className = 'btn btn-secondary w-100 mt-3 py-2 fw-semibold';
        } else if(days && days > remaining){
            warn.className = 'alert alert-danger py-2 px-2 mt-2';
            warn.textContent = `Melebihi sisa! Sisa ${remaining} hari, pengajuan ${days} hari. Kurangi tanggal.`;
            warn.classList.remove('d-none');
            btn.disabled = true;
            btn.textContent = `Melebihi Sisa (${days} > ${remaining})`;
            btn.className = 'btn btn-danger w-100 mt-3 py-2 fw-semibold';
        } else if(days && days > 0){
            const after = remaining - days;
            warn.className = 'alert alert-success py-2 px-2 mt-2';
            warn.textContent = `Sisa setelah pengajuan: ${after} hari.`;
            warn.classList.remove('d-none');
            btn.disabled = false;
            btn.textContent = 'Kirim Pengajuan ke Atasan';
            btn.className = 'btn btn-primary w-100 mt-3 py-2 fw-semibold';
        } else {
            warn.classList.add('d-none');
            btn.disabled = false;
            btn.textContent = 'Kirim Pengajuan ke Atasan';
            btn.className = 'btn btn-primary w-100 mt-3 py-2 fw-semibold';
        }
    } else {
        info.textContent = name.includes('Sakit') ? 'Sakit wajib surat dokter, tidak potong kuota.' : 'Izin tidak potong kuota Cuti Tahunan.';
        info.className = 'd-block mt-1 text-muted';
        warn.classList.add('d-none');
        btn.disabled = false;
        btn.textContent = 'Kirim Pengajuan ke Atasan';
        btn.className = 'btn btn-primary w-100 mt-3 py-2 fw-semibold';
    }
}
document.getElementById('leave_type_id').addEventListener('change', updateQuotaInfo);
document.getElementById('start_date').addEventListener('change', updateQuotaInfo);
document.getElementById('end_date').addEventListener('change', updateQuotaInfo);
updateQuotaInfo();
</script>
@endpush
@endsection
