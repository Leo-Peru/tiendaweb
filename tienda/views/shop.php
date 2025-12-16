<?php
// Vista de listado de productos. Variables esperadas: $products, $meta, $links, $errorMessage (opcional)
?>
<div class="container tiendaweb-product-list">
    <?php if (!empty($error)): ?>
        <!-- Mostrar error de API -->
        <div class="row">
            <div class="col-12">
                <div class="tiendaweb-alert tiendaweb-alert-error" role="alert">
                    <div class="tiendaweb-alert-icon">⚠️</div>
                    <div class="tiendaweb-alert-content">
                        <h3 class="tiendaweb-alert-title"><?php echo htmlspecialchars($errorMessage ?? 'No se pueden cargar los productos'); ?></h3>
                        <?php if (!empty($errorDetails)): ?>
                            <p class="tiendaweb-alert-message"><?php echo htmlspecialchars($errorDetails); ?></p>
                        <?php endif; ?>
                        <?php if (!empty($errorActionUrl)): ?>
                            <p class="tiendaweb-alert-action">
                                <a href="<?php echo htmlspecialchars($errorActionUrl); ?>" class="btn btn-primary" target="_blank">
                                    <?php echo htmlspecialchars($errorActionLabel ?? 'Ir a la acción'); ?>
                                </a>
                            </p>
                        <?php elseif (isset($errorCode) && $errorCode === 402): ?>
                            <p class="tiendaweb-alert-action">
                                <a href="https://tiendaweb.pe/admin/shop" class="btn btn-primary" target="_blank">
                                    Activar suscripción
                                </a>
                            </p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    <?php elseif (empty($products)): ?>
        <!-- No hay productos -->
        <div class="row">
            <div class="col-12">
                <div class="tiendaweb-empty">No se encontraron productos.</div>
            </div>
        </div>
    <?php else: ?>
        <!-- Listado de productos -->
        <div class="row">
            <?php foreach ($products as $p): ?>
                <?php
                $name = $p['name'] ?? '';
                $desc = $p['description'] ?? ($p['short_description'] ?? '');
                $price = number_format((float)($p['price'] ?? 0), 2);
                $slug = isset($p['slug']) ? $p['slug'] : (isset($p['name'])
                    ? strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $p['name']), '-'))
                    : '');
                $imgFile = $p['image'] ?? null;
                $img = $imgFile
                    ? rtrim($imagesBaseUrl, '/') . '/' . ltrim($imgFile, '/')
                    : 'assets/img/placeholder.png';
                ?>

                <div class="col-sm-6 col-md-4 mb-4">
                    <a href="<?php echo htmlspecialchars(site_url('shop/' . $this->config['codigo'] . '/products/' .  $p['id'] . '/' . $slug)); ?>"
                        class="tiendaweb-card-link" aria-label="<?php echo htmlspecialchars($name); ?>">
                        <article class="tiendaweb-card-cover">
                            <div class="tiendaweb-product-image">
                                <span class="tiendaweb-price-tag">S/ <?php echo htmlspecialchars($price); ?></span>
                                <img src="<?php echo htmlspecialchars($img); ?>"
                                    alt="<?php echo htmlspecialchars($name); ?>">
                            </div>

                            <div class="tiendaweb-card-body">
                                <h3 class="tiendaweb-product-title">
                                    <?php echo htmlspecialchars($name); ?>
                                </h3>
                                <p class="tiendaweb-product-desc">
                                    <?php
                                    $short = mb_strimwidth(strip_tags($desc), 0, 120, '…');
                                    echo htmlspecialchars($short);
                                    ?>
                                </p>
                            </div>
                        </article>
                    </a>
                </div>
            <?php endforeach; ?>
        </div> <!-- /.row -->
    <?php endif; ?>
</div> <!-- /.container -->

<?php if (!empty($meta) && isset($meta['links']) && is_array($meta['links'])): ?>
    <nav class="tiendaweb-center" aria-label="Paginación de productos">
        <ul class="tiendaweb-pagination" role="list">
            <?php foreach ($meta['links'] as $link): ?>
                <?php
                $localUrl = null;
                if (!empty($link['url'])) {
                    if (preg_match('/[?&]page=(\d+)/', $link['url'], $m)) {
                        $pageNum = (int)$m[1];
                        $localUrl = site_url('') . ($pageNum === 1 ? '' : '?page=' . $pageNum);
                    } else {
                        $localUrl = site_url('');
                    }
                }
                $label = (string)$link['label'];
                $isActive = !empty($link['active']);
                ?>
                <li role="listitem">
                    <?php if (is_null($link['url'])): ?>
                        <span class="disabled" aria-disabled="true"><?php echo htmlspecialchars($label); ?></span>
                    <?php elseif ($isActive): ?>
                        <span class="active" aria-current="page"><?php echo htmlspecialchars($label); ?></span>
                    <?php else: ?>
                        <a href="<?php echo htmlspecialchars($localUrl); ?>"><?php echo htmlspecialchars($label); ?></a>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>
    </nav>
<?php endif; ?>