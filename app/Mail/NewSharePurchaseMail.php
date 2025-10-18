<?php

namespace App\Mail;

use App\Models\User;
use App\Models\Trade;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewSharePurchaseMail extends Mailable
{
    use Queueable, SerializesModels;

    public User $user;
    public Trade $trade;
    public float $amount;
    public int $sharesCount;
    public string $ticketNo;

    /**
     * Create a new message instance.
     *
     * @param User $user
     * @param Trade $trade
     * @param float $amount
     * @param int $sharesCount
     * @param string $ticketNo
     * @return void
     */
    public function __construct(User $user, Trade $trade, float $amount, int $sharesCount, string $ticketNo)
    {
        $this->user = $user;
        $this->trade = $trade;
        $this->amount = $amount;
        $this->sharesCount = $sharesCount;
        $this->ticketNo = $ticketNo;
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
            subject: 'New Share Purchase - ' . $this->user->name . ' - ' . $this->ticketNo,
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
            view: 'emails.new-share-purchase',
            with: [
                'user' => $this->user,
                'trade' => $this->trade,
                'amount' => $this->amount,
                'sharesCount' => $this->sharesCount,
                'ticketNo' => $this->ticketNo,
                'purchaseDate' => now()->format('Y-m-d H:i:s'),
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
