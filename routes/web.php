<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return redirect('/login');
});

Route::get('/dashboard', function () {
    $user = auth()->user();
    if ($user && $user->role === 'staff') return redirect('/employee/home');
    return redirect('/admin/dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Admin (HRD & Supervisor) - AdminLTE 3
Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard');
    Route::get('/attendances', [\App\Http\Controllers\Admin\AttendanceController::class, 'index'])->name('attendances.index');
    Route::get('/attendances/live-map', [\App\Http\Controllers\Admin\AttendanceController::class, 'liveMap'])->name('attendances.live-map');
    Route::get('/attendances/live-map-data', [\App\Http\Controllers\Admin\AttendanceController::class, 'liveMapData'])->name('attendances.live-map-data');
    Route::get('/attendances/{attendance}/edit', [\App\Http\Controllers\Admin\AttendanceController::class, 'edit'])->name('attendances.edit');
    Route::put('/attendances/{attendance}', [\App\Http\Controllers\Admin\AttendanceController::class, 'update'])->name('attendances.update');

    // Master Data - Departemen & Jabatan & Shift & Lokasi
    Route::resource('departments', \App\Http\Controllers\Admin\DepartmentController::class)->except(['show']);
    Route::resource('positions', \App\Http\Controllers\Admin\PositionController::class)->except(['show']);
    Route::resource('shifts', \App\Http\Controllers\Admin\ShiftController::class)->except(['show']);
    Route::resource('office-locations', \App\Http\Controllers\Admin\OfficeLocationController::class)->except(['show']);

    // Karyawan
    Route::get('employees/resigned', [\App\Http\Controllers\Admin\EmployeeController::class,'resigned'])->name('employees.resigned');
    Route::post('employees/{employee}/resign', [\App\Http\Controllers\Admin\EmployeeController::class,'resign'])->name('employees.resign');
    Route::resource('employees', \App\Http\Controllers\Admin\EmployeeController::class)->except(['show']);

    // Cuti 2 Level
    Route::get('/leaves', [\App\Http\Controllers\Admin\LeaveController::class, 'index'])->name('leaves.index');
    Route::post('/leaves/{leave}/approve-supervisor', [\App\Http\Controllers\Admin\LeaveController::class, 'approveSupervisor'])->name('leaves.approve-supervisor');
    Route::post('/leaves/{leave}/approve-hrd', [\App\Http\Controllers\Admin\LeaveController::class, 'approveHrd'])->name('leaves.approve-hrd');

    // Profile Admin (biodata + avatar)
    Route::get('/profile', [\App\Http\Controllers\Admin\ProfileController::class, 'index'])->name('profile.index');
    Route::put('/profile', [\App\Http\Controllers\Admin\ProfileController::class, 'update'])->name('profile.update');

    // Laporan & Export
    Route::get('/reports', [\App\Http\Controllers\Admin\ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/export-attendance', [\App\Http\Controllers\Admin\ReportController::class, 'exportAttendance'])->name('reports.export-attendance');
    Route::get('/reports/export-payroll', [\App\Http\Controllers\Admin\ReportController::class, 'exportPayroll'])->name('reports.export-payroll');
    Route::get('/reports/export-leave', [\App\Http\Controllers\Admin\ReportController::class, 'exportLeave'])->name('reports.export-leave');

    // Payroll HRD Full
    Route::get('/payrolls', [\App\Http\Controllers\Admin\PayrollController::class, 'index'])->name('payrolls.index');
    Route::get('/payrolls/create', [\App\Http\Controllers\Admin\PayrollController::class, 'create'])->name('payrolls.create');
    Route::post('/payrolls', [\App\Http\Controllers\Admin\PayrollController::class, 'store'])->name('payrolls.store');
    Route::get('/payrolls/{payroll}', [\App\Http\Controllers\Admin\PayrollController::class, 'show'])->name('payrolls.show');
    Route::post('/payrolls/{payroll}/lock', [\App\Http\Controllers\Admin\PayrollController::class, 'lock'])->name('payrolls.lock');
    Route::post('/payrolls/{payroll}/unlock', [\App\Http\Controllers\Admin\PayrollController::class, 'unlock'])->name('payrolls.unlock');
    Route::get('/payrolls/{payroll}/slip-massal', [\App\Http\Controllers\Admin\PayrollController::class, 'slipMassal'])->name('payrolls.slip-massal');
    Route::get('/payroll-detail/{detail}/slip', [\App\Http\Controllers\Admin\PayrollController::class, 'slipPdf'])->name('payrolls.slip');
    Route::get('/payroll-detail/{detail}/edit', [\App\Http\Controllers\Admin\PayrollController::class, 'editDetail'])->name('payrolls.edit-detail');
    Route::put('/payroll-detail/{detail}', [\App\Http\Controllers\Admin\PayrollController::class, 'updateDetail'])->name('payrolls.update-detail');
});

// Employee Mobile - Bootstrap 5 Bottom Bar
Route::middleware(['auth'])->prefix('employee')->name('employee.')->group(function () {
    Route::get('/menu', function(){ return view('employee.menu'); })->name('menu');
    Route::get('/notifications', [\App\Http\Controllers\Employee\NotificationController::class, 'index'])->name('notifications');
    Route::get('/home', [\App\Http\Controllers\Employee\AttendanceController::class, 'home'])->name('home');
    Route::post('/attendance/check-in', [\App\Http\Controllers\Employee\AttendanceController::class, 'checkIn'])->name('attendance.checkIn');
    Route::post('/attendance/check-out', [\App\Http\Controllers\Employee\AttendanceController::class, 'checkOut'])->name('attendance.checkOut');
    Route::get('/history', [\App\Http\Controllers\Employee\AttendanceController::class, 'history'])->name('history');
    Route::get('/payslip', [\App\Http\Controllers\Employee\PayrollController::class, 'index'])->name('payslip');
    Route::get('/payslip/{detail}/pdf', [\App\Http\Controllers\Employee\PayrollController::class, 'download'])->name('payslip.pdf');
    Route::get('/profile', [\App\Http\Controllers\Employee\ProfileController::class, 'index'])->name('profile');
    Route::patch('/profile', [\App\Http\Controllers\Employee\ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/avatar', [\App\Http\Controllers\Employee\ProfileController::class, 'updateAvatar'])->name('profile.avatar');

    // Cuti
    Route::get('/leaves', [\App\Http\Controllers\Employee\LeaveController::class, 'index'])->name('leaves.index');
    Route::get('/leaves/create', [\App\Http\Controllers\Employee\LeaveController::class, 'create'])->name('leaves.create');
    Route::post('/leaves', [\App\Http\Controllers\Employee\LeaveController::class, 'store'])->name('leaves.store');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
