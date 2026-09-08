<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Department;
use App\Models\Position;
use App\Models\Shift;
use App\Models\OfficeLocation;
use App\Models\LeaveType;
use App\Models\Employee;
use Illuminate\Support\Facades\Hash;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        // Departments
        $it = Department::create(['name' => 'IT', 'description' => 'Teknologi Informasi']);
        $hrd = Department::create(['name' => 'HRD', 'description' => 'Human Resource']);
        $prod = Department::create(['name' => 'Produksi', 'description' => 'Produksi']);

        // Positions
        $staff = Position::create(['department_id' => $it->id, 'name' => 'Staff IT', 'basic_salary_default' => 5000000]);
        $spv = Position::create(['department_id' => $it->id, 'name' => 'Supervisor IT', 'basic_salary_default' => 8000000]);

        // Shifts - 3 shift sesuai PRD
        Shift::create(['name' => 'Pagi', 'start_time' => '07:00:00', 'end_time' => '15:00:00', 'tolerance_late' => 15, 'is_overnight' => false, 'color' => '#198754']);
        Shift::create(['name' => 'Siang', 'start_time' => '14:00:00', 'end_time' => '22:00:00', 'tolerance_late' => 15, 'is_overnight' => false, 'color' => '#ffc107']);
        Shift::create(['name' => 'Malam', 'start_time' => '22:00:00', 'end_time' => '06:00:00', 'tolerance_late' => 15, 'is_overnight' => true, 'color' => '#6f42c1']);

        // Office Location dengan lat/lng
        $office = OfficeLocation::create([
            'name' => 'Kantor Pusat',
            'address' => 'Jl. Contoh No.1 Jakarta',
            'latitude' => -6.20880000,
            'longitude' => 106.84560000,
            'radius_meter' => 100,
            'is_active' => true,
        ]);

        // Leave Types
        LeaveType::create(['name' => 'Cuti Tahunan', 'quota_days' => 12, 'is_paid' => true, 'requires_document' => false]);
        LeaveType::create(['name' => 'Sakit', 'quota_days' => 0, 'is_paid' => true, 'requires_document' => true]);
        LeaveType::create(['name' => 'Izin', 'quota_days' => 0, 'is_paid' => false, 'requires_document' => false]);

        // Users
        $hrdUser = User::create(['name' => 'HRD Admin', 'nik' => 'HRD001', 'email' => 'hrd@example.com', 'role' => 'hrd', 'password' => Hash::make('password')]);
        $spvUser = User::create(['name' => 'Supervisor IT', 'nik' => 'SPV001', 'email' => 'spv@example.com', 'role' => 'supervisor', 'password' => Hash::make('password')]);
        $staffUser = User::create(['name' => 'Budi Karyawan', 'nik' => 'STF001', 'email' => 'budi@example.com', 'role' => 'staff', 'password' => Hash::make('password')]);

        // Employees
        Employee::create(['user_id' => $hrdUser->id, 'department_id' => $hrd->id, 'position_id' => $spv->id, 'shift_id' => 1, 'office_location_id' => $office->id, 'employee_code' => 'EMP-HRD001', 'join_date' => '2023-01-01', 'employment_status' => 'tetap', 'is_active' => true]);
        Employee::create(['user_id' => $spvUser->id, 'department_id' => $it->id, 'position_id' => $spv->id, 'shift_id' => 1, 'office_location_id' => $office->id, 'employee_code' => 'EMP-SPV001', 'join_date' => '2023-02-01', 'employment_status' => 'tetap', 'is_active' => true]);
        Employee::create(['user_id' => $staffUser->id, 'department_id' => $it->id, 'position_id' => $staff->id, 'shift_id' => 1, 'office_location_id' => $office->id, 'employee_code' => 'EMP-STF001', 'join_date' => '2024-01-15', 'employment_status' => 'kontrak', 'contract_end_date' => '2026-12-31', 'is_active' => true]);

        // Assign Spatie Roles
        \Spatie\Permission\Models\Role::create(['name' => 'hrd']);
        \Spatie\Permission\Models\Role::create(['name' => 'supervisor']);
        \Spatie\Permission\Models\Role::create(['name' => 'staff']);
        $hrdUser->assignRole('hrd');
        $spvUser->assignRole('supervisor');
        $staffUser->assignRole('staff');
    }
}
