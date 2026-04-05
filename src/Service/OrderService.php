<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Order;
use App\Repository\OrderRepository;

class OrderService
{
    private OrderRepository $orderRepository;

    public function __construct(OrderRepository $orderRepository)
    {
        $this->orderRepository = $orderRepository;
    }

    public function getAllOrders(): array
    {
        return $this->orderRepository->findAll();
    }

    public function getOrderById(int $id): ?Order
    {
        return $this->orderRepository->findById($id);
    }

    public function getOrdersByUserId(int $userId): array
    {
        return $this->orderRepository->findByUserId($userId);
    }

    public function createOrder(array $data): Order
    {
        $order = new Order(
            (int) $data['user_id'],
            (float) $data['total_amount'],
            $data['status'] ?? 'pending'
        );

        if (isset($data['items']) && is_array($data['items'])) {
            $order->setItems($data['items']);
        }

        return $this->orderRepository->create($order);
    }

    public function updateOrder(int $id, array $data): ?Order
    {
        $order = $this->orderRepository->findById($id);

        if (!$order) {
            return null;
        }

        if (isset($data['status'])) {
            $order->setStatus($data['status']);
        }

        if (isset($data['total_amount'])) {
            $order->setTotalAmount((float) $data['total_amount']);
        }

        if (isset($data['items']) && is_array($data['items'])) {
            $order->setItems($data['items']);
        }

        return $this->orderRepository->update($order);
    }

    public function deleteOrder(int $id): bool
    {
        return $this->orderRepository->delete($id);
    }
}
