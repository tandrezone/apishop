<?php

declare(strict_types=1);

namespace App\Repository;

use App\Config\Database;
use App\Entity\Product;
use PDO;

class ProductRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function findAll(): array
    {
        $stmt = $this->db->query(
            'SELECT * FROM products ORDER BY created_at DESC'
        );
        $results = $stmt->fetchAll();

        return array_map([$this, 'hydrateProduct'], $results);
    }

    public function findById(int $id): ?Product
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM products WHERE id = :id'
        );
        $stmt->execute(['id' => $id]);
        $result = $stmt->fetch();

        return $result ? $this->hydrateProduct($result) : null;
    }

    public function create(Product $product): Product
    {
        $stmt = $this->db->prepare(
            'INSERT INTO products (name, description, price, stock, created_at) 
             VALUES (:name, :description, :price, :stock, :created_at)'
        );

        $stmt->execute([
            'name' => $product->getName(),
            'description' => $product->getDescription(),
            'price' => $product->getPrice(),
            'stock' => $product->getStock(),
            'created_at' => $product->getCreatedAt()->format('Y-m-d H:i:s'),
        ]);

        $product->setId((int) $this->db->lastInsertId());

        return $product;
    }

    public function update(Product $product): Product
    {
        $product->setUpdatedAt(new \DateTime());

        $stmt = $this->db->prepare(
            'UPDATE products 
             SET name = :name, description = :description, 
                 price = :price, stock = :stock, updated_at = :updated_at 
             WHERE id = :id'
        );

        $stmt->execute([
            'id' => $product->getId(),
            'name' => $product->getName(),
            'description' => $product->getDescription(),
            'price' => $product->getPrice(),
            'stock' => $product->getStock(),
            'updated_at' => $product->getUpdatedAt()->format('Y-m-d H:i:s'),
        ]);

        return $product;
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM products WHERE id = :id');
        $stmt->execute(['id' => $id]);

        return $stmt->rowCount() > 0;
    }

    private function hydrateProduct(array $data): Product
    {
        $product = new Product(
            $data['name'],
            $data['description'],
            (float) $data['price'],
            (int) $data['stock']
        );

        $product->setId((int) $data['id']);

        if (isset($data['updated_at'])) {
            $product->setUpdatedAt(new \DateTime($data['updated_at']));
        }

        return $product;
    }
}
