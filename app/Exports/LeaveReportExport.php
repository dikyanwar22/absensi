<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class LeaveReportExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    protected $rows;
    public function __construct($rows){ $this->rows = $rows; }
    public function collection(){ return $this->rows; }
    public function headings(): array { return ['Karyawan','NIK','Departemen','Cuti Tahunan Terpakai','Sisa Cuti','Total Pengajuan','Disetujui','Pending','Ditolak']; }
    public function map($row): array {
        return [
            $row['name'],
            $row['nik'],
            $row['dept'],
            $row['used'],
            $row['remaining'],
            $row['total'],
            $row['approved'],
            $row['pending'],
            $row['rejected'],
        ];
    }
}
