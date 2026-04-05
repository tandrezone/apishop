<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\User;
use App\Repository\UserRepository;

class UserService
{
    private UserRepository $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function getAllUsers(): array
    {
        return $this->userRepository->findAll();
    }

    public function getUserById(int $id): ?User
    {
        return $this->userRepository->findById($id);
    }

    public function createUser(array $data): User
    {
        // Hash the password
        $passwordHash = password_hash($data['password'], PASSWORD_DEFAULT);

        $user = new User(
            $data['email'],
            $passwordHash,
            $data['name'],
            $data['role'] ?? 'user'
        );

        return $this->userRepository->create($user);
    }

    public function updateUser(int $id, array $data): ?User
    {
        $user = $this->userRepository->findById($id);

        if (!$user) {
            return null;
        }

        if (isset($data['email'])) {
            $user->setEmail($data['email']);
        }

        if (isset($data['password'])) {
            $passwordHash = password_hash($data['password'], PASSWORD_DEFAULT);
            $user->setPasswordHash($passwordHash);
        }

        if (isset($data['name'])) {
            $user->setName($data['name']);
        }

        if (isset($data['role'])) {
            $user->setRole($data['role']);
        }

        return $this->userRepository->update($user);
    }

    public function deleteUser(int $id): bool
    {
        return $this->userRepository->delete($id);
    }

    public function authenticate(string $email, string $password): ?User
    {
        $user = $this->userRepository->findByEmail($email);

        if (!$user || !password_verify($password, $user->getPasswordHash())) {
            return null;
        }

        return $user;
    }
}
