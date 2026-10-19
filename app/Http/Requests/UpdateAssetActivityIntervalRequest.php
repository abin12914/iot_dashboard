<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAssetActivityIntervalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status'         => ['sometimes', 'string', Rule::in(['active', 'idling', 'ignition_off'])],
            'started_at_utc' => ['sometimes', 'date'],
            'ended_at_utc'   => ['sometimes', 'date', 'after_or_equal:started_at_utc'],
        ];
    }
}
