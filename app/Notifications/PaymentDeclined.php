<?php

namespace App\Notifications;

use App\Models\UserSharePayment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaymentDeclined extends Notification
{
    use Queueable;

    protected $payment;
    protected $isSecondChance;
    protected $declineReason;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(UserSharePayment $payment, bool $isSecondChance = true, $declineReason = null)
    {
        $this->payment = $payment;
        $this->isSecondChance = $isSecondChance;
        $this->declineReason = $declineReason;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['database'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $subject = $this->isSecondChance 
            ? 'Payment Declined - Please Verify Your Payment'
            : 'Final Payment Decline - Transaction Failed';

        return (new MailMessage)
            ->subject($subject)
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line($this->getMailMessage())
            ->line('Transaction ID: ' . $this->payment->txs_id)
            ->line('Amount: ' . formatPrice($this->payment->amount))
            ->when($this->declineReason, function ($mail) {
                return $mail->line('Reason: ' . $this->declineReason);
            })
            ->when($this->isSecondChance, function ($mail) {
                return $mail->action('Review Payment', url('/bought-shares/view/' . $this->payment->user_share_id));
            })
            ->line('Thank you for using our platform!');
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
            'type' => 'payment_declined',
            'heading' => $this->isSecondChance ? 'Payment Declined - Second Chance' : 'Payment Permanently Declined',
            'payment_id' => $this->payment->id,
            'user_share_id' => $this->payment->user_share_id,
            'user_share_pair_id' => $this->payment->user_share_pair_id,
            'amount' => $this->payment->amount,
            'txs_id' => $this->payment->txs_id,
            'is_second_chance' => $this->isSecondChance,
            'decline_reason' => $this->declineReason,
            'message' => $this->getNotificationMessage(),
            'action_url' => $this->isSecondChance ? url('/bought-shares/view/' . $this->payment->user_share_id) : null,
        ];
    }

    /**
     * Get the notification message for database storage
     */
    private function getNotificationMessage()
    {
        if ($this->isSecondChance) {
            return "Your payment of " . formatPrice($this->payment->amount) . " has been declined. Please verify your payment details and try again. This is your second chance to confirm the payment.";
        }

        return "Your payment of " . formatPrice($this->payment->amount) . " has been permanently declined. The transaction has failed and you will be matched with a new seller.";
    }

    /**
     * Get the mail message content
     */
    private function getMailMessage()
    {
        if ($this->isSecondChance) {
            return "Your payment has been declined by the seller. Please verify that you have made the payment correctly and reconfirm. This is your second opportunity to complete this transaction.";
        }

        return "Your payment has been declined for the second time. The transaction has failed and your shares will be automatically re-matched with a new seller.";
    }
}
