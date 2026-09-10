<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Department;
use App\Models\OfficeLocation;
use App\Models\Position;
use App\Models\Shift;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\AttendanceListExport;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $query = Attendance::with(['user.employee.department', 'user.employee.position', 'shift'])
            ->orderByDesc('date')
            ->orderByDesc('check_in');

        // Filter by NIK / email / nama (q)
        if ($q = $request->query('q')) {
            $query->whereHas('user', function ($uq) use ($q) {
                $uq->where('name', 'like', "%{$q}%")
                   ->orWhere('nik', 'like', "%{$q}%")
                   ->orWhere('email', 'like', "%{$q}%");
            });
        }

        // Filter departemen
        if ($deptId = $request->query('department_id')) {
            $query->whereHas('user.employee', fn($qq) => $qq->where('department_id', $deptId));
        }

        // Filter jabatan
        if ($posId = $request->query('position_id')) {
            $query->whereHas('user.employee', fn($qq) => $qq->where('position_id', $posId));
        }

        // Filter jarak >10m
        if ($distance = $request->query('distance')) {
            if ($distance === '>10') {
                $query->where('distance_in_meter', '>', 10);
            } elseif ($distance === '<=10') {
                $query->where('distance_in_meter', '<=', 10);
            } elseif ($distance === '>50') {
                $query->where('distance_in_meter', '>', 50);
            }
        }

        // Filter status hadir / tidak hadir
        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        // Filter shift pagi/siang/malam (by name atau id)
        if ($shiftId = $request->query('shift_id')) {
            $query->where('shift_id', $shiftId);
        }

        // Filter tanggal: support 1-31, custom range, atau single date
        $start = $request->query('start_date');
        $end = $request->query('end_date');
        $singleDate = $request->query('date');
        if ($start && $end) {
            try {
                $s = Carbon::parse($start)->toDateString();
                $e = Carbon::parse($end)->toDateString();
                if (Carbon::parse($s)->gt(Carbon::parse($e))) [$s,$e]=[$e,$s];
                $query->whereBetween('date', [$s, $e]);
            } catch (\Throwable $e) {}
        } elseif ($start) {
            $query->whereDate('date', Carbon::parse($start)->toDateString());
        } elseif ($singleDate) {
            $query->whereDate('date', Carbon::parse($singleDate)->toDateString());
        }

        // Default jangan tampilkan semua (hindari 1000 data lelet) → hanya tampil saat ada filter
        $hasFilter = $request->filled('q') || $request->filled('department_id') || $request->filled('position_id') || $request->filled('shift_id') || $request->filled('status') || $request->filled('distance') || $request->filled('start_date') || $request->filled('end_date') || $request->filled('date');
        if (!$hasFilter) {
            $attendances = collect();
        } else {
            $attendances = $query->get();
        }

        $departments = Department::orderBy('name')->get();
        $positions = Position::with('department')->orderBy('name')->get();
        $shifts = Shift::orderBy('start_time')->get();

        return view('admin.attendances.index', compact('attendances','departments','positions','shifts'));
    }

    public function export(Request $request)
    {
        $query = Attendance::with(['user.employee.department', 'user.employee.position', 'shift'])
            ->orderBy('date')->orderBy('check_in');

        if ($q = $request->query('q')) {
            $query->whereHas('user', function ($uq) use ($q) {
                $uq->where('name','like',"%{$q}%")->orWhere('nik','like',"%{$q}%")->orWhere('email','like',"%{$q}%");
            });
        }
        if ($deptId = $request->query('department_id')) {
            $query->whereHas('user.employee', fn($qq)=>$qq->where('department_id',$deptId));
        }
        if ($posId = $request->query('position_id')) {
            $query->whereHas('user.employee', fn($qq)=>$qq->where('position_id',$posId));
        }
        if ($distance = $request->query('distance')) {
            if ($distance === '>10') $query->where('distance_in_meter','>',10);
            elseif ($distance === '<=10') $query->where('distance_in_meter','<=',10);
            elseif ($distance === '>50') $query->where('distance_in_meter','>',50);
        }
        if ($status = $request->query('status')) {
            $query->where('status',$status);
        }
        if ($shiftId = $request->query('shift_id')) {
            $query->where('shift_id',$shiftId);
        }
        $start = $request->query('start_date');
        $end = $request->query('end_date');
        $singleDate = $request->query('date');
        if ($start && $end) {
            try { $s=Carbon::parse($start)->toDateString(); $e=Carbon::parse($end)->toDateString(); if(Carbon::parse($s)->gt(Carbon::parse($e))) [$s,$e]=[$e,$s]; $query->whereBetween('date',[$s,$e]); } catch(\Throwable $e){}
        } elseif ($start) {
            $query->whereDate('date', Carbon::parse($start)->toDateString());
        } elseif ($singleDate) {
            $query->whereDate('date', Carbon::parse($singleDate)->toDateString());
        }

        $attendances = $query->get();
        $filename = 'absensi-'.($start && $end ? $start.'_sd_'.$end : ($start ?? $singleDate ?? date('Y-m-d'))).'.xlsx';
        return Excel::download(new AttendanceListExport($attendances), $filename);
    }

    public function liveMap()
    {
        $offices = OfficeLocation::where('is_active', true)->get();
        return view('admin.live-map', compact('offices'));
    }

    /**
     * JSON untuk Leaflet Live Map - app/Http/Controllers/Admin/AttendanceController.php:40
     */
    public function edit(Attendance $attendance)
    {
        $attendance->load(['user.employee','shift']);
        $shifts = Shift::orderBy('name')->get();
        return view('admin.attendances.edit', compact('attendance','shifts'));
    }

    public function update(Request $request, Attendance $attendance)
    {
        $request->validate([
            'date' => 'required|date',
            'shift_id' => 'nullable|exists:shifts,id',
            'check_in' => 'nullable|date_format:Y-m-d\TH:i',
            'check_out' => 'nullable|date_format:Y-m-d\TH:i|after_or_equal:check_in',
            'status' => 'required|in:hadir,terlambat,pulang_cepat,alpha',
            'late_minutes' => 'nullable|integer|min:0|max:1440',
            'overtime_hours' => 'nullable|numeric|min:0|max:24',
            'lat_in' => 'nullable|numeric|between:-90,90',
            'lng_in' => 'nullable|numeric|between:-180,180',
            'lat_out' => 'nullable|numeric|between:-90,90',
            'lng_out' => 'nullable|numeric|between:-180,180',
            'accuracy_in' => 'nullable|integer|min:0',
            'accuracy_out' => 'nullable|integer|min:0',
            'is_fake_gps' => 'nullable|boolean',
        ]);

        $checkIn = $request->check_in ? Carbon::parse($request->check_in) : null;
        $checkOut = $request->check_out ? Carbon::parse($request->check_out) : null;

        // auto hitung late jika status terlambat tapi late_minutes kosong -> hitung dari shift
        $lateMinutes = $request->late_minutes;
        if ($request->status === 'terlambat' && $lateMinutes === null && $checkIn && $attendance->shift_id) {
            $shift = Shift::find($request->shift_id ?? $attendance->shift_id);
            if ($shift) {
                $shiftStart = Carbon::parse($request->date.' '.$shift->start_time);
                $deadline = $shiftStart->copy()->addMinutes($shift->tolerance_late ?? 15);
                if ($checkIn->gt($deadline)) {
                    $lateMinutes = $deadline->diffInMinutes($checkIn);
                } else {
                    $lateMinutes = 0;
                }
            }
        }

        $attendance->update([
            'date' => $request->date,
            'shift_id' => $request->shift_id,
            'check_in' => $checkIn,
            'check_out' => $checkOut,
            'status' => $request->status,
            'late_minutes' => $lateMinutes ?? $request->late_minutes ?? 0,
            'overtime_hours' => $request->overtime_hours ?? 0,
            'lat_in' => $request->lat_in,
            'lng_in' => $request->lng_in,
            'lat_out' => $request->lat_out,
            'lng_out' => $request->lng_out,
            'accuracy_in' => $request->accuracy_in,
            'accuracy_out' => $request->accuracy_out,
            'is_fake_gps' => $request->boolean('is_fake_gps'),
        ]);

        return redirect()->route('admin.attendances.index', ['date' => $request->date])->with('success','Jam absen berhasil diperbarui oleh HRD');
    }

    public function liveMapData(Request $request)
    {
        $date = $request->get('date', Carbon::today()->toDateString());
        $attendances = Attendance::with(['user.employee.department'])
            ->whereDate('date', $date)
            ->whereNotNull('lat_in')
            ->get()
            ->map(function ($a) {
                return [
                    'id' => $a->id,
                    'name' => $a->user->name,
                    'nik' => $a->user->nik,
                    'department' => $a->user->employee->department->name ?? '-',
                    'lat' => (float) $a->lat_in,
                    'lng' => (float) $a->lng_in,
                    'lat_out' => $a->lat_out ? (float) $a->lat_out : null,
                    'lng_out' => $a->lng_out ? (float) $a->lng_out : null,
                    'status' => $a->status,
                    'check_in' => $a->check_in ? Carbon::parse($a->check_in)->format('H:i') : '-',
                    'check_out' => $a->check_out ? Carbon::parse($a->check_out)->format('H:i') : '-',
                    'distance' => $a->distance_in_meter,
                    'photo_in' => $a->photo_in ? asset('uploads/' . $a->photo_in) : null,
                    'is_fake_gps' => $a->is_fake_gps,
                ];
            });

        // Belum absen hari ini
        $allStaffIds = \App\Models\User::where('role', 'staff')->pluck('id');
        $hadirIds = Attendance::whereDate('date', $date)->pluck('user_id');
        $belumAbsen = \App\Models\User::with('employee.department')
            ->whereIn('id', $allStaffIds->diff($hadirIds))
            ->get()
            ->map(fn($u) => [
                'name' => $u->name,
                'department' => $u->employee->department->name ?? '-',
            ]);

        return response()->json([
            'date' => $date,
            'attendances' => $attendances,
            'belum_absen' => $belumAbsen,
            'offices' => OfficeLocation::where('is_active', true)->get(['name','latitude','longitude','radius_meter']),
        ]);
    }
}
