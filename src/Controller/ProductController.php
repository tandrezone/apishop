<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\ProductService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Psr7\Response;

class ProductController
{
    private ProductService $productService;

    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
    }

    public function index(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $products = $this->productService->getAllProducts();
        $productsArray = array_map(fn($product) => $product->toArray(), $products);

        $response->getBody()->write(json_encode($productsArray));

        return $response->withHeader('Content-Type', 'application/json');
    }

    public function show(
        ServerRequestInterface $request,
        ResponseInterface $response,
        array $args
    ): ResponseInterface {
        $id = (int) $args['id'];
        $product = $this->productService->getProductById($id);

        if (!$product) {
            $response->getBody()->write(json_encode([
                'error' => 'Not Found',
                'message' => 'Product not found',
            ]));
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(404);
        }

        $response->getBody()->write(json_encode($product->toArray()));

        return $response->withHeader('Content-Type', 'application/json');
    }

    public function create(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $data = json_decode((string) $request->getBody(), true);

        if (!isset($data['name'], $data['description'], $data['price'], $data['stock'])) {
            $response->getBody()->write(json_encode([
                'error' => 'Bad Request',
                'message' => 'Missing required fields: name, description, price, stock',
            ]));
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(400);
        }

        try {
            $product = $this->productService->createProduct($data);
            $response->getBody()->write(json_encode($product->toArray()));

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
        $data = json_decode((string) $request->getBody(), true);

        $product = $this->productService->updateProduct($id, $data);

        if (!$product) {
            $response->getBody()->write(json_encode([
                'error' => 'Not Found',
                'message' => 'Product not found',
            ]));
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(404);
        }

        $response->getBody()->write(json_encode($product->toArray()));

        return $response->withHeader('Content-Type', 'application/json');
    }

    public function delete(
        ServerRequestInterface $request,
        ResponseInterface $response,
        array $args
    ): ResponseInterface {
        $id = (int) $args['id'];

        $deleted = $this->productService->deleteProduct($id);

        if (!$deleted) {
            $response->getBody()->write(json_encode([
                'error' => 'Not Found',
                'message' => 'Product not found',
            ]));
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(404);
        }

        $response->getBody()->write(json_encode([
            'message' => 'Product deleted successfully',
        ]));

        return $response->withHeader('Content-Type', 'application/json');
    }
}
