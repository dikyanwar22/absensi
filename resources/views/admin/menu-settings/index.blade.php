@extends('layouts.admin')
@section('title','Setting Menu')
@section('header','Setting Hak Akses Menu per Role')
@section('content')
@if(session('success'))<div class="alert alert-success alert-dismissible"><button type="button" class="close" data-dismiss="alert">&times;</button>{{ session('success') }}</div>@endif
@if($errors->any())<div class="alert alert-danger"><button type="button" class="close" data-dismiss="alert">&times;</button>@foreach($errors->all() as $e){{ $e }}<br>@endforeach</div>@endif

{{-- Akses Mobile — role yang dicentang langsung ke /employee/home, tidak tampil di matrix Setting Menu di bawah --}}
<div class="card card-outline card-info">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-mobile-alt"></i> Akses Mobile — Direct ke Smartphone</h3>
        <div class="card-tools"><span class="badge badge-info">{{ count($mobileRoles ?? []) }} role mobile</span></div>
    </div>
    <div class="card-body">
        <div class="alert alert-warning small py-2"><i class="fas fa-info-circle"></i> Centang role apapun di sini → saat <b>login berhasil langsung ke halaman mobile</b> <code>/employee/home</code> (smartphone). Jika <b>tidak dicentang</b> → bisa login ke <b>halaman admin</b>. Role yang sudah akses mobile <b>tidak muncul</b> di matrix Setting Menu di bawah karena sudah dikhususkan hanya mobile.</div>
        <form method="POST" action="{{ route('admin.menu-settings.mobile') }}">
            @csrf
            <div class="d-flex flex-wrap" style="gap:6px;">
                @foreach($allRoles as $r)
                    @php $isMobile = in_array($r, $mobileRoles ?? []); @endphp
                    <label class="btn btn-sm {{ $isMobile ? 'btn-primary' : 'btn-outline-secondary' }}" style="cursor:pointer;">
                        <input type="checkbox" name="roles[]" value="{{ $r }}" {{ $isMobile ? 'checked' : '' }} style="display:none;" onchange="this.parentElement.classList.toggle('btn-primary'); this.parentElement.classList.toggle('btn-outline-secondary')">
                        {{ strtoupper($r) }} @if($isMobile)<i class="fas fa-mobile-alt ml-1"></i>@endif
                    </label>
                @endforeach
            </div>
            <button class="btn btn-info btn-sm mt-3"><i class="fas fa-save"></i> Simpan Akses Mobile</button>
            <small class="text-muted ml-2">Kosongkan = semua role ke admin (kecuali yang di-setting mobile).</small>
        </form>
    </div>
    <div class="card-footer"><small class="text-muted">Disimpan di <code>menu_settings</code> key <code>akses_mobile</code> (is_allowed). Dipakai di <code>/dashboard</code> redirect & middleware.</small></div>
</div>

