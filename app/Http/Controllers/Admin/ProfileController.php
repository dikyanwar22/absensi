<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    public function index()
    {
        $user = auth()->user()->load(['employee.department','employee.position','employee.shift']);
        $employee = $user->employee;
        return view('admin.profile.index', compact('user','employee'));
    }

    public function update(Request $request)
    {
        $user = auth()->user();
        $employee = $user->employee;

        $request->validate([
            'name' => ['required','string','max:255'],
            'nik' => ['nullable','string','max:20', Rule::unique('users','nik')->ignore($user->id)],
            'email' => ['required','email','max:255', Rule::unique('users','email')->ignore($user->id)],
            'phone' => ['nullable','string','max:20'],
            'address' => ['nullable','string','max:500'],
            'photo' => ['nullable','image','mimes:jpeg,jpg,png,webp','max:2048'],
        ]);

        $user->name = $request->name;
        $user->nik = $request->nik;
        if ($user->email !== $request->email) {
            $user->email = $request->email;
            $user->email_verified_at = null;
        }
        $user->save();

        if (!$employee) {
            $employee = new \App\Models\Employee();
            $employee->user_id = $user->id;
            $employee->employee_code = 'EMP'.str_pad($user->id,4,'0',STR_PAD_LEFT);
            $employee->join_date = now()->toDateString();
        }
        $employee->phone = $request->phone;
        $employee->address = $request->address;

        if ($request->hasFile('photo')) {
            if ($employee->photo && Storage::disk('public')->exists($employee->photo)) {
                Storage::disk('public')->delete($employee->photo);
            }
            $employee->photo = $request->file('photo')->store('photos/employees','public');
            $employee->save();
        } else {
            $employee->save();
        }

        // password update opsional via field terpisah
        if ($request->filled('password')) {
            $request->validate(['password'=>'required|string|min:6|max:50|confirmed']);
            $user->password = Hash::make($request->password);
            $user->save();
        }

        return back()->with('success','Profil berhasil diperbarui');
    }
}
