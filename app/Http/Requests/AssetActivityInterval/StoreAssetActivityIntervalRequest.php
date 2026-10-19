<?php

namespace App\Http\Requests\AssetActivityInterval;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAssetActivityIntervalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'asset_id'       => ['required', 'integer', 'exists:assets,id'],
            'status'         => ['required', 'string', Rule::in(['active', 'idling', 'ignition_off'])],
            'started_at_utc' => ['required', 'date'],
            'ended_at_utc'   => ['required', 'date', 'after_or_equal:started_at_utc'],
        ];
    }
}
