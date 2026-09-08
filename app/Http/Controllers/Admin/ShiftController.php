<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Shift;
use Illuminate\Http\Request;

class ShiftController extends Controller
{
    public function index()
    {
        $shifts = Shift::withCount('employees')->orderBy('start_time')->get();
        return view('admin.shifts.index', compact('shifts'));
    }

    public function create()
    {
        return view('admin.shifts.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:50|unique:shifts,name',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i',
            'tolerance_late' => 'required|integer|min:0|max:120',
            'is_overnight' => 'nullable|boolean',
            'color' => 'nullable|string|max:20',
        ]);

        Shift::create([
            'name' => $request->name,
            'start_time' => $request->start_time . ':00',
            'end_time' => $request->end_time . ':00',
            'tolerance_late' => $request->tolerance_late,
            'is_overnight' => $request->boolean('is_overnight'),
            'color' => $request->color ?: '#0d6efd',
        ]);

        return redirect()->route('admin.shifts.index')->with('success','Shift berhasil ditambahkan');
    }

    public function edit(Shift $shift)
    {
        return view('admin.shifts.edit', compact('shift'));
    }

    public function update(Request $request, Shift $shift)
    {
        $request->validate([
            'name' => 'required|string|max:50|unique:shifts,name,'.$shift->id,
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i',
            'tolerance_late' => 'required|integer|min:0|max:120',
            'is_overnight' => 'nullable|boolean',
            'color' => 'nullable|string|max:20',
        ]);

        $shift->update([
            'name' => $request->name,
            'start_time' => $request->start_time . ':00',
            'end_time' => $request->end_time . ':00',
            'tolerance_late' => $request->tolerance_late,
            'is_overnight' => $request->boolean('is_overnight'),
            'color' => $request->color ?: '#0d6efd',
        ]);

        return redirect()->route('admin.shifts.index')->with('success','Shift berhasil diperbarui');
    }

    public function destroy(Shift $shift)
    {
        $shift->loadCount('employees');
        if ($shift->employees_count > 0) {
            return back()->withErrors(['msg' => "Tidak bisa hapus: masih ada {$shift->employees_count} karyawan pakai shift ini. Ganti shift karyawan dulu."]);
        }
        $shift->delete();
        return redirect()->route('admin.shifts.index')->with('success','Shift dihapus');
    }
}
