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
use Illuminate\Support\Facades\Storage;
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
                $qq->where('employee_code','like',"%{$q}%")
                   ->orWhere('phone','like',"%{$q}%")
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
            // default: tampilkan aktif saja kecuali filter resigned explicit
            if (!$request->has('employment_status') || $request->employment_status !== 'resigned') {
                // jangan filter, tampil semua, tapi resigned dipisah via tab resigned; tetap tampil aktif + lainnya
            }
        }

        $employees = $query->paginate(15)->withQueryString();
        $departments = Department::orderBy('name')->get();

        return view('admin.employees.index', compact('employees','departments'));
    }

    public function resigned(Request $request)
    {
        $query = Employee::with(['user','department','position'])
            ->where('employment_status','resigned')
            ->orWhere('is_active', false)
            ->orderByDesc('resign_date');

        if ($q = $request->query('q')) {
            $query->where(function($qq) use ($q){
                $qq->where('employee_code','like',"%{$q}%")
                   ->orWhereHas('user', fn($u)=>$u->where('name','like',"%{$q}%")->orWhere('nik','like',"%{$q}%"));
            });
        }

        $employees = $query->paginate(15)->withQueryString();
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
            'nik' => 'nullable|string|max:20|unique:users,nik',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:6|max:50',
            'role' => 'required|in:hrd,supervisor,staff',
            'employee_code' => 'required|string|max:20|unique:employees,employee_code',
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

        DB::transaction(function() use ($request){
            $user = User::create([
                'name' => $request->name,
                'nik' => $request->nik,
                'email' => $request->email,
                'role' => $request->role,
                'password' => Hash::make($request->password),
            ]);

            $photoPath = null;
            if ($request->hasFile('photo')) {
                $photoPath = $request->file('photo')->store('photos/employees','public');
            }

            Employee::create([
                'user_id' => $user->id,
                'employee_code' => $request->employee_code,
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
            'nik' => ['nullable','string','max:20', Rule::unique('users','nik')->ignore($user->id)],
            'email' => ['required','email','max:255', Rule::unique('users','email')->ignore($user->id)],
            'role' => 'required|in:hrd,supervisor,staff',
            'password' => 'nullable|string|min:6|max:50',
            'employee_code' => ['required','string','max:20', Rule::unique('employees','employee_code')->ignore($employee->id)],
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

        DB::transaction(function() use ($request, $employee, $user){
            $user->update([
                'name' => $request->name,
                'nik' => $request->nik,
                'email' => $request->email,
                'role' => $request->role,
                'password' => $request->filled('password') ? Hash::make($request->password) : $user->password,
            ]);

            $data = $request->only(['employee_code','department_id','position_id','shift_id','office_location_id','phone','address','join_date','employment_status','contract_end_date','resign_date','bank_name','bank_account','bpjs_kes','bpjs_tk']);
            $data['is_active'] = $request->has('is_active') ? $request->boolean('is_active') : ($request->employment_status !== 'resigned');

            if ($request->hasFile('photo')) {
                if ($employee->photo && Storage::disk('public')->exists($employee->photo)) {
                    Storage::disk('public')->delete($employee->photo);
                }
                $data['photo'] = $request->file('photo')->store('photos/employees','public');
            }

            $employee->update($data);
        });

        return redirect()->route('admin.employees.index')->with('success','Karyawan berhasil diperbarui');
    }

    public function destroy(Employee $employee)
    {
        DB::transaction(function() use ($employee){
            $user = $employee->user;
            if ($employee->photo && Storage::disk('public')->exists($employee->photo)) {
                Storage::disk('public')->delete($employee->photo);
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

        $employee->update([
            'employment_status' => 'resigned',
            'resign_date' => $request->resign_date,
            'is_active' => false,
        ]);

        return back()->with('success','Karyawan di-cut off (resigned) pada '.$request->resign_date);
    }
}
