<?php

namespace App\Http\Requests\Admin;

use App\Concerns\ProfileValidationRules;
use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class UpdateShopOwnerRequest extends FormRequest
{
    use ProfileValidationRules;

    /**
     * Access is limited to admins by the route's role middleware.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * The password is optional: leave it blank to keep the current one.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        /** @var User $shopOwner */
        $shopOwner = $this->route('shop_owner');

        return [
            ...$this->profileRules($shopOwner->id),
            'password' => ['nullable', 'string', Password::default(), 'confirmed'],
        ];
    }
}
