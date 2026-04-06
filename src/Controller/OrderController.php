<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\OrderService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Psr7\Response;

class OrderController extends BaseController
{
    private OrderService $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    public function index(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $currentUserId = $request->getAttribute('user_id');
        $currentUserRole = $request->getAttribute('user_role');

        // Regular users can only see their own orders
        if ($currentUserRole === 'admin') {
            $orders = $this->orderService->getAllOrders();
        } else {
            $orders = $this->orderService->getOrdersByUserId($currentUserId);
        }

        $ordersArray = array_map(fn($order) => $order->toArray(), $orders);

        $response->getBody()->write(json_encode($ordersArray));

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

        $order = $this->orderService->getOrderById($id);

        if (!$order) {
            $response->getBody()->write(json_encode([
                'error' => 'Not Found',
                'message' => 'Order not found',
            ]));
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(404);
        }

        // Users can only view their own orders unless they're admin
        if ($currentUserRole !== 'admin' && $order->getUserId() !== $currentUserId) {
            $response->getBody()->write(json_encode([
                'error' => 'Forbidden',
                'message' => 'You can only view your own orders',
            ]));
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(403);
        }

        $response->getBody()->write(json_encode($order->toArray()));

        return $response->withHeader('Content-Type', 'application/json');
    }

    public function create(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $data = $this->parseJsonBody((string) $request->getBody());

        if ($data === null) {
            return $this->jsonErrorResponse(
                'Bad Request',
                'Invalid JSON in request body'
            );
        }

        $currentUserId = $request->getAttribute('user_id');
        $currentUserRole = $request->getAttribute('user_role');

        // Ensure user_id matches the authenticated user unless admin
        if ($currentUserRole !== 'admin') {
            $data['user_id'] = $currentUserId;
        }

        if (!isset($data['user_id'], $data['total_amount'])) {
            return $this->jsonErrorResponse(
                'Bad Request',
                'Missing required fields: user_id, total_amount'
            );
        }

        try {
            $order = $this->orderService->createOrder($data);
            $response->getBody()->write(json_encode($order->toArray()));

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

        $order = $this->orderService->getOrderById($id);

        if (!$order) {
            $response->getBody()->write(json_encode([
                'error' => 'Not Found',
                'message' => 'Order not found',
            ]));
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(404);
        }

        // Users can only update their own orders unless they're admin
        if ($currentUserRole !== 'admin' && $order->getUserId() !== $currentUserId) {
            return $this->jsonErrorResponse(
                'Forbidden',
                'You can only update your own orders',
                403
            );
        }

        $data = $this->parseJsonBody((string) $request->getBody());

        if ($data === null) {
            return $this->jsonErrorResponse(
                'Bad Request',
                'Invalid JSON in request body'
            );
        }

        $order = $this->orderService->updateOrder($id, $data);

        if (!$order) {
            $response->getBody()->write(json_encode([
                'error' => 'Not Found',
                'message' => 'Order not found',
            ]));
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(404);
        }

        $response->getBody()->write(json_encode($order->toArray()));

        return $response->withHeader('Content-Type', 'application/json');
    }

    public function delete(
        ServerRequestInterface $request,
        ResponseInterface $response,
        array $args
    ): ResponseInterface {
        $id = (int) $args['id'];

        $deleted = $this->orderService->deleteOrder($id);

        if (!$deleted) {
            $response->getBody()->write(json_encode([
                'error' => 'Not Found',
                'message' => 'Order not found',
            ]));
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(404);
        }

        $response->getBody()->write(json_encode([
            'message' => 'Order deleted successfully',
        ]));

        return $response->withHeader('Content-Type', 'application/json');
    }
}
