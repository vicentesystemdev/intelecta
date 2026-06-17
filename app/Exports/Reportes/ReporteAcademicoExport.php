<?php

namespace App\Exports\Reportes;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ReporteAcademicoExport implements FromCollection, ShouldAutoSize, WithHeadings, WithMapping, WithStyles, WithTitle
{
    public function __construct(private readonly array $reporte)
    {
    }

    public function collection(): Collection
    {
        return collect($this->reporte['filas'] ?? []);
    }

    public function headings(): array
    {
        return array_values($this->reporte['columnas'] ?? []);
    }

    public function map($row): array
    {
        $fila = (array) $row;

        return collect($this->reporte['columnas'] ?? [])
            ->keys()
            ->map(fn (string $key) => $fila[$key] ?? '')
            ->all();
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '16213E'],
                ],
            ],
        ];
    }

    public function title(): string
    {
        return mb_substr((string) ($this->reporte['hoja'] ?? 'Reporte'), 0, 31);
    }
}
