<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DeductionType;
use Illuminate\Http\Request;

class DeductionTypeController extends Controller
{
    public function index()
    {
        $deductions = DeductionType::with('creator')->orderBy('name')->withTrashed()->get();
        // pisahkan aktif & nonaktif untuk UI
        return view('admin.deduction-types.index', compact('deductions'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100|unique:deduction_types,name',
            'default_amount' => 'required|numeric|min:0|max:9999999999',
            'description' => 'nullable|string|max:500',
        ]);

        DeductionType::create([
            'name' => trim($request->name),
            'default_amount' => $request->default_amount,
            'is_active' => true,
            'description' => $request->description,
            'created_by' => auth()->id(),
        ]);

        return back()->with('success', "Potongan '{$request->name}' berhasil ditambah. Akan otomatis terpotong saat Generate Gaji berikutnya.");
    }

    public function update(Request $request, DeductionType $deductionType)
    {
        $request->validate([
            'name' => 'required|string|max:100|unique:deduction_types,name,' . $deductionType->id,
            'default_amount' => 'required|numeric|min:0|max:9999999999',
            'description' => 'nullable|string|max:500',
            'is_active' => 'nullable|boolean',
        ]);

        $deductionType->update([
            'name' => trim($request->name),
            'default_amount' => $request->default_amount,
            'description' => $request->description,
            'is_active' => $request->has('is_active') ? (bool)$request->is_active : $deductionType->is_active,
        ]);

        // jika is_active di-uncheck via edit, tetap handle
        if ($request->has('is_active_toggle')) {
            $deductionType->update(['is_active' => (bool)$request->is_active_toggle]);
        }

        return back()->with('success', "Potongan '{$deductionType->name}' diperbarui. Perubahan hanya berlaku untuk periode gaji berikutnya, slip lama tidak berubah.");
    }

    public function toggle(DeductionType $deductionType)
    {
        $deductionType->update(['is_active' => !$deductionType->is_active]);
        $msg = $deductionType->is_active ? 'diaktifkan' : 'dinonaktifkan (soft delete)';
        return back()->with('success', "Potongan '{$deductionType->name}' $msg. History slip bulan lalu tetap aman.");
    }

    public function destroy(DeductionType $deductionType)
    {
        // Soft delete: nonaktifkan + softDelete agar tidak muncul di generate berikutnya
        // Cek apakah sudah pernah terpakai di payroll_deduction_items
        $used = $deductionType->items()->exists();
        $deductionType->update(['is_active' => false]);
        $deductionType->delete(); // soft delete

        $msg = $used
            ? "Potongan '{$deductionType->name}' dinonaktifkan & diarsipkan (soft delete). History slip tetap ada, tidak terhapus."
            : "Potongan '{$deductionType->name}' dihapus (soft delete).";
        return back()->with('success', $msg);
    }

    public function restore($id)
    {
        $dt = DeductionType::withTrashed()->findOrFail($id);
        $dt->restore();
        $dt->update(['is_active' => true]);
        return back()->with('success', "Potongan '{$dt->name}' dipulihkan & diaktifkan kembali.");
    }
}
