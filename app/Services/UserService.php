<?php

namespace App\Services;

use App\Contracts\UserRepositoryInterface;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class UserService
{
    public function __construct(
        private UserRepositoryInterface $userRepository
    ) {}

    public function createEmployee(array $data, int $managerId): User
    {
        if ($this->userRepository->existsByCpf($data['cpf'])) {
            throw ValidationException::withMessages([
                'cpf' => ['Este CPF já está cadastrado no sistema.']
            ]);
        }
        
        if ($this->userRepository->existsByEmail($data['email'])) {
            throw ValidationException::withMessages([
                'email' => ['Este email já está cadastrado no sistema.']
            ]);
        }
        
        if (!$this->isValidCpf($data['cpf'])) {
            throw ValidationException::withMessages([
                'cpf' => ['CPF inválido.']
            ]);
        }
        
        $userData = array_merge($data, [
            'role' => 'employee',
            'manager_id' => $managerId,
            'password' => Hash::make($data['password'])
        ]);
        
        return $this->userRepository->create($userData);
    }
    
    public function updateEmployee(int $id, array $data): User
    {
        $user = $this->userRepository->findById($id);
        
        if (!$user) {
            throw ValidationException::withMessages([
                'user' => ['Funcionário não encontrado.']
            ]);
        }
        
        if (isset($data['cpf']) && $this->userRepository->existsByCpf($data['cpf'], $id)) {
            throw ValidationException::withMessages([
                'cpf' => ['Este CPF já está cadastrado no sistema.']
            ]);
        }
        
        if (isset($data['email']) && $this->userRepository->existsByEmail($data['email'], $id)) {
            throw ValidationException::withMessages([
                'email' => ['Este email já está cadastrado no sistema.']
            ]);
        }
        
        if (isset($data['cpf']) && !$this->isValidCpf($data['cpf'])) {
            throw ValidationException::withMessages([
                'cpf' => ['CPF inválido.']
            ]);
        }
        
        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }
        
        return $this->userRepository->update($id, $data);
    }
    
    public function deleteEmployee(int $id): bool
    {
        $user = $this->userRepository->findById($id);
        
        if (!$user) {
            throw ValidationException::withMessages([
                'user' => ['Funcionário não encontrado.']
            ]);
        }
        
        if ($user->role === 'admin') {
            throw ValidationException::withMessages([
                'user' => ['Não é possível deletar um administrador.']
            ]);
        }
        
        return $this->userRepository->delete($id);
    }

    public function getEmployeesByManager(int $managerId): Collection
    {
        return $this->userRepository->getEmployeesByManager($managerId);
    }

    public function getAllEmployees(): Collection
    {
        return $this->userRepository->getAllEmployees();
    }
    
    public function getPaginatedEmployees(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $filters['role'] = 'employee'; // Garantir que só retorne funcionários
        return $this->userRepository->getPaginated($filters, $perPage);
    }
    
    public function changePassword(int $userId, string $currentPassword, string $newPassword): bool
    {
        $user = $this->userRepository->findById($userId);
        
        if (!$user) {
            throw ValidationException::withMessages([
                'user' => ['Usuário não encontrado.']
            ]);
        }
        
        if (!Hash::check($currentPassword, $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['Senha atual incorreta.']
            ]);
        }
        
        $this->userRepository->update($userId, [
            'password' => Hash::make($newPassword)
        ]);
        
        return true;
    }
    
    public function findById(int $id): ?User
    {
        return $this->userRepository->findById($id);
    }

    private function isValidCpf(string $cpf): bool
    {
        $cpf = preg_replace('/[^0-9]/', '', $cpf);
        
        if (strlen($cpf) !== 11) {
            return false;
        }
        
        if (preg_match('/(\d)\1{10}/', $cpf)) {
            return false;
        }
        
        $sum = 0;
        for ($i = 0; $i < 9; $i++) {
            $sum += intval($cpf[$i]) * (10 - $i);
        }
        $remainder = $sum % 11;
        $digit1 = $remainder < 2 ? 0 : 11 - $remainder;
        
        if (intval($cpf[9]) !== $digit1) {
            return false;
        }
        
        $sum = 0;
        for ($i = 0; $i < 10; $i++) {
            $sum += intval($cpf[$i]) * (11 - $i);
        }
        $remainder = $sum % 11;
        $digit2 = $remainder < 2 ? 0 : 11 - $remainder;
        
        return intval($cpf[10]) === $digit2;
    }
}
