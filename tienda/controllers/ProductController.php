<?php

require_once __DIR__ . '/../vendor/autoload.php';

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\TransferStats;

class ProductController
{
    private $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function index()
    {
        $pageTitle = 'Tienda Web';

        // Variables para la vista
        $products = [];
        $meta = [];
        $links = [];
        $error = false;
        $errorMessage = null;
        $errorCode = null;
        $errorCodeName = null;
        $errorActionUrl = null;
        $errorActionLabel = null;
        $errorDetails = null;
        $imagesBaseUrl = 'https://www.tiendaweb.pe//public/uploads/shop/';

        // Construir URL de la API para obtener productos de la tienda, manejando paginación
        $url = $this->config['api_dominio'] . '/shop/' . $this->config['codigo'] . '/products';
        $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
        if ($page > 1) {
            $url .= '?page=' . $page;
        }

        // Validar token antes de llamar a la API
        $apiToken = isset($this->config['token']) ? (string)$this->config['token'] : '';
        if ($apiToken === '') {
            $error = true;
            $errorCode = 401;
            $errorCodeName = 'missing_token';
            $errorMessage = 'Token de acceso no proporcionado';
            $errorActionUrl = 'https://tiendaweb.pe/settings/page';
            $errorActionLabel = 'Obtener su token';

            ob_start();
            include __DIR__ . '/../views/shop.php';
            $content = ob_get_clean();
            include __DIR__ . '/../views/layout.php';
            return;
        }

        try {
            // Opciones de red robustas + logging
            $options = [
                'headers' => [
                    'Accept' => 'application/json',
                    'Authorization' => 'Bearer ' . $apiToken,
                ],
                // No lanzar excepciones en 4xx/5xx; así podemos leer el body del error
                'http_errors' => false,
                // Tiempos razonables
                'timeout' => 10,
                'connect_timeout' => 5,
                // Forzar IPv4 para evitar problemas de resolución localhost -> ::1
                'curl' => [CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4],
                // Stats de la transferencia para log de diagnóstico
                'on_stats' => function (TransferStats $stats) use ($url) {
                    $info = $stats->getHandlerStats();
                    $primaryIp = $info['primary_ip'] ?? 'unknown';
                    $totalTime = $info['total_time'] ?? 0;
                    error_log('[Guzzle] Effective URL: ' . (string)$stats->getEffectiveUri());
                    error_log('[Guzzle] Primary IP: ' . $primaryIp . ' | Total time: ' . $totalTime . 's');
                },
            ];

            error_log('[Guzzle] Requesting: ' . $url);
            $client = new HttpClient([
                'timeout' => 20,
                'handler' => \GuzzleHttp\HandlerStack::create(),
            ]);
            $response = $client->request('GET', $url, $options);

            $statusCode = $response->getStatusCode();
            $body = (string) $response->getBody();
            error_log('API status: ' . $statusCode);
            error_log('API body: ' . $body);
            $jsonData = json_decode($body, true);

            error_log('API JSON: ' . json_encode($jsonData));

            // Manejar errores de API
            if ($statusCode !== 200) {
                $error = true;
                $errorCode = $statusCode;
                // Extraer mensaje y acciones del nuevo esquema
                if (is_array($jsonData)) {
                    $errorMessage = $jsonData['message'] ?? 'No se pueden cargar los productos en este momento.';
                    $errorCodeName = $jsonData['code'] ?? null;
                    $errorActionUrl = $jsonData['action_url'] ?? null;
                    $errorActionLabel = $jsonData['action_label'] ?? null;
                    $errorDetails = $jsonData['details'] ?? null;
                } else {
                    $errorMessage = 'No se pueden cargar los productos en este momento.';
                }

                // Renderizar vista con error
                ob_start();
                include __DIR__ . '/../views/shop.php';
                $content = ob_get_clean();
                include __DIR__ . '/../views/layout.php';
                return;
            }

            // Procesar respuesta exitosa
            if (!empty($jsonData) && is_array($jsonData)) {
                if (isset($jsonData['data']) && is_array($jsonData['data'])) {
                    $products = $jsonData['data'];
                    $meta = $jsonData['meta'] ?? [];
                    $links = $jsonData['links'] ?? [];
                }
            }
        } catch (RequestException $e) {
            // Errores de transporte; log detallado
            $error = true;
            $errorCode = 500;
            $errorMessage = 'Conexión fallida: ' . $e->getMessage();
            error_log('[Guzzle] RequestException: ' . $e->getMessage());
            if ($e->hasResponse()) {
                $resp = $e->getResponse();
                $sc = $resp ? $resp->getStatusCode() : 0;
                $rb = $resp ? (string)$resp->getBody() : '';
                error_log('[Guzzle] Response in exception - Status: ' . $sc . ' Body: ' . $rb);
            }

            ob_start();
            include __DIR__ . '/../views/shop.php';
            $content = ob_get_clean();
            include __DIR__ . '/../views/layout.php';
            return;
        } catch (\Exception $e) {
            // Error genérico
            $error = true;
            $errorCode = 500;
            $errorMessage = 'Conexión fallida: ' . $e->getMessage();
            error_log('[Guzzle] Exception: ' . $e->getMessage());

            ob_start();
            include __DIR__ . '/../views/shop.php';
            $content = ob_get_clean();
            include __DIR__ . '/../views/layout.php';
            return;
        }

        // Normalizar campos esperados por la vista
        foreach ($products as &$p) {
            if (!isset($p['description']) && isset($p['short_description'])) {
                $p['description'] = $p['short_description'];
            }
            if (!isset($p['slug']) && isset($p['name'])) {
                // generar slug básico
                $p['slug'] = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $p['name']), '-'));
            }
        }
        unset($p);

        // Renderizar la vista dentro del layout (buffer)
        ob_start();
        include __DIR__ . '/../views/shop.php';
        $content = ob_get_clean();

        include __DIR__ . '/../views/layout.php';
    }

    public function show($shopId, $productId = null, $slug = null)
    {
        if ($productId === null || !is_numeric($productId)) {
            http_response_code(404);
            echo 'Producto no encontrado';
            return;
        }

        try {
            $client = new HttpClient();

            $url = $this->config['api_dominio'] . '/shop/' . $this->config['codigo'] . '/products/' . $productId;
            $request = $client->request('GET', $url, [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer ' . $this->config['token'],
                ],
                'body' => json_encode(array_merge_recursive([
                    'shop_id' => $shopId,
                    'product_id' => $productId
                ]))
            ]);
        } catch (Exception $e) {
            http_response_code(404);
            echo 'Producto no encontrado';
            return;
        }

        $productData = json_decode($request->getBody(), true);

        if (!isset($productData['data'])) {
            http_response_code(404);
            echo 'Producto no encontrado';
            return;
        }

        $product = $productData['data'];
        $pageTitle = 'Producto - ' . ($product['name'] ?? '');
        $imagesBaseUrl = 'https://www.tiendaweb.pe//public/uploads/shop/';

        ob_start();
        include __DIR__ . '/../views/product.php';
        $content = ob_get_clean();

        include __DIR__ . '/../views/layout.php';
    }
}
