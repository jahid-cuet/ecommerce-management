<?php

namespace App\Http\Requests\Admin;

use App\Enums\UserRole;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validates creating and updating a shop.
 */
class ShopRequest extends FormRequest
{
    /**
     * Access is limited to admins by the route's role middleware.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * An unchecked checkbox sends nothing, so normalise it to a real boolean.
     */
    protected function prepareForValidation(): void
    {
        $this->merge(['is_active' => $this->boolean('is_active')]);
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'owner_id' => [
                'required',
                'integer',
                Rule::exists('users', 'id')->where('role', UserRole::ShopOwner->value),
            ],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'is_active' => ['boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'owner_id.required' => 'Please choose a shop owner.',
            'owner_id.exists' => 'Please choose a valid shop owner.',
        ];
    }
}
