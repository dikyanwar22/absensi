<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CompanySetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CompanySettingController extends Controller
{
    public function index()
    {
        $company = CompanySetting::get();
        return view('admin.company-settings.index', compact('company'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:150',
            'address' => 'nullable|string|max:500',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:100',
            'website' => 'nullable|string|max:150',
            'logo' => 'nullable|image|mimes:png,jpg,jpeg|max:2048',
            'remove_logo' => 'nullable|boolean',
        ]);

        $company = CompanySetting::get();
        $data = $request->only(['name','address','phone','email','website']);

        // handle logo upload
        if ($request->hasFile('logo')) {
            // hapus lama
            if ($company->logo_path && Storage::disk('public')->exists($company->logo_path)) {
                Storage::disk('public')->delete($company->logo_path);
            }
            $path = $request->file('logo')->store('company', 'public'); // ex: company/xyz.png
            $data['logo_path'] = $path;
        } elseif ($request->boolean('remove_logo')) {
            if ($company->logo_path && Storage::disk('public')->exists($company->logo_path)) {
                Storage::disk('public')->delete($company->logo_path);
            }
            $data['logo_path'] = null;
        }

        $company->update($data);

        return back()->with('success', 'Pengaturan perusahaan diperbarui. Slip gaji sekarang menampilkan logo/nama/alamat baru.');
    }
}
