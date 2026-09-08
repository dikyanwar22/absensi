<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Payroll;
use App\Models\PayrollDetail;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class PayrollController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $selectedPeriod = $request->query('period');

        // daftar periode untuk filter (hanya periode locked yang punya slip untuk user ini) — jadi history bulan-bulan sebelumnya tetap muncul
        $periods = Payroll::where('status', 'locked')
            ->whereHas('details', fn($q) => $q->where('user_id', $user->id))
            ->orderByDesc('period')
            ->pluck('period');

        // fallback jika belum ada detail per user tapi payroll locked tetap tampilkan semua locked (agar filter tetap ada)
        if ($periods->isEmpty()) {
            $periods = Payroll::where('status', 'locked')->orderByDesc('period')->pluck('period');
        }

        $payrolls = PayrollDetail::with('payroll')
            ->where('user_id', $user->id)
            ->whereHas('payroll', function($q) use ($selectedPeriod) {
                $q->where('status', 'locked');
                if ($selectedPeriod) $q->where('period', $selectedPeriod);
            })
            // history: urut periode terbaru dulu (YYYY-MM desc)
            ->join('payrolls', 'payrolls.id', '=', 'payroll_details.payroll_id')
            ->orderByDesc('payrolls.period')
            ->select('payroll_details.*')
            ->paginate(12)
            ->withQueryString();

        return view('employee.payslip', compact('payrolls','periods','selectedPeriod'));
    }

    public function download(PayrollDetail $detail)
    {
        $user = auth()->user();
        // pastikan hanya milik sendiri
        if ($detail->user_id !== $user->id) {
            abort(403, 'Tidak boleh akses slip gaji orang lain');
        }
        // pastikan payroll sudah dikunci
        $detail->load(['user.employee.department','user.employee.position','payroll']);
        if ($detail->payroll->status !== 'locked') {
            return back()->withErrors(['msg' => 'Slip gaji belum tersedia, menunggu HRD kunci periode.']);
        }
        $pdf = Pdf::loadView('admin.payrolls.slip', compact('detail'));
        return $pdf->download("Slip-{$detail->user->nik}-{$detail->payroll->period}.pdf");
    }
}
