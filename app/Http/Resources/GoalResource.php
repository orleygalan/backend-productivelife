<?php

namespace App\Http\Resources;

use App\Models\Goal;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GoalResource extends JsonResource
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
            'user_id' => $this->user_id,
            'title' => $this->title,
            'description' => $this->description,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'term' => $this->term,
            'term_label' => Goal::termLabel($this->term),
            'status' => $this->status,
            'current_streak' => $this->current_streak,
            'max_streak' => $this->max_streak,
            'missed_days' => $this->missed_days,
            'bonus_granted' => $this->bonus_granted,
            'tasks' => GoalTaskResource::collection(
                $this->whenLoaded('tasks')
            ),
            'created_at' => $this->created_at,
        ];
    }
}
