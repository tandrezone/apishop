<?php

declare(strict_types=1);

use App\Controller\AuthController;
use App\Controller\OrderController;
use App\Controller\ProductController;
use App\Controller\UserController;
use App\Middleware\AuthenticationMiddleware;
use App\Middleware\AuthorizationMiddleware;
use App\Repository\OrderRepository;
use App\Repository\ProductRepository;
use App\Repository\UserRepository;
use App\Service\JWTService;
use App\Service\OrderService;
use App\Service\ProductService;
use App\Service\UserService;
use Dotenv\Dotenv;
use Slim\Factory\AppFactory;

require __DIR__ . '/../vendor/autoload.php';

// Load environment variables
if (file_exists(__DIR__ . '/../.env')) {
    $dotenv = Dotenv::createImmutable(__DIR__ . '/..');
    $dotenv->load();
}

// Dependency injection container setup
$container = new \DI\Container();

// Register repositories
$container->set(UserRepository::class, function () {
    return new UserRepository();
});

$container->set(ProductRepository::class, function () {
    return new ProductRepository();
});

$container->set(OrderRepository::class, function () {
    return new OrderRepository();
});

// Register services
$container->set(JWTService::class, function () {
    return new JWTService();
});

$container->set(UserService::class, function ($c) {
    return new UserService($c->get(UserRepository::class));
});

$container->set(ProductService::class, function ($c) {
    return new ProductService($c->get(ProductRepository::class));
});

$container->set(OrderService::class, function ($c) {
    return new OrderService($c->get(OrderRepository::class));
});

// Register controllers
$container->set(AuthController::class, function ($c) {
    return new AuthController(
        $c->get(UserService::class),
        $c->get(JWTService::class)
    );
});

$container->set(UserController::class, function ($c) {
    return new UserController($c->get(UserService::class));
});

$container->set(ProductController::class, function ($c) {
    return new ProductController($c->get(ProductService::class));
});

$container->set(OrderController::class, function ($c) {
    return new OrderController($c->get(OrderService::class));
});

// Register middleware
$container->set(AuthenticationMiddleware::class, function ($c) {
    return new AuthenticationMiddleware($c->get(JWTService::class));
});

$container->set(AuthorizationMiddleware::class, function () {
    return new AuthorizationMiddleware();
});

// Set container to app
AppFactory::setContainer($container);
$app = AppFactory::create();

// Add routing middleware
$app->addRoutingMiddleware();

// Add error middleware
$app->addErrorMiddleware(
    (bool) ($_ENV['APP_DEBUG'] ?? true),
    true,
    true
);

// Public routes (no authentication required)
$app->post('/api/auth/login', [AuthController::class, 'login']);
$app->post('/api/auth/register', [AuthController::class, 'register']);

// Protected routes (authentication required)
$app->group('/api', function ($group) use ($container) {
    // User routes
    $group->get('/users', [UserController::class, 'index']);
    $group->get('/users/{id}', [UserController::class, 'show']);
    $group->post('/users', [UserController::class, 'create']);
    $group->put('/users/{id}', [UserController::class, 'update']);
    $group->delete('/users/{id}', [UserController::class, 'delete']);

    // Product routes
    $group->get('/products', [ProductController::class, 'index']);
    $group->get('/products/{id}', [ProductController::class, 'show']);
    $group->post('/products', [ProductController::class, 'create']);
    $group->put('/products/{id}', [ProductController::class, 'update']);
    $group->delete('/products/{id}', [ProductController::class, 'delete']);

    // Order routes
    $group->get('/orders', [OrderController::class, 'index']);
    $group->get('/orders/{id}', [OrderController::class, 'show']);
    $group->post('/orders', [OrderController::class, 'create']);
    $group->put('/orders/{id}', [OrderController::class, 'update']);
    $group->delete('/orders/{id}', [OrderController::class, 'delete']);
})
    ->add($container->get(AuthorizationMiddleware::class))
    ->add($container->get(AuthenticationMiddleware::class));

$app->run();
