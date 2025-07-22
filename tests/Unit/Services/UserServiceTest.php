<?php

use App\Contracts\UserRepositoryInterface;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

beforeEach(function () {
    $this->userRepository = Mockery::mock(UserRepositoryInterface::class);
    $this->service = new UserService($this->userRepository);
});

afterEach(function () {
    Mockery::close();
});

describe('UserService', function () {
    describe('createEmployee', function () {
        it('should create employee with valid data', function () {
            $data = [
                'name' => 'John Doe',
                'email' => 'john@example.com',
                'cpf' => '12345678909',
                'password' => 'password123'
            ];
            $managerId = 1;

            $expectedUser = new User([
                'id' => 1,
                'name' => 'John Doe',
                'email' => 'john@example.com',
                'cpf' => '12345678909',
                'role' => 'employee',
                'manager_id' => $managerId
            ]);

            $this->userRepository
                ->shouldReceive('existsByCpf')
                ->with($data['cpf'])
                ->once()
                ->andReturn(false);

            $this->userRepository
                ->shouldReceive('existsByEmail')
                ->with($data['email'])
                ->once()
                ->andReturn(false);

            $this->userRepository
                ->shouldReceive('create')
                ->once()
                ->andReturn($expectedUser);

            Hash::shouldReceive('make')
                ->with($data['password'])
                ->once()
                ->andReturn('hashed_password');

            $result = $this->service->createEmployee($data, $managerId);

            expect($result)->toBeInstanceOf(User::class);
        });

        it('should throw exception when CPF already exists', function () {
            $data = [
                'name' => 'John Doe',
                'email' => 'john@example.com',
                'cpf' => '12345678909',
                'password' => 'password123'
            ];
            $managerId = 1;

            $this->userRepository
                ->shouldReceive('existsByCpf')
                ->with($data['cpf'])
                ->once()
                ->andReturn(true);

            expect(fn() => $this->service->createEmployee($data, $managerId))
                ->toThrow(ValidationException::class);
        });

        it('should throw exception when email already exists', function () {
            $data = [
                'name' => 'John Doe',
                'email' => 'john@example.com',
                'cpf' => '12345678909',
                'password' => 'password123'
            ];
            $managerId = 1;

            $this->userRepository
                ->shouldReceive('existsByCpf')
                ->with($data['cpf'])
                ->once()
                ->andReturn(false);

            $this->userRepository
                ->shouldReceive('existsByEmail')
                ->with($data['email'])
                ->once()
                ->andReturn(true);

            expect(fn() => $this->service->createEmployee($data, $managerId))
                ->toThrow(ValidationException::class);
        });

        it('should throw exception when CPF is invalid', function () {
            $data = [
                'name' => 'John Doe',
                'email' => 'john@example.com',
                'cpf' => '11111111111', // CPF inválido
                'password' => 'password123'
            ];
            $managerId = 1;

            $this->userRepository
                ->shouldReceive('existsByCpf')
                ->with($data['cpf'])
                ->once()
                ->andReturn(false);

            $this->userRepository
                ->shouldReceive('existsByEmail')
                ->with($data['email'])
                ->once()
                ->andReturn(false);

            expect(fn() => $this->service->createEmployee($data, $managerId))
                ->toThrow(ValidationException::class);
        });
    });

    describe('updateEmployee', function () {
        it('should update employee with valid data', function () {
            $id = 1;
            $data = [
                'name' => 'John Updated',
                'email' => 'john.updated@example.com'
            ];

            $user = new User([
                'id' => $id,
                'name' => 'John Doe',
                'email' => 'john@example.com',
                'role' => 'employee'
            ]);

            $updatedUser = new User([
                'id' => $id,
                'name' => 'John Updated',
                'email' => 'john.updated@example.com',
                'role' => 'employee'
            ]);

            $this->userRepository
                ->shouldReceive('findById')
                ->with($id)
                ->once()
                ->andReturn($user);

            $this->userRepository
                ->shouldReceive('existsByEmail')
                ->with($data['email'], $id)
                ->once()
                ->andReturn(false);

            $this->userRepository
                ->shouldReceive('update')
                ->with($id, $data)
                ->once()
                ->andReturn($updatedUser);

            $result = $this->service->updateEmployee($id, $data);

            expect($result)->toBeInstanceOf(User::class);
        });

        it('should throw exception when user not found', function () {
            $id = 999;
            $data = ['name' => 'John Updated'];

            $this->userRepository
                ->shouldReceive('findById')
                ->with($id)
                ->once()
                ->andReturn(null);

            expect(fn() => $this->service->updateEmployee($id, $data))
                ->toThrow(ValidationException::class);
        });

        it('should update password when provided', function () {
            $id = 1;
            $data = [
                'name' => 'John Updated',
                'password' => 'newpassword123'
            ];

            $user = new User([
                'id' => $id,
                'name' => 'John Doe',
                'role' => 'employee'
            ]);

            $this->userRepository
                ->shouldReceive('findById')
                ->with($id)
                ->once()
                ->andReturn($user);

            Hash::shouldReceive('make')
                ->with($data['password'])
                ->once()
                ->andReturn('hashed_new_password');

            $this->userRepository
                ->shouldReceive('update')
                ->once()
                ->andReturn($user);

            $result = $this->service->updateEmployee($id, $data);

            expect($result)->toBeInstanceOf(User::class);
        });
    });

    describe('deleteEmployee', function () {
        it('should delete employee successfully', function () {
            $id = 1;
            $user = new User([
                'id' => $id,
                'name' => 'John Doe',
                'role' => 'employee'
            ]);

            $this->userRepository
                ->shouldReceive('findById')
                ->with($id)
                ->once()
                ->andReturn($user);

            $this->userRepository
                ->shouldReceive('delete')
                ->with($id)
                ->once()
                ->andReturn(true);

            $result = $this->service->deleteEmployee($id);

            expect($result)->toBeTrue();
        });

        it('should throw exception when user not found', function () {
            $id = 999;

            $this->userRepository
                ->shouldReceive('findById')
                ->with($id)
                ->once()
                ->andReturn(null);

            expect(fn() => $this->service->deleteEmployee($id))
                ->toThrow(ValidationException::class);
        });

        it('should throw exception when trying to delete admin', function () {
            $id = 1;
            $user = new User([
                'id' => $id,
                'name' => 'Admin User',
                'role' => 'admin'
            ]);

            $this->userRepository
                ->shouldReceive('findById')
                ->with($id)
                ->once()
                ->andReturn($user);

            expect(fn() => $this->service->deleteEmployee($id))
                ->toThrow(ValidationException::class);
        });
    });

    describe('changePassword', function () {
        it('should change password with valid current password', function () {
            $userId = 1;
            $currentPassword = 'oldpassword';
            $newPassword = 'newpassword123';

            $user = new User([
                'id' => $userId,
                'name' => 'John Doe',
                'password' => 'hashed_old_password'
            ]);

            $this->userRepository
                ->shouldReceive('findById')
                ->with($userId)
                ->once()
                ->andReturn($user);

            Hash::shouldReceive('check')
                ->with($currentPassword, $user->password)
                ->once()
                ->andReturn(true);

            Hash::shouldReceive('make')
                ->with($newPassword)
                ->once()
                ->andReturn('hashed_new_password');

            $this->userRepository
                ->shouldReceive('update')
                ->with($userId, ['password' => 'hashed_new_password'])
                ->once();

            $result = $this->service->changePassword($userId, $currentPassword, $newPassword);

            expect($result)->toBeTrue();
        });

        it('should throw exception when user not found', function () {
            $userId = 999;
            $currentPassword = 'oldpassword';
            $newPassword = 'newpassword123';

            $this->userRepository
                ->shouldReceive('findById')
                ->with($userId)
                ->once()
                ->andReturn(null);

            expect(fn() => $this->service->changePassword($userId, $currentPassword, $newPassword))
                ->toThrow(ValidationException::class);
        });

        it('should throw exception when current password is incorrect', function () {
            $userId = 1;
            $currentPassword = 'wrongpassword';
            $newPassword = 'newpassword123';

            $user = new User([
                'id' => $userId,
                'name' => 'John Doe',
                'password' => 'hashed_old_password'
            ]);

            $this->userRepository
                ->shouldReceive('findById')
                ->with($userId)
                ->once()
                ->andReturn($user);

            Hash::shouldReceive('check')
                ->with($currentPassword, $user->password)
                ->once()
                ->andReturn(false);

            expect(fn() => $this->service->changePassword($userId, $currentPassword, $newPassword))
                ->toThrow(ValidationException::class);
        });
    });

    describe('getAllEmployees', function () {
        it('should return all employees', function () {
            $expectedCollection = new Collection([]);

            $this->userRepository
                ->shouldReceive('getAllEmployees')
                ->once()
                ->andReturn($expectedCollection);

            $result = $this->service->getAllEmployees();

            expect($result)->toBeInstanceOf(Collection::class);
        });
    });

    describe('getEmployeesByManager', function () {
        it('should return employees by manager', function () {
            $managerId = 1;
            $expectedCollection = new Collection([]);

            $this->userRepository
                ->shouldReceive('getEmployeesByManager')
                ->with($managerId)
                ->once()
                ->andReturn($expectedCollection);

            $result = $this->service->getEmployeesByManager($managerId);

            expect($result)->toBeInstanceOf(Collection::class);
        });
    });

    describe('findById', function () {
        it('should return user when found', function () {
            $id = 1;
            $expectedUser = new User([
                'id' => $id,
                'name' => 'John Doe'
            ]);

            $this->userRepository
                ->shouldReceive('findById')
                ->with($id)
                ->once()
                ->andReturn($expectedUser);

            $result = $this->service->findById($id);

            expect($result)->toBeInstanceOf(User::class);
        });

        it('should return null when user not found', function () {
            $id = 999;

            $this->userRepository
                ->shouldReceive('findById')
                ->with($id)
                ->once()
                ->andReturn(null);

            $result = $this->service->findById($id);

            expect($result)->toBeNull();
        });
    });
});
