<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payroll;
use App\Models\PayrollDetail;
use App\Models\User;
use App\Models\Attendance;
use App\Models\Leave;
use App\Models\Employee;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class PayrollController extends Controller
{
    public function index()
    {
        $payrolls = Payroll::orderByDesc('period')->get();
        return view('admin.payrolls.index', compact('payrolls'));
    }

    public function create()
    {
        return view('admin.payrolls.create');
    }

    /**
     * Generate Payroll - Hitung otomatis dari absensi & cuti
     * app/Http/Controllers/Admin/PayrollController.php:25
     */
    public function store(Request $request)
    {
        $request->validate([
            'period' => 'required|regex:/^\d{4}-\d{2}$/|unique:payrolls,period',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $payroll = Payroll::create([
            'period' => $request->period,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'status' => 'draft',
            'created_by' => auth()->id(),
        ]);

        // Generate detail per karyawan aktif (exclude resigned)
        $employees = Employee::with(['user','position','department'])
            ->where('is_active', true)
            ->where('employment_status', '!=', 'resigned')
            ->get();

        $totalAmount = 0;
        foreach ($employees as $emp) {
            $user = $emp->user;
            if (!$user) continue;

            // Hitung rekap absensi periode
            $attendances = Attendance::where('user_id', $user->id)
                ->whereBetween('date', [$request->start_date, $request->end_date])
                ->get();
            $hadir = $attendances->whereIn('status', ['hadir','terlambat'])->count();
            $terlambat = $attendances->where('status','terlambat')->count();
            $lateMinutes = $attendances->sum('late_minutes');
            $overtimeHours = $attendances->sum('overtime_hours');

            // Cek alpha: hari kerja - hadir - cuti approved
            $totalWorkDays = Carbon::parse($request->start_date)->diffInDays(Carbon::parse($request->end_date)) + 1;
            // Sederhanakan: anggap semua hari adalah hari kerja kecuali weekend bisa di-filter nanti
            $cutiApproved = Leave::where('user_id', $user->id)
                ->where('final_status','approved')
                ->whereBetween('start_date', [$request->start_date, $request->end_date])
                ->sum('total_days');
            $alpha = max(0, $totalWorkDays - $hadir - $cutiApproved);

            // Gaji komponen (ambil dari position atau default)
            $basic = $emp->position->basic_salary_default ?? 5000000;
            $allowances = ['makan' => 500000, 'transport' => 300000, 'jabatan' => 200000];
            $overtimePay = $overtimeHours * 25000; // rate per jam
            $bonus = 0;
            $thr = 0;

            // Potongan
            $potTelat = $lateMinutes * 10000; // Rp 10k per menit
            $potAlpha = $alpha * 150000;
            $bpjsKes = $basic * 0.01;
            $bpjsTk = $basic * 0.02;
            $deductions = [
                'terlambat' => $potTelat,
                'alpha' => $potAlpha,
                'bpjs_kes' => $bpjsKes,
                'bpjs_tk' => $bpjsTk,
            ];

            $gross = $basic + array_sum($allowances) + $overtimePay + $bonus + $thr;
            $totalDeduction = array_sum($deductions);
            $net = $gross - $totalDeduction;
            $totalAmount += $net;

            PayrollDetail::create([
                'payroll_id' => $payroll->id,
                'user_id' => $user->id,
                'basic_salary' => $basic,
                'allowances' => $allowances,
                'overtime_pay' => $overtimePay,
                'bonus' => $bonus,
                'thr' => $thr,
                'deductions' => $deductions,
                'gross_salary' => $gross,
                'total_deduction' => $totalDeduction,
                'net_salary' => $net,
                'attendance_summary' => ['hadir' => $hadir, 'terlambat' => $terlambat, 'alpha' => $alpha, 'late_minutes' => $lateMinutes, 'overtime_hours' => $overtimeHours],
            ]);
        }

        $payroll->update(['total_employees' => $employees->count(), 'total_amount' => $totalAmount]);

        return redirect()->route('admin.payrolls.show', $payroll)->with('success', "Payroll {$request->period} berhasil digenerate untuk {$employees->count()} karyawan. Total Rp " . number_format($totalAmount,0,',','.'));
    }

    public function show(Payroll $payroll)
    {
        $payroll->load(['details.user.employee']);
        return view('admin.payrolls.show', compact('payroll'));
    }

    public function lock(Payroll $payroll)
    {
        if ($payroll->status !== 'draft') return back()->withErrors(['msg' => 'Hanya draft yang bisa dikunci']);
        $payroll->update(['status' => 'locked', 'locked_at' => now()]);
        return back()->with('success', 'Periode dikunci! Slip gaji siap dilihat karyawan.');
    }

    public function unlock(Payroll $payroll)
    {
        if ($payroll->status !== 'locked') return back()->withErrors(['msg' => 'Hanya locked yang bisa di-unlock']);
        $payroll->update(['status' => 'draft', 'locked_at' => null]);
        return back()->with('success', 'Periode di-unlock ke Draft! Sekarang bisa edit rupiah lagi, lalu Kunci kembali.');
    }

    public function slipPdf(PayrollDetail $detail)
    {
        $detail->load(['user.employee.department','user.employee.position','payroll']);
        $pdf = Pdf::loadView('admin.payrolls.slip', compact('detail'));
        return $pdf->stream("Slip-{$detail->user->nik}-{$detail->payroll->period}.pdf");
    }

    public function slipMassal(Payroll $payroll)
    {
        $payroll->load('details.user');
        $pdf = Pdf::loadView('admin.payrolls.slip_massal', compact('payroll'));
        return $pdf->stream("Slip-Massal-{$payroll->period}.pdf");
    }

    public function editDetail(PayrollDetail $detail)
    {
        $detail->load(['user.employee.department','payroll']);
        if ($detail->payroll->status !== 'draft') {
            return redirect()->route('admin.payrolls.show', $detail->payroll)->withErrors(['msg' => 'Hanya draft yang bisa diedit. Periode sudah locked.']);
        }
        return view('admin.payrolls.edit-detail', compact('detail'));
    }

    public function updateDetail(Request $request, PayrollDetail $detail)
    {
        $detail->load('payroll');
        if ($detail->payroll->status !== 'draft') {
            return back()->withErrors(['msg' => 'Hanya draft yang bisa diedit']);
        }

        $request->validate([
            'basic_salary' => 'required|numeric|min:0',
            'allow_makan' => 'required|numeric|min:0',
            'allow_transport' => 'required|numeric|min:0',
            'allow_jabatan' => 'required|numeric|min:0',
            'overtime_pay' => 'required|numeric|min:0',
            'bonus' => 'required|numeric|min:0',
            'thr' => 'required|numeric|min:0',
            'ded_terlambat' => 'required|numeric|min:0',
            'ded_alpha' => 'required|numeric|min:0',
            'ded_bpjs_kes' => 'required|numeric|min:0',
            'ded_bpjs_tk' => 'required|numeric|min:0',
            'ded_lain' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:1000',
        ]);

        $allowances = ['makan'=>$request->allow_makan,'transport'=>$request->allow_transport,'jabatan'=>$request->allow_jabatan];
        $deductions = ['terlambat'=>$request->ded_terlambat,'alpha'=>$request->ded_alpha,'bpjs_kes'=>$request->ded_bpjs_kes,'bpjs_tk'=>$request->ded_bpjs_tk,'lain'=>$request->ded_lain];

        $gross = $request->basic_salary + array_sum($allowances) + $request->overtime_pay + $request->bonus + $request->thr;
        $totalDeduction = array_sum($deductions);
        $net = $gross - $totalDeduction;

        $detail->update([
            'basic_salary' => $request->basic_salary,
            'allowances' => $allowances,
            'overtime_pay' => $request->overtime_pay,
            'bonus' => $request->bonus,
            'thr' => $request->thr,
            'deductions' => $deductions,
            'gross_salary' => $gross,
            'total_deduction' => $totalDeduction,
            'net_salary' => $net,
            'notes' => $request->notes,
        ]);

        // update total_amount payroll
        $payroll = $detail->payroll;
        $payroll->update(['total_amount' => $payroll->details()->sum('net_salary')]);

        return redirect()->route('admin.payrolls.show', $payroll)->with('success', "Gaji {$detail->user->name} diperbarui. Net: Rp ".number_format($net,0,',','.'));
    }
}
