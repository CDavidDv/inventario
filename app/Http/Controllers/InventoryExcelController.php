<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Category;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ItemsExport;
use App\Imports\ItemsImport;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class InventoryExcelController extends Controller
{
    /**
     * Exportar items por tipo (element, component, kit) o todos
     */
    public function export(Request $request)
    {
        $type = $request->input('type', 'all'); // all, element, component, kit

        $filename = 'inventario_' . ($type === 'all' ? 'completo' : $type) . '_' . date('Y-m-d_His') . '.xlsx';

        return Excel::download(new ItemsExport($type), $filename);
    }

    /**
     * Importar items desde Excel
     */
    public function import(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|file|mimes:xlsx,xls,csv|max:10240', // Max 10MB
            'type' => 'required|in:all,element,component,kit'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Archivo inválido',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $type = $request->input('type');
            $file = $request->file('file');

            $import = new ItemsImport($type);
            Excel::import($import, $file);

            $results = $import->getResults();

            return response()->json([
                'success' => true,
                'message' => 'Importación completada',
                'results' => $results
            ]);

        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            $failures = $e->failures();

            $errors = [];
            foreach ($failures as $failure) {
                $errors[] = [
                    'row' => $failure->row(),
                    'attribute' => $failure->attribute(),
                    'errors' => $failure->errors(),
                    'values' => $failure->values()
                ];
            }

            return response()->json([
                'success' => false,
                'message' => 'Error de validación en el archivo',
                'errors' => $errors
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al procesar el archivo: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Previsualizar datos del archivo antes de importar
     */
    public function preview(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|file|mimes:xlsx,xls,csv|max:10240'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Archivo inválido',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $file = $request->file('file');
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file->getPathname());
            $worksheet = $spreadsheet->getActiveSheet();

            $rows = [];
            $rowIterator = $worksheet->getRowIterator();
            $maxPreviewRows = 10; // Mostrar primeras 10 filas
            $rowCount = 0;

            foreach ($rowIterator as $row) {
                if ($rowCount >= $maxPreviewRows) break;

                $cellIterator = $row->getCellIterator();
                $cellIterator->setIterateOnlyExistingCells(false);

                $cells = [];
                foreach ($cellIterator as $cell) {
                    $cells[] = $cell->getValue();
                }

                $rows[] = $cells;
                $rowCount++;
            }

            return response()->json([
                'success' => true,
                'preview' => $rows,
                'total_rows' => $worksheet->getHighestRow() - 1 // Excluir encabezado
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al leer el archivo: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Descargar plantilla de Excel para importación
     */
    public function downloadTemplate(Request $request)
    {
        $type = $request->input('type', 'all');

        $filename = 'plantilla_inventario_' . ($type === 'all' ? 'completo' : $type) . '.xlsx';

        return Excel::download(new ItemsExport($type, true), $filename);
    }
}
