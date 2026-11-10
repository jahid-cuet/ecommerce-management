<?php

namespace App\Http\Requests;

use App\Support\Thumbnail;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Validates creating and updating a catalog product.
 */
class ProductRequest extends FormRequest
{
    /**
     * Access is controlled by the route's role middleware.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'price' => ['required', 'numeric', 'min:0', 'max:99999999.99', 'decimal:0,2'],
            'thumbnail' => Thumbnail::rules(),
            'remove_thumbnail' => ['boolean'],
        ];
    }
}
