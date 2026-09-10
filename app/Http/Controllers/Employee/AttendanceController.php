<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\OfficeLocation;
use App\Models\Employee;
use App\Helpers\GeoHelper;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class AttendanceController extends Controller
{
    public function home()
    {
        $user = auth()->user();
        $today = Carbon::today()->toDateString();
        $attendance = Attendance::where('user_id', $user->id)->whereDate('date', $today)->first();
        $employee = Employee::with(['shift', 'officeLocation', 'department', 'position'])->where('user_id', $user->id)->first();
        $office = $employee?->officeLocation ?? OfficeLocation::where('is_active', true)->first();

        return view('employee.home', compact('attendance', 'employee', 'office'));
    }

    public function history(Request $request)
    {
        $user = auth()->user();

        // Default 01 - 31 bulan ini (tidak berat jika sudah banyak data)
        $start = $request->query('start_date') ?: Carbon::now()->startOfMonth()->toDateString();
        $end = $request->query('end_date') ?: Carbon::now()->endOfMonth()->toDateString();

        // Validasi format & range
        try {
            $startCarbon = Carbon::parse($start);
            $endCarbon = Carbon::parse($end);
            if ($startCarbon->gt($endCarbon)) {
                [$start, $end] = [$end, $start];
            }
        } catch (\Throwable $e) {
            $start = Carbon::now()->startOfMonth()->toDateString();
            $end = Carbon::now()->endOfMonth()->toDateString();
        }

        $attendances = Attendance::where('user_id', $user->id)
            ->whereBetween('date', [$start, $end])
            ->orderByDesc('date')
            ->paginate(20)
            ->withQueryString();

        return view('employee.history', compact('attendances','start','end'));
    }

    /**
     * Check-In dengan validasi lat/lng + Haversine + foto selfie
     * app/Http/Controllers/Employee/AttendanceController.php:40
     */
    public function checkIn(Request $request)
    {
        $request->validate([
            'lat' => 'required|numeric|between:-90,90',
            'lng' => 'required|numeric|between:-180,180',
            'accuracy' => 'nullable|integer',
            'is_mocked' => 'nullable|boolean',
            'photo_base64' => 'nullable|string',
        ]);

        $user = auth()->user();
        $today = Carbon::today()->toDateString();

        // Cek sudah absen masuk? (pakai whereDate agar kompatibel SQLite/MySQL)
        $existing = Attendance::where('user_id', $user->id)->whereDate('date', $today)->first();
        if ($existing && $existing->check_in) {
            return response()->json(['message' => 'Anda sudah absen masuk hari ini jam ' . Carbon::parse($existing->check_in)->format('H:i')], 422);
        }

        // Anti Fake GPS
        if ($request->boolean('is_mocked')) {
            return response()->json(['message' => 'Fake GPS terdeteksi! Absen ditolak.'], 422);
        }

        // Validasi radius geofence
        $employee = Employee::with('officeLocation')->where('user_id', $user->id)->first();
        $office = $employee?->officeLocation ?? OfficeLocation::where('is_active', true)->first();
        if (!$office) {
            return response()->json(['message' => 'Lokasi kantor belum di-set oleh HRD'], 422);
        }

        $distance = GeoHelper::distance((float)$request->lat, (float)$request->lng, (float)$office->latitude, (float)$office->longitude);
        if ($distance > $office->radius_meter) {
            return response()->json([
                'message' => "Di luar jangkauan kantor! Jarak " . round($distance) . "m, maks {$office->radius_meter}m dari {$office->name}",
                'distance' => round($distance),
                'max' => $office->radius_meter,
            ], 422);
        }

        if ($request->accuracy && $request->accuracy > 50) {
            // warning tapi tetap lanjut, bisa jadi diperketat jadi reject jika >100
        }

        // Hitung terlambat berdasarkan shift
        $shift = $employee?->shift;
        $status = 'hadir';
        $lateMinutes = 0;
        if ($shift) {
            $shiftStart = Carbon::parse($today . ' ' . $shift->start_time);
            $now = Carbon::now();
            $tolerance = $shift->tolerance_late ?? 15;
            $deadline = $shiftStart->copy()->addMinutes($tolerance);
            if ($now->gt($deadline)) {
                $status = 'terlambat';
                $lateMinutes = $deadline->diffInMinutes($now);
            }
        }

        // Simpan foto selfie (base64) ke public/uploads/attendances
        $photoPath = null;
        if ($request->photo_base64) {
            $base64 = $request->photo_base64;
            // Remove data:image/jpeg;base64,
            if (str_contains($base64, ',')) {
                $base64 = explode(',', $base64)[1];
            }
            $imageData = base64_decode($base64);
            if ($imageData) {
                $filename = 'attendances/' . $user->id . '_' . $today . '_in.jpg';
                $fullPath = public_path('uploads/' . $filename);
                $dir = dirname($fullPath);
                if (!file_exists($dir)) mkdir($dir, 0755, true);
                file_put_contents($fullPath, $imageData);
                $photoPath = $filename;
            }
        }

        // INSERT murni — tidak UPDATE. Jika tidak ada data, insert 1 row/hari. Untuk 1000 user = 1000 insert/hari, sangat ringan (indexed by UNIQUE user_id+date)
        // Jika race condition (2 request bersamaan), UNIQUE akan throw exception, tangkap sebagai "sudah absen"
        try {
            $attendance = Attendance::create([
                'user_id' => $user->id,
                'date' => $today,
                'shift_id' => $shift?->id,
                'check_in' => Carbon::now(),
                'lat_in' => $request->lat,
                'lng_in' => $request->lng,
                'accuracy_in' => $request->accuracy,
                'distance_in_meter' => round($distance),
                'photo_in' => $photoPath,
                'status' => $status,
                'late_minutes' => $lateMinutes,
                'is_fake_gps' => $request->boolean('is_mocked'),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
        } catch (\Illuminate\Database\QueryException $e) {
            // Jika sudah ter-insert oleh request lain bersamaan (UNIQUE violation code 23000)
            if (str_contains($e->getMessage(), 'UNIQUE') || $e->getCode() === '23000') {
                return response()->json(['message' => 'Anda sudah absen masuk hari ini (duplikat terdeteksi)'], 422);
            }
            throw $e;
        }

        return response()->json([
            'message' => $status === 'terlambat' ? "Absen masuk berhasil! Terlambat {$lateMinutes} menit" : 'Absen masuk berhasil! Hadir tepat waktu',
            'data' => $attendance,
            'distance' => round($distance),
            'status' => $status,
        ]);
    }

    public function checkOut(Request $request)
    {
        $request->validate([
            'lat' => 'required|numeric|between:-90,90',
            'lng' => 'required|numeric|between:-180,180',
            'accuracy' => 'nullable|integer',
            'is_mocked' => 'nullable|boolean',
            'photo_base64' => 'nullable|string',
        ]);

        $user = auth()->user();
        $today = Carbon::today()->toDateString();
        $attendance = Attendance::where('user_id', $user->id)->whereDate('date', $today)->first();

        if (!$attendance || !$attendance->check_in) {
            return response()->json(['message' => 'Anda belum absen masuk hari ini'], 422);
        }
        if ($attendance->check_out) {
            return response()->json(['message' => 'Anda sudah absen pulang hari ini jam ' . Carbon::parse($attendance->check_out)->format('H:i')], 422);
        }
        if ($request->boolean('is_mocked')) {
            return response()->json(['message' => 'Fake GPS terdeteksi!'], 422);
        }

        $employee = Employee::with('officeLocation')->where('user_id', $user->id)->first();
        $office = $employee?->officeLocation ?? OfficeLocation::where('is_active', true)->first();
        $distance = $office ? GeoHelper::distance((float)$request->lat, (float)$request->lng, (float)$office->latitude, (float)$office->longitude) : 0;
        if ($office && $distance > $office->radius_meter) {
            return response()->json(['message' => "Di luar jangkauan kantor untuk absen pulang! Jarak " . round($distance) . "m"], 422);
        }

        // Foto pulang ke public/uploads/attendances
        $photoPath = null;
        if ($request->photo_base64) {
            $base64 = $request->photo_base64;
            if (str_contains($base64, ',')) $base64 = explode(',', $base64)[1];
            $imageData = base64_decode($base64);
            if ($imageData) {
                $filename = 'attendances/' . $user->id . '_' . $today . '_out.jpg';
                $fullPath = public_path('uploads/' . $filename);
                $dir = dirname($fullPath);
                if (!file_exists($dir)) mkdir($dir, 0755, true);
                file_put_contents($fullPath, $imageData);
                $photoPath = $filename;
            }
        }

        // Hitung lembur jika shift ada
        $overtime = 0;
        if ($employee?->shift) {
            $shiftEnd = Carbon::parse($today . ' ' . $employee->shift->end_time);
            if ($employee->shift->is_overnight && Carbon::now()->hour < 12) {
                $shiftEnd->addDay();
            }
            if (Carbon::now()->gt($shiftEnd)) {
                $overtime = $shiftEnd->diffInMinutes(Carbon::now()) / 60;
            }
        }

        $attendance->update([
            'check_out' => Carbon::now(),
            'lat_out' => $request->lat,
            'lng_out' => $request->lng,
            'accuracy_out' => $request->accuracy,
            'distance_out_meter' => round($distance),
            'photo_out' => $photoPath,
            'overtime_hours' => round($overtime, 2),
            'ip_address' => $request->ip(),
        ]);

        return response()->json(['message' => 'Absen pulang berhasil!', 'data' => $attendance]);
    }
}
