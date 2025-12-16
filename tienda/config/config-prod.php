<?php

return [

    //Dominio de tu página web con la barra final. Ejemplo: https://midominio.com
    'dominio' => 'https://midominio.com',
    //Carpeta donde se instaló la tienda. Ejemplo: '' si está en la raiz o 'tienda' si está en una carpeta interna llamada tienda
    'carpeta' => 'tienda',
    // Código de tu tienda. 0 = tienda demo
    'codigo'   => 0,
    // Token público asignado a tu tienda
    'token'    => 'TUTOKENPUBLICOAQUI',
    // Menús de tu página web: Descripción => 'ruta'. Se puede agregar otros menús si lo deseas, se puede ocultar con #
    'menu' => [
        'Inicio' => '', // Ejemplo: '', 'index.html', 'index.php' u otro de su página web
        #'Quienes Somos' => 'quienes_somos',
        #'Servicios' => 'servicios.html',
        'Tienda' => 'tienda', // Ejemplo: '' = Si la tienda estará en la raiz, 'tienda/' = si estará en una carpeta interna, por ejemplo tienda
        #'Contacto' => 'contacto.php',
    ],

    /* --- NO MODIFICAR --- */
    'js_version'   => '1.0.3',
    'api_dominio' => 'https://tiendaweb.pe/api/v1',

];
