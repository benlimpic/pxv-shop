<?php

require __DIR__ . '/../vendor/autoload.php';

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

function fetchShopifyProducts(): array
{
    $accessToken = getenv('SHOPIFY_ACCESS_TOKEN');
    $storeDomain = getenv('SHOPIFY_STORE_DOMAIN');

    if (!$accessToken || !$storeDomain) {
        throw new Exception('Missing Shopify credentials.');
    }

    $parsedDomain = parse_url($storeDomain, PHP_URL_HOST);
    $url = "https://$parsedDomain/admin/api/2024-07/products.json";

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "X-Shopify-Access-Token: $accessToken",
        "Content-Type: application/json"
    ]);

    $response = curl_exec($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($status !== 200) {
        throw new Exception("Shopify API error (HTTP $status)");
    }

    $data = json_decode($response, true);
    return $data['products'] ?? [];
}

function withCors(Response $response): Response
{
    $response->headers->set('Access-Control-Allow-Origin', '*');
    $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, OPTIONS');
    $response->headers->set('Access-Control-Allow-Headers', 'Content-Type');
    return $response;
}

$request = Request::createFromGlobals();
$method = strtoupper($request->getMethod());
$path = $request->getPathInfo();

if ($method === 'OPTIONS') {
    withCors(new Response('', 200))->send();
    exit;
}

if ($path === '/' || $path === '/api') {
    withCors(new Response('Hello from Shopify Lambda', 200))->send();
    exit;
}

if ($path === '/api/products') {
    try {
        $products = fetchShopifyProducts();
        withCors(new JsonResponse($products))->send();
    } catch (\Throwable $e) {
        withCors(new JsonResponse(['error' => $e->getMessage()], 500))->send();
    }
    exit;
}

withCors(new Response('Not Found', 404))->send();
