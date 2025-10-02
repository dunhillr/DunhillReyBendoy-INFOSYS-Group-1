<?php

namespace App\Models;

//to allow inserting values
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'net_weight',
        'net_weight_unit_id',
        'price',
        'category_id',
        'quantity',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    // Add this method to define the unit relationship
    public function unit()
    {
        return $this->belongsTo(Unit::class, 'net_weight_unit_id');
    }
}

