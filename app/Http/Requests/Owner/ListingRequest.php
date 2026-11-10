<?php

namespace App\Http\Requests\Owner;

use App\Models\Shop;
use App\Support\Thumbnail;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validates a shop's listing of a catalog product.
 */
class ListingRequest extends FormRequest
{
    /**
     * Access is controlled by the route's role and shop ownership middleware.
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
        /** @var Shop $shop */
        $shop = $this->route('shop');

        $rules = [
            'price' => ['required', 'numeric', 'min:0', 'max:99999999.99', 'decimal:0,2'],
            'description' => ['nullable', 'string', 'max:5000'],
            'thumbnail' => Thumbnail::rules(),
            'remove_thumbnail' => ['boolean'],
        ];

        // The product is chosen when the listing is created and cannot change afterwards.
        if ($this->isMethod('post')) {
            $rules['product_id'] = [
                'required',
                'integer',
                Rule::exists('products', 'id'),
                Rule::unique('listings', 'product_id')->where('shop_id', $shop->id),
            ];
        }

        return $rules;
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'product_id.unique' => __('This product is already listed in this shop.'),
        ];
    }
}
