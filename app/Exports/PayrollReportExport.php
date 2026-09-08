<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class PayrollReportExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    protected $rows;
    public function __construct($rows){ $this->rows = $rows; }
    public function collection(){ return $this->rows; }
    public function headings(): array { return ['Periode','Karyawan','NIK','Departemen','Gaji Pokok','Tunjangan','Lembur','Bonus+THR','Gross','Potongan','Gaji Bersih','Status Periode']; }
    public function map($row): array {
        return [
            $row['period'],
            $row['name'],
            $row['nik'],
            $row['dept'],
            $row['basic'],
            $row['allowances'],
            $row['overtime'],
            $row['bonus_thr'],
            $row['gross'],
            $row['deduction'],
            $row['net'],
            $row['status'],
        ];
    }
}
