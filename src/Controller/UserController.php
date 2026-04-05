<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\UserService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Psr7\Response;

class UserController
{
    private UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function index(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $users = $this->userService->getAllUsers();
        $usersArray = array_map(fn($user) => $user->toArray(), $users);

        $response->getBody()->write(json_encode($usersArray));

        return $response->withHeader('Content-Type', 'application/json');
    }

    public function show(
        ServerRequestInterface $request,
        ResponseInterface $response,
        array $args
    ): ResponseInterface {
        $id = (int) $args['id'];
        $currentUserId = $request->getAttribute('user_id');
        $currentUserRole = $request->getAttribute('user_role');

        // Users can only view their own profile unless they're admin
        if ($currentUserRole !== 'admin' && $currentUserId !== $id) {
            $response->getBody()->write(json_encode([
                'error' => 'Forbidden',
                'message' => 'You can only view your own profile',
            ]));
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(403);
        }

        $user = $this->userService->getUserById($id);

        if (!$user) {
            $response->getBody()->write(json_encode([
                'error' => 'Not Found',
                'message' => 'User not found',
            ]));
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(404);
        }

        $response->getBody()->write(json_encode($user->toArray()));

        return $response->withHeader('Content-Type', 'application/json');
    }

    public function create(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
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
            $user = $this->userService->createUser($data);
            $response->getBody()->write(json_encode($user->toArray()));

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

    public function update(
        ServerRequestInterface $request,
        ResponseInterface $response,
        array $args
    ): ResponseInterface {
        $id = (int) $args['id'];
        $currentUserId = $request->getAttribute('user_id');
        $currentUserRole = $request->getAttribute('user_role');

        // Users can only update their own profile unless they're admin
        if ($currentUserRole !== 'admin' && $currentUserId !== $id) {
            $response->getBody()->write(json_encode([
                'error' => 'Forbidden',
                'message' => 'You can only update your own profile',
            ]));
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(403);
        }

        $data = json_decode((string) $request->getBody(), true);

        $user = $this->userService->updateUser($id, $data);

        if (!$user) {
            $response->getBody()->write(json_encode([
                'error' => 'Not Found',
                'message' => 'User not found',
            ]));
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(404);
        }

        $response->getBody()->write(json_encode($user->toArray()));

        return $response->withHeader('Content-Type', 'application/json');
    }

    public function delete(
        ServerRequestInterface $request,
        ResponseInterface $response,
        array $args
    ): ResponseInterface {
        $id = (int) $args['id'];

        $deleted = $this->userService->deleteUser($id);

        if (!$deleted) {
            $response->getBody()->write(json_encode([
                'error' => 'Not Found',
                'message' => 'User not found',
            ]));
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(404);
        }

        $response->getBody()->write(json_encode([
            'message' => 'User deleted successfully',
        ]));

        return $response->withHeader('Content-Type', 'application/json');
    }
}
