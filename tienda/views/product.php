<?php

if (!isset($product)) {
    if (isset($apiResponse['data'])) {
        $product = $apiResponse['data'];
    } else {
        $product = [];
    }
}

// Helpers locales
function money_fmt($v)
{
    return 'S/ ' . number_format((float)$v, 2);
}

function build_image_url($file, $imagesBaseUrl)
{
    if (!$file) return 'assets/img/placeholder.png';
    if (preg_match('#^https?://#i', $file)) return $file;
    return rtrim($imagesBaseUrl, '/') . '/' . ltrim($file, '/');
}

// obtener imagen principal desde previews o file
$mainFile = null;
if (!empty($product['previews']) && is_array($product['previews'])) {
    $firstPreview = reset($product['previews']);
    if (is_array($firstPreview)) {
        $mainFile = $firstPreview['name'] ?? null;
    } elseif (is_object($firstPreview)) {
        $mainFile = $firstPreview->name ?? null;
    }
}
if (empty($mainFile) && !empty($product['file'])) {
    $mainFile = $product['file'];
}
$img = build_image_url($mainFile, $imagesBaseUrl);

// campos
$name = $product['name'] ?? 'Producto';
$createdAt = !empty($product['created_at']) ? date('d/m/Y', strtotime($product['created_at'])) : null;
$description = $product['description'] ?? '';
$quantity = isset($product['quantity']) ? (int)$product['quantity'] : 0;
$box_contents = $product['box_contents'] ?? '';
$tags = $product['tags'] ?? [];


$price = isset($product['price']) && $product['price'] !== '' ? (float)$product['price'] : null;
$priceOffer = isset($product['price_internet']) && $product['price_internet'] !== '' ? (float)$product['price_internet'] : null;
$priceNormal = isset($product['price_normal']) && $product['price_normal'] !== '' ? (float)$product['price_normal'] : null;
$hasDiscount = $priceNormal && $priceNormal > $price;
$discountPercent = $hasDiscount ? round((($priceNormal - $price) / $priceNormal) * 100) : 0;

// urls
$shopId = $product['user_id'] ?? null;
$productId = $product['id'] ?? null;
$slug = isset($product['slug']) ? $product['slug'] : '';
// ✅ Obtener la URL actual de forma dinámica y segura
$currentUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http')
    . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

// ✅ Crear el texto para WhatsApp
$countryId = preg_replace('/\D/', '', $product['store']['countries_id']);
$phone = preg_replace('/\D/', '', $product['store']['phone'] ?? '');
$fullPhone = $countryId . $phone;

$whatsappText = urlencode("Hola, estoy interesado en *{$name}* - {$currentUrl}");
$whatsappUrl = "https://wa.me/{$fullPhone}?text={$whatsappText}";

// helper para mostrar precio
function render_price_label($label, $value, $is_strikethrough = false)
{
    if ($value === null) return '';

    $price_html = '<span class="fw-bold">' . htmlspecialchars(money_fmt($value)) . '</span>';

    if ($is_strikethrough) {
        $price_html = '<small class="text-muted"><s>' . $price_html . '</s></small>';
    } else {
        $price_html = '<span style="font-size:1.3rem">' . $price_html . '</span>';
    }

    return '<div><small class="text-muted d-block">' . htmlspecialchars($label) . '</small>' . $price_html . '</div>';
}

// calcular descuento relativo a priceNormal (si existe)
if ($priceNormal > 0) {
    $discountPercent = round((($priceNormal - $price) / $priceNormal) * 100);
}

// galería
$previews = !empty($product['previews']) && is_array($product['previews']) ? $product['previews'] : [];
$galleryImgs = [];
if (!empty($previews)) {
    foreach ($previews as $pv) {
        $fname = is_array($pv) ? ($pv['name'] ?? null) : ($pv->name ?? null);
        if ($fname) $galleryImgs[] = build_image_url($fname, $imagesBaseUrl);
    }
}
if (empty($galleryImgs) && !empty($product['file'])) {
    $galleryImgs[] = build_image_url($product['file'], $imagesBaseUrl);
}
if (empty($galleryImgs)) {
    $galleryImgs[] = 'assets/img/placeholder.png';
}
?>

