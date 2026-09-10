<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Attendance;
use App\Models\Leave;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $today = Carbon::today()->toDateString();
        $totalKaryawan = User::where('role', 'staff')->count();
        $hadirHariIni = Attendance::whereDate('date', $today)->where('status', '!=', 'alpha')->count();
        $terlambatHariIni = Attendance::whereDate('date', $today)->where('status', 'terlambat')->count();
        $cutiPending = Leave::where('final_status', 'pending')->count();
        $akunPending = 0;
        try { $akunPending = User::where('status_account', 0)->count(); } catch (\Throwable $e) {}

        return view('admin.dashboard', compact('totalKaryawan', 'hadirHariIni', 'terlambatHariIni', 'cutiPending','akunPending'));
    }
}
