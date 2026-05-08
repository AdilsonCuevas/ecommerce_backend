<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => base64_encode($this->uuid),
            'items' => CartItemResource::collection($this->whenLoaded('items')),
            'total' => $this->items->sum(function ($item) {
                return $item->product->price * $item->quantity;
            }),
        ];
    }
}