<div class="card">
    <div class="card-header d-flex flex-wrap align-items-center" style="gap:8px;">
        <h3 class="card-title"><i class="fas fa-cogs"></i> Setting Menu — Role: <span class="badge badge-primary">{{ strtoupper($selectedRole) }}</span></h3>
        <div class="ml-auto d-flex flex-wrap" style="gap:6px;">
            @foreach($roles as $r)
                <a href="{{ route('admin.menu-settings.index',['role'=>$r]) }}" class="btn btn-sm {{ $selectedRole===$r ? 'btn-primary' : 'btn-outline-secondary' }}" title="Role dari jabatan: {{ $r }}">{{ strtoupper($r) }}</a>
            @endforeach
        </div>
    </div>
    <div class="card-body">
        <div class="alert alert-info small"><i class="fas fa-info-circle"></i> Role diambil dari <b>jabatan</b> (contoh: jabatan <code>Manajer Finance</code> → role <code>manajer_finance</code> di <code>users.role</code>). Centang menu yang <b>berhak diakses</b> oleh role <b>{{ strtoupper($selectedRole) }}</b>. Bisa banyak role (tidak terbatas HRD/Supervisor/Staff). Menu admin tidak semua bisa diakses sembarang user kecuali di-setting di sini. Setelah simpan, sidebar & middleware otomatis menyesuaikan.</div>
        <form method="POST" action="{{ route('admin.menu-settings.update') }}">
            @csrf
            <input type="hidden" name="role" value="{{ $selectedRole }}">
            @foreach($menus as $group => $items)
                <div class="card card-outline card-secondary mb-3">
                    <div class="card-header py-2">
                        <h3 class="card-title text-sm" style="font-weight:600;">{{ $group ?: 'TANPA GROUP' }}</h3>
                        <div class="card-tools">
                            <label class="mb-0 small"><input type="checkbox" class="check-all-group" data-group="{{ md5($group) }}"> Centang Semua</label>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-sm table-hover mb-0">
                            <thead class="thead-light"><tr><th style="width:40px;"><input type="checkbox" disabled></th><th>Menu</th><th>Key</th><th>Route</th><th style="width:90px;">Akses</th></tr></thead>
                            <tbody>
                            @foreach($items as $m)
                                @php $allowed = $settings[$m->id] ?? false; @endphp
                                <tr>
                                    <td><i class="fas {{ $m->icon }}"></i></td>
                                    <td><strong>{{ $m->name }}</strong> <small class="text-muted">({{ $m->group }})</small></td>
                                    <td><code>{{ $m->key }}</code></td>
                                    <td><small class="text-muted">{{ $m->route_name }}</small></td>
                                    <td>
                                        <div class="custom-control custom-switch">
                                            <input type="hidden" name="permissions[{{ $m->id }}]" value="0">
                                            <input type="checkbox" class="custom-control-input group-{{ md5($group) }}" id="menu-{{ $m->id }}" name="permissions[{{ $m->id }}]" value="1" {{ $allowed ? 'checked' : '' }}>
                                            <label class="custom-control-label" for="menu-{{ $m->id }}">{{ $allowed ? 'Ya' : 'Tidak' }}</label>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endforeach
            <div class="d-flex justify-content-between">
                <a href="{{ route('admin.menu-settings.reset',['role'=>$selectedRole]) }}" onclick="return confirm('Reset ke default untuk {{ strtoupper($selectedRole) }}?')" class="btn btn-outline-secondary"><i class="fas fa-undo"></i> Reset Default</a>
                <button class="btn btn-primary"><i class="fas fa-save"></i> Simpan Setting</button>
            </div>
        </form>
    </div>
    <div class="card-footer"><small class="text-muted">Total {{ \App\Models\Menu::count() }} menu — Setting disimpan di <code>menu_settings</code> (role + menu_id + is_allowed). Middleware <code>menu.access</code> blok akses jika tidak berhak.</small></div>
</div>

