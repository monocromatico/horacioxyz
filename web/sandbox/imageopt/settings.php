<?php
// settings.php - configuración general del sistema de imágenes

// Ruta base para almacenar archivos subidos y optimizados (carpeta raíz de proyectos)
$base_dir = __DIR__ . '/files';

// Ejemplo: si accedes a las imágenes en https://tusitio.com/imageopt/files/...
$base_url = 'https://horacioxyz.lndo.site/sandbox/imageopt/files';

// Formatos permitidos para imágenes (extensiones sin punto)
$allowed_formats = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

// Tamaño máximo permitido para subir en bytes (10 MB)
$max_upload_size = 10 * 1024 * 1024;

// Calidad por defecto para JPEG/WebP (1 a 100)
$default_quality = 85;

// Tiempo en segundos para cache HTTP (30 días)
$http_cache_time = 60 * 60 * 24 * 30;

// Activar modo debug para mostrar errores y logs
$debug = false;

$thumbnail_presets = [
    ['width' => 400, 'height' => 300],
    ['width' => 800, 'height' => 600],
    ['width' => 150, 'height' => 0], // alto automático
];

// Habilitar generación de miniaturas por presets
$enable_default_thumbnails = true;

// Habilitar la creación de miniaturas personalizadas desde el formulario
$enable_custom_thumbnails = true;

// Estilo visual de los listados (puedes cambiarlo por 'grid', 'table', etc.)
$list_view_style = 'table';

// Nombre del zip temporal para descarga (se agregará sufijo según proyecto)
$temp_zip_name = 'miniaturas';

// Zona horaria predeterminada para nombrar carpetas o usar timestamps
date_default_timezone_set('America/Mexico_City');