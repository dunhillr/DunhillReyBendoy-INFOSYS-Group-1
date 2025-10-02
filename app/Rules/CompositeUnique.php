<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\DB;

class CompositeUnique implements ValidationRule
{
    public function __construct(protected string $table, protected array $columns)
    {
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $query = DB::table($this->table);

        foreach ($this->columns as $column) {
            $query->where($column, request($column));
        }
        
        // This is a safety check to exclude the current product during updates
        if ($this->table === 'products' && request()->route('product')) {
            $query->where('id', '!=', request()->route('product')->id);
        }

        if ($query->count() > 0) {
            $fail('A product with this combination of attributes already exists.');
        }
    }
}