{{-- CRUD Menu — tambah / update / delete menu tidak terbatas --}}
<div class="card card-outline card-primary">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-list"></i> Kelola Daftar Menu (CRUD)</h3>
        <div class="card-tools">
            <button class="btn btn-sm btn-primary" data-toggle="modal" data-target="#addMenuModal"><i class="fas fa-plus"></i> Tambah Menu</button>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-sm table-hover mb-0">
                <thead class="thead-light"><tr><th>#</th><th>Key</th><th>Nama</th><th>Group</th><th>Route / URL</th><th>Icon</th><th>Sort</th><th style="width:160px;">Aksi</th></tr></thead>
                <tbody>
                @forelse($allMenusFlat as $m)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td><code>{{ $m->key }}</code></td>
                        <td><strong>{{ $m->name }}</strong></td>
                        <td><span class="badge badge-secondary">{{ $m->group ?: '-' }}</span></td>
                        <td><small class="text-muted">{{ $m->route_name ?: $m->url }}</small></td>
                        <td><i class="fas {{ $m->icon }}"></i> <small>{{ $m->icon }}</small></td>
                        <td>{{ $m->sort_order }}</td>
                        <td>
                            <button class="btn btn-xs btn-warning" data-toggle="modal" data-target="#editMenuModal{{ $m->id }}"><i class="fas fa-edit"></i> Edit</button>
                            <form method="POST" action="{{ route('admin.menu-settings.menus.destroy',$m) }}" class="d-inline" onsubmit="return confirm('Hapus menu {{ $m->name }} ({{ $m->key }})? Hak akses role akan ikut terhapus.')">
                                @csrf @method('DELETE')
                                <button class="btn btn-xs btn-danger"><i class="fas fa-trash"></i></button>
                            </form>
                            <!-- Edit Modal -->
                            <div class="modal fade" id="editMenuModal{{ $m->id }}" tabindex="-1">
                                <div class="modal-dialog">
                                    <form method="POST" action="{{ route('admin.menu-settings.menus.update',$m) }}">
                                        @csrf @method('PUT')
                                        <div class="modal-content">
                                            <div class="modal-header"><h5 class="modal-title">Edit Menu: {{ $m->name }}</h5><button type="button" class="close" data-dismiss="modal">&times;</button></div>
                                            <div class="modal-body">
                                                <div class="form-group"><label>Key (unik, lowercase _)</label><input type="text" class="form-control" value="{{ $m->key }}" disabled><small class="text-muted">Key tidak bisa diubah (hapus & buat baru jika perlu)</small></div>
                                                <div class="form-group"><label>Nama</label><input type="text" name="name" class="form-control" value="{{ $m->name }}" required></div>
                                                <div class="form-group"><label>Group</label><input type="text" name="group" class="form-control" value="{{ $m->group }}" placeholder="MASTER DATA / KARYAWAN / ..."></div>
                                                <div class="form-group"><label>Route Name</label><input type="text" name="route_name" class="form-control" value="{{ $m->route_name }}" placeholder="admin.departments.index"></div>
                                                <div class="form-group"><label>URL</label><input type="text" name="url" class="form-control" value="{{ $m->url }}" placeholder="/admin/departments"></div>
                                                <div class="form-group"><label>Icon (fa-xxx)</label><input type="text" name="icon" class="form-control" value="{{ $m->icon }}" placeholder="fa-building"></div>
                                                <div class="form-group"><label>Sort Order</label><input type="number" name="sort_order" class="form-control" value="{{ $m->sort_order }}"></div>
                                            </div>
                                            <div class="modal-footer"><button type="button" class="btn btn-default" data-dismiss="modal">Batal</button><button class="btn btn-warning">Update</button></div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center text-muted">Belum ada menu</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer"><small class="text-muted">Tambah menu baru akan otomatis dibuat setting untuk semua role (HRD=true, lain false) — silakan ceklist per role di atas.</small></div>
</div>

<!-- Add Menu Modal -->
<div class="modal fade" id="addMenuModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('admin.menu-settings.menus.store') }}">
            @csrf
            <div class="modal-content">
                <div class="modal-header"><h5 class="modal-title"><i class="fas fa-plus"></i> Tambah Menu Baru</h5><button type="button" class="close" data-dismiss="modal">&times;</button></div>
                <div class="modal-body">
                    <div class="form-group"><label>Key <span class="text-danger">*</span> <small>(unik, contoh: finance_report)</small></label><input type="text" name="key" class="form-control" required pattern="[a-z0-9_]+" placeholder="manager_finance"></div>
                    <div class="form-group"><label>Nama <span class="text-danger">*</span></label><input type="text" name="name" class="form-control" required placeholder="Laporan Finance"></div>
                    <div class="form-group"><label>Group</label><input type="text" name="group" class="form-control" placeholder="CUTI & PAYROLL"></div>
                    <div class="form-group"><label>Route Name</label><input type="text" name="route_name" class="form-control" placeholder="admin.reports.index"></div>
                    <div class="form-group"><label>URL</label><input type="text" name="url" class="form-control" placeholder="/admin/finance-report"></div>
                    <div class="form-group"><label>Icon</label><input type="text" name="icon" class="form-control" placeholder="fa-chart-bar"></div>
                    <div class="form-group"><label>Sort Order</label><input type="number" name="sort_order" class="form-control" value="99"></div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-default" data-dismiss="modal">Batal</button><button class="btn btn-primary">Simpan Menu</button></div>
            </div>
        </form>
    </div>
</div>
@endsection
@push('js')
<script>
document.querySelectorAll('.check-all-group').forEach(el=>{
    el.addEventListener('change', function(){
        const grp = this.getAttribute('data-group');
        document.querySelectorAll('.group-'+grp).forEach(cb=>{
            cb.checked = el.checked;
        });
    });
});
</script>
@endpush
