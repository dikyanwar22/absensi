<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Attendance;
use App\Models\Leave;
use App\Models\Payroll;
use App\Exports\AttendanceReportExport;
use App\Exports\PayrollReportExport;
use App\Exports\LeaveReportExport;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $tab = $request->query('tab', 'attendance');
        $departments = Department::orderBy('name')->get();
        $payrolls = Payroll::orderByDesc('period')->get();

        // defaults
        $start = $request->query('start_date', Carbon::now()->startOfMonth()->toDateString());
        $end = $request->query('end_date', Carbon::now()->endOfMonth()->toDateString());
        $departmentId = $request->query('department_id');
        $payrollId = $request->query('payroll_id');

        $attendanceRows = collect();
        $payrollRows = collect();
        $leaveRows = collect();

        // Attendance Report
        if ($tab === 'attendance') {
            $employees = Employee::with(['user','department','position'])
                ->when($departmentId, fn($q)=>$q->where('department_id',$departmentId))
                ->where('is_active', true)
                ->get();

            foreach ($employees as $emp) {
                $user = $emp->user;
                if (!$user) continue;

                $atts = Attendance::where('user_id',$user->id)
                    ->whereBetween('date', [$start,$end])->get();
                $hadir = $atts->whereIn('status',['hadir','terlambat'])->count();
                $terlambat = $atts->where('status','terlambat')->count();
                $lateMinutes = $atts->sum('late_minutes');
                $overtime = $atts->sum('overtime_hours');
                $totalWorkDays = Carbon::parse($start)->diffInDays(Carbon::parse($end))+1;
                $cutiApproved = Leave::where('user_id',$user->id)->where('final_status','approved')
                    ->whereBetween('start_date', [$start,$end])->sum('total_days');
                $alpha = max(0, $totalWorkDays - $hadir - $cutiApproved);

                $attendanceRows->push([
                    'name' => $user->name,
                    'nik' => $user->nik ?? '-',
                    'dept' => $emp->department->name ?? '-',
                    'position' => $emp->position->name ?? '-',
                    'hadir' => $hadir,
                    'terlambat' => $terlambat,
                    'alpha' => $alpha,
                    'late_minutes' => $lateMinutes,
                    'overtime' => $overtime,
                    'period' => "$start s/d $end",
                ]);
            }
        }

        // Payroll Report
        if ($tab === 'payroll') {
            $detailsQuery = \App\Models\PayrollDetail::with(['user.employee.department','payroll'])
                ->when($payrollId, fn($q)=>$q->where('payroll_id',$payrollId))
                ->when(!$payrollId && $start && $end, function($q) use ($start,$end){
                    $q->whereHas('payroll', fn($qq)=>$qq->whereBetween('start_date',[$start,$end]));
                })
                ->when($departmentId, fn($q)=>$q->whereHas('user.employee', fn($qq)=>$qq->where('department_id',$departmentId)));

            foreach ($detailsQuery->get() as $d) {
                $payrollRows->push([
                    'period' => $d->payroll->period ?? '-',
                    'name' => $d->user->name ?? '-',
                    'nik' => $d->user->nik ?? '-',
                    'dept' => $d->user->employee->department->name ?? '-',
                    'basic' => $d->basic_salary,
                    'allowances' => is_array($d->allowances) ? array_sum($d->allowances) : 0,
                    'overtime' => $d->overtime_pay,
                    'bonus_thr' => $d->bonus + $d->thr,
                    'gross' => $d->gross_salary,
                    'deduction' => $d->total_deduction,
                    'net' => $d->net_salary,
                    'status' => $d->payroll->status ?? '-',
                ]);
            }
        }

        // Leave Report
        if ($tab === 'leave') {
            $leaveTypesQuota = \App\Models\LeaveType::where('name','Cuti Tahunan')->first();
            $quota = $leaveTypesQuota->quota_days ?? 12;

            $employees = Employee::with(['user','department'])
                ->when($departmentId, fn($q)=>$q->where('department_id',$departmentId))
                ->where('is_active', true)->get();

            foreach ($employees as $emp) {
                $user = $emp->user;
                if (!$user) continue;
                $leaves = Leave::where('user_id',$user->id)->get();
                $used = $leaves->where('final_status','approved')->sum('total_days');
                $total = $leaves->count();
                $approved = $leaves->where('final_status','approved')->count();
                $pending = $leaves->where('final_status','pending')->count();
                $rejected = $leaves->where('final_status','rejected')->count();

                $leaveRows->push([
                    'name' => $user->name,
                    'nik' => $user->nik ?? '-',
                    'dept' => $emp->department->name ?? '-',
                    'used' => $used,
                    'remaining' => max(0, $quota - $used),
                    'total' => $total,
                    'approved' => $approved,
                    'pending' => $pending,
                    'rejected' => $rejected,
                ]);
            }
        }

        return view('admin.reports.index', compact('tab','departments','payrolls','start','end','departmentId','payrollId','attendanceRows','payrollRows','leaveRows'));
    }

    public function exportAttendance(Request $request)
    {
        $request->merge(['tab'=>'attendance']);
        $data = $this->buildAttendanceRows($request);
        return Excel::download(new AttendanceReportExport($data), 'rekap-absensi-'.date('Y-m-d').'.xlsx');
    }

    public function exportPayroll(Request $request)
    {
        $data = $this->buildPayrollRows($request);
        return Excel::download(new PayrollReportExport($data), 'rekap-gaji-'.date('Y-m-d').'.xlsx');
    }

    public function exportLeave(Request $request)
    {
        $data = $this->buildLeaveRows($request);
        return Excel::download(new LeaveReportExport($data), 'rekap-cuti-'.date('Y-m-d').'.xlsx');
    }

    private function buildAttendanceRows(Request $request){
        $start = $request->query('start_date', Carbon::now()->startOfMonth()->toDateString());
        $end = $request->query('end_date', Carbon::now()->endOfMonth()->toDateString());
        $departmentId = $request->query('department_id');
        $employees = Employee::with(['user','department','position'])
            ->when($departmentId, fn($q)=>$q->where('department_id',$departmentId))
            ->where('is_active', true)->get();
        $rows = collect();
        foreach ($employees as $emp) {
            $user = $emp->user; if (!$user) continue;
            $atts = Attendance::where('user_id',$user->id)->whereBetween('date', [$start,$end])->get();
            $hadir = $atts->whereIn('status',['hadir','terlambat'])->count();
            $terlambat = $atts->where('status','terlambat')->count();
            $rows->push([
                'name'=>$user->name,'nik'=>$user->nik??'-','dept'=>$emp->department->name??'-','position'=>$emp->position->name??'-',
                'hadir'=>$hadir,'terlambat'=>$terlambat,'alpha'=>max(0, Carbon::parse($start)->diffInDays(Carbon::parse($end))+1 - $hadir - Leave::where('user_id',$user->id)->where('final_status','approved')->whereBetween('start_date',[$start,$end])->sum('total_days')),
                'late_minutes'=>$atts->sum('late_minutes'),'overtime'=>$atts->sum('overtime_hours'),'period'=>"$start s/d $end",
            ]);
        }
        return $rows;
    }

    private function buildPayrollRows(Request $request){
        $departmentId = $request->query('department_id');
        $payrollId = $request->query('payroll_id');
        $start = $request->query('start_date');
        $end = $request->query('end_date');
        $q = \App\Models\PayrollDetail::with(['user.employee.department','payroll'])
            ->when($payrollId, fn($qq)=>$qq->where('payroll_id',$payrollId))
            ->when(!$payrollId && $start && $end, fn($qq)=>$qq->whereHas('payroll', fn($qq2)=>$qq2->whereBetween('start_date',[$start,$end])))
            ->when($departmentId, fn($qq)=>$qq->whereHas('user.employee', fn($qq2)=>$qq2->where('department_id',$departmentId)));
        $rows = collect();
        foreach ($q->get() as $d) {
            $rows->push([
                'period'=>$d->payroll->period??'-','name'=>$d->user->name??'-','nik'=>$d->user->nik??'-','dept'=>$d->user->employee->department->name??'-',
                'basic'=>$d->basic_salary,'allowances'=>is_array($d->allowances)?array_sum($d->allowances):0,'overtime'=>$d->overtime_pay,'bonus_thr'=>$d->bonus+$d->thr,
                'gross'=>$d->gross_salary,'deduction'=>$d->total_deduction,'net'=>$d->net_salary,'status'=>$d->payroll->status??'-',
            ]);
        }
        return $rows;
    }

    private function buildLeaveRows(Request $request){
        $departmentId = $request->query('department_id');
        $quota = (\App\Models\LeaveType::where('name','Cuti Tahunan')->first()->quota_days ?? 12);
        $employees = Employee::with(['user','department'])->when($departmentId, fn($q)=>$q->where('department_id',$departmentId))->where('is_active',true)->get();
        $rows = collect();
        foreach ($employees as $emp) {
            $user=$emp->user; if(!$user) continue;
            $leaves=Leave::where('user_id',$user->id)->get();
            $used=$leaves->where('final_status','approved')->sum('total_days');
            $rows->push(['name'=>$user->name,'nik'=>$user->nik??'-','dept'=>$emp->department->name??'-','used'=>$used,'remaining'=>max(0,$quota-$used),'total'=>$leaves->count(),'approved'=>$leaves->where('final_status','approved')->count(),'pending'=>$leaves->where('final_status','pending')->count(),'rejected'=>$leaves->where('final_status','rejected')->count()]);
        }
        return $rows;
    }
}
