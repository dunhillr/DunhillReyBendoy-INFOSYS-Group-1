<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Product;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
         // fetch all products from the database
        $products = \App\Models\Product::all();

        // return them to a view
        return view('products.index', compact('products'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('products.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validate input
    $request->validate([
        'name' => 'required|string|max:255',
        'description' => 'nullable|string',
        'quantity' => 'required|integer|min:0',
        'price' => 'required|numeric|min:0',
    ]);

    // Save product
    \App\Models\Product::create($request->all());

    // Redirect back to list with success message
    return redirect()->route('products.index')->with('success', 'Product added successfully!');
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
        return view('products.edit', compact('product')); // pass it to the edit.blade.php
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product)
    {
        $request->validate([
        'name' => 'required',
        'description' => 'nullable',
        'quantity' => 'required|integer',
        'price' => 'required|numeric',
    ]);

        $product->update($request->all()); // mass assignment (make sure fillable is set)
    
        return redirect()->route('products.index')->with('success', 'Product updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        $product->delete();

        return redirect()->route('products.index')->with('success', 'Product deleted successfully.');
    }
}
