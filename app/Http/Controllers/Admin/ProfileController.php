<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

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
            $employee->join_date = now()->toDateString();
        }
        $employee->phone = $request->phone;
        $employee->address = $request->address;

        if ($request->hasFile('photo')) {
            if ($employee->photo) {
                $oldPath = public_path('uploads/' . $employee->photo);
                if (file_exists($oldPath)) unlink($oldPath);
                $altOld = public_path($employee->photo);
                if (file_exists($altOld) && str_starts_with($employee->photo, 'uploads/')) unlink($altOld);
            }
            $file = $request->file('photo');
            $filename = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
            $dir = public_path('uploads/photos/employees');
            if (!file_exists($dir)) mkdir($dir, 0755, true);
            $file->move($dir, $filename);
            $employee->photo = 'photos/employees/' . $filename;
            $employee->save();
        } else {
            $employee->save();
        }

        // password update opsional via field terpisah - tanpa batasan
        if ($request->filled('password')) {
            $request->validate(['password'=>'required|string|confirmed']);
            $user->password = Hash::make($request->password);
            $user->save();
        }

        return back()->with('success','Profil berhasil diperbarui');
    }
}
