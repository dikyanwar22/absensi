<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OfficeLocation;
use Illuminate\Http\Request;

class OfficeLocationController extends Controller
{
    public function index()
    {
        $locations = OfficeLocation::withCount('employees')->orderByDesc('is_active')->orderBy('name')->get();
        return view('admin.office-locations.index', compact('locations'));
    }

    public function create()
    {
        return view('admin.office-locations.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100|unique:office_locations,name',
            'address' => 'nullable|string|max:500',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'radius_meter' => 'required|integer|min:10|max:1000',
            'is_active' => 'nullable|boolean',
        ]);

        OfficeLocation::create([
            'name' => $request->name,
            'address' => $request->address,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'radius_meter' => $request->radius_meter,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('admin.office-locations.index')->with('success','Lokasi kantor berhasil ditambahkan');
    }

    public function edit(OfficeLocation $officeLocation)
    {
        return view('admin.office-locations.edit', compact('officeLocation'));
    }

    public function update(Request $request, OfficeLocation $officeLocation)
    {
        $request->validate([
            'name' => 'required|string|max:100|unique:office_locations,name,'.$officeLocation->id,
            'address' => 'nullable|string|max:500',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'radius_meter' => 'required|integer|min:10|max:1000',
            'is_active' => 'nullable|boolean',
        ]);

        $officeLocation->update([
            'name' => $request->name,
            'address' => $request->address,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'radius_meter' => $request->radius_meter,
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('admin.office-locations.index')->with('success','Lokasi kantor diperbarui');
    }

    public function destroy(OfficeLocation $officeLocation)
    {
        $officeLocation->loadCount('employees');
        if ($officeLocation->employees_count > 0) {
            return back()->withErrors(['msg' => "Tidak bisa hapus: masih ada {$officeLocation->employees_count} karyawan pakai lokasi ini"]);
        }
        $officeLocation->delete();
        return redirect()->route('admin.office-locations.index')->with('success','Lokasi dihapus');
    }
}
