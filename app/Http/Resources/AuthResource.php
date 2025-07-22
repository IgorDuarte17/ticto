<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AuthResource extends JsonResource
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
            'permissions' => $this->getPermissions(),
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

    /**
     * Obter permissões do usuário
     */
    private function getPermissions(): array
    {
        $permissions = [
            'can_record_time' => $this->role === 'employee',
            'can_change_password' => true,
            'can_view_own_records' => true,
        ];

        if (in_array($this->role, ['admin', 'manager'])) {
            $permissions = array_merge($permissions, [
                'can_manage_employees' => true,
                'can_view_all_records' => $this->role === 'admin',
                'can_view_subordinate_records' => true,
                'can_create_employees' => true,
                'can_update_employees' => true,
                'can_delete_employees' => true,
            ]);
        }

        return $permissions;
    }
}
