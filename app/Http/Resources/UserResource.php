<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'cpf' => $this->formatCpf($this->cpf),
            'position' => $this->position,
            'birth_date' => $this->birth_date?->format('d/m/Y'),
            'age' => $this->age,
            'cep' => $this->formatCep($this->cep),
            'address' => $this->address,
            'address_number' => $this->address_number,
            'address_complement' => $this->address_complement,
            'neighborhood' => $this->neighborhood,
            'city' => $this->city,
            'state' => $this->state,
            'full_address' => $this->full_address,
            'role' => $this->role,
            'role_label' => $this->getRoleLabel(),
            'manager' => $this->whenLoaded('manager', function () {
                return new UserBasicResource($this->manager);
            }),
            'employees_count' => $this->whenCounted('employees'),
            'time_records_count' => $this->whenCounted('timeRecords'),
            'created_at' => $this->created_at?->format('d/m/Y H:i:s'),
            'updated_at' => $this->updated_at?->format('d/m/Y H:i:s'),
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

    private function formatCep(?string $cep): ?string
    {
        if (!$cep || strlen($cep) !== 8) {
            return $cep;
        }

        return substr($cep, 0, 5) . '-' . substr($cep, 5, 3);
    }

    private function getRoleLabel(): string
    {
        return match ($this->role) {
            'admin' => 'Administrador',
            'manager' => 'Gestor',
            'employee' => 'Funcionário',
            default => 'Indefinido'
        };
    }
}
