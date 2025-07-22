<?php

namespace App\Contracts;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface UserRepositoryInterface
{
    public function findById(int $id): ?User;
    
    public function findByEmail(string $email): ?User;
    
    public function findByCpf(string $cpf): ?User;
    
    public function create(array $data): User;
    
    public function update(int $id, array $data): User;
    
    public function delete(int $id): bool;
    
    public function getEmployeesByManager(int $managerId): Collection;
    
    public function getAllEmployees(): Collection;
    
    public function getPaginated(array $filters = [], int $perPage = 15): LengthAwarePaginator;
    
    public function existsByCpf(string $cpf, ?int $excludeId = null): bool;
    
    public function existsByEmail(string $email, ?int $excludeId = null): bool;
}
