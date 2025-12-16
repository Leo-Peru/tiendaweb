<header class="tiendaweb-header shadow-sm">
  <div class="container">
    <div class="tiendaweb-navbar d-flex align-items-center justify-content-between py-3">
      <!-- LOGO DE TU TIENDA -->
      <div class="tiendaweb-brand d-flex align-items-center">
        <a href="<?php echo htmlspecialchars(site_url('')); ?>" class="tiendaweb-logo-link text-decoration-none">
          <!-- Si tienes logo, colócalo aquí -->
          <!-- <img src="assets/img/logo.png" alt="Logo" class="tiendaweb-logo me-2"> -->
        </a>
      </div>

      <!-- MENÚ DE NAVEGACIÓN -->
      <nav class="tiendaweb-nav">
        <?php
        $dominio = $this->config['dominio'];
        $menuItems = $this->config['menu'];
        ?>

        <ul class="tiendaweb-nav-list list-inline mb-0 d-flex align-items-center gap-3">
          <?php foreach ($menuItems as $menuItemName => $menuItemLink): ?>
            <?php if (!empty($menuItemName)): ?>
              <li class="list-inline-item">
                <a href="<?php echo $dominio .'/'. $menuItemLink; ?>" class="tiendaweb-nav-link"><?php echo $menuItemName; ?></a>
              </li>
            <?php endif; ?>
          <?php endforeach; ?>
        </ul>

      </nav>

      <!-- ACCIONES DERECHA (ej. login / carrito) -->
      <div class="tiendaweb-actions d-flex align-items-center gap-2">
        <!-- Aquí puedes agregar íconos o enlaces para el carrito de compras, login, etc. -->
      </div>

    </div>
  </div>
</header>