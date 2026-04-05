<?php

declare(strict_types=1);

namespace App\Service;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JWTService
{
    private string $secretKey;
    private int $expiryTime;

    public function __construct()
    {
        $this->secretKey = $_ENV['JWT_SECRET'] ?? 'default-secret-key-change-me';
        $this->expiryTime = (int) ($_ENV['JWT_EXPIRY'] ?? 3600);
    }

    public function generateToken(int $userId, string $email, string $role): string
    {
        $issuedAt = time();
        $expirationTime = $issuedAt + $this->expiryTime;

        $payload = [
            'iat' => $issuedAt,
            'exp' => $expirationTime,
            'user_id' => $userId,
            'email' => $email,
            'role' => $role,
        ];

        return JWT::encode($payload, $this->secretKey, 'HS256');
    }

    public function validateToken(string $token): ?array
    {
        try {
            $decoded = JWT::decode($token, new Key($this->secretKey, 'HS256'));
            return (array) $decoded;
        } catch (\Exception $e) {
            return null;
        }
    }

    public function getUserIdFromToken(string $token): ?int
    {
        $payload = $this->validateToken($token);
        return $payload['user_id'] ?? null;
    }

    public function getRoleFromToken(string $token): ?string
    {
        $payload = $this->validateToken($token);
        return $payload['role'] ?? null;
    }
}
