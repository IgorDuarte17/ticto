<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Criar um administrador padrão
        $admin = \App\Models\User::create([
            'name' => 'Administrador Principal',
            'cpf' => '12345678901',
            'email' => 'admin@ticto.com',
            'password' => bcrypt('admin123'),
            'position' => 'Gerente Geral',
            'birth_date' => '1985-05-15',
            'cep' => '01310100',
            'street' => 'Av. Paulista',
            'number' => '1000',
            'complement' => 'Andar 10',
            'neighborhood' => 'Bela Vista',
            'city' => 'São Paulo',
            'state' => 'SP',
            'role' => 'admin',
            'manager_id' => null,
        ]);

        // Criar alguns funcionários de exemplo
        \App\Models\User::create([
            'name' => 'João Silva Santos',
            'cpf' => '98765432100',
            'email' => 'joao@ticto.com',
            'password' => bcrypt('123456'),
            'position' => 'Desenvolvedor',
            'birth_date' => '1990-03-20',
            'cep' => '04567890',
            'street' => 'Rua das Flores',
            'number' => '123',
            'complement' => 'Apto 45',
            'neighborhood' => 'Vila Madalena',
            'city' => 'São Paulo',
            'state' => 'SP',
            'role' => 'employee',
            'manager_id' => $admin->id,
        ]);

        \App\Models\User::create([
            'name' => 'Maria Oliveira Costa',
            'cpf' => '11122233344',
            'email' => 'maria@ticto.com',
            'password' => bcrypt('123456'),
            'position' => 'Analista',
            'birth_date' => '1988-07-10',
            'cep' => '05678901',
            'street' => 'Av. Rebouças',
            'number' => '456',
            'complement' => null,
            'neighborhood' => 'Pinheiros',
            'city' => 'São Paulo',
            'state' => 'SP',
            'role' => 'employee',
            'manager_id' => $admin->id,
        ]);

        // Criar alguns registros de ponto de exemplo
        $employees = \App\Models\User::where('role', 'employee')->get();
        
        foreach ($employees as $employee) {
            // Registros dos últimos 5 dias
            for ($i = 4; $i >= 0; $i--) {
                $date = now()->subDays($i);
                
                // Entrada pela manhã
                \App\Models\TimeRecord::create([
                    'user_id' => $employee->id,
                    'recorded_at' => $date->copy()->setTime(8, rand(0, 30)),
                ]);
                
                // Saída para almoço
                \App\Models\TimeRecord::create([
                    'user_id' => $employee->id,
                    'recorded_at' => $date->copy()->setTime(12, rand(0, 30)),
                ]);
                
                // Retorno do almoço
                \App\Models\TimeRecord::create([
                    'user_id' => $employee->id,
                    'recorded_at' => $date->copy()->setTime(13, rand(30, 59)),
                ]);
                
                // Saída no final do dia
                \App\Models\TimeRecord::create([
                    'user_id' => $employee->id,
                    'recorded_at' => $date->copy()->setTime(17, rand(30, 59)),
                ]);
            }
        }
    }
}