<!-- BEGIN Product Detail View (adaptado al JSON dado) -->
<div class="container my-4">
    <div class="row g-4">
        <!-- IMAGEN + DESCRIPCION -->
        <div class="col-lg-7">
            <div class="card border-0">
                <div class="card-body p-0">
                    <!-- GALLERY START -->
                    <div class="tiendaweb-gallery" id="product-gallery" data-initial="0" aria-label="Galería de imágenes del producto">
                        <div class="tgw-main">
                            <button class="tgw-btn tgw-prev" type="button" aria-label="Imagen anterior">&larr;</button>
                            <div class="tgw-viewport" tabindex="0">
                                <?php foreach ($galleryImgs as $i => $src): ?>
                                    <img
                                        class="tgw-image <?php echo $i === 0 ? 'active' : ''; ?>"
                                        data-index="<?php echo $i; ?>"
                                        src="<?php echo htmlspecialchars($src); ?>"
                                        alt="<?php echo htmlspecialchars($name . ($i > 0 ? " - imagen " . ($i + 1) : '')); ?>"
                                        loading="lazy"
                                        draggable="false" />
                                <?php endforeach; ?>
                            </div>
                            <button class="tgw-btn tgw-next" type="button" aria-label="Siguiente imagen">&rarr;</button>
                        </div>

                        <?php if (count($galleryImgs) > 1): ?>
                            <div class="tgw-thumbs" role="tablist" aria-label="Miniaturas">
                                <?php foreach ($galleryImgs as $i => $src): ?>
                                    <button class="tgw-thumb <?php echo $i === 0 ? 'active' : ''; ?>" data-index="<?php echo $i; ?>" aria-selected="<?php echo $i === 0 ? 'true' : 'false'; ?>" role="tab">
                                        <img src="<?php echo htmlspecialchars($src); ?>" alt="<?php echo "Miniatura " . ($i + 1); ?>" loading="lazy" draggable="false" />
                                    </button>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <!-- GALLERY END -->

                    <section class="mt-3">
                        <h4 class="fw-bold">Descripción</h4>
                        <div class="text-muted mt-2" style="line-height:1.6;">
                            <?php echo nl2br(htmlspecialchars($description)); ?>
                        </div>
                    </section>
                </div>
            </div>
        </div>

        <!-- SIDEBAR -->
        <div class="col-lg-5">
            <aside class="position-sticky" style="top:90px;">
                <div class="card tiendaweb-card-cover p-3">

                    <h2 class="tiendaweb-product-title mb-2" style="font-size:1.25rem;"><?php echo htmlspecialchars($name); ?></h2>
                    <!-- PRECIO -->
                    <div class="mb-3" aria-label="Información de precios del producto">
                        <?php if ($price > 0 && $priceOffer <= 0 && $priceNormal <= 0): ?>
                            <!-- 1) Solo viene price -->
                            <?php echo render_price_label('Precio', $price); ?>

                        <?php elseif ($price > 0 && $priceNormal > 0 && $priceOffer <= 0): ?>
                            <!-- 2) Vienen price y priceNormal -->
                            <div class="d-flex flex-column gap-1">
                                <?php
                                echo render_price_label('Precio Oferta', $price);
                                echo render_price_label('Precio Normal', $priceNormal, true);
                                ?>
                            </div>

                        <?php elseif ($price > 0 && $priceOffer > 0 && $priceNormal > 0): ?>
                            <!-- 3) Vienen price, priceOffer y priceNormal -->
                            <div class="d-flex flex-column gap-1">
                                <?php
                                echo render_price_label('Precio de liquidación', $price);
                                echo render_price_label('Precio Oferta', $priceOffer, true);
                                echo render_price_label('Precio Normal', $priceNormal, true);
                                ?>
                            </div>

                        <?php endif; ?>
                        <?php if ($discountPercent): ?>
                            <div class="mt-1">
                                <span class="badge bg-danger text-white">¡Ahorra <?php echo $discountPercent; ?>%!</span>
                            </div>
                        <?php endif; ?>

                    </div>

                    <!-- Comprar por WhatsApp -->
                    <div class="d-grid mb-3">
                        <a href="<?php echo $whatsappUrl; ?>" target="_blank" class="btn btn-success btn-lg" style="border-radius:30px;">
                            Comprar por WhatsApp
                        </a>
                    </div>

                    <!-- Delivery info -->
                    <div class="mb-3">
                        <h6 class="small fw-semibold">Opciones de entrega:</h6>
                        <ul class="list-unstyled small text-muted mb-0">
                            <li>🏬 Recoger en tienda: <?php echo isset($product['shipping_fee_store']) ? money_fmt($product['shipping_fee_store']) : 'S/0.00'; ?></li>
                            <li>💵 Contra entrega: <?php echo isset($product['shipping_fee']) ? money_fmt($product['shipping_fee']) : 'S/5.00'; ?></li>
                            <li>🚚 Delivery a domicilio: Desde <?php echo isset($product['shipping_fee']) ? money_fmt($product['shipping_fee']) : 'S/5.00'; ?></li>
                        </ul>
                    </div>

                    <!-- Meta -->
                    <div class="mb-3 small text-muted">
                        <?php if (!empty($product['product_condition'])): ?>
                            <div>📦 <strong>Condición:</strong> <?php echo htmlspecialchars($product['product_condition']); ?></div>
                        <?php endif; ?>
                        <div class="mt-2">🟢 <strong>Cantidad disponible:</strong> <?php echo $quantity; ?></div>
                        <?php if (!empty($box_contents)): ?>
                            <div class="mt-1 small">📦 <?php echo htmlspecialchars($box_contents); ?></div>
                        <?php endif; ?>
                    </div>

                    <!-- Tags -->
                    <div class="mb-3">
                        <?php if (!empty($tags)): ?>
                            <div class="small mb-2">Etiquetas:</div>
                            <?php foreach ($tags as $t): ?>
                                <span class="badge bg-light text-decoration-none me-1">#<?php echo htmlspecialchars($t); ?></span>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                    <!-- Compartir -->
                    <div class="mt-2">
                        <div class="small fw-semibold mb-2">Compartir:</div>
                        <div class="d-flex gap-2">
                            <a class="btn btn-outline-secondary btn-sm" target="_blank" href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode($currentUrl); ?>">Facebook</a>
                            <a class="btn btn-outline-secondary btn-sm" target="_blank" href="https://twitter.com/intent/tweet?text=<?php echo urlencode($name); ?>&url=<?php echo urlencode($currentUrl); ?>">Twitter</a>
                            <a class="btn btn-outline-secondary btn-sm" target="_blank" href="https://wa.me/?text=<?php echo urlencode($name . ' ' . $currentUrl); ?>">WhatsApp</a>
                        </div>
                    </div>

                </div>
            </aside>
        </div>

    </div>
