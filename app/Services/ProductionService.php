<?php

namespace App\Services;

use App\Models\ProductionOrder;
use Illuminate\Validation\ValidationException;

class ProductionService
{
    public function send(ProductionOrder $order): void
    {
        if ($order->status !== 'draft') {
            throw ValidationException::withMessages([
                'status' => 'Production order tidak dapat dikirim karena statusnya tidak valid.',
            ]);
        }

        if ($order->isExternal() && ! $order->contractor_id) {
            throw ValidationException::withMessages([
                'contractor_id' => 'Kontraktor wajib diisi untuk produksi eksternal.',
            ]);
        }

        $order->forceFill([
            'status' => $order->isExternal() ? 'sent' : 'in_progress',
            'sent_date' => $order->sent_date ?? now()->toDateString(),
        ])->save();
    }
}
