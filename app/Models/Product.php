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

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // ✅ Global scope to automatically filter active products
    protected static function booted()
    {
        static::addGlobalScope('active', function ($query) {
            $query->where('is_active', 1);
        });
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    // Add this method to define the unit relationship
    public function unit()
    {
        return $this->belongsTo(Unit::class, 'net_weight_unit_id');
    }

    // ✅ Use this when intentionally want to include archived products
    public static function withInactive() {
        return static::withoutGlobalScope('active');
    }
}

