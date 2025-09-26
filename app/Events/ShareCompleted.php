<?php

namespace App\Events;

use App\Models\UserShare;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ShareCompleted
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $buyerShare;

    /**
     * Create a new event instance.
     *
     * @param  \App\Models\UserShare  $buyerShare
     * @return void
     */
    public function __construct(UserShare $buyerShare)
    {
        $this->buyerShare = $buyerShare;
    }
}
