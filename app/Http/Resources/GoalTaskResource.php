<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GoalTaskResource extends JsonResource
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
            'goal_id' => $this->goal_id,
            'title' => $this->title,
            'xp_per_day' => $this->xp_per_day,
            'is_editable' => $this->isEditable(),
            'completed' => $this->completed ?? false,
            'completed_at' => $this->completed_at ?? null,
            'created_at' => $this->created_at,
        ];
    }
}
