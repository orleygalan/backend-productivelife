<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PointLogResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'amount' => $this->amount,
            'type' => $this->type,
            'description' => $this->description,
            'goal' => new GoalResource($this->whenLoaded('goal')),
            'goal_task' => new GoalTaskResource($this->whenLoaded('goalTask')),
            'created_at' => $this->created_at,
        ];
    }
}
