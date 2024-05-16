<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WasteCollectionSchedule extends Notification
{
    use Queueable;

    protected $schedules;

    /**
     * Create a new notification instance.
     */
    public function __construct($schedules)
    {
        $this->schedules = $schedules;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
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
        // Filter schedules to only include those with the same location as the user
        $userLocationSchedules = $this->schedules->filter(function ($schedule) use ($notifiable) {
            return $schedule->location === $notifiable->location;
        });

        // If there are no schedules for the user's location, return an empty MailMessage
        if ($userLocationSchedules->isEmpty()) {
            return (new MailMessage);
        }

        // Create a string to display date and time information
        $scheduleInfo = '';

        foreach ($userLocationSchedules as $schedule) {
            // Assuming you have 'date' and 'time' columns in your Schedule model
            $scheduleInfo .= "Plate No: {$schedule->plate_no}, Location: {$schedule->location}, Date: {$schedule->start}, Time: {$schedule->time}\n";
        }

        $mailMessage = (new MailMessage)
            ->line('The scheduled waste collection will be on:');

        // Use the line method for each schedule
        foreach ($userLocationSchedules as $schedule) {
            $mailMessage->line("Plate No: {$schedule->plate_no}, Location: {$schedule->location}, Date: {$schedule->start}, Time: {$schedule->time}");
        }

        $mailMessage->action('Notification Action', url('/'))
            ->line('Thank you for understanding!');

        return $mailMessage;
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
