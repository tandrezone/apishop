<?php

declare(strict_types=1);

namespace App\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Psr7\Response;
use Slim\Routing\RouteContext;

class AuthorizationMiddleware implements MiddlewareInterface
{
    private const RESOURCE_PRODUCTS = 'products';
    private const RESOURCE_USERS = 'users';
    private const RESOURCE_ORDERS = 'orders';

    private const ACTION_READ = 'read';
    private const ACTION_CREATE = 'create';
    private const ACTION_UPDATE = 'update';
    private const ACTION_DELETE = 'delete';

    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        $userRole = $request->getAttribute('user_role');
        $userId = $request->getAttribute('user_id');

        // Admin has full access to everything
        if ($userRole === 'admin') {
            return $handler->handle($request);
        }

        // Determine the resource and action from the route
        $routeContext = RouteContext::fromRequest($request);
        $route = $routeContext->getRoute();

        if ($route === null) {
            return $handler->handle($request);
        }

        $routePattern = $route->getPattern();
        $method = $request->getMethod();

        $resource = $this->extractResource($routePattern);
        $action = $this->determineAction($method);
        $resourceId = $this->extractResourceId($route);

        // Check permissions based on role and resource
        if (!$this->hasPermission($userRole, $resource, $action, $userId, $resourceId)) {
            return $this->forbiddenResponse(
                'You do not have permission to perform this action'
            );
        }

        return $handler->handle($request);
    }

    private function hasPermission(
        string $role,
        ?string $resource,
        string $action,
        int $userId,
        ?int $resourceId
    ): bool {
        // User role permissions
        if ($role === 'user') {
            switch ($resource) {
                case self::RESOURCE_PRODUCTS:
                    // Users can only read products
                    return $action === self::ACTION_READ;

                case self::RESOURCE_USERS:
                    // Users can only read/update their own profile
                    if ($action === self::ACTION_READ || $action === self::ACTION_UPDATE) {
                        return $resourceId === null || $resourceId === $userId;
                    }
                    return false;

                case self::RESOURCE_ORDERS:
                    // Users can read/update only their own orders
                    if ($action === self::ACTION_READ || $action === self::ACTION_UPDATE) {
                        // For list endpoints (no resourceId), we'll filter by userId in the controller
                        // For specific resource, we need to verify ownership
                        // This requires checking in the controller as well
                        return true; // Controller must verify ownership
                    }
                    // Users can create orders
                    if ($action === self::ACTION_CREATE) {
                        return true;
                    }
                    return false;

                default:
                    return false;
            }
        }

        return false;
    }

    private function extractResource(string $routePattern): ?string
    {
        // Extract resource from route pattern (e.g., /api/products, /api/users/{id})
        if (preg_match('#/api/([^/]+)#', $routePattern, $matches)) {
            return $matches[1];
        }

        return null;
    }

    private function determineAction(string $method): string
    {
        return match ($method) {
            'GET' => self::ACTION_READ,
            'POST' => self::ACTION_CREATE,
            'PUT', 'PATCH' => self::ACTION_UPDATE,
            'DELETE' => self::ACTION_DELETE,
            default => self::ACTION_READ,
        };
    }

    private function extractResourceId($route): ?int
    {
        $args = $route->getArguments();

        if (isset($args['id'])) {
            return (int) $args['id'];
        }

        return null;
    }

    private function forbiddenResponse(string $message): ResponseInterface
    {
        $response = new Response();
        $response->getBody()->write(json_encode([
            'error' => 'Forbidden',
            'message' => $message,
        ]));

        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(403);
    }
}
