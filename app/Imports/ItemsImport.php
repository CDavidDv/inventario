<?php

namespace App\Imports;

use App\Models\Item;
use App\Models\Category;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ItemsImport implements ToCollection, WithHeadingRow
{
    protected $type;
    protected $results = [
        'created' => 0,
        'updated' => 0,
        'skipped' => 0,
        'errors' => []
    ];

    public function __construct($type = 'all')
    {
        $this->type = $type;
    }

    /**
     * Procesar la colección de filas del Excel
     */
    public function collection(Collection $rows)
    {
        DB::beginTransaction();

        try {
            foreach ($rows as $index => $row) {
                $rowNumber = $index + 2; // +2 porque el índice empieza en 0 y hay 1 fila de encabezado

                try {
                    // Normalizar los datos de la fila
                    $data = $this->normalizeRowData($row);

                    // Validar la fila
                    $validator = $this->validateRow($data, $rowNumber);

                    if ($validator->fails()) {
                        $this->results['skipped']++;
                        $this->results['errors'][] = [
                            'row' => $rowNumber,
                            'errors' => $validator->errors()->all()
                        ];
                        continue;
                    }

                    // Verificar si el tipo coincide con el filtro
                    if ($this->type !== 'all' && $data['type'] !== $this->type) {
                        $this->results['skipped']++;
                        $this->results['errors'][] = [
                            'row' => $rowNumber,
                            'errors' => ['El tipo del item no coincide con el tipo de importación seleccionado']
                        ];
                        continue;
                    }

                    // Obtener o crear la categoría
                    $category = $this->getOrCreateCategory($data['category_name'], $data['type']);

                    // Preparar datos del item
                    $itemData = [
                        'type' => $data['type'],
                        'name' => $data['name'],
                        'description' => $data['description'],
                        'category_id' => $category->id,
                        'current_stock' => $data['current_stock'],
                        'min_stock' => $data['min_stock'],
                        'max_stock' => $data['max_stock'],
                        'purchase_cost' => $data['purchase_cost'],
                        'sale_price' => $data['sale_price'],
                        'unit' => $data['unit'],
                        'active' => $data['active']
                    ];

                    // Verificar si existe un item con el mismo ID o nombre
                    if (!empty($data['id'])) {
                        // Actualizar por ID
                        $item = Item::find($data['id']);
                        if ($item) {
                            $item->update($itemData);
                            $this->results['updated']++;
                        } else {
                            // ID no encontrado, crear nuevo
                            Item::create($itemData);
                            $this->results['created']++;
                        }
                    } else {
                        // Buscar por nombre y tipo
                        $item = Item::where('name', $data['name'])
                            ->where('type', $data['type'])
                            ->first();

                        if ($item) {
                            // Actualizar item existente
                            $item->update($itemData);
                            $this->results['updated']++;
                        } else {
                            // Crear nuevo item
                            Item::create($itemData);
                            $this->results['created']++;
                        }
                    }

                } catch (\Exception $e) {
                    $this->results['skipped']++;
                    $this->results['errors'][] = [
                        'row' => $rowNumber,
                        'errors' => ['Error al procesar: ' . $e->getMessage()]
                    ];
                }
            }

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Normalizar los datos de una fila
     */
    private function normalizeRowData($row)
    {
        // Normalizar tipo
        $type = $this->normalizeType($row['tipo'] ?? '');

        // Normalizar booleano activo
        $active = $this->normalizeBoolean($row['activo'] ?? 'sí');

        return [
            'id' => !empty($row['id']) ? (int)$row['id'] : null,
            'type' => $type,
            'name' => trim($row['nombre'] ?? ''),
            'description' => trim($row['descripcion'] ?? ''),
            'category_name' => $this->normalizeCategoryName($row['categoria'] ?? ''),
            'current_stock' => (float)($row['stock_actual'] ?? 0),
            'min_stock' => (float)($row['stock_minimo'] ?? 0),
            'max_stock' => (float)($row['stock_maximo'] ?? 0),
            'purchase_cost' => (float)($row['costo_de_compra'] ?? 0),
            'sale_price' => (float)($row['precio_de_venta'] ?? 0),
            'unit' => trim($row['unidad_de_medida'] ?? 'unidad'),
            'active' => $active
        ];
    }

    /**
     * Normalizar el tipo de item
     */
    private function normalizeType($type)
    {
        $type = strtolower(trim($type));
        $type = $this->removeAccents($type);

        $typeMap = [
            'elemento' => 'element',
            'element' => 'element',
            'componente' => 'component',
            'component' => 'component',
            'kit' => 'kit'
        ];

        return $typeMap[$type] ?? 'element';
    }

    /**
     * Normalizar nombre de categoría
     */
    private function normalizeCategoryName($name)
    {
        // Eliminar espacios extras
        $name = trim(preg_replace('/\s+/', ' ', $name));

        // Capitalizar primera letra de cada palabra
        $name = mb_convert_case($name, MB_CASE_TITLE, "UTF-8");

        return $name;
    }

    /**
     * Normalizar valor booleano
     */
    private function normalizeBoolean($value)
    {
        // Si el valor es null o vacío, por defecto es true (activo)
        if ($value === null || $value === '') {
            return true;
        }

        // Convertir a string y normalizar
        $value = strtolower(trim((string)$value));
        $value = $this->removeAccents($value);

        // Valores que se consideran true
        $trueValues = ['si', 'sí', 'yes', 'true', '1', 'activo', 'verdadero', 's', 'y'];

        // Valores que se consideran false
        $falseValues = ['no', 'false', '0', 'inactivo', 'falso', 'n'];

        if (in_array($value, $trueValues)) {
            return true;
        }

        if (in_array($value, $falseValues)) {
            return false;
        }

        // Por defecto, si no coincide con ninguno, se considera activo
        return true;
    }

    /**
     * Eliminar acentos de una cadena
     */
    private function removeAccents($string)
    {
        $unwanted = [
            'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u',
            'Á' => 'A', 'É' => 'E', 'Í' => 'I', 'Ó' => 'O', 'Ú' => 'U',
            'ñ' => 'n', 'Ñ' => 'N'
        ];

        return strtr($string, $unwanted);
    }

    /**
     * Validar los datos de una fila
     */
    private function validateRow($data, $rowNumber)
    {
        $rules = [
            'type' => 'required|in:element,component,kit',
            'name' => 'required|string|max:255',
            'current_stock' => 'required|numeric|min:0',
            'min_stock' => 'required|numeric|min:0',
            'max_stock' => 'required|numeric|min:0|gte:min_stock',
            'purchase_cost' => 'nullable|numeric|min:0',
            'sale_price' => 'nullable|numeric|min:0',
            'category_name' => 'required|string|max:255'
        ];

        $messages = [
            'type.required' => 'El tipo es requerido',
            'type.in' => 'El tipo debe ser: Elemento, Componente o Kit',
            'name.required' => 'El nombre es requerido',
            'name.max' => 'El nombre no puede exceder 255 caracteres',
            'current_stock.required' => 'El stock actual es requerido',
            'current_stock.numeric' => 'El stock actual debe ser un número',
            'current_stock.min' => 'El stock actual debe ser mayor o igual a 0',
            'min_stock.required' => 'El stock mínimo es requerido',
            'min_stock.numeric' => 'El stock mínimo debe ser un número',
            'min_stock.min' => 'El stock mínimo debe ser mayor o igual a 0',
            'max_stock.required' => 'El stock máximo es requerido',
            'max_stock.numeric' => 'El stock máximo debe ser un número',
            'max_stock.min' => 'El stock máximo debe ser mayor o igual a 0',
            'max_stock.gte' => 'El stock máximo debe ser mayor o igual al stock mínimo',
            'purchase_cost.numeric' => 'El costo de compra debe ser un número',
            'purchase_cost.min' => 'El costo de compra debe ser mayor o igual a 0',
            'sale_price.numeric' => 'El precio de venta debe ser un número',
            'sale_price.min' => 'El precio de venta debe ser mayor o igual a 0',
            'category_name.required' => 'La categoría es requerida',
            'category_name.max' => 'La categoría no puede exceder 255 caracteres'
        ];

        return Validator::make($data, $rules, $messages);
    }

    /**
     * Obtener o crear una categoría
     */
    private function getOrCreateCategory($categoryName, $type)
    {
        // Buscar categoría existente (case-insensitive)
        $category = Category::where('type', $type)
            ->whereRaw('LOWER(name) = ?', [strtolower($categoryName)])
            ->first();

        if (!$category) {
            // Crear nueva categoría
            $category = Category::create([
                'name' => $categoryName,
                'type' => $type,
                'description' => 'Categoría creada automáticamente durante importación'
            ]);
        }

        return $category;
    }

    /**
     * Obtener resultados de la importación
     */
    public function getResults()
    {
        return $this->results;
    }
}
