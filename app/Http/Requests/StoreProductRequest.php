<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Rules\CompositeUnique;

class StoreProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'min:2',
                'max:255',
                new CompositeUnique('products', ['name', 'net_weight', 'net_weight_unit_id', 'category_id'])
            ],
            'net_weight' => ['required', 'numeric', 'min:0', 'max:9999999'],
            'net_weight_unit_id' => ['required', 'integer', 'exists:units,id'],
            'price' => ['required', 'numeric', 'min:0', 'max:9999999'],
            'category' => ['required', 'string', 'max:255', 'regex:/^[a-zA-Z\s]+$/'],
        ];
    }
}
