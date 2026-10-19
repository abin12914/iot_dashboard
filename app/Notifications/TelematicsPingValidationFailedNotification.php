<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TelematicsPingValidationFailedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public array $payload;
    public array $errors;
    public string $ipAddress;

    /**
     * Create a new notification instance.
     */
    public function __construct(array $payload, array $errors, string $ipAddress)
    {
        $this->payload   = $payload;
        $this->errors    = $errors;
        $this->ipAddress = $ipAddress;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $assetIdentifier = $this->payload['asset_id'] ?? $this->payload['code'] ?? 'Unknown Device';

        return (new MailMessage)
            ->error()
            ->subject("[ALERT] Telematics IoT Ping Validation Failure - Asset ID: {$assetIdentifier}")
            ->greeting("Hello Administrator,")
            ->line("An incoming telematics ping from an IoT device failed schema validation.")
            ->line("**Device IP:** " . $this->ipAddress)
            ->line("**Validation Errors:** " . json_encode($this->errors, JSON_PRETTY_PRINT))
            ->line("**Raw Payload Received:** " . json_encode($this->payload, JSON_PRETTY_PRINT))
            ->action("View Telematics Dashboard", url('/'))
            ->line("Please verify device CAN-bus/GPS firmware configurations.");
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'payload'    => $this->payload,
            'errors'     => $this->errors,
            'ip_address' => $this->ipAddress,
        ];
    }
}
