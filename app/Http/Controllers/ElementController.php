<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class ElementController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $elements = Item::where('type', 'element')
            ->where('active', true)
            ->with('category')
            ->orderBy('name')
            ->paginate(20);

        //devolver como un json si se encontraron elementos
        
        return response()->json(['success' => true, 'data' => $elements]);
        
    }

    public function create()
    {
        $categories = Category::all();
        
        return Inertia::render('Elements/Create', [
            'categories' => $categories
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category_id' => 'required|exists:category,id',
            'unit' => 'required|string|max:50',
            'min_stock' => 'required|numeric|min:0',
            'max_stock' => 'required|numeric|min:0',
            'purchase_cost' => 'nullable|numeric|min:0',
            'sale_price' => 'nullable|numeric|min:0',
            'location' => 'nullable|string|max:255',
        ]);

        $element = Item::create([
            'name' => $request->name,
            'description' => $request->description,
            'type' => 'component',
            'category_id' => $request->category_id,
            'unit' => $request->unit,
            'min_stock' => $request->min_stock,
            'max_stock' => $request->max_stock,
            'current_stock' => 0,
            'purchase_cost' => $request->purchase_cost,
            'sale_price' => $request->sale_price,
            'location' => $request->location,
            'active' => true
        ]);

        return redirect()->route('elements.index')
            ->with('success', 'Elemento creado exitosamente');
    }

    public function show(Item $element)
    {
        if ($element->type !== 'component') {
            abort(404);
        }

        $element->load('category');

        return Inertia::render('Elements/Show', [
            'element' => $element
        ]);
    }

    public function edit(Item $element)
    {
        if ($element->type !== 'component') {
            abort(404);
        }

        $element->load('category');

        $categories = Category::all();


        return Inertia::render('Elements/Edit', [
            'element' => $element,
            'categories' => $categories
        ]);
    }

    public function update(Request $request, Item $element)
    {
        if ($element->type !== 'component') {
            abort(404);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category_id' => 'required|exists:category,id',
            'unit' => 'required|string|max:50',
            'min_stock' => 'required|numeric|min:0',
            'max_stock' => 'required|numeric|min:0',
            'purchase_cost' => 'nullable|numeric|min:0',
            'sale_price' => 'nullable|numeric|min:0',
            'location' => 'nullable|string|max:255',
        ]);

        $element->update([
            'name' => $request->name,
            'description' => $request->description,
            'category_id' => $request->category_id,
            'unit' => $request->unit,
            'min_stock' => $request->min_stock,
            'max_stock' => $request->max_stock,
            'purchase_cost' => $request->purchase_cost,
            'sale_price' => $request->sale_price,
            'location' => $request->location,
        ]);

        return redirect()->route('elements.index')
            ->with('success', 'Elemento actualizado exitosamente');
    }

    public function destroy(Item $element)
    {
        if ($element->type !== 'component') {
            abort(404);
        }

        $element->delete();

        return redirect()->route('elements.index')
            ->with('success', 'Elemento eliminado exitosamente');
    }

    public function updateStock(Request $request, Item $element)
    {
        if ($element->type !== 'component') {
            abort(404);
        }

        $request->validate([
            'quantity' => 'required|integer',
            'type' => 'required|in:in,out,adjustment',
            'notes' => 'nullable|string'
        ]);

        $oldStock = $element->current_stock;
        
        switch ($request->type) {
            case 'in':
                $newStock = $oldStock + $request->quantity;
                break;
            case 'out':
                $newStock = $oldStock - $request->quantity;
                if ($newStock < 0) {
                    return back()->withErrors(['error' => 'No hay suficiente stock disponible']);
                }
                break;
            case 'adjustment':
                $newStock = $request->quantity;
                break;
        }

        $element->update(['current_stock' => $newStock]);

        return back()->with('success', 'Stock actualizado exitosamente');
    }

    public function search(Request $request)
    {
        $query = $request->get('q', '');
        
        $elements = Item::where('type', 'component')
            ->where('active', true)
            ->where(function($q) use ($query) {
                $q->where('name', 'LIKE', "%{$query}%")
                  ->orWhere('description', 'LIKE', "%{$query}%");
            })
            ->with('category')
            ->limit(10)
            ->get();

        return response()->json($elements);
    }

    public function dashboardStats()
    {
        $stats = [
            'total_elements' => Item::where('type', 'component')->where('active', true)->count(),
            'low_stock_elements' => Item::where('type', 'component')
                ->where('active', true)
                ->where('current_stock', '<=', 'min_stock')
                ->count(),
            'over_stock_elements' => Item::where('type', 'component')
                ->where('active', true)
                ->where('current_stock', '>=', 'max_stock')
                ->count(),
            'total_value' => Item::where('type', 'component')
                ->where('active', true)
                ->sum(DB::raw('current_stock * purchase_cost'))
        ];

        return response()->json($stats);
    }
}
