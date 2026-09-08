<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class AttendanceReportExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    protected $rows;

    public function __construct($rows)
    {
        $this->rows = $rows;
    }

    public function collection()
    {
        return $this->rows;
    }

    public function headings(): array
    {
        return ['Karyawan','NIK','Departemen','Jabatan','Hadir','Terlambat','Alpha/Cuti?','Total Telat (menit)','Jam Lembur','Periode'];
    }

    public function map($row): array
    {
        return [
            $row['name'],
            $row['nik'],
            $row['dept'],
            $row['position'],
            $row['hadir'],
            $row['terlambat'],
            $row['alpha'],
            $row['late_minutes'],
            $row['overtime'],
            $row['period'],
        ];
    }
}
