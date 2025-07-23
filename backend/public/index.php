<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

return function (array $event): array {
    $path = $event['rawPath'] ?? '/';

    if ($path === '/' || $path === '/api') {
        return [
            'statusCode' => 200,
            'headers' => ['Content-Type' => 'text/html'],
            'body' => '<h1>Hello from Lambda</h1>',
        ];
    }

    if ($path === '/api/products') {
        return [
            'statusCode' => 200,
            'headers' => ['Content-Type' => 'application/json'],
            'body' => json_encode([
                ['id' => 1, 'title' => 'T-shirt', 'price' => 29.99],
                ['id' => 2, 'title' => 'Hat', 'price' => 19.99],
            ]),
        ];
    }

    return [
        'statusCode' => 404,
        'headers' => ['Content-Type' => 'text/html'],
        'body' => '<h1>404 Not Found</h1>',
    ];
};
