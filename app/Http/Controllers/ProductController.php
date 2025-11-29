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

    public function category(Request $request)
    {
        // Logic to show categories or filter products by category
        return view('products.category'); // Or reuse your main product view
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

            // Check for the 'stay' parameter in the request
            if ($request->has('stay')) {
                return redirect()->back()->with('success', 'Product updated successfully!');
            }

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
    // Eager load category and unit relationships
    $products = Product::with(['category', 'unit'])->select('products.*');

    // 1. NEW LOGIC: Check for array of category IDs (Correct!)
    if ($request->has('category_ids') && is_array($request->category_ids) && count($request->category_ids) > 0) {
        $products->whereIn('category_id', $request->category_ids);
    }
    // Fallback for single ID (good to keep)
    elseif ($request->has('category_id') && !empty($request->category_id)) {
        $products->where('category_id', $request->category_id);
    }

    return DataTables::of($products)
        ->addColumn('category_name', function (Product $product) {
            return $product->category ? $product->category->name : 'N/A';
        })
        // 2. FIX: Ensure this matches the JavaScript expectation
        // The JS expects "net_weight_unit" to be available. 
        // Since we are using "with('unit')", the relationship "unit" is already in the JSON.
        // But adding this explicitly helps searching/sorting if needed.
        ->addColumn('net_weight_unit', function (Product $product) {
        // 🛠️ FIX: Return ONLY the name string, not the whole object
            return $product->unit ? $product->unit->name : ''; 
        })
        // 3. MODERNIZATION: Use the Dropdown Action Menu
        ->addColumn('actions', function (Product $product) {
            return '
                <div class="dropdown">
                    <button class="btn btn-sm btn-light border dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        Actions
                    </button>
                    <ul class="dropdown-menu">
                        <li>
                            <a class="dropdown-item" href="' . route('products.edit', $product->id) . '">
                                <i class="fas fa-edit me-2 text-warning"></i>Edit
                            </a>
                        </li>
                        <li>
                            <button class="dropdown-item delete-product" 
                                    data-id="'.$product->id.'" 
                                    data-route="'.route('products.destroy', $product->id).'">
                                <i class="fas fa-trash-alt me-2 text-danger"></i>Archive
                            </button>
                        </li>
                    </ul>
                </div>
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
        // Use 'withInactive' or 'withoutGlobalScopes' depending on your model setup
        $products = Product::withoutGlobalScopes() 
            ->with(['category', 'unit'])
            ->where('is_active', 0)
            ->orderByDesc('updated_at')
            ->select('products.*');

        return DataTables::of($products)
            ->addColumn('category_name', fn($p) => $p->category->name ?? 'N/A')
            // Pass the whole unit object for JS rendering
            ->addColumn('net_weight_unit', function (Product $product) {
                return $product->unit ? $product->unit->name : ''; 
            })
            
            ->addColumn('actions', fn($p) => '
                <button class="btn btn-success btn-sm restore-product shadow-sm" 
                        data-id="' . $p->id . '" 
                        data-bs-toggle="tooltip" 
                        title="Restore this product">
                    <i class="fas fa-trash-restore me-1"></i> Restore
                </button>
            ')
            ->rawColumns(['actions'])
            ->make(true);
    }

}