<?php

declare(strict_types=1);

namespace App\Controller;

use Psr\Http\Message\ResponseInterface;
use Slim\Psr7\Response;

abstract class BaseController
{
    protected function parseJsonBody(string $body): ?array
    {
        if (empty($body)) {
            return null;
        }

        $data = json_decode($body, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return null;
        }

        return $data;
    }

    protected function jsonErrorResponse(
        string $error,
        string $message,
        int $statusCode = 400
    ): ResponseInterface {
        $response = new Response();
        $response->getBody()->write(json_encode([
            'error' => $error,
            'message' => $message,
        ]));

        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus($statusCode);
    }
}
