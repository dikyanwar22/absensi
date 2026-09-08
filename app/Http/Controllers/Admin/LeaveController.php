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
            // Supervisor melihat yang diajukan ke dirinya (supervisor_id) + fallback dept scope untuk data lama
            $deptId = $user->employee?->department_id;
            $query->where(function($q) use ($user, $deptId){
                $q->where('supervisor_id', $user->id);
                if ($deptId) {
                    $q->orWhere(function($qq) use ($deptId){
                        $qq->whereNull('supervisor_id')->whereHas('user.employee', fn($q2) => $q2->where('department_id', $deptId));
                    });
                }
            });
            $query->where('status_supervisor', 'pending');
        }

        $leaves = $query->paginate(15);
        return view('admin.leaves.index', compact('leaves'));
    }

    // Supervisor Approve Level 1
    public function approveSupervisor(Request $request, Leave $leave)
    {
        $request->validate(['action' => 'required|in:approved,rejected', 'note' => 'nullable|string']);
        
        if ($leave->status_supervisor !== 'pending') {
            return back()->withErrors(['msg' => 'Sudah diproses supervisor']);
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
        $request->validate(['action' => 'required|in:approved,rejected', 'note' => 'nullable|string']);

        if ($leave->status_supervisor !== 'approved') {
            return back()->withErrors(['msg' => 'Harus di-approve supervisor dulu!']);
        }
        if ($leave->status_hrd !== 'pending') {
            return back()->withErrors(['msg' => 'Sudah diproses HRD']);
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
