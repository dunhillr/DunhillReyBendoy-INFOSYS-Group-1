<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Use the policy to check if the authenticated user can update this product
        return $this->user()->can('update', $this->route('product'));
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
            'net_weight' => ['required', 'numeric', 'min:0', 'max:9999999'],
            'net_weight_unit_id' => ['required', 'integer', 'exists:units,id'],
            'price' => 'required|numeric|min:0|max:9999999',
            'category' => ['required', 'string', 'max:255', 'regex:/^[a-zA-Z\s]+$/'],
        ];
    }
}
