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
        // Spatie Roles - buat dulu agar assignRole tidak error, gunakan firstOrCreate
        $roleHrd = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'hrd']);
        $roleSpv = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'supervisor']);
        $roleStaff = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'staff']);

        // Departments (idempotent)
        $it = Department::firstOrCreate(['name' => 'IT'], ['description' => 'Teknologi Informasi']);
        $hrdDept = Department::firstOrCreate(['name' => 'HRD'], ['description' => 'Human Resource']);
        $prod = Department::firstOrCreate(['name' => 'Produksi'], ['description' => 'Produksi']);

        // Positions (firstOrCreate by name + department)
        $staffIt = Position::firstOrCreate(['name' => 'Staff IT', 'department_id' => $it->id], ['basic_salary_default' => 5000000]);
        $spvIt = Position::firstOrCreate(['name' => 'Supervisor IT', 'department_id' => $it->id], ['basic_salary_default' => 8000000]);
        $staffHrd = Position::firstOrCreate(['name' => 'Staff HRD', 'department_id' => $hrdDept->id], ['basic_salary_default' => 6000000]);
        $managerHrd = Position::firstOrCreate(['name' => 'Manager HRD', 'department_id' => $hrdDept->id], ['basic_salary_default' => 10000000]);
        $staffProd = Position::firstOrCreate(['name' => 'Staff Produksi', 'department_id' => $prod->id], ['basic_salary_default' => 4500000]);

        // Shifts - 3 shift sesuai PRD
        $shiftPagi = Shift::firstOrCreate(['name' => 'Pagi'], ['start_time' => '07:00:00', 'end_time' => '15:00:00', 'tolerance_late' => 15, 'is_overnight' => false, 'color' => '#198754']);
        Shift::firstOrCreate(['name' => 'Siang'], ['start_time' => '14:00:00', 'end_time' => '22:00:00', 'tolerance_late' => 15, 'is_overnight' => false, 'color' => '#ffc107']);
        Shift::firstOrCreate(['name' => 'Malam'], ['start_time' => '22:00:00', 'end_time' => '06:00:00', 'tolerance_late' => 15, 'is_overnight' => true, 'color' => '#6f42c1']);

        // Office Location dengan lat/lng
        $office = OfficeLocation::firstOrCreate(
            ['name' => 'Kantor Pusat'],
            ['address' => 'Jl. Contoh No.1 Jakarta', 'latitude' => -6.20880000, 'longitude' => 106.84560000, 'radius_meter' => 100, 'is_active' => true]
        );

        // Leave Types
        LeaveType::firstOrCreate(['name' => 'Cuti Tahunan'], ['quota_days' => 12, 'is_paid' => true, 'requires_document' => false]);
        LeaveType::firstOrCreate(['name' => 'Sakit'], ['quota_days' => 0, 'is_paid' => true, 'requires_document' => true]);
        LeaveType::firstOrCreate(['name' => 'Izin'], ['quota_days' => 0, 'is_paid' => false, 'requires_document' => false]);
        LeaveType::firstOrCreate(['name' => 'Cuti Penting'], ['quota_days' => 3, 'is_paid' => true, 'requires_document' => false]);

        // Helper buat user + employee (1 login semua role tetap punya employee agar bisa absen/cuti)
        $makeUser = function (array $userData, array $empData) use ($office, $shiftPagi) {
            $user = User::firstOrCreate(
                ['email' => $userData['email']],
                [
                    'name' => $userData['name'],
                    'nik' => $userData['nik'],
                    'role' => $userData['role'],
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                ]
            );
            // update nik/role jika sudah ada tapi beda
            if ($user->nik !== $userData['nik'] || $user->role !== $userData['role']) {
                $user->update(['nik' => $userData['nik'], 'role' => $userData['role']]);
            }
            // assign spatie role
            if (!$user->hasRole($userData['role'])) {
                $user->syncRoles([$userData['role']]);
            }
            // buat employee jika belum ada â€” WAJIB agar HRD/Supervisor bisa absen, ajukan cuti, lihat riwayat & slip gaji
            Employee::firstOrCreate(
                ['user_id' => $user->id],
                array_merge([
                    'shift_id' => $shiftPagi->id,
                    'office_location_id' => $office->id,
                    'is_active' => true,
                ], $empData)
            );
            return $user;
        };

        // 1. HRD â€” juga karyawan (bisa absen/cuti/riwayat/slip gaji milik sendiri)
        $makeUser(
            ['name' => 'HRD Admin', 'nik' => 'HRD001', 'email' => 'hrd@example.com', 'role' => 'hrd'],
        );
        $makeUser(
            ['name' => 'Siti HRD', 'nik' => 'HRD002', 'email' => 'siti.hrd@example.com', 'role' => 'hrd'],
        );

        // 2. Supervisor â€” juga karyawan
        $makeUser(
            ['name' => 'Supervisor IT', 'nik' => 'SPV001', 'email' => 'spv@example.com', 'role' => 'supervisor'],
        );
        $makeUser(
            ['name' => 'Supervisor Produksi', 'nik' => 'SPV002', 'email' => 'spv.prod@example.com', 'role' => 'supervisor'],
        );

        // 3. Staff â€” karyawan biasa
        $makeUser(
            ['name' => 'Budi Karyawan', 'nik' => 'STF001', 'email' => 'budi@example.com', 'role' => 'staff'],
        );
        $makeUser(
            ['name' => 'Andi Produksi', 'nik' => 'STF002', 'email' => 'andi@example.com', 'role' => 'staff'],
        );
    }
}
