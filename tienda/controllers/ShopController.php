<?php
class ShopController
{
    private $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function index()
    {
        $pageTitle = 'Tienda Web';

        // Construir URL de la API para obtener productos de la tienda, manejando paginación
        $url = $this->config['api_dominio'] . '/shop/' . $this->config['codigo'] . '/products';
        $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
        if ($page > 1) {
            $url .= '?page=' . $page;
        }

        // Llamada HTTP segura con timeout y manejo de errores

        $resp = api_get($url, $this->config['token']);

        // Preparar variables de error para la vista si la API respondió con error
        $error = false;
        $errorMessage = null;
        $errorCode = null;
        $errorCodeName = null;
        $errorActionUrl = null;
        $errorActionLabel = null;
        $errorDetails = null;

        if (isset($resp['status']) && $resp['status'] !== 200) {
            $error = true;
            $errorCode = (int)$resp['status'];
            $payload = is_array($resp['json']) ? $resp['json'] : [];
            $errorMessage = $payload['message'] ?? 'No se pueden cargar los productos en este momento.';
            $errorCodeName = $payload['code'] ?? null;
            $errorActionUrl = $payload['action_url'] ?? null;
            $errorActionLabel = $payload['action_label'] ?? null;
            $errorDetails = $payload['details'] ?? null;
        }

        $products = [];
        $meta = [];
        $links = [];
        if (!empty($resp['json']) && is_array($resp['json']) && !$error) {
            // Adaptar a nueva estructura tipo Laravel: { data: [...], meta: {...}, links: {...} }
            if (isset($resp['json']['data']) && is_array($resp['json']['data'])) {
                $products = $resp['json']['data'];
                $meta = $resp['json']['meta'] ?? [];
                $links = $resp['json']['links'] ?? [];
            }
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

        $imagesBaseUrl = 'https://www.tiendaweb.pe//public/uploads/shop/';

        // Renderizar la vista dentro del layout (buffer)
        ob_start();
        include __DIR__ . '/../views/shop.php';
        $content = ob_get_clean();

        include __DIR__ . '/../views/layout.php';
    }
}
