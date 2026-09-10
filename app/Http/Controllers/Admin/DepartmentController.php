<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    public function index(Request $request)
    {
        $query = Department::withCount(['employees','positions'])->orderBy('name');

        if ($search = $request->query('q')) {
            $query->where(function($q) use ($search){
                $q->where('name','like',"%{$search}%")
                  ->orWhere('description','like',"%{$search}%");
            });
        }

        $departments = $query->get();
        return view('admin.departments.index', compact('departments'));
    }

    public function create()
    {
        return view('admin.departments.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100|unique:departments,name',
            'description' => 'nullable|string|max:500',
        ]);

        Department::create($request->only('name','description'));

        return redirect()->route('admin.departments.index')->with('success','Departemen berhasil ditambahkan');
    }

    public function edit(Department $department)
    {
        $department->loadCount(['employees','positions']);
        return view('admin.departments.edit', compact('department'));
    }

    public function update(Request $request, Department $department)
    {
        $request->validate([
            'name' => 'required|string|max:100|unique:departments,name,'.$department->id,
            'description' => 'nullable|string|max:500',
        ]);

        $department->update($request->only('name','description'));

        return redirect()->route('admin.departments.index')->with('success','Departemen berhasil diperbarui');
    }

    public function destroy(Department $department)
    {
        $department->loadCount(['employees','positions']);
        if ($department->employees_count > 0) {
            return back()->withErrors(['msg' => "Tidak bisa hapus: masih ada {$department->employees_count} karyawan di departemen ini. Pindahkan dulu."]);
        }
        if ($department->positions_count > 0) {
            return back()->withErrors(['msg' => "Tidak bisa hapus: masih ada {$department->positions_count} jabatan di departemen ini."]);
        }
        $department->delete();
        return redirect()->route('admin.departments.index')->with('success','Departemen dihapus');
    }
}
