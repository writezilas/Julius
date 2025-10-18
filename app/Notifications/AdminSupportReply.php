<?php

namespace App\Notifications;

use App\Models\Support;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AdminSupportReply extends Notification
{
    use Queueable;

    public $support;
    public $admin;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Support $support, User $admin)
    {
        $this->support = $support;
        $this->admin = $admin;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $appName = get_gs_value('site_name', 'Autobidder');
        $supportEmail = get_gs_value('support_email', 'support@autobidder.live');
        
        return (new MailMessage)
                    ->subject("Response to Your Support Request - {$appName}")
                    ->greeting("Dear {$this->support->first_name},")
                    ->line("We have received your support request and our team has responded to your inquiry.")
                    ->line("**Your Original Message:**")
                    ->line($this->support->message)
                    ->line("**Our Response:**")
                    ->line($this->support->admin_reply)
                    ->line("If you have any additional questions or concerns, please feel free to contact us again.")
                    ->line("Thank you for using {$appName}!")
                    ->salutation("Best regards,\nThe {$appName} Support Team")
                    ->from($supportEmail, "{$appName} Support");
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'support_id' => $this->support->id,
            'admin_id' => $this->admin->id,
            'reply_message' => $this->support->admin_reply,
            'replied_at' => $this->support->replied_at,
        ];
    }
}