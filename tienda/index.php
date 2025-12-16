<?php
// index.php
// Punto de entrada principal de la aplicación "tienda"

// ===========================================
// 1. Cargar configuración y dependencias
// ===========================================
$config = require __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/helpers.php';

// Controladores
require_once __DIR__ . '/controllers/ShopController.php';
require_once __DIR__ . '/controllers/ProductController.php';

// ===========================================
// 2. Determinar ruta "amigable"
//    (soportada por .htaccess o ?route=...)
// ===========================================
$rawRoute = $_GET['route'] ?? ''; // viene desde RewriteRule ^(.*)$ index.php?route=$1 [QSA,L]
$rawRoute = trim($rawRoute, '/');
$routeParts = $rawRoute === '' ? [] : explode('/', $rawRoute);

// ===========================================
// 3. Enrutamiento manual
// ===========================================
switch (true) {

    // ======================
    // Página de inicio / tienda
    // ======================
    case ($rawRoute === ''):
        (new ProductController($config))->index();
        break;

    // ======================
    // Detalle de producto
    // ======================
    case (isset($routeParts[0], $routeParts[1], $routeParts[2]) &&
    $routeParts[0] === 'shop' &&
    $routeParts[2] === 'products'):

    $shopId = (int)($routeParts[1] ?? 0);
    $productId = isset($routeParts[3]) ? (int)$routeParts[3] : 0;
    $slug = $routeParts[4] ?? null;

    // Depuración temporal
    error_log("DEBUG: shopId=$shopId, productId=$productId, slug=$slug");
    error_log("DEBUG: routeParts=" . print_r($routeParts, true));

    if ($productId > 0) {
        (new ProductController($config))->show($shopId, $productId, $slug);
    } else {
        http_response_code(404);
        echo 'Producto no encontrado.';
    }
    break;
    // ======================
    // Ruta no encontrada
    // ======================
    default:
        http_response_code(404);
        echo 'Página no encontrada';
        break;
}
