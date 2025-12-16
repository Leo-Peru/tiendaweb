<?php
// Helper HTTP sencillo para llamadas a la API usando cURL.
// Devuelve un array con keys: status (int), body (string), json (decoded|null), error (string|null)
function api_get(string $url, ?string $token = null): array
{
    if (!function_exists('curl_init')) {
        // Fallback to file_get_contents but with very basic behavior
        $opts = [];
        $headers = [];
        if ($token) {
            $headers[] = 'Authorization: Bearer ' . $token;
            $headers[] = 'X-API-TOKEN: ' . $token;
        }
        if ($headers) {
            $opts['http'] = ['header' => implode("\r\n", $headers)];
        }

        $context = stream_context_create($opts);
        $body = @file_get_contents($url, false, $context);
        $status = $body === false ? 0 : 200;
        $json = null;
        if ($body) {
            $decoded = json_decode($body, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $json = $decoded;
            }
        }
        return ['status' => $status, 'body' => $body, 'json' => $json, 'error' => $body === false ? 'file_get_contents failed' : null];
    }

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 8);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

    $headers = [
        'Accept: application/json',
    ];
    if ($token) {
        // Solo enviamos el token en Authorization: Bearer
        $headers[] = 'Authorization: Bearer ' . $token;
    }
    // Log para depuración
    error_log('URL: ' . $url);
    error_log('HEADERS: ' . print_r($headers, true));
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $body = curl_exec($ch);
    $err = null;
    if ($body === false) {
        $err = curl_error($ch);
    }
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE) ?: 0;
    curl_close($ch);

    $json = null;
    if ($body) {
        $decoded = json_decode($body, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            $json = $decoded;
        }
    }

    return ['status' => $status, 'body' => $body, 'json' => $json, 'error' => $err];
}

// Construye una URL basada en la ubicación del script (útil para generar rutas "limpias")
function site_url(string $path = ''): string
{
    // Determinar base a partir de SCRIPT_NAME (por ejemplo '/tienda/index.php')
    $script = $_SERVER['SCRIPT_NAME'] ?? '';
    $base = rtrim(str_replace('\\', '/', dirname($script)), '/');
    // Si base es solo "." o vacío, usar cadena vacía
    if ($base === '.' || $base === '') {
        $base = '';
    }
    $path = ltrim($path, '/');
    return ($base === '' ? '/' : $base . '/') . $path;
}
