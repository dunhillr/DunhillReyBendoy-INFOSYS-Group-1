<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

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
        'name' => 'required|string|min:2|max:255',
        'description' => 'required|string',
        'quantity' => 'required|integer|min:0|max:9999999',
        'price' => 'required|numeric|min:0|max:9999999',
        'category' => 'required|string|max:255',
    ];
    }
}
