<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payroll;
use App\Models\PayrollDetail;
use App\Models\PayrollDeductionItem;
use App\Models\DeductionType;
use App\Models\CompanySetting;
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

            // Potongan sistem
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

            // Potongan dinamis HRD: ambil semua master aktif
            $customTypes = DeductionType::active()->get();
            foreach ($customTypes as $ct) {
                // nama sebagai key agar muncul di slip per baris
                $deductions[$ct->name] = (float) $ct->default_amount;
            }

            $gross = $basic + array_sum($allowances) + $overtimePay + $bonus + $thr;
            $totalDeduction = array_sum($deductions);
            $net = $gross - $totalDeduction;
            $totalAmount += $net;

            $detail = PayrollDetail::create([
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

            // Simpan rincian per item untuk slip & audit (snapshot nama + amount)
            foreach ($deductions as $name => $amount) {
                $typeId = $customTypes->firstWhere('name', $name)?->id;
                // untuk potongan sistem, typeId null
                if ($typeId === null && in_array($name, ['terlambat','alpha','bpjs_kes','bpjs_tk'])) $typeId = null;
                PayrollDeductionItem::create([
                    'payroll_detail_id' => $detail->id,
                    'deduction_type_id' => $typeId,
                    'name' => $name,
                    'amount' => $amount,
                ]);
            }
        }

        $payroll->update(['total_employees' => $employees->count(), 'total_amount' => $totalAmount]);

        return redirect()->route('admin.payrolls.show', $payroll)->with('success', "Payroll {$request->period} berhasil digenerate untuk {$employees->count()} karyawan. Total Rp " . number_format($totalAmount,0,',','.'));
    }

    public function show(Payroll $payroll)
    {
        $payroll->load(['details.user.employee','details.deductionItems']);
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
        $detail->load(['user.employee.department','user.employee.position','payroll','deductionItems']);
        $company = CompanySetting::get();
        $pdf = Pdf::loadView('admin.payrolls.slip', compact('detail','company'));
        return $pdf->stream("Slip-{$detail->user->nik}-{$detail->payroll->period}.pdf");
    }

    public function slipMassal(Payroll $payroll)
    {
        $payroll->load('details.user');
        $company = CompanySetting::get();
        $pdf = Pdf::loadView('admin.payrolls.slip_massal', compact('payroll','company'));
        return $pdf->stream("Slip-Massal-{$payroll->period}.pdf");
    }

    public function editDetail(PayrollDetail $detail)
    {
        $detail->load(['user.employee.department','payroll','deductionItems']);
        if ($detail->payroll->status !== 'draft') {
            return redirect()->route('admin.payrolls.show', $detail->payroll)->withErrors(['msg' => 'Hanya draft yang bisa diedit. Periode sudah locked.']);
        }
        // siapkan master potongan untuk tambah dinamis di edit
        $deductionTypes = DeductionType::active()->orderBy('name')->get();
        return view('admin.payrolls.edit-detail', compact('detail','deductionTypes'));
    }

    public function updateDetail(Request $request, PayrollDetail $detail)
    {
        $detail->load(['payroll','deductionItems']);
        if ($detail->payroll->status !== 'draft') {
            return back()->withErrors(['msg' => 'Hanya draft yang bisa diedit. Unlock dulu jika komplain.']);
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
            'notes' => 'nullable|string|max:1000',
            'deductions_dynamic' => 'nullable|array',
            'deductions_dynamic.*' => 'nullable|numeric|min:0',
            'deduction_names' => 'nullable|array',
            'deduction_names.*' => 'nullable|string|max:100',
            'deduction_amounts' => 'nullable|array',
            'deduction_amounts.*' => 'nullable|numeric|min:0',
        ]);

        $allowances = ['makan'=>$request->allow_makan,'transport'=>$request->allow_transport,'jabatan'=>$request->allow_jabatan];

        // Potongan sistem tetap
        $deductions = [
            'terlambat'=>$request->ded_terlambat,
            'alpha'=>$request->ded_alpha,
            'bpjs_kes'=>$request->ded_bpjs_kes,
            'bpjs_tk'=>$request->ded_bpjs_tk,
        ];

        // Potongan dinamis: dari input deductions_dynamic [name => amount]
        // Hapus yang amount 0 atau kosong dianggap dihapus per karyawan
        if ($request->has('deductions_dynamic')) {
            foreach ($request->deductions_dynamic as $name => $amount) {
                $amount = (float) $amount;
                if ($amount > 0) {
                    $deductions[$name] = $amount;
                }
                // jika 0/hapus maka tidak dimasukkan (dianggap hapus untuk karyawan ini)
            }
        }
        // Tambahan baris manual baru (HRD tambah potongan insidentil khusus karyawan ini)
        if ($request->has('deduction_names') && $request->has('deduction_amounts')) {
            foreach ($request->deduction_names as $idx => $name) {
                $name = trim($name);
                $amt = (float) ($request->deduction_amounts[$idx] ?? 0);
                if ($name !== '' && $amt > 0) {
                    $deductions[$name] = $amt;
                }
            }
        }

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

        // Sync payroll_deduction_items: hapus lama, buat baru snapshot
        $detail->deductionItems()->delete();
        $customMap = DeductionType::withTrashed()->get()->keyBy('name');
        foreach ($deductions as $name => $amount) {
            PayrollDeductionItem::create([
                'payroll_detail_id' => $detail->id,
                'deduction_type_id' => $customMap->get($name)?->id,
                'name' => $name,
                'amount' => $amount,
            ]);
        }

        // update total_amount payroll
        $payroll = $detail->payroll;
        $payroll->update(['total_amount' => $payroll->details()->sum('net_salary')]);

        return redirect()->route('admin.payrolls.show', $payroll)->with('success', "Gaji {$detail->user->name} diperbarui (dinamis). Net: Rp ".number_format($net,0,',','.'));
    }
}
