<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\OfficeLocation;
use App\Models\Shift;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $query = Attendance::with(['user.employee', 'shift'])
            ->orderByDesc('date')
            ->orderByDesc('check_in');

        if ($request->date) {
            $query->whereDate('date', $request->date);
        } else {
            $query->whereDate('date', Carbon::today());
        }

        $attendances = $query->paginate(20);
        return view('admin.attendances.index', compact('attendances'));
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
                    'photo_in' => $a->photo_in ? asset('storage/' . $a->photo_in) : null,
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
