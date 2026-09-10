<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
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
            'email' => ['required','string','email','max:255'],
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
            $employee->join_date = now()->toDateString();
        }

        $employee->phone = $request->phone;
        $employee->address = $request->address;
        $employee->bank_name = $request->bank_name;
        $employee->bank_account = $request->bank_account;

        if ($request->hasFile('photo')) {
            // hapus lama di public/uploads
            if ($employee->photo) {
                $oldPath = public_path('uploads/' . $employee->photo);
                if (file_exists($oldPath)) unlink($oldPath);
                // fallback jika tersimpan dengan prefix uploads/
                $altOld = public_path($employee->photo);
                if (file_exists($altOld) && str_starts_with($employee->photo, 'uploads/')) unlink($altOld);
            }
            $file = $request->file('photo');
            $filename = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
            $dir = public_path('uploads/photos/employees');
            if (!file_exists($dir)) mkdir($dir, 0755, true);
            $file->move($dir, $filename);
            $employee->photo = 'photos/employees/' . $filename;
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
                'join_date' => now()->toDateString(),
            ]);
        }
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
        $employee->photo = 'photos/employees/' . $filename;
        $employee->save();
        return back()->with('success', 'Avatar berhasil diperbarui!');
    }
}