</div>

<!-- LIGHTBOX / ZOOM MODAL -->
<div id="tgw-zoom-modal" class="tgw-zoom-modal" aria-hidden="true" role="dialog" aria-label="Visor de imagenes">
    <div class="tgw-zoom-toolbar" role="toolbar" aria-label="Controles de imagen">
        <button class="tgw-zoom-btn" data-action="zoom-out" aria-label="Alejar">−</button>
        <button class="tgw-zoom-btn" data-action="zoom-in" aria-label="Acercar">＋</button>
        <button class="tgw-zoom-btn" data-action="reset" aria-label="Restablecer">⟳</button>
        <button class="tgw-zoom-btn" data-action="download" aria-label="Descargar">⬇</button>
        <button class="tgw-zoom-btn" data-action="close" aria-label="Cerrar">✕</button>
    </div>

    <div class="tgw-zoom-stage" tabindex="0">
        <img id="tgw-zoom-image" src="" alt="">
    </div>
</div>

<!-- OPTIONAL small inline CSS for detail view (add to tienda.css preferentemente) -->
<style>
    Scoped to avoid conflicts 
.tiendaweb-product-detail-image img { border-radius: 12px; }
.tiendaweb-card-cover { border-radius: 12px; }
.tiendaweb-product-title { color: #1f2937; }
.badge.bg-danger { background: #e53935 !important; }
.btn-success { background: #12a454; border-color: #12a454; }
@media (max-width: 991px) {
  .position-sticky { position: static !important; top: auto !important; }
}
</style>
<!-- END Product Detail View -->