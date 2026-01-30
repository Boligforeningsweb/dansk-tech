<?php

function load_products(): array
{
    $productsFile = __DIR__ . '/../products.json';

    if (!file_exists($productsFile)) {
        return [];
    }

    $productsContent = file_get_contents($productsFile);
    $products = json_decode($productsContent, true);

    if (json_last_error() !== JSON_ERROR_NONE || !is_array($products)) {
        return [];
    }

    usort($products, function ($a, $b) {
        return strcasecmp($a['name'], $b['name']);
    });

    return $products;
}

function load_original_products(): array
{
    $originalFile = __DIR__ . '/../original-products.json';

    if (!file_exists($originalFile)) {
        return [];
    }

    $originalContent = file_get_contents($originalFile);
    $originalProducts = json_decode($originalContent, true);

    if (json_last_error() !== JSON_ERROR_NONE || !is_array($originalProducts)) {
        return [];
    }

    return $originalProducts;
}

function get_favicon_url(string $url): string
{
    $domain = parse_url($url, PHP_URL_HOST);

    if (!$domain) {
        return '';
    }

    $domain = str_replace('www.', '', $domain);
    return 'https://www.google.com/s2/favicons?domain=' . urlencode($domain) . '&sz=32';
}

function is_original_product(string $url, array $originalProducts): bool
{
    return in_array($url, $originalProducts);
}

function e(string $string): string
{
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}
