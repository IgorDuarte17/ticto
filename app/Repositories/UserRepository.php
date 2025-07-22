<?php

namespace App\Repositories;

use App\Contracts\UserRepositoryInterface;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class UserRepository implements UserRepositoryInterface
{
    public function findById(int $id): ?User
    {
        return User::find($id);
    }
    
    public function findByEmail(string $email): ?User
    {
        return User::where('email', $email)->first();
    }
    
    public function findByCpf(string $cpf): ?User
    {
        return User::where('cpf', $cpf)->first();
    }
    
    public function create(array $data): User
    {
        return User::create($data);
    }
    
    public function update(int $id, array $data): User
    {
        $user = User::findOrFail($id);
        $user->update($data);
        return $user->fresh();
    }
    
    public function delete(int $id): bool
    {
        $user = User::findOrFail($id);
        return $user->delete();
    }
    
    public function getEmployeesByManager(int $managerId): Collection
    {
        return User::where('manager_id', $managerId)
                   ->where('role', 'employee')
                   ->get();
    }
    
    public function getAllEmployees(): Collection
    {
        return User::where('role', 'employee')->get();
    }
    
    public function getPaginated(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = User::query();
        
        if (isset($filters['role'])) {
            $query->where('role', $filters['role']);
        }
        
        if (isset($filters['manager_id'])) {
            $query->where('manager_id', $filters['manager_id']);
        }
        
        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('cpf', 'like', "%{$search}%")
                  ->orWhere('position', 'like', "%{$search}%");
            });
        }
        
        return $query->with('manager')->paginate($perPage);
    }
    
    public function existsByCpf(string $cpf, ?int $excludeId = null): bool
    {
        $query = User::where('cpf', $cpf);
        
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }
        
        return $query->exists();
    }
    
    public function existsByEmail(string $email, ?int $excludeId = null): bool
    {
        $query = User::where('email', $email);
        
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }
        
        return $query->exists();
    }
}
