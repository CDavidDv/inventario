<?php

namespace App\Exports;

use App\Models\Item;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class ItemsExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected $type;
    protected $isTemplate;

    public function __construct($type = 'all', $isTemplate = false)
    {
        $this->type = $type;
        $this->isTemplate = $isTemplate;
    }

    /**
     * Obtener la colección de items a exportar
     */
    public function collection()
    {
        if ($this->isTemplate) {
            // Retornar colección vacía para plantilla
            return collect([]);
        }

        $query = Item::with('category');

        // Filtrar por tipo si no es 'all'
        if ($this->type !== 'all') {
            $query->where('type', $this->type);
        }

        return $query->orderBy('type')->orderBy('name')->get();
    }

    /**
     * Encabezados de las columnas
     */
    public function headings(): array
    {
        return [
            'ID',
            'Tipo',
            'Nombre',
            'Descripción',
            'Categoría',
            'Stock Actual',
            'Stock Mínimo',
            'Stock Máximo',
            'Costo de Compra',
            'Precio de Venta',
            'Unidad de Medida',
            'Activo'
        ];
    }

    /**
     * Mapear cada item a una fila
     */
    public function map($item): array
    {
        return [
            $item->id,
            $this->getTypeLabel($item->type),
            $item->name,
            $item->description ?? '',
            $item->category ? $item->category->name : '',
            $item->current_stock,
            $item->min_stock,
            $item->max_stock,
            $item->purchase_cost ?? 0,
            $item->sale_price ?? 0,
            $item->unit ?? 'unidad',
            $item->active ? 'Sí' : 'No'
        ];
    }

    /**
     * Aplicar estilos a la hoja
     */
    public function styles(Worksheet $sheet)
    {
        return [
            // Estilo para los encabezados
            1 => [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                    'size' => 12
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4472C4']
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER
                ]
            ]
        ];
    }

    /**
     * Obtener etiqueta del tipo
     */
    private function getTypeLabel($type)
    {
        $types = [
            'element' => 'Elemento',
            'component' => 'Componente',
            'kit' => 'Kit'
        ];

        return $types[$type] ?? $type;
    }
}
