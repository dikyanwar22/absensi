<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Leave;
use Illuminate\Http\Request;

class LeaveController extends Controller
{
    public function index(Request $request)
    {
        $query = Leave::with(['user.employee.department','leaveType','user','supervisor','backupUser'])
            ->orderByDesc('created_at');

        $user = auth()->user();
        if ($user->role === 'supervisor') {
            // Supervisor melihat yang diajukan ke dirinya (supervisor_id = dirinya) + fallback dept scope untuk data lama (supervisor_id NULL)
            $deptId = $user->employee?->department_id;
            $query->where(function($q) use ($user, $deptId){
                $q->where('supervisor_id', $user->id);
                if ($deptId) {
                    $q->orWhere(function($qq) use ($deptId){
                        $qq->whereNull('supervisor_id')->whereHas('user.employee', fn($q2) => $q2->where('department_id', $deptId));
                    });
                }
            });
            // Hanya yang masih pending di level supervisor, dan bukan pengajuan dirinya sendiri
            $query->where('status_supervisor', 'pending')->where('user_id','!=',$user->id);
        } elseif ($user->role === 'hrd') {
            // HRD melihat semua, tapi bisa filter via ?filter=pending_hrd / pending_spv / approved / rejected
            if ($filter = $request->query('filter')) {
                if ($filter === 'pending_hrd') {
                    $query->where('status_supervisor','approved')->where('status_hrd','pending');
                } elseif ($filter === 'pending_spv') {
                    $query->where('status_supervisor','pending');
                } elseif ($filter === 'approved') {
                    $query->where('final_status','approved');
                } elseif ($filter === 'rejected') {
                    $query->where('final_status','rejected');
                }
            }
            // Default: pending HRD di atas (order pending HRD first)
            $query->orderByRaw("CASE WHEN status_supervisor='approved' AND status_hrd='pending' THEN 0 ELSE 1 END");
        }

        $leaves = $query->get();
        return view('admin.leaves.index', compact('leaves'));
    }

    // Supervisor Approve Level 1
    public function approveSupervisor(Request $request, Leave $leave)
    {
        $request->validate(['action' => 'required|in:approved,rejected', 'note' => 'nullable|string']);
        
        if ($leave->status_supervisor !== 'pending') {
            return back()->withErrors(['msg' => 'Sudah diproses supervisor']);
        }
        // Otorisasi: hanya supervisor yang ditunjuk atau 1 departemen yang sama yang boleh approve
        $user = auth()->user();
        if ($leave->supervisor_id && $leave->supervisor_id !== $user->id) {
            return back()->withErrors(['msg' => 'Anda bukan atasan yang ditunjuk untuk pengajuan ini (ditujukan ke '.($leave->supervisor->name ?? 'atasan lain').')']);
        }
        if (is_null($leave->supervisor_id)) {
            $deptId = $user->employee?->department_id;
            $leaveDept = $leave->user->employee?->department_id;
            if ($deptId && $leaveDept && $deptId !== $leaveDept) {
                return back()->withErrors(['msg' => 'Hanya atasan 1 departemen yang sama boleh approve (beda departemen)']);
            }
        }
        if ($leave->user_id === $user->id) {
            return back()->withErrors(['msg' => 'Tidak boleh approve pengajuan diri sendiri']);
        }

        $leave->update([
            'status_supervisor' => $request->action,
            'supervisor_note' => $request->note,
            'approved_by_supervisor_id' => auth()->id(),
            'final_status' => $request->action === 'rejected' ? 'rejected' : 'pending', // jika reject langsung final
            'status_hrd' => $request->action === 'approved' ? 'pending' : 'rejected',
        ]);

        if ($request->action === 'rejected') {
            $leave->update(['status_hrd' => 'rejected']);
        }

        return back()->with('success', 'Supervisor ' . $request->action . ' berhasil');
    }

    // HRD Final Approve Level 2
    public function approveHrd(Request $request, Leave $leave)
    {
        if (auth()->user()->role !== 'hrd') {
            return back()->withErrors(['msg' => 'Hanya HRD yang bisa final approve']);
        }
        $request->validate(['action' => 'required|in:approved,rejected', 'note' => 'nullable|string']);

        if ($leave->status_supervisor !== 'approved') {
            return back()->withErrors(['msg' => 'Harus di-approve supervisor dulu!']);
        }
        if ($leave->status_hrd !== 'pending') {
            return back()->withErrors(['msg' => 'Sudah diproses HRD']);
        }

        // Cek kuota Cuti Tahunan saat final approve HRD (otomatis berkurang setelah approve)
        if ($request->action === 'approved') {
            $leave->loadMissing(['leaveType']);
            if ($leave->leaveType && $leave->leaveType->name === 'Cuti Tahunan' && $leave->leaveType->quota_days > 0) {
                $year = \Carbon\Carbon::parse($leave->start_date)->year;
                $used = \App\Models\Leave::where('user_id', $leave->user_id)
                    ->where('leave_type_id', $leave->leave_type_id)
                    ->where('final_status', 'approved')
                    ->whereYear('start_date', $year)
                    ->sum('total_days');
                $remaining = $leave->leaveType->quota_days - $used;
                // $used belum termasuk $leave ini (karena masih pending)
                if ($remaining <= 0) {
                    return back()->withErrors(['msg' => "Jatah cuti tahun $year sudah habis ($used/{$leave->leaveType->quota_days}). Tidak bisa approve."]);
                }
                if ($used + $leave->total_days > $leave->leaveType->quota_days) {
                    return back()->withErrors(['msg' => "Gagal approve: jatah cuti {$leave->user->name} tahun $year sisa $remaining hari, pengajuan {$leave->total_days} hari melebihi kuota {$leave->leaveType->quota_days}."]);
                }
            }
        }

        $leave->update([
            'status_hrd' => $request->action,
            'hrd_note' => $request->note,
            'approved_by_hrd_id' => auth()->id(),
            'final_status' => $request->action,
        ]);

        return back()->with('success', 'HRD ' . $request->action . ' berhasil. Status final: ' . $request->action);
    }
}
