<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Position;
use App\Models\Department;
use Illuminate\Http\Request;

class PositionController extends Controller
{
    public function index(Request $request)
    {
        $query = Position::with(['department'])->withCount('employees')->orderBy('name');

        if ($search = $request->query('q')) {
            $query->where('name','like',"%{$search}%")
                  ->orWhereHas('department', fn($q)=>$q->where('name','like',"%{$search}%"));
        }
        if ($dept = $request->query('department_id')) {
            $query->where('department_id', $dept);
        }

        $positions = $query->paginate(15)->withQueryString();
        $departments = Department::orderBy('name')->get();
        return view('admin.positions.index', compact('positions','departments'));
    }

    public function create()
    {
        $departments = Department::orderBy('name')->get();
        if ($departments->isEmpty()) {
            return redirect()->route('admin.departments.create')->withErrors(['msg' => 'Buat Departemen dulu sebelum tambah Jabatan']);
        }
        return view('admin.positions.create', compact('departments'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'department_id' => 'required|exists:departments,id',
            'name' => 'required|string|max:100',
            'basic_salary_default' => 'required|numeric|min:0|max:999999999',
        ]);

        Position::create([
            'department_id' => $request->department_id,
            'name' => $request->name,
            'basic_salary_default' => $request->basic_salary_default,
        ]);

        return redirect()->route('admin.positions.index')->with('success','Jabatan berhasil ditambahkan');
    }

    public function edit(Position $position)
    {
        $departments = Department::orderBy('name')->get();
        return view('admin.positions.edit', compact('position','departments'));
    }

    public function update(Request $request, Position $position)
    {
        $request->validate([
            'department_id' => 'required|exists:departments,id',
            'name' => 'required|string|max:100',
            'basic_salary_default' => 'required|numeric|min:0|max:999999999',
        ]);

        $position->update($request->only('department_id','name','basic_salary_default'));

        return redirect()->route('admin.positions.index')->with('success','Jabatan berhasil diperbarui');
    }

    public function destroy(Position $position)
    {
        $position->loadCount('employees');
        if ($position->employees_count > 0) {
            return back()->withErrors(['msg' => "Tidak bisa hapus: masih ada {$position->employees_count} karyawan dengan jabatan ini"]);
        }
        $position->delete();
        return redirect()->route('admin.positions.index')->with('success','Jabatan dihapus');
    }
}
