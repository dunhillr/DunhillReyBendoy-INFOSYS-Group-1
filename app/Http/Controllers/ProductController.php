<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
         // fetch all products from the database
        $products = Product::all();

        // return them to a view
        return view('products.product', compact('products'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Fetch all categories to populate a dropdown in the form
        $categories = Category::all();

        // Pass them to the view
        return view('products.create', compact('categories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProductRequest $request)
    {

    // Find category or create a new one, using the validated data
    $category = Category::firstOrCreate([
        'name' => $request->validated('category')
    ]);

    // Create product and associate it with the category
    $category->products()->create([
        'name' => $request->validated('name'),
        'description' => $request->validated('description'),
        'quantity' => $request->validated('quantity'),
        'price' => $request->validated('price'),
    ]);

    // Redirect back to list with success message
    return redirect()->route('products.product')->with('success', 'Product added successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Product $product)
    {
        $categories = Category::all();

        return view('products.edit', compact('product', 'categories'));
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProductRequest $request, Product $product)
    {
        // Use the validated quantity from the form request.
        // The 'quantity' input should represent the amount sold, which is then added to the stock.
        $product->increment('quantity', $request->validated('quantity'));
    
        return redirect()->route('products.product')->with('success', 'Product updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        $product->delete();

        return redirect()->route('products.product')->with('success', 'Product deleted successfully.');
    }
}
