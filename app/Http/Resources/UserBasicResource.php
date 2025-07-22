<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserBasicResource extends JsonResource
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
            'position' => $this->position,
            'role' => $this->role,
            'role_label' => $this->getRoleLabel(),
        ];
    }

    /**
     * Obter label do role
     */
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
