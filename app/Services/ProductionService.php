<?php

namespace App\Services;

use App\Models\ProductionBatch;
use App\Models\ProductionOrder;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ProductionService
{
    public function send(ProductionOrder $order): void
    {
        if (! in_array($order->status, ['draft', 'pending'], true)) {
            throw ValidationException::withMessages([
                'status' => 'Production order tidak dapat dikirim karena statusnya tidak valid.',
            ]);
        }

        $sentDate = now()->toDateString();

        if ($order->isExternal()) {
            if (! $order->contractor_id) {
                throw ValidationException::withMessages([
                    'contractor_id' => 'Kontraktor wajib diisi untuk produksi eksternal.',
                ]);
            }

            $order->forceFill([
                'status' => 'sent',
                'sent_date' => $order->sent_date ?? $sentDate,
            ])->save();

            return;
        }

        $order->forceFill([
            'status' => 'in_progress',
            'sent_date' => $order->sent_date ?? $sentDate,
        ])->save();
    }

    /**
     * @param  array{
     *   quantity_received:int,
     *   quantity_good:int,
     *   quantity_defect?:int,
     *   quantity_reject?:int,
     *   grade:string,
     *   labor_cost_actual?:numeric,
     *   production_cost?:numeric,
     *   production_date:string,
     *   received_date:string,
     *   expiry_date?:string|null,
     *   qc_notes?:string|null,
     *   defect_reasons?:string|null,
     *   qc_checklist?:array|null,
     *   received_by?:int|null
     * }  $batchData
     */
    public function receive(ProductionOrder $order, array $batchData): ProductionBatch
    {
        if (! $order->canBeReceived()) {
            throw ValidationException::withMessages([
                'status' => 'Production order tidak dapat diterima karena statusnya tidak valid.',
            ]);
        }

        return DB::transaction(function () use ($order, $batchData): ProductionBatch {
            $batch = $order->batches()->create($batchData);

            $order->quantity_produced += $batch->quantity_received;
            $order->quantity_good += $batch->quantity_good;
            $order->quantity_reject += $batch->quantity_reject;

            // Check if order is completed (reached target quantity)
            $completed = $order->quantity_produced >= $order->quantity_requested;

            // Status will remain 'in_progress' until manually marked as completed
            if ($order->status !== 'completed' && $completed) {
                $order->status = 'completed';
                $order->completed_date = $batch->received_date;
            } elseif ($order->status !== 'completed') {
                $order->status = 'in_progress';
            }
            $order->sent_date = $order->sent_date ?? $batch->production_date;

            $order->save();

            return $batch;
        });
    }
}
