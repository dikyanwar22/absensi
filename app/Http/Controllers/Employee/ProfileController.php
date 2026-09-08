<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class ProfileController extends Controller
{
    public function index()
    {
        $user = auth()->user()->load(['employee.department','employee.position','employee.shift','employee.officeLocation']);
        $employee = $user->employee;

        // hitung sisa cuti (dari leave_types quota 12 dikurangi terpakai tahun ini)
        $leaveQuota = 12;
        $usedLeave = 0;
        $remainingLeave = $leaveQuota;
        try {
            $quotaType = \App\Models\LeaveType::where('name','Cuti Tahunan')->first();
            if ($quotaType) $leaveQuota = $quotaType->quota_days ?? 12;
            $usedLeave = \App\Models\Leave::where('user_id',$user->id)
                ->where('final_status','approved')
                ->whereYear('start_date', date('Y'))
                ->sum('total_days');
            $remainingLeave = max(0, $leaveQuota - $usedLeave);
        } catch (\Throwable $e) {}

        // slip gaji terakhir
        $lastPayroll = null;
        try {
            $lastPayroll = \App\Models\PayrollDetail::with('payroll')
                ->where('user_id',$user->id)
                ->whereHas('payroll', fn($q)=>$q->where('status','locked'))
                ->orderByDesc('created_at')->first();
        } catch (\Throwable $e) {}

        // rekap absensi bulan ini
        $monthAttend = null;
        try {
            $monthAttend = \App\Models\Attendance::where('user_id',$user->id)
                ->whereMonth('date', date('m'))->whereYear('date', date('Y'))->get();
        } catch (\Throwable $e) {}

        // kontrak sisa hari
        $contractDaysLeft = null;
        $contractProgress = null;
        if ($employee && $employee->contract_end_date) {
            $contractDaysLeft = Carbon::now()->diffInDays(Carbon::parse($employee->contract_end_date), false);
            // progress sederhana
            if ($employee->join_date && $employee->contract_end_date) {
                $total = Carbon::parse($employee->join_date)->diffInDays(Carbon::parse($employee->contract_end_date));
                $elapsed = Carbon::parse($employee->join_date)->diffInDays(Carbon::now());
                $contractProgress = $total > 0 ? min(100, max(0, round($elapsed/$total*100))) : 0;
            }
        }

        return view('employee.profile', compact('user','employee','leaveQuota','usedLeave','remainingLeave','lastPayroll','monthAttend','contractDaysLeft','contractProgress'));
    }

    public function update(Request $request)
    {
        $user = auth()->user();
        $employee = $user->employee;

        $request->validate([
            'name' => ['required','string','max:255'],
            'email' => ['required','string','email','max:255', Rule::unique('users')->ignore($user->id)],
            'phone' => ['nullable','string','max:20'],
            'address' => ['nullable','string','max:500'],
            'bank_name' => ['nullable','string','max:50'],
            'bank_account' => ['nullable','string','max:50'],
            'photo' => ['nullable','image','mimes:jpeg,jpg,png,webp','max:2048'],
        ]);

        // update user
        $user->name = $request->name;
        if ($user->email !== $request->email) {
            $user->email = $request->email;
            $user->email_verified_at = null;
        }
        $user->save();

        // ensure employee exists
        if (!$employee) {
            $employee = new \App\Models\Employee();
            $employee->user_id = $user->id;
            $employee->employee_code = 'EMP' . str_pad($user->id, 4, '0', STR_PAD_LEFT);
            $employee->join_date = now()->toDateString();
        }

        $employee->phone = $request->phone;
        $employee->address = $request->address;
        $employee->bank_name = $request->bank_name;
        $employee->bank_account = $request->bank_account;

        if ($request->hasFile('photo')) {
            // hapus lama
            if ($employee->photo && Storage::disk('public')->exists($employee->photo)) {
                Storage::disk('public')->delete($employee->photo);
            }
            $path = $request->file('photo')->store('photos/employees', 'public');
            $employee->photo = $path;
        }

        $employee->save();

        return back()->with('success', 'Profil berhasil diperbarui!');
    }

    public function updateAvatar(Request $request)
    {
        $request->validate([
            'photo' => ['required','image','mimes:jpeg,jpg,png,webp','max:2048'],
        ]);
        $user = auth()->user();
        $employee = $user->employee;
        if (!$employee) {
            $employee = \App\Models\Employee::create([
                'user_id' => $user->id,
                'employee_code' => 'EMP' . str_pad($user->id, 4, '0', STR_PAD_LEFT),
                'join_date' => now()->toDateString(),
            ]);
        }
        if ($employee->photo && Storage::disk('public')->exists($employee->photo)) {
            Storage::disk('public')->delete($employee->photo);
        }
        $employee->photo = $request->file('photo')->store('photos/employees', 'public');
        $employee->save();
        return back()->with('success', 'Avatar berhasil diperbarui!');
    }
}
