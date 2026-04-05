<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\JWTService;
use App\Service\UserService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Psr7\Response;

class AuthController
{
    private UserService $userService;
    private JWTService $jwtService;

    public function __construct(UserService $userService, JWTService $jwtService)
    {
        $this->userService = $userService;
        $this->jwtService = $jwtService;
    }

    public function login(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $data = json_decode((string) $request->getBody(), true);

        if (!isset($data['email'], $data['password'])) {
            $response->getBody()->write(json_encode([
                'error' => 'Bad Request',
                'message' => 'Missing required fields: email, password',
            ]));
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(400);
        }

        $user = $this->userService->authenticate($data['email'], $data['password']);

        if (!$user) {
            $response->getBody()->write(json_encode([
                'error' => 'Unauthorized',
                'message' => 'Invalid credentials',
            ]));
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(401);
        }

        $token = $this->jwtService->generateToken(
            $user->getId(),
            $user->getEmail(),
            $user->getRole()
        );

        $response->getBody()->write(json_encode([
            'token' => $token,
            'user' => $user->toArray(),
        ]));

        return $response->withHeader('Content-Type', 'application/json');
    }

    public function register(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $data = json_decode((string) $request->getBody(), true);

        if (!isset($data['email'], $data['password'], $data['name'])) {
            $response->getBody()->write(json_encode([
                'error' => 'Bad Request',
                'message' => 'Missing required fields: email, password, name',
            ]));
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(400);
        }

        try {
            // Force role to 'user' for registration
            $data['role'] = 'user';
            $user = $this->userService->createUser($data);

            $token = $this->jwtService->generateToken(
                $user->getId(),
                $user->getEmail(),
                $user->getRole()
            );

            $response->getBody()->write(json_encode([
                'token' => $token,
                'user' => $user->toArray(),
            ]));

            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(201);
        } catch (\Exception $e) {
            $response->getBody()->write(json_encode([
                'error' => 'Server Error',
                'message' => $e->getMessage(),
            ]));
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(500);
        }
    }
}
