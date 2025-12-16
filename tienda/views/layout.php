<!doctype html>
<html lang="es">
<?php
$path_tienda = $this->config['dominio'] . ($this->config['carpeta'] ? '/' . $this->config['carpeta'] : '');
?>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) : 'Tienda'; ?></title>

    <!-- Bootstrap primero -->
    <link rel="stylesheet" href="<?php echo $path_tienda ?>/assets/css/bootstrap.min.css">
    <!-- CSS base de la plataforma -->
    <link rel="stylesheet" href="<?php echo $path_tienda ?>/assets/css/tienda.css">
    <!-- Overrides por cliente: carga después de tienda.css -->
    <?php if (!empty($clientCss) && file_exists($clientCss)): ?>
        <link rel="stylesheet" href="<?php echo htmlspecialchars($clientCss); ?>">
    <?php endif; ?>
</head>

<body>
    <?php
    // Opcional: generar clase por cliente, sanitizar
    $clientClass = '';
    if (!empty($_SERVER['HTTP_HOST'])) {
        // ejemplo simple: host sin puerto y sin puntos -> tiendaweb o usar alguna lógica personalizada
        $host = preg_replace('/:\d+$/', '', $_SERVER['HTTP_HOST']);
        $clientClass = preg_replace('/[^a-z0-9_-]/i', '-', explode('.', $host)[0]);
    }
    ?>

    <!-- WRAPPER: tiendaweb-root + clase por cliente si aplica -->
    <div class="<?php echo trim(($clientClass ? $clientClass . ' ' : '') . 'tiendaweb-root'); ?>">

        <?php include __DIR__ . '/header.php'; ?>

        <main>
            <?php echo $content ?? ''; ?>
        </main>

        <?php include __DIR__ . '/footer.php'; ?>

    </div> <!-- /.tiendaweb-root -->

    <script src="<?php echo $path_tienda ?>/assets/js/galery.js?v=<?php echo $this->config['js_version']; ?>"></script>
    <script src="<?php echo $path_tienda ?>/assets/js/lightbox.js?v=<?php echo $this->config['js_version']; ?>"></script>
</body>

</html>