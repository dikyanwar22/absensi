<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Employee;
use App\Models\Department;
use App\Models\Position;
use App\Models\Shift;
use App\Models\OfficeLocation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

class EmployeeController extends Controller
{
    public function index(Request $request)
    {
        $query = Employee::with(['user','department','position','shift','officeLocation'])
            ->orderByDesc('created_at');

        if ($q = $request->query('q')) {
            $query->where(function($qq) use ($q){
                $qq->where('phone','like',"%{$q}%")
                   ->orWhereHas('user', fn($u)=>$u->where('name','like',"%{$q}%")->orWhere('nik','like',"%{$q}%")->orWhere('email','like',"%{$q}%"));
            });
        }
        if ($dept = $request->query('department_id')) {
            $query->where('department_id', $dept);
        }
        if ($status = $request->query('employment_status')) {
            $query->where('employment_status', $status);
        }
        if ($request->query('is_active') !== null && $request->query('is_active') !== '') {
            $query->where('is_active', $request->boolean('is_active'));
        } else {
            if (!$request->has('employment_status') || $request->employment_status !== 'resigned') {
            }
        }
        // filter status_account (pending/active) untuk approval HRD
        if ($request->filled('status_account')) {
            $sa = $request->query('status_account');
            $query->whereHas('user', fn($u)=>$u->where('status_account', $sa));
        }

        // Default kosong agar tidak lelet 1000 data — hanya tampil saat ada filter atau ?all=1
        $hasFilter = $request->filled('q') || $request->filled('department_id') || $request->filled('employment_status') || $request->filled('status_account') || $request->filled('all');
        if (!$hasFilter) {
            $employees = collect();
        } else {
            $employees = $query->get();
        }
        $departments = Department::orderBy('name')->get();
        $pendingCount = User::where('status_account', 0)->count();

        return view('admin.employees.index', compact('employees','departments','pendingCount'));
    }

    public function pending(Request $request)
    {
        $query = Employee::with(['user','department','position','shift','officeLocation'])
            ->whereHas('user', fn($u)=>$u->where('status_account', 0))
            ->orderByDesc('created_at');
        if ($q = $request->query('q')) {
            $query->where(function($qq) use ($q){
                $qq->where('phone','like',"%{$q}%")
                   ->orWhereHas('user', fn($u)=>$u->where('name','like',"%{$q}%")->orWhere('nik','like',"%{$q}%")->orWhere('email','like',"%{$q}%"));
            });
        }
        $employees = $query->get();
        $departments = Department::orderBy('name')->get();
        return view('admin.employees.pending', compact('employees','departments'));
    }

    public function toggleStatus(Employee $employee)
    {
        $user = $employee->user;
        if (!$user) return back()->withErrors(['msg' => 'User tidak ditemukan']);
        $newStatus = (int) $user->status_account === 1 ? 0 : 1;
        $user->update(['status_account' => $newStatus]);
        // sinkron is_active employee jika dinonaktifkan
        if ($newStatus === 0) {
            // tidak paksa is_active false, biar tetap bisa dilihat HRD, tapi login diblok
        }
        return back()->with('success', $newStatus === 1 ? "Akun {$user->name} ({$user->nik}) diaktifkan (bisa login)" : "Akun {$user->name} dinonaktifkan (pending, tidak bisa login)");
    }

    public function resigned(Request $request)
    {
        $query = Employee::with(['user','department','position'])
            ->where(function($q){
                $q->where('employment_status','resigned')->orWhere('is_active', false);
            })
            ->orderByDesc('resign_date');

        if ($q = $request->query('q')) {
            $query->where(function($qq) use ($q){
                $qq->where('phone','like',"%{$q}%")
                   ->orWhereHas('user', fn($u)=>$u->where('name','like',"%{$q}%")->orWhere('nik','like',"%{$q}%"));
            });
        }

        $employees = $query->get();
        return view('admin.employees.resigned', compact('employees'));
    }

    public function create()
    {
        $departments = Department::orderBy('name')->get();
        $positions = Position::with('department')->orderBy('name')->get();
        $shifts = Shift::orderBy('name')->get();
        $offices = OfficeLocation::where('is_active', true)->orderBy('name')->get();

        if ($departments->isEmpty() || $positions->isEmpty() || $shifts->isEmpty()) {
            return redirect()->route('admin.departments.index')->withErrors(['msg' => 'Lengkapi Master Data (Departemen, Jabatan, Shift, Lokasi) dulu']);
        }

        return view('admin.employees.create', compact('departments','positions','shifts','offices'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'nik' => 'nullable|string|size:9|regex:/^[0-9]{9}$/|unique:users,nik',
            'email' => 'required|email|max:255',
            'password' => 'required|string',
            'role' => 'nullable|string|max:50',
            'department_id' => 'required|exists:departments,id',
            'position_id' => 'required|exists:positions,id',
            'shift_id' => 'nullable|exists:shifts,id',
            'office_location_id' => 'nullable|exists:office_locations,id',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'join_date' => 'required|date',
            'employment_status' => 'required|in:kontrak,tetap,magang,probation,resigned',
            'contract_end_date' => 'nullable|date|after:join_date',
            'bank_name' => 'nullable|string|max:50',
            'bank_account' => 'nullable|string|max:50',
            'bpjs_kes' => 'nullable|string|max:20',
            'bpjs_tk' => 'nullable|string|max:20',
            'photo' => 'nullable|image|mimes:jpeg,jpg,png,webp|max:2048',
        ]);

        // Email boleh duplikat hanya jika yang lama status 0; jika ada yang masih 1 → tolak
        if (User::where('email', $request->email)->where('status_account', 1)->exists()) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'email' => 'Email sudah terdaftar dan masih aktif (status 1). Tidak bisa tambah. Hanya jika akun lama nonaktif/pending (0) baru bisa pakai email sama.',
            ]);
        }

        DB::transaction(function() use ($request){
            // role dinamis dari jabatan: slug jabatan (manager_finance) — jika role tidak diisi, fallback ke jabatan
            $positionForRole = Position::find($request->position_id);
            $derivedRole = $positionForRole ? strtolower(Str::slug($positionForRole->name, '_')) : null;
            $effectiveRole = $request->filled('role') ? strtolower(Str::slug($request->role, '_')) : $derivedRole;
            if ($derivedRole) $effectiveRole = $derivedRole; // jabatan selalu menang (sesuai request: jabatan menentukan role)
            if (!$effectiveRole) $effectiveRole = 'staff';
            // pastikan Spatie role ada
            try { \Spatie\Permission\Models\Role::firstOrCreate(['name' => $effectiveRole]); } catch (\Throwable $e) {}

            $user = User::create([
                'name' => $request->name,
                'nik' => $request->nik,
                'email' => $request->email,
                'role' => $effectiveRole,
                'status_account' => 1, // HRD buat langsung aktif
                'password' => Hash::make($request->password),
            ]);
            // assign spatie role
            try { if (method_exists($user,'assignRole')) $user->assignRole($effectiveRole); } catch (\Throwable $e) {}

            $photoPath = null;
            if ($request->hasFile('photo')) {
                $file = $request->file('photo');
                $filename = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
                $dir = public_path('uploads/photos/employees');
                if (!file_exists($dir)) mkdir($dir, 0755, true);
                $file->move($dir, $filename);
                $photoPath = 'photos/employees/' . $filename;
            }

            Employee::create([
                'user_id' => $user->id,
                'department_id' => $request->department_id,
                'position_id' => $request->position_id,
                'shift_id' => $request->shift_id,
                'office_location_id' => $request->office_location_id,
                'phone' => $request->phone,
                'address' => $request->address,
                'join_date' => $request->join_date,
                'employment_status' => $request->employment_status,
                'contract_end_date' => $request->contract_end_date,
                'bank_name' => $request->bank_name,
                'bank_account' => $request->bank_account,
                'bpjs_kes' => $request->bpjs_kes,
                'bpjs_tk' => $request->bpjs_tk,
                'is_active' => $request->employment_status !== 'resigned',
                'photo' => $photoPath,
            ]);
        });

        return redirect()->route('admin.employees.index')->with('success','Karyawan berhasil ditambahkan');
    }

    public function edit(Employee $employee)
    {
        $employee->load(['user','department','position']);
        $departments = Department::orderBy('name')->get();
        $positions = Position::with('department')->orderBy('name')->get();
        $shifts = Shift::orderBy('name')->get();
        $offices = OfficeLocation::orderBy('name')->get();
        return view('admin.employees.edit', compact('employee','departments','positions','shifts','offices'));
    }

    public function update(Request $request, Employee $employee)
    {
        $user = $employee->user;
        $request->validate([
            'name' => 'required|string|max:255',
            'nik' => ['nullable','string','max:9', Rule::unique('users','nik')->ignore($user->id)],
            'email' => ['required','email','max:255'],
            'role' => 'nullable|string|max:50',
            'password' => 'nullable|string',
            'department_id' => 'required|exists:departments,id',
            'position_id' => 'required|exists:positions,id',
            'shift_id' => 'nullable|exists:shifts,id',
            'office_location_id' => 'nullable|exists:office_locations,id',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'join_date' => 'required|date',
            'employment_status' => 'required|in:kontrak,tetap,magang,probation,resigned',
            'contract_end_date' => 'nullable|date|after:join_date',
            'resign_date' => 'nullable|date|after:join_date',
            'bank_name' => 'nullable|string|max:50',
            'bank_account' => 'nullable|string|max:50',
            'bpjs_kes' => 'nullable|string|max:20',
            'bpjs_tk' => 'nullable|string|max:20',
            'photo' => 'nullable|image|mimes:jpeg,jpg,png,webp|max:2048',
            'is_active' => 'nullable|boolean',
        ]);

        // Jika ganti email ke yang sudah ada dan masih aktif (status 1) dengan user lain → tolak, kecuali yang lama sudah 0
        if ($request->email !== $user->email && User::where('email', $request->email)->where('status_account', 1)->where('id','!=',$user->id)->exists()) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'email' => 'Email sudah dipakai akun lain yang masih aktif (status 1). Hanya bisa pakai email sama jika akun lama sudah nonaktif/pending (0).',
            ]);
        }

        DB::transaction(function() use ($request, $employee, $user){
            // Sinkron role dengan jabatan: role = slug jabatan (manager_finance) — jabatan menentukan role
            $effectiveRole = $request->filled('role') ? strtolower(Str::slug($request->role, '_')) : $user->role;
            $posChanged = (int)$request->position_id !== (int)$employee->position_id;
            $deptChanged = (int)$request->department_id !== (int)$employee->department_id;
            if ($posChanged || $deptChanged) {
                $newPos = Position::find($request->position_id);
                if ($newPos) {
                    $effectiveRole = strtolower(Str::slug($newPos->name, '_'));
                }
                // fallback jika jabatan kosong, cek departemen HRD
                if (!$effectiveRole || $effectiveRole === '') {
                    $newDept = Department::find($request->department_id);
                    $effectiveRole = $newDept ? strtolower(Str::slug($newDept->name, '_')) : 'staff';
                }
            } else {
                // jika jabatan tidak berubah tapi role diisi manual, pakai slug role manual
                if ($request->filled('role')) {
                    $effectiveRole = strtolower(Str::slug($request->role, '_'));
                }
            }
            if (!$effectiveRole) $effectiveRole = 'staff';
            try { \Spatie\Permission\Models\Role::firstOrCreate(['name' => $effectiveRole]); } catch (\Throwable $e) {}

            $user->update([
                'name' => $request->name,
                'nik' => $request->nik,
                'email' => $request->email,
                'role' => $effectiveRole,
                'password' => $request->filled('password') ? Hash::make($request->password) : $user->password,
            ]);
            // sinkron Spatie
            try { if (method_exists($user,'syncRoles')) $user->syncRoles([$effectiveRole]); } catch (\Throwable $e) {}

            $data = $request->only(['department_id','position_id','shift_id','office_location_id','phone','address','join_date','employment_status','contract_end_date','resign_date','bank_name','bank_account','bpjs_kes','bpjs_tk']);
            $data['is_active'] = $request->has('is_active') ? $request->boolean('is_active') : ($request->employment_status !== 'resigned');

            if ($request->hasFile('photo')) {
                if ($employee->photo) {
                    $oldPath = public_path('uploads/' . $employee->photo);
                    if (file_exists($oldPath)) unlink($oldPath);
                    $altOld = public_path($employee->photo);
                    if (file_exists($altOld) && str_starts_with($employee->photo, 'uploads/')) unlink($altOld);
                }
                $file = $request->file('photo');
                $filename = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
                $dir = public_path('uploads/photos/employees');
                if (!file_exists($dir)) mkdir($dir, 0755, true);
                $file->move($dir, $filename);
                $data['photo'] = 'photos/employees/' . $filename;
            }

            $employee->update($data);

            // sinkron status_account dengan resigned: resigned => 0 (tidak bisa login), dikembalikan => 1
            if ($request->employment_status === 'resigned') {
                $user->update(['status_account' => 0]);
            } else {
                // jika dikembalikan dari resigned ke aktif (kontrak/tetap/dll) → aktifkan kembali supaya bisa login
                if ($employee->getOriginal('employment_status') === 'resigned' && $request->employment_status !== 'resigned') {
                    $user->update(['status_account' => 1]);
                }
            }
        });

        return redirect()->route('admin.employees.index')->with('success','Karyawan berhasil diperbarui');
    }

    public function destroy(Employee $employee)
    {
        DB::transaction(function() use ($employee){
            $user = $employee->user;
            if ($employee->photo) {
                $oldPath = public_path('uploads/' . $employee->photo);
                if (file_exists($oldPath)) unlink($oldPath);
                $altOld = public_path($employee->photo);
                if (file_exists($altOld) && str_starts_with($employee->photo, 'uploads/')) unlink($altOld);
            }
            $employee->delete();
            if ($user) $user->delete();
        });
        return redirect()->route('admin.employees.index')->with('success','Karyawan & akun dihapus');
    }

    public function resign(Request $request, Employee $employee)
    {
        $request->validate([
            'resign_date' => 'required|date|after_or_equal:join_date',
            'reason' => 'nullable|string|max:500',
        ]);

        DB::transaction(function() use ($request, $employee) {
            $employee->update([
                'employment_status' => 'resigned',
                'resign_date' => $request->resign_date,
                'is_active' => false,
            ]);
            // sesuai request: resign harus nonaktifkan login
            if ($employee->user) {
                $employee->user->update(['status_account' => 0]);
            }
        });

        return back()->with('success','Karyawan di-cut off (resigned) pada '.$request->resign_date.' - akun login dinonaktifkan (status_account=0)');
    }

    public function restore(Employee $employee)
    {
        DB::transaction(function() use ($employee) {
            $employee->update([
                'employment_status' => 'kontrak',
                'resign_date' => null,
                'is_active' => true,
            ]);
            // hapus resigned = aktifkan kembali supaya bisa login
            if ($employee->user) {
                $employee->user->update(['status_account' => 1]);
            }
        });
        return back()->with('success','Karyawan '.$employee->user->name.' dikembalikan aktif - akun login diaktifkan kembali (status_account=1)');
    }

    /**
     * Infer role dari jabatan/posisi + departemen terpilih: HRD dept atau nama mengandung HRD/Manager -> hrd, Supervisor -> supervisor, else staff
     */
    private function inferRoleFromPosition(Position $position, ?Department $deptOverride = null): string
    {
        $posName = strtolower($position->name ?? '');
        $deptName = strtolower($position->department->name ?? '');
        $overrideDeptName = strtolower($deptOverride->name ?? '');
        // departemen terpilih HRD → langsung hrd
        if ($overrideDeptName === 'hrd' || $deptName === 'hrd') {
            return 'hrd';
        }
        if (str_contains($posName, 'manager') || str_contains($posName, 'hrd')) {
            return 'hrd';
        }
        if (str_contains($posName, 'supervisor') || str_contains($posName, 'spv') || str_contains($posName, 'leader') || str_contains($posName, 'kepala')) {
            return 'supervisor';
        }
        return 'staff';
    }
}
