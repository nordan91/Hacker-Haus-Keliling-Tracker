<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DailyInventoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
            'shift_id'      => $this->shift_id,
            'product_id'    => $this->product_id,
            'product'       => new ProductResource($this->whenLoaded('product')),
            'start_qty'     => $this->start_qty,
            'sold_qty'      => $this->sold_qty,
            'remaining_qty' => $this->remaining_qty,
        ];
    }
}

