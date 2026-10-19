<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAssetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:50', Rule::unique('assets', 'code')],
            'name' => ['required', 'string', 'max:150'],
            'type' => ['required', 'string', Rule::in(['loader', 'bulldozer', 'wheel_loader', 'excavator'])],
            'timezone' => ['required', 'string', 'timezone:all'],
        ];
    }
}
