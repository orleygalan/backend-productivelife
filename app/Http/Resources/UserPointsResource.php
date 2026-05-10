<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserPointsResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'balance' => $this->balance,
            'total_earned' => $this->total_earned,
            'total_spent' => $this->total_spent,
        ];
    }
}