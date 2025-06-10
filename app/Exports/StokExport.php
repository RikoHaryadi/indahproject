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
        $stok_dus = $data->isidus > 0 ? floor($data->stok / $data->isidus) : 0;
        $stok_sisa_pcs = $data->isidus > 0 ? $data->stok % $data->isidus : $data->stok;
        $stok_lsn = floor($stok_sisa_pcs / 12);
        $stok_pcs = $stok_sisa_pcs % 12;

        $harga_jual = $data->stok * $data->nilairp;
        $harga_beli = $data->stok * $data->harga;

        $this->totalJual += $harga_jual;
        $this->totalBeli += $harga_beli;
        $this->rowCount++;

        return [
            $this->rowCount,
            $data->kode_barang,
            $data->nama_barang,
            $data->isidus,
            $stok_dus,
            $stok_lsn,
            $stok_pcs,
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
                $lastDataRow = $this->rowCount + 1; // +1 karena header di baris 1
                $lastRow = $lastDataRow + 1;

                // Tambahkan baris total
                $sheet->appendRows([
                    [
                        '', '', '', '', '', '', 'Total:',
                        number_format($this->totalJual, 2),
                        number_format($this->totalBeli, 2),
                    ]
                ], $sheet);

                // Styling header (baris 1)
                $sheet->getStyle('A1:I1')->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => ['rgb' => '4CAF50'] // warna hijau
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                        ],
                    ],
                    'alignment' => ['horizontal' => 'center'],
                ]);

                // Border untuk seluruh tabel data + total
                $sheet->getStyle("A1:I$lastRow")->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                        ],
                    ],
                ]);
            },
        ];
    }
}
