<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Leave;
use App\Models\LeaveType;
use Illuminate\Http\Request;
use Carbon\Carbon;

class LeaveController extends Controller
{
    public function index(Request $request)
    {
        // Default 01 - 31 bulan ini (tidak berat jika sudah banyak data)
        $start = $request->query('start_date') ?: Carbon::now()->startOfMonth()->toDateString();
        $end = $request->query('end_date') ?: Carbon::now()->endOfMonth()->toDateString();

        try {
            $startCarbon = Carbon::parse($start);
            $endCarbon = Carbon::parse($end);
            if ($startCarbon->gt($endCarbon)) {
                [$start, $end] = [$end, $start];
            }
        } catch (\Throwable $e) {
            $start = Carbon::now()->startOfMonth()->toDateString();
            $end = Carbon::now()->endOfMonth()->toDateString();
        }

        $query = Leave::with(['leaveType','supervisor','backupUser'])
            ->where('user_id', auth()->id())
            // Filter overlap: cuti yang bersinggungan dengan range filter
            ->where(function($q) use ($start, $end) {
                $q->whereBetween('start_date', [$start, $end])
                  ->orWhereBetween('end_date', [$start, $end])
                  ->orWhere(function($q2) use ($start, $end) {
                      $q2->where('start_date', '<=', $start)->where('end_date', '>=', $end);
                  });
            })
            ->orderByDesc('created_at');

        $leaves = $query->paginate(10)->withQueryString();

        return view('employee.leaves.index', compact('leaves','start','end'));
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

        // Hitung sisa jatah cuti tahunan (kuota 12) - hanya yang sudah approved HRD yang mengurangi
        $cutiType = LeaveType::where('name', 'Cuti Tahunan')->first();
        $used = 0;
        $remaining = $cutiType?->quota_days ?? 12;
        $year = date('Y');
        if ($cutiType) {
            $used = Leave::where('user_id', auth()->id())
                ->where('leave_type_id', $cutiType->id)
                ->where('final_status', 'approved')
                ->whereYear('start_date', $year)
                ->sum('total_days');
            $remaining = max(0, $cutiType->quota_days - $used);
        }

        return view('employee.leaves.create', compact('types','supervisors','backups','cutiType','used','remaining','year'));
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

        // Cek saldo cuti tahunan (12 hari) - hitung per tahun dari start_date pengajuan
        if ($type->name === 'Cuti Tahunan' && $type->quota_days > 0) {
            $year = $start->year;
            $used = Leave::where('user_id', auth()->id())
                ->where('leave_type_id', $type->id)
                ->where('final_status', 'approved')
                ->whereYear('start_date', $year)
                ->sum('total_days');
            $remaining = $type->quota_days - $used;
            if ($remaining <= 0) {
                return back()->withErrors(['quota' => "Jatah cuti tahun $year sudah habis ($used/{$type->quota_days} hari terpakai). Tidak bisa mengajukan Cuti Tahunan lagi."])->withInput();
            }
            if ($used + $totalDays > $type->quota_days) {
                return back()->withErrors(['quota' => "Saldo cuti tahun $year tidak cukup! Terpakai $used dari {$type->quota_days} hari, sisa $remaining hari, ajukan $totalDays hari."])->withInput();
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
            $file = $request->file('document');
            $filename = time() . '_' . \Illuminate\Support\Str::random(8) . '.' . $file->getClientOriginalExtension();
            $dir = public_path('uploads/leave_docs');
            if (!file_exists($dir)) mkdir($dir, 0755, true);
            $file->move($dir, $filename);
            $docPath = 'leave_docs/' . $filename;
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
