<?php

declare(strict_types=1);

namespace App\Repository;

use App\Config\Database;
use App\Entity\User;
use PDO;

class UserRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function findAll(): array
    {
        $stmt = $this->db->query(
            'SELECT * FROM users ORDER BY created_at DESC'
        );
        $results = $stmt->fetchAll();

        return array_map([$this, 'hydrateUser'], $results);
    }

    public function findById(int $id): ?User
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM users WHERE id = :id'
        );
        $stmt->execute(['id' => $id]);
        $result = $stmt->fetch();

        return $result ? $this->hydrateUser($result) : null;
    }

    public function findByEmail(string $email): ?User
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM users WHERE email = :email'
        );
        $stmt->execute(['email' => $email]);
        $result = $stmt->fetch();

        return $result ? $this->hydrateUser($result) : null;
    }

    public function create(User $user): User
    {
        $stmt = $this->db->prepare(
            'INSERT INTO users (email, password_hash, name, role, created_at) 
             VALUES (:email, :password_hash, :name, :role, :created_at)'
        );

        $stmt->execute([
            'email' => $user->getEmail(),
            'password_hash' => $user->getPasswordHash(),
            'name' => $user->getName(),
            'role' => $user->getRole(),
            'created_at' => $user->getCreatedAt()->format('Y-m-d H:i:s'),
        ]);

        $user->setId((int) $this->db->lastInsertId());

        return $user;
    }

    public function update(User $user): User
    {
        $user->setUpdatedAt(new \DateTime());

        $stmt = $this->db->prepare(
            'UPDATE users 
             SET email = :email, password_hash = :password_hash, 
                 name = :name, role = :role, updated_at = :updated_at 
             WHERE id = :id'
        );

        $stmt->execute([
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            'password_hash' => $user->getPasswordHash(),
            'name' => $user->getName(),
            'role' => $user->getRole(),
            'updated_at' => $user->getUpdatedAt()->format('Y-m-d H:i:s'),
        ]);

        return $user;
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM users WHERE id = :id');
        $stmt->execute(['id' => $id]);

        return $stmt->rowCount() > 0;
    }

    private function hydrateUser(array $data): User
    {
        $user = new User(
            $data['email'],
            $data['password_hash'],
            $data['name'],
            $data['role']
        );

        $user->setId((int) $data['id']);

        if (isset($data['updated_at'])) {
            $user->setUpdatedAt(new \DateTime($data['updated_at']));
        }

        return $user;
    }
}
