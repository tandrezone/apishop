<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Service\JWTService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Psr7\Response;

class AuthenticationMiddleware implements MiddlewareInterface
{
    private JWTService $jwtService;

    public function __construct(JWTService $jwtService)
    {
        $this->jwtService = $jwtService;
    }

    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        $authHeader = $request->getHeaderLine('Authorization');

        if (empty($authHeader)) {
            return $this->unauthorizedResponse('Missing Authorization header');
        }

        // Extract token from "Bearer <token>"
        if (!preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
            return $this->unauthorizedResponse('Invalid Authorization header format');
        }

        $token = $matches[1];
        $payload = $this->jwtService->validateToken($token);

        if ($payload === null) {
            return $this->unauthorizedResponse('Invalid or expired token');
        }

        // Add user information to request attributes
        $request = $request
            ->withAttribute('user_id', $payload['user_id'])
            ->withAttribute('user_email', $payload['email'])
            ->withAttribute('user_role', $payload['role']);

        return $handler->handle($request);
    }

    private function unauthorizedResponse(string $message): ResponseInterface
    {
        $response = new Response();
        $response->getBody()->write(json_encode([
            'error' => 'Unauthorized',
            'message' => $message,
        ]));

        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(401);
    }
}
