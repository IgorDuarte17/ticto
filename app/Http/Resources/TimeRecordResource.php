<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TimeRecordResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'recorded_at' => $this->recorded_at->format('d/m/Y H:i:s'),
            'recorded_date' => $this->recorded_at->format('d/m/Y'),
            'recorded_time' => $this->recorded_at->format('H:i:s'),
            'recorded_weekday' => $this->getWeekdayName(),
            'user' => $this->whenLoaded('user', function () {
                return new UserBasicResource($this->user);
            }),
            'created_at' => $this->created_at?->format('d/m/Y H:i:s'),
        ];
    }

    private function getWeekdayName(): string
    {
        $weekdays = [
            0 => 'Domingo',
            1 => 'Segunda-feira',
            2 => 'Terça-feira',
            3 => 'Quarta-feira',
            4 => 'Quinta-feira',
            5 => 'Sexta-feira',
            6 => 'Sábado',
        ];

        return $weekdays[$this->recorded_at->dayOfWeek];
    }
}
