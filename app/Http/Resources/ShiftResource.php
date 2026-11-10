<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ShiftResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'user_id'    => $this->user_id,
            'driver'     => $this->whenLoaded('user', fn () => [
                'id'   => $this->user->id,
                'name' => $this->user->name,
            ]),
            'shift_date' => $this->shift_date->toDateString(),
            'status'     => $this->status,
            'notes'      => $this->notes,
            'started_at' => $this->started_at?->toIso8601String(),
            'ended_at'   => $this->ended_at?->toIso8601String(),
            'inventories' => DailyInventoryResource::collection($this->whenLoaded('dailyInventories')),
            'transactions' => TransactionResource::collection($this->whenLoaded('transactions')),

            // Summary fields (appended when loaded)
            'summary' => $this->when($this->relationLoaded('transactions'), fn () => [
                'total_transactions' => $this->transactions->count(),
                'total_cash'           => $this->totalCash(),
                'total_qris_validated' => $this->totalQrisValidated(),
                'total_qris_pending'   => $this->totalQrisPending(),
                'total_confirmed'      => $this->totalCash() + $this->totalQrisValidated(),
            ]),

            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}

