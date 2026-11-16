<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreFotoMobilRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user?->status === 'aktif'
            && $user->roleKey() !== 'owner'
            && ($user->roleName() === 'admin' || $user->hasPermission('inventory.manage'));
    }
    public function rules(): array { return ['foto' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120']]; }
}
