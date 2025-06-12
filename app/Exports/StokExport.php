<?php

namespace App\Exports;

use App\Models\Barang;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class StokExport implements FromCollection, WithHeadings, WithMapping, WithEvents
{
    protected $totalJual = 0;
    protected $totalBeli = 0;
    protected $rowCount = 0;

    public function collection()
    {
        return Barang::where('stok', '>', 0)->get();
    }

   public function map($data): array
{
    $stok_dus = ($data->isidus > 0) ? floor($data->stok / $data->isidus) : 0;
    $stok_sisa_pcs = ($data->isidus > 0) ? $data->stok % $data->isidus : $data->stok;
    $stok_lsn = floor($stok_sisa_pcs / 12);
    $stok_pcs = $stok_sisa_pcs % 12;

    // Pastikan tidak ada nilai kosong
    $stok_dus = $stok_dus ?: 0;
    $stok_lsn = $stok_lsn ?: 0;
    $stok_pcs = $stok_pcs ?: 0;
    // (string) $stok_dus ?: '0';
    // (string) $stok_lsn ?: '0';
    // (string) $stok_pcs ?: '0';

    $harga_jual = $data->stok * $data->nilairp;
    $harga_beli = $data->stok * $data->harga;

    $this->totalJual += $harga_jual;
    $this->totalBeli += $harga_beli;
    $this->rowCount++;

    return [
    $this->rowCount,
    $data->kode_barang,
    $data->nama_barang,
    (int) $data->isidus,
    (int) $stok_dus,
    (int) $stok_lsn,
    (int) $stok_pcs,
    number_format($harga_jual, 2),
    number_format($harga_beli, 2),
    ];
}

    public function headings(): array
    {
        return [
            'No.',
            'Kode Barang',
            'Nama Barang',
            'Isi Dus',
            'Stok Dus',
            'Stok Lusin',
            'Stok Pcs',
            'Harga Jual',
            'Harga Beli',
        ];
    }

 public function registerEvents(): array
{
    return [
        AfterSheet::class => function (AfterSheet $event) {
            $sheet = $event->sheet;
            $lastDataRow = $this->rowCount + 1; // karena header di baris 1
            $totalRow = $lastDataRow + 1;

            // Tulis label Total di kolom D
            $sheet->setCellValue("D$totalRow", 'Total:');

            // Isi total stok dus, lusin, pcs, harga jual & beli
            $sheet->setCellValue("E$totalRow", $this->totalStokDus);
            $sheet->setCellValue("F$totalRow", $this->totalStokLusin);
            $sheet->setCellValue("G$totalRow", $this->totalStokPcs);
            $sheet->setCellValue("H$totalRow", $this->totalJual);
            $sheet->setCellValue("I$totalRow", $this->totalBeli);

            // Format angka (harga jual & beli)
            $sheet->getStyle("H2:I$totalRow")
                ->getNumberFormat()
                ->setFormatCode('#,##0.00');

            // Styling header
            $sheet->getStyle('A1:I1')->applyFromArray([
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4CAF50']
                ],
                'alignment' => ['horizontal' => 'center'],
            ]);

            // Border seluruh area
            $sheet->getStyle("A1:I$totalRow")->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    ],
                ],
            ]);

            // Styling baris total
            $sheet->getStyle("A$totalRow:I$totalRow")->applyFromArray([
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'FFF9C4'] // kuning muda
                ],
                'alignment' => ['horizontal' => 'right'],
            ]);
        },
    ];
}

}
