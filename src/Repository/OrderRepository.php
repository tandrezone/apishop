<?php

declare(strict_types=1);

namespace App\Repository;

use App\Config\Database;
use App\Entity\Order;
use PDO;

class OrderRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function findAll(): array
    {
        $stmt = $this->db->query(
            'SELECT * FROM orders ORDER BY created_at DESC'
        );
        $results = $stmt->fetchAll();

        return array_map([$this, 'hydrateOrder'], $results);
    }

    public function findById(int $id): ?Order
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM orders WHERE id = :id'
        );
        $stmt->execute(['id' => $id]);
        $result = $stmt->fetch();

        return $result ? $this->hydrateOrder($result) : null;
    }

    public function findByUserId(int $userId): array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM orders WHERE user_id = :user_id ORDER BY created_at DESC'
        );
        $stmt->execute(['user_id' => $userId]);
        $results = $stmt->fetchAll();

        return array_map([$this, 'hydrateOrder'], $results);
    }

    public function create(Order $order): Order
    {
        $stmt = $this->db->prepare(
            'INSERT INTO orders (user_id, total_amount, status, items, created_at) 
             VALUES (:user_id, :total_amount, :status, :items, :created_at)'
        );

        $stmt->execute([
            'user_id' => $order->getUserId(),
            'total_amount' => $order->getTotalAmount(),
            'status' => $order->getStatus(),
            'items' => json_encode($order->getItems()),
            'created_at' => $order->getCreatedAt()->format('Y-m-d H:i:s'),
        ]);

        $order->setId((int) $this->db->lastInsertId());

        return $order;
    }

    public function update(Order $order): Order
    {
        $order->setUpdatedAt(new \DateTime());

        $stmt = $this->db->prepare(
            'UPDATE orders 
             SET user_id = :user_id, total_amount = :total_amount, 
                 status = :status, items = :items, updated_at = :updated_at 
             WHERE id = :id'
        );

        $stmt->execute([
            'id' => $order->getId(),
            'user_id' => $order->getUserId(),
            'total_amount' => $order->getTotalAmount(),
            'status' => $order->getStatus(),
            'items' => json_encode($order->getItems()),
            'updated_at' => $order->getUpdatedAt()->format('Y-m-d H:i:s'),
        ]);

        return $order;
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM orders WHERE id = :id');
        $stmt->execute(['id' => $id]);

        return $stmt->rowCount() > 0;
    }

    private function hydrateOrder(array $data): Order
    {
        $order = new Order(
            (int) $data['user_id'],
            (float) $data['total_amount'],
            $data['status']
        );

        $order->setId((int) $data['id']);

        if (isset($data['items'])) {
            $items = json_decode($data['items'], true);
            $order->setItems(is_array($items) ? $items : []);
        }

        if (isset($data['updated_at'])) {
            $order->setUpdatedAt(new \DateTime($data['updated_at']));
        }

        return $order;
    }
}
