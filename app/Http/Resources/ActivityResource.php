<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ActivityResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'description' => $this->description,
            'log_name' => $this->log_name,
            'event' => $this->event,
            'causer' => $this->causer?->only(['id', 'name', 'email']),
            'properties' => $this->properties,
            'created_at' => $this->created_at->toDateTimeString(),
        ];
    }
}
