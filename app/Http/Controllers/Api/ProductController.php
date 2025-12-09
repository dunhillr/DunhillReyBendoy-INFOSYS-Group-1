<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;

class ProductController extends Controller
{
    /// app/Http/Controllers/Api/ProductController.php
    public function index() {
        // Return JSON, not a View!
        return response()->json(Product::all()); 
    }
}
