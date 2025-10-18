<?php

namespace App\Mail;

use App\Models\User;
use App\Models\UserSharePayment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SharePaymentApprovedMail extends Mailable
{
    use Queueable, SerializesModels;

    public User $user;
    public UserSharePayment $payment;
    public float $amount;

    /**
     * Create a new message instance.
     *
     * @param User $user
     * @param UserSharePayment $payment
     * @param float $amount
     * @return void
     */
    public function __construct(User $user, UserSharePayment $payment, float $amount)
    {
        $this->user = $user;
        $this->payment = $payment;
        $this->amount = $amount;
    }

    /**
     * Get the message envelope.
     *
     * @return \Illuminate\Mail\Mailables\Envelope
     */
    public function envelope()
    {
        return new Envelope(
            from: new \Illuminate\Mail\Mailables\Address(
                env('MAIL_FROM_ADDRESS', 'noreply@autobidder.live'),
                env('MAIL_FROM_NAME', 'AutoBidder System')
            ),
            subject: 'Share Payment Approved - ' . $this->user->name . ' - $' . number_format($this->amount, 2),
        );
    }

    /**
     * Get the message content definition.
     *
     * @return \Illuminate\Mail\Mailables\Content
     */
    public function content()
    {
        return new Content(
            view: 'emails.share-payment-approved',
            with: [
                'user' => $this->user,
                'payment' => $this->payment,
                'amount' => $this->amount,
                'approvedDate' => now()->format('Y-m-d H:i:s'),
            ]
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array
     */
    public function attachments()
    {
        return [];
    }
}
