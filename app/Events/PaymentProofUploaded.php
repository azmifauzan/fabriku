<?php

namespace App\Events;

use App\Models\SubscriptionPayment;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PaymentProofUploaded
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public SubscriptionPayment $payment
    ) {}
}
