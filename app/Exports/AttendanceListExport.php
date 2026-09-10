<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Carbon\Carbon;

class AttendanceListExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected $attendances;

    public function __construct($attendances)
    {
        $this->attendances = $attendances;
    }

    public function collection()
    {
        return $this->attendances;
    }

    public function headings(): array
    {
        return [
            'No',
            'NIK',
            'Nama',
            'Email',
            'Departemen',
            'Jabatan',
            'Tanggal',
            'Shift',
            'Jam Masuk',
            'Jam Pulang',
            'Status',
            'Terlambat (menit)',
            'Lembur (jam)',
            'Jarak Masuk (m)',
            'Lat Masuk',
            'Lng Masuk',
            'Akurasi (m)',
            'Fake GPS',
        ];
    }

    public function map($row): array
    {
        static $no = 0;
        $no++;
        return [
            $no,
            $row->user->nik ?? '-',
            $row->user->name ?? '-',
            $row->user->email ?? '-',
            $row->user->employee->department->name ?? '-',
            $row->user->employee->position->name ?? '-',
            $row->date ? Carbon::parse($row->date)->format('d-m-Y') : '-',
            $row->shift->name ?? '-',
            $row->check_in ? Carbon::parse($row->check_in)->format('H:i:s') : '-',
            $row->check_out ? Carbon::parse($row->check_out)->format('H:i:s') : '-',
            $row->status ?? '-',
            $row->late_minutes ?? 0,
            $row->overtime_hours ?? 0,
            $row->distance_in_meter ?? '-',
            $row->lat_in ?? '-',
            $row->lng_in ?? '-',
            $row->accuracy_in ?? '-',
            $row->is_fake_gps ? 'YA' : 'Tidak',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']], 'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '0d6efd']]],
        ];
    }
}
