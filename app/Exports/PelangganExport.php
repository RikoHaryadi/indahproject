<?php

namespace App\Exports;

use App\Models\Pelanggan;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithMapping;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class PelangganExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    private $rowNumber = 0;

    public function collection()
    {
        return Pelanggan::all();
    }

    public function map($pelanggan): array
    {
        $this->rowNumber++;

        return [
            $this->rowNumber,
            $pelanggan->Kode_pelanggan,
            $pelanggan->Nama_pelanggan,
            $pelanggan->alamat,
            $pelanggan->telepon,
            $pelanggan->top,
            $pelanggan->kredit_limit,
            $pelanggan->nama_sales,
            $pelanggan->hari_kunjungan,
        ];
    }

    public function headings(): array
    {
        return [
            'No',
            'Kode Pelanggan',
            'Nama',
            'Alamat',
            'Telepon',
            'TOP',
            'Kredit Limit',
            'Salesman',
            'Hari Kunjungan',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1:I1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => 'solid',
                'startColor' => ['rgb' => '28a745'], // Hijau
            ],
            'borders' => [
                'allBorders' => ['borderStyle' => 'thin'],
            ],
        ]);

        $lastRow = $this->rowNumber + 1;
        $sheet->getStyle("A1:I{$lastRow}")->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
    }
}
