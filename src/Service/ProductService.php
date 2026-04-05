<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Product;
use App\Repository\ProductRepository;

class ProductService
{
    private ProductRepository $productRepository;

    public function __construct(ProductRepository $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    public function getAllProducts(): array
    {
        return $this->productRepository->findAll();
    }

    public function getProductById(int $id): ?Product
    {
        return $this->productRepository->findById($id);
    }

    public function createProduct(array $data): Product
    {
        $product = new Product(
            $data['name'],
            $data['description'],
            (float) $data['price'],
            (int) $data['stock']
        );

        return $this->productRepository->create($product);
    }

    public function updateProduct(int $id, array $data): ?Product
    {
        $product = $this->productRepository->findById($id);

        if (!$product) {
            return null;
        }

        if (isset($data['name'])) {
            $product->setName($data['name']);
        }

        if (isset($data['description'])) {
            $product->setDescription($data['description']);
        }

        if (isset($data['price'])) {
            $product->setPrice((float) $data['price']);
        }

        if (isset($data['stock'])) {
            $product->setStock((int) $data['stock']);
        }

        return $this->productRepository->update($product);
    }

    public function deleteProduct(int $id): bool
    {
        return $this->productRepository->delete($id);
    }
}
