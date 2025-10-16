<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CategoryController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:manage_inventory');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Category::withCount('items')->where('active', true);

        // Filtrar por tipo si se especifica
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        $categories = $query->orderBy('name')->get();

        return response()->json($categories);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Log de debug
        Log::info('Category creation attempt', [
            'request_data' => $request->all(),
            'user_id' => auth()->id(),
            'headers' => $request->headers->all()
        ]);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:category,name',
            'description' => 'nullable|string|max:1000',
            'color' => 'nullable|string|max:7|regex:/^#[0-9A-F]{6}$/i',
            'type' => 'required|string|in:element,kit,component',
        ], [
            'name.required' => 'El nombre de la categoría es obligatorio',
            'name.unique' => 'Ya existe una categoría con ese nombre',
            'type.required' => 'El tipo de categoría es obligatorio',
            'type.in' => 'El tipo debe ser: element, kit o component',
            'color.regex' => 'El color debe tener un formato válido (ej: #FF0000)'
        ]);

        if ($validator->fails()) {
            Log::warning('Category validation failed', [
                'errors' => $validator->errors()->toArray()
            ]);
            
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $category = Category::create([
                'name' => trim($request->name),
                'description' => $request->description ? trim($request->description) : null,
                'color' => $request->color ?? '#3B82F6',
                'type' => $request->type,
                'active' => true,
                'created_by' => auth()->id(),
            ]);

            DB::commit();

            Log::info('Category created successfully', [
                'category_id' => $category->id,
                'category_name' => $category->name
            ]);

            return response()->json([
                'message' => 'Categoría creada correctamente',
                'category' => $category
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating category: ' . $e->getMessage(), [
                'request_data' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'message' => 'Error al crear la categoría',
                'error' => 'Error interno del servidor'
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Category $category)
    {
        return response()->json($category->load('items'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Category $category)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:category,name,' . $category->id,
            'description' => 'nullable|string|max:1000',
            'color' => 'nullable|string|max:7|regex:/^#[0-9A-F]{6}$/i',
        ], [
            'name.required' => 'El nombre de la categoría es obligatorio',
            'name.unique' => 'Ya existe una categoría con ese nombre',
            'color.regex' => 'El color debe tener un formato válido (ej: #FF0000)'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $category->update([
                'name' => trim($request->name),
                'description' => $request->description ? trim($request->description) : null,
                'color' => $request->color ?? $category->color,
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Categoría actualizada correctamente',
                'category' => $category
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating category: ' . $e->getMessage());
            
            return response()->json([
                'message' => 'Error al actualizar la categoría',
                'error' => 'Error interno del servidor'
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Category $category)
    {
        try {
            // Verificar si la categoría tiene items asociados
            if ($category->items()->exists()) {
                return response()->json([
                    'message' => 'No se puede eliminar la categoría porque tiene items asociados'
                ], 422);
            }

            // En lugar de eliminar físicamente, marcamos como inactivo
            $category->update(['active' => false]);

            return response()->json([
                'message' => 'Categoría eliminada correctamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al eliminar la categoría',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get categories by type
     */
    public function getByType($type)
    {
        $validator = Validator::make(['type' => $type], [
            'type' => 'required|in:element,kit,component'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Tipo de categoría inválido'
            ], 422);
        }

        $categories = Category::withCount('items')
            ->where('type', $type)
            ->where('active', true)
            ->orderBy('name')
            ->get();

        return response()->json($categories);
    }

    /**
     * Deactivate a category
     */
    public function deactivate(Category $category)
    {
        try {
            $category->update(['active' => false]);

            return response()->json([
                'message' => 'Categoría desactivada correctamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al desactivar la categoría',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reactivate a category
     */
    public function reactivate(Category $category)
    {
        try {
            $category->update(['active' => true]);

            return response()->json([
                'message' => 'Categoría reactivada correctamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al reactivar la categoría',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
