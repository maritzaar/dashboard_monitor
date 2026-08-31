<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WelcomeNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $roleStr = strtolower($notifiable->role) === 'admin' ? __('Admin') : __('Operator');
        return [
            'message' => __("Selamat datang kembali, :role :name! Semoga hari Anda menyenangkan.", ['role' => $roleStr, 'name' => $notifiable->name]),
            'url' => route('monitoring.working_hour')
        ];
    }
}
