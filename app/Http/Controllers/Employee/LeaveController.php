<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Leave;
use App\Models\LeaveType;
use Illuminate\Http\Request;
use Carbon\Carbon;

class LeaveController extends Controller
{
    public function index()
    {
        $leaves = Leave::with(['leaveType','supervisor','backupUser'])
            ->where('user_id', auth()->id())
            ->orderByDesc('created_at')
            ->paginate(10);
        return view('employee.leaves.index', compact('leaves'));
    }

    public function create()
    {
        $types = LeaveType::all();
        $deptId = auth()->user()->employee?->department_id;
        if ($deptId) {
            $supervisors = \App\Models\User::whereIn('role',['supervisor','hrd'])
                ->whereHas('employee', fn($q) => $q->where('department_id', $deptId))
                ->with('employee.department')
                ->orderBy('name')->get();
            $backups = \App\Models\User::where('id','!=',auth()->id())
                ->whereHas('employee', fn($q) => $q->where('department_id', $deptId))
                ->with('employee.department')
                ->orderBy('name')->get();
        } else {
            // fallback jika user belum punya employee/department: kosongkan biar tidak lintas dept
            $supervisors = collect();
            $backups = collect();
        }
        return view('employee.leaves.create', compact('types','supervisors','backups'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'leave_type_id' => 'required|exists:leave_types,id',
            'supervisor_id' => 'required|exists:users,id',
            'backup_user_id' => 'nullable|exists:users,id|different:supervisor_id',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|string|max:1000',
            'document' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);
        if ($request->backup_user_id && $request->backup_user_id == auth()->id()) {
            return back()->withErrors(['backup_user_id'=>'Backup tidak boleh diri sendiri'])->withInput();
        }

        // Validasi 1 departemen yang sama (bukan lintas department)
        $deptId = auth()->user()->employee?->department_id;
        if ($deptId) {
            $isSupervisorSameDept = \App\Models\User::where('id', $request->supervisor_id)
                ->whereHas('employee', fn($q) => $q->where('department_id', $deptId))
                ->exists();
            if (!$isSupervisorSameDept) {
                return back()->withErrors(['supervisor_id'=>'Atasan harus 1 departemen yang sama dengan Anda'])->withInput();
            }
            if ($request->backup_user_id) {
                $isBackupSameDept = \App\Models\User::where('id', $request->backup_user_id)
                    ->whereHas('employee', fn($q) => $q->where('department_id', $deptId))
                    ->exists();
                if (!$isBackupSameDept) {
                    return back()->withErrors(['backup_user_id'=>'Backup harus 1 departemen yang sama dengan Anda'])->withInput();
                }
            }
        }

        $type = LeaveType::findOrFail($request->leave_type_id);
        $start = Carbon::parse($request->start_date);
        $end = Carbon::parse($request->end_date);
        $totalDays = $start->diffInDays($end) + 1;

        // Cek saldo cuti tahunan (12 hari)
        if ($type->name === 'Cuti Tahunan') {
            $used = Leave::where('user_id', auth()->id())
                ->where('leave_type_id', $type->id)
                ->where('final_status', 'approved')
                ->whereYear('start_date', date('Y'))
                ->sum('total_days');
            if ($used + $totalDays > $type->quota_days) {
                return back()->withErrors(['quota' => "Saldo cuti tidak cukup! Sisa " . ($type->quota_days - $used) . " hari, ajukan $totalDays hari."])->withInput();
            }
        }

        // Cek overlap
        $overlap = Leave::where('user_id', auth()->id())
            ->where('final_status', '!=', 'rejected')
            ->where(function($q) use ($request){
                $q->whereBetween('start_date', [$request->start_date, $request->end_date])
                  ->orWhereBetween('end_date', [$request->start_date, $request->end_date])
                  ->orWhere(function($q2) use ($request){
                      $q2->where('start_date','<=',$request->start_date)->where('end_date','>=',$request->end_date);
                  });
            })->exists();
        if ($overlap) {
            return back()->withErrors(['overlap' => 'Tanggal overlap dengan pengajuan lain yang masih pending/approved'])->withInput();
        }

        // Validasi dokumen untuk Sakit
        if ($type->requires_document && !$request->hasFile('document')) {
            return back()->withErrors(['document' => 'Jenis Sakit wajib upload surat dokter'])->withInput();
        }

        $docPath = null;
        if ($request->hasFile('document')) {
            $docPath = $request->file('document')->store('leave_docs', 'public');
        }

        Leave::create([
            'user_id' => auth()->id(),
            'supervisor_id' => $request->supervisor_id,
            'backup_user_id' => $request->backup_user_id,
            'leave_type_id' => $type->id,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'total_days' => $totalDays,
            'reason' => $request->reason,
            'document_path' => $docPath,
            'status_supervisor' => 'pending',
            'status_hrd' => 'pending',
            'final_status' => 'pending',
        ]);

        return redirect()->route('employee.leaves.index')->with('success', 'Pengajuan cuti berhasil dikirim! Menunggu approve Supervisor.');
    }
}
