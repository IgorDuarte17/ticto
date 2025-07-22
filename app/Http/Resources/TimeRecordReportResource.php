<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TimeRecordReportResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'employee_name' => $this->user->name,
            'employee_cpf' => $this->formatCpf($this->user->cpf),
            'position' => $this->user->position,
            'age' => $this->user->age,
            'manager_name' => $this->user->manager?->name ?? 'N/A',
            'recorded_at' => $this->recorded_at->format('d/m/Y H:i:s'),
            'recorded_date' => $this->recorded_at->format('d/m/Y'),
            'recorded_time' => $this->recorded_at->format('H:i:s'),
            'recorded_weekday' => $this->getWeekdayName(),
            'recorded_period' => $this->getPeriodName(),
        ];
    }

    private function formatCpf(?string $cpf): ?string
    {
        if (!$cpf || strlen($cpf) !== 11) {
            return $cpf;
        }

        return substr($cpf, 0, 3) . '.' . 
               substr($cpf, 3, 3) . '.' . 
               substr($cpf, 6, 3) . '-' . 
               substr($cpf, 9, 2);
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

    private function getPeriodName(): string
    {
        $hour = (int) $this->recorded_at->format('H');

        return match (true) {
            $hour >= 5 && $hour < 12 => 'Manhã',
            $hour >= 12 && $hour < 18 => 'Tarde',
            $hour >= 18 && $hour < 24 => 'Noite',
            default => 'Madrugada'
        };
    }
}
