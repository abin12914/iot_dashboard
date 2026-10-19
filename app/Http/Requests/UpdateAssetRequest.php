<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAssetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'code' => ['sometimes', 'string', 'max:50', Rule::unique('assets', 'code')->ignore($this->route('asset'))],
            'name' => ['sometimes', 'string', 'max:150'],
            'type' => ['sometimes', 'string', Rule::in(['loader', 'bulldozer', 'wheel_loader', 'excavator'])],
            'timezone' => ['sometimes', 'string', 'timezone:all'],
        ];
    }
}
