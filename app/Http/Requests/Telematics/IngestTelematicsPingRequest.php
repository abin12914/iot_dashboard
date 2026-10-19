<?php

namespace App\Http\Requests\Telematics;

use App\Notifications\TelematicsPingValidationFailedNotification;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\Rule;

class IngestTelematicsPingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'asset_id'  => ['required', 'integer', 'exists:assets,id'],
            'status'    => ['required', 'string', Rule::in(['active', 'idling', 'ignition_off'])],
            'timestamp' => ['nullable', 'date'],
        ];
    }

    /**
     * Handle a failed validation attempt.
     * Logs critical telemetry anomaly and queues an alert notification for the admin,
     * rate-limiting alerts to once per hour/24-hour window per device anomaly signature.
     */
    protected function failedValidation(Validator $validator): void
    {
        $errors = $validator->errors()->toArray();
        $payload = $this->all();
        $clientIp = $this->ip() ?: 'Unknown';

        // 1. Log critical anomaly in system logs (always recorded for audit trail)
        Log::critical("Telematics IoT Ping validation failed!", [
            'ip'      => $clientIp,
            'payload' => $payload,
            'errors'  => $errors,
        ]);

        // 2. Anomaly Signature Fingerprint (Rate-Limiting / Deduplication)
        // Groups identical failures by device asset identifier & failed validation keys
        $assetIdentifier = $payload['asset_id'] ?? $payload['code'] ?? $clientIp;
        $errorSignature = implode(',', array_keys($errors));
        $cacheKey = "iot_ping_alert:" . md5("{$assetIdentifier}:{$errorSignature}");

        // 3. Notification Throttling Window (defaults to 1 hour, configurable up to 24 hours)
        $throttleMinutes = (int) config('telematics.alert_throttle_minutes', 60);

        $notificationStatus = 'sent';
        $adminEmail = config('mail.admin_address', env('ADMIN_EMAIL', 'admin@example.com'));

        // Check if an identical alert was already sent within the throttle window
        if (!Cache::has($cacheKey)) {
            // Lock cache key for the throttle window
            Cache::put($cacheKey, true, now()->addMinutes($throttleMinutes));

            try {
                Notification::route('mail', $adminEmail)
                    ->notify((new TelematicsPingValidationFailedNotification($payload, $errors, $clientIp))->onQueue('notifications'));
                $notificationStatus = "Alert email queued to admin ({$adminEmail}) on queue [notifications]";
            } catch (\Throwable $e) {
                Log::error("Failed to queue admin notification for IoT validation failure: " . $e->getMessage());
                $notificationStatus = "Failed to queue email: " . $e->getMessage();
            }
        } else {
            Log::info("Throttling duplicate telematics alert notification for {$assetIdentifier} (already sent in last {$throttleMinutes} minutes).");
            $notificationStatus = "Skipped (Duplicate anomaly within {$throttleMinutes}m rate-limit window)";
        }

        // 4. Return clean JSON response with 422 Unprocessable Entity
        throw new HttpResponseException(response()->json([
            'message'             => 'IoT Telemetry packet rejected due to validation failure.',
            'errors'              => $errors,
            'notification_action' => $notificationStatus,
            'is_duplicate'        => $notificationStatus !== "Alert email queued to admin ({$adminEmail}) on queue [notifications]",
        ], 422));
    }
}
