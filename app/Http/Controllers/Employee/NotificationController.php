<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Leave;
use App\Models\PayrollDetail;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class NotificationController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $userId = $user->id;
        $notifications = collect();

        // 1. Cuti/Izin/Sakit - semua pengajuan milik user
        $leaves = Leave::with('leaveType')
            ->where('user_id', $userId)
            ->orderByDesc('updated_at')
            ->get();

        foreach ($leaves as $l) {
            $type = $l->leaveType->name ?? 'Cuti';
            $range = Carbon::parse($l->start_date)->format('d M Y') . ' - ' . Carbon::parse($l->end_date)->format('d M Y');
            if ($l->final_status === 'approved') {
                $notifications->push([
                    'icon' => 'bi-check-circle-fill',
                    'color' => 'success',
                    'bg' => '#d1e7dd',
                    'title' => "$type Disetujui ({$l->total_days} hari)",
                    'desc' => "$range • {$l->reason}",
                    'time' => $l->updated_at,
                    'link' => route('employee.leaves.index'),
                    'badge' => 'Disetujui',
                ]);
            } elseif ($l->final_status === 'rejected') {
                $note = $l->hrd_note ?: $l->supervisor_note;
                $notifications->push([
                    'icon' => 'bi-x-circle-fill',
                    'color' => 'danger',
                    'bg' => '#f8d7da',
                    'title' => "$type Ditolak",
                    'desc' => "$range" . ($note ? " • Alasan: $note" : " • {$l->reason}"),
                    'time' => $l->updated_at,
                    'link' => route('employee.leaves.index'),
                    'badge' => 'Ditolak',
                ]);
            } elseif ($l->status_supervisor === 'pending') {
                $notifications->push([
                    'icon' => 'bi-hourglass-split',
                    'color' => 'warning',
                    'bg' => '#fff3cd',
                    'title' => "$type Menunggu Supervisor",
                    'desc' => "$range • {$l->total_days} hari • {$l->reason}",
                    'time' => $l->created_at,
                    'link' => route('employee.leaves.index'),
                    'badge' => 'Menunggu SV',
                ]);
            } elseif ($l->status_supervisor === 'approved' && $l->status_hrd === 'pending') {
                $notifications->push([
                    'icon' => 'bi-hourglass-top',
                    'color' => 'warning',
                    'bg' => '#fff3cd',
                    'title' => "$type Menunggu HRD (Lolos Supervisor)",
                    'desc' => "$range • {$l->reason}",
                    'time' => $l->updated_at,
                    'link' => route('employee.leaves.index'),
                    'badge' => 'Menunggu HRD',
                ]);
            } else {
                $notifications->push([
                    'icon' => 'bi-clock-history',
                    'color' => 'secondary',
                    'bg' => '#e9ecef',
                    'title' => "$type Pending",
                    'desc' => "$range • {$l->reason}",
                    'time' => $l->updated_at,
                    'link' => route('employee.leaves.index'),
                    'badge' => 'Pending',
                ]);
            }
        }

        // 2. Slip Gaji - payroll terkunci milik user
        $payrolls = PayrollDetail::with('payroll')
            ->where('user_id', $userId)
            ->whereHas('payroll', fn($q) => $q->where('status', 'locked'))
            ->orderByDesc('updated_at')
            ->get();

        foreach ($payrolls as $p) {
            $notifications->push([
                'icon' => 'bi-wallet2',
                'color' => 'primary',
                'bg' => '#e7f1ff',
                'title' => "Slip Gaji {$p->payroll->period} Siap",
                'desc' => "Gaji bersih Rp " . number_format($p->net_salary,0,',','.') . " • Klik untuk download PDF",
                'time' => $p->payroll->locked_at ?? $p->updated_at,
                'link' => route('employee.payslip'),
                'badge' => 'Slip Gaji',
            ]);
        }

        // 3. Absensi hari ini
        $today = Carbon::today()->toDateString();
        $todayAtt = Attendance::where('user_id', $userId)->whereDate('date', $today)->first();
        if (!$todayAtt) {
            $notifications->push([
                'icon' => 'bi-exclamation-triangle-fill',
                'color' => 'danger',
                'bg' => '#f8d7da',
                'title' => "Belum Absen Hari Ini",
                'desc' => Carbon::now()->translatedFormat('l, d F Y') . " • Segera absen masuk sebelum terlambat",
                'time' => Carbon::now(),
                'link' => route('employee.home'),
                'badge' => 'Absen',
            ]);
        } else {
            if ($todayAtt->status === 'terlambat') {
                $notifications->push([
                    'icon' => 'bi-alarm-fill',
                    'color' => 'warning',
                    'bg' => '#fff3cd',
                    'title' => "Terlambat {$todayAtt->late_minutes} menit Hari Ini",
                    'desc' => "Masuk " . Carbon::parse($todayAtt->check_in)->format('H:i') . " • Tetap semangat!",
                    'time' => $todayAtt->check_in,
                    'link' => route('employee.history'),
                    'badge' => 'Terlambat',
                ]);
            }
            if (!$todayAtt->check_out) {
                $notifications->push([
                    'icon' => 'bi-box-arrow-right',
                    'color' => 'info',
                    'bg' => '#cff4fc',
                    'title' => "Jangan Lupa Absen Pulang",
                    'desc' => "Masuk " . Carbon::parse($todayAtt->check_in)->format('H:i') . " • Absen pulang " . ($todayAtt->check_in ? "tersisa" : "menunggu"),
                    'time' => $todayAtt->check_in,
                    'link' => route('employee.home'),
                    'badge' => 'Pulang',
                ]);
            }
        }
        // riwayat terlambat 7 hari terakhir (selain hari ini)
        $recentLate = Attendance::where('user_id', $userId)
            ->where('status','terlambat')
            ->whereDate('date','!=',$today)
            ->orderByDesc('date')->limit(3)->get();
        foreach ($recentLate as $a) {
            $notifications->push([
                'icon' => 'bi-alarm',
                'color' => 'warning',
                'bg' => '#fff3cd',
                'title' => "Terlambat pada " . Carbon::parse($a->date)->format('d M Y'),
                'desc' => "Telat {$a->late_minutes} menit • Masuk " . Carbon::parse($a->check_in)->format('H:i'),
                'time' => $a->check_in ?? $a->date,
                'link' => route('employee.history'),
                'badge' => 'Riwayat',
            ]);
        }

        // 4. Announcements aktif
        if (Schema::hasTable('announcements')) {
            $anns = DB::table('announcements')->where('is_active',1)->orderByDesc('created_at')->limit(5)->get();
            foreach ($anns as $ann) {
                $notifications->push([
                    'icon' => 'bi-megaphone-fill',
                    'color' => 'info',
                    'bg' => '#cff4fc',
                    'title' => $ann->title,
                    'desc' => mb_strimwidth($ann->content, 0, 80, '...'),
                    'time' => Carbon::parse($ann->created_at),
                    'link' => route('employee.home'),
                    'badge' => 'Pengumuman',
                ]);
            }
        }

        // 5. Kontrak akan habis
        $employee = $user->employee;
        if ($employee && $employee->contract_end_date) {
            $daysLeft = Carbon::now()->diffInDays(Carbon::parse($employee->contract_end_date), false);
            if ($daysLeft >= 0 && $daysLeft <= 30) {
                $notifications->push([
                    'icon' => 'bi-calendar-x-fill',
                    'color' => 'danger',
                    'bg' => '#f8d7da',
                    'title' => "Kontrak Habis {$daysLeft} hari lagi",
                    'desc' => "Berakhir " . Carbon::parse($employee->contract_end_date)->format('d M Y') . " • Status {$employee->employment_status} • Hubungi HRD",
                    'time' => Carbon::parse($employee->contract_end_date)->subDays(7),
                    'link' => route('employee.profile'),
                    'badge' => 'Kontrak',
                ]);
            }
        }

        // urutkan terbaru dulu
        $notifications = $notifications->sortByDesc(fn($n) => Carbon::parse($n['time'])->timestamp)->values();

        return view('employee.notifications', compact('notifications'));
    }
}
