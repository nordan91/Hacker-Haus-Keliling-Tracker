<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->id,
            'shift_id'        => $this->shift_id,
            'invoice_number'  => $this->invoice_number,
            'payment_method'  => $this->payment_method,
            'payment_status'  => $this->payment_status,
            'total_amount'    => $this->total_amount,
            'total_formatted' => 'Rp ' . number_format($this->total_amount, 0, ',', '.'),
            'latitude'        => $this->latitude ? (float) $this->latitude : null,
            'longitude'       => $this->longitude ? (float) $this->longitude : null,
            'location_id'     => $this->location_id,
            'location_name'   => $this->location_name,
            'location'        => new LocationResource($this->whenLoaded('location')),
            'details'         => TransactionDetailResource::collection($this->whenLoaded('details')),
            'transacted_at'   => $this->transacted_at?->toIso8601String(),
            'created_at'      => $this->created_at?->toIso8601String(),
        ];
    }
}

