<?php

namespace App\Http\Controllers;

use App\Models\Unit;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Category;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Log;


class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $categories = Category::has('products')->withCount('products')->get();
        $showCategories = $request->has('show_categories') || $request->has('category_id');

        // Fetch the category if a category_id is present
        $category = null;
            if ($request->has('category_id')) {
                $category = Category::find($request->category_id);
            }

        return view('products.product', compact('categories', 'showCategories', 'category'));

    }
    
    public function create()
    {
        // Fetch all categories and units to populate dropdowns
        $categories = Category::all();
        $units = Unit::all(); // <-- The $units variable is defined here

        // Pass them to the view
        return view('products.create', compact('categories', 'units'));
    }

    //Controller for Autocomplete
    public function search(Request $request)
    {
        $searchTerm = $request->get('query');
        $products = Product::with('unit:id,name')
            ->where('name', 'like', "%{$searchTerm}%")
            ->orWhere('id', 'like', "%{$searchTerm}%")
            ->select('id', 'name', 'price', 'net_weight', 'net_weight_unit_id')
            ->get();

        // Format the products for the autocomplete on the server
        return response()->json(
            $products->map(function ($product) {
                return [
                    'label' => $product->name . " (ID: {$product->id})",
                    'value'=> $product->name,
                    'id' => $product->id,
                    'price' => $product->price,
                    'net_weight' => $product->net_weight,
                    'net_weight_unit_name' => optional($product->unit)->name
                ];
            })->values()->toArray()
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProductRequest $request)
    {

        try {
            $category = Category::firstOrCreate([
                'name' => $request->validated('category')
            ]);

            $category->products()->create([
                'name' => $request->validated('name'),
                'net_weight' => $request->validated('net_weight'),
                'net_weight_unit_id' => $request->validated('net_weight_unit_id'), // Corrected key
                'price' => $request->validated('price'),
            ]);

            return redirect()->route('products.index')->with('success', 'Product added successfully!');
        } catch (\Illuminate\Database\QueryException $e) {
            return redirect()->back()->with('error', 'A product with this combination of attributes already exists.')->withInput();
        }
    }
    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        return view('products.show', compact('product'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Product $product)
    {
        $categories = Category::all();
        $units = Unit::all(); // <-- Fetch units here

        return view('products.edit', compact('product', 'categories', 'units'));
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProductRequest $request, Product $product)
    {
        $category = Category::firstOrCreate([
            'name' => $request->validated()['category']
        ]);

        // Update the product, and use the category ID from the new or existing category
        $product->update([
            'name' => $request->validated('name'),
            'net_weight' => $request->validated('net_weight'),
            'net_weight_unit_id' => $request->validated('net_weight_unit_id'),
            'price' => $request->validated('price'),
            'category_id' => $category->id,
        ]);

        // Check for the 'stay' parameter in the request
        if ($request->has('stay')) {
            return redirect()->back()->with('success', 'Product updated successfully!');
        }

        return redirect()->route('products.index')->with('success', 'Product updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        $product->is_active = 0;
        $product->save();
        
        if (request()->ajax()) {
            return response()->json(['message' => 'Product archived successfully.']);
        }
    
        return redirect()->back()->with('success', 'Product archived successfully.');
    }

    public function getData(Request $request)
    {
        $products = Product::with(['category', 'unit'])
                ->select('products.*');
    
        // Check if a category_id filter is present and not empty
        if ($request->has('category_id') && !empty($request->category_id)) {
            $products->where('category_id', $request->category_id);
        }
    
        return DataTables::of($products)
            ->addColumn('category_name', function (Product $product) {
                return $product->category ? $product->category->name : 'N/A';
            })
            ->addColumn('net_weight_unit', function (Product $product) {
                return $product->unit ? $product->unit->name : '';
            })
            ->addColumn('actions', function (Product $product) {
                return '
                    <a href="' . route('products.edit', $product->id) . '" class="btn btn-warning btn-sm">Edit</a>
                    <button class="btn btn-danger btn-sm delete-product" data-id="'.$product->id.'">Delete</button>
                ';
            })

            ->rawColumns(['actions'])
            ->make(true);
    }
    
    // Show archived products (is_active = 0)
    public function archived()
    {
        $categories = Category::has('products')->withCount('products')->get();
    
        $archivedProducts = Product::with(['category', 'unit'])
            ->where('is_active', 0)
            ->get();
    
        return view('products.archived', compact('archivedProducts', 'categories'));
    }
    
    // Restore a previously archived product
    public function restore($id)
    {
        $product = Product::withoutGlobalScope('active')->findOrFail($id);

        $product->is_active = 1;
        $product->save();

        return response()->json(['message' => 'Product restored successfully!']);
    }
    
    public function getArchivedData(Request $request)
    {
        $products = Product::withoutGlobalScopes() // 👈 disables the default "active only" filter
        ->with(['category', 'unit'])
        ->where('is_active', 0)
        ->orderByDesc('updated_at')
        ->select('products.*');

    return DataTables::of($products)
        ->addColumn('category_name', fn($p) => $p->category->name ?? 'N/A')
        ->addColumn('unit_name', fn($p) => $p->unit->name ?? '')
        ->addColumn('net_weight_unit', fn($p) => $p->unit->name ?? '')
        ->addColumn('actions', fn($p) => '
            <button class="btn btn-success btn-sm restore-product" data-id="' . $p->id . '">
                Restore
            </button>
        ')
        ->rawColumns(['actions'])
        ->make(true);

}

}