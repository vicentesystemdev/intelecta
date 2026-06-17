<?php

namespace App\Exports\Seguridad;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class BitacoraSistemaExport implements FromCollection, ShouldAutoSize, WithHeadings, WithMapping, WithStyles, WithTitle
{
    public function __construct(private readonly Collection $eventos)
    {
    }

    public function collection(): Collection
    {
        return $this->eventos;
    }

    public function headings(): array
    {
        return [
            'Fecha',
            'Usuario',
            'Correo',
            'Rol',
            'Accion',
            'Modulo',
            'Entidad',
            'Entidad ID',
            'Severidad',
            'IP',
            'Ruta',
            'Descripcion',
        ];
    }

    public function map($evento): array
    {
        return [
            $evento->created_at?->format('d/m/Y H:i:s'),
            $evento->nombre_usuario,
            $evento->correo_usuario,
            $evento->rol_usuario,
            $evento->accion,
            $evento->modulo,
            $evento->entidad,
            $evento->entidad_id,
            $evento->severidad,
            $evento->ip,
            $evento->ruta,
            $evento->descripcion,
        ];
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
        return 'Bitacora';
    }
}
