<?php
include __DIR__ . '/settings.php';

error_reporting($debug ? E_ALL : 0);
ini_set('display_errors', $debug ? '1' : '0');

// Leer parámetros básicos
$src = $_GET['src'] ?? null;
$maxWidth = isset($_GET['max-width']) ? (int)$_GET['max-width'] : null;
$maxHeight = isset($_GET['max-height']) ? (int)$_GET['max-height'] : null;
$quality = isset($_GET['quality']) ? (int)$_GET['quality'] : $default_quality;
$format = isset($_GET['format']) ? strtolower($_GET['format']) : null;

// Validar parámetros mínimos
if (!$src) {
    header('HTTP/1.1 400 Bad Request');
    echo 'Falta el parámetro src';
    exit;
}

// Validar calidad
if ($quality < 1 || $quality > 100) {
    $quality = $default_quality;
}

// Construir ruta absoluta a la imagen
$imagePath = $base_dir . '/' . $src;

// Validar archivo existe
if (!file_exists($imagePath)) {
    header('HTTP/1.1 404 Not Found');
    echo 'Archivo no encontrado';
    exit;
}

// Obtener extensión y validar formato
$info = pathinfo($imagePath);
$ext = strtolower($info['extension']);

if (!in_array($ext, $allowed_formats)) {
    header('HTTP/1.1 415 Unsupported Media Type');
    echo 'Formato no permitido';
    exit;
}

// Si no se especifica formato de salida, mantener el mismo que la imagen original
if (!$format) {
    $format = $ext;
}
if (!in_array($format, $allowed_formats)) {
    $format = $ext; // fallback
}

// Cargar imagen según tipo
switch ($ext) {
    case 'jpg':
    case 'jpeg':
        $img = imagecreatefromjpeg($imagePath);
        break;
    case 'png':
        $img = imagecreatefrompng($imagePath);
        break;
    case 'gif':
        $img = imagecreatefromgif($imagePath);
        break;
    case 'webp':
        if (function_exists('imagecreatefromwebp')) {
            $img = imagecreatefromwebp($imagePath);
        } else {
            header('HTTP/1.1 415 Unsupported Media Type');
            echo 'WebP no soportado en este servidor';
            exit;
        }
        break;
    default:
        header('HTTP/1.1 415 Unsupported Media Type');
        echo 'Formato no soportado';
        exit;
}

// Obtener tamaño original
$origWidth = imagesx($img);
$origHeight = imagesy($img);

// Calcular dimensiones nuevas manteniendo aspecto
$newWidth = $origWidth;
$newHeight = $origHeight;

if ($maxWidth && $origWidth > $maxWidth) {
    $newWidth = $maxWidth;
    $newHeight = intval($origHeight * ($maxWidth / $origWidth));
}

if ($maxHeight && $newHeight > $maxHeight) {
    $newHeight = $maxHeight;
    $newWidth = intval($origWidth * ($maxHeight / $origHeight));
}

// Crear imagen redimensionada
$newImg = imagecreatetruecolor($newWidth, $newHeight);

// Mantener transparencia para PNG y GIF
if (in_array($format, ['png', 'gif'])) {
    imagecolortransparent($newImg, imagecolorallocatealpha($newImg, 0, 0, 0, 127));
    imagealphablending($newImg, false);
    imagesavealpha($newImg, true);
}

// Redimensionar
imagecopyresampled($newImg, $img, 0, 0, 0, 0, $newWidth, $newHeight, $origWidth, $origHeight);

// Preparar carpeta assets para guardar la imagen cacheada
$projectName = explode('/', $src)[0]; // Extrae el proyecto del src
$assetsDir = $base_dir . '/' . $projectName . '/assets';

// Crear carpeta assets si no existe
if (!is_dir($assetsDir)) {
    mkdir($assetsDir, 0775, true);
}

// Nombre para el archivo cacheado
$filename = pathinfo($src, PATHINFO_FILENAME);
$cacheFilename = "{$filename}_{$newWidth}x{$newHeight}.{$format}";
$cachePath = $assetsDir . '/' . $cacheFilename;

// Si existe la imagen cacheada, enviarla directamente
if (file_exists($cachePath)) {
    sendImage($cachePath, $format);
    imagedestroy($img);
    imagedestroy($newImg);
    exit;
}

// Guardar imagen optimizada según formato
switch ($format) {
    case 'jpg':
    case 'jpeg':
        imagejpeg($newImg, $cachePath, $quality);
        break;
    case 'png':
        // calidad PNG 0-9 (invertido respecto a JPG quality)
        $pngQuality = 9 - round(($quality / 100) * 9);
        imagepng($newImg, $cachePath, $pngQuality);
        break;
    case 'gif':
        imagegif($newImg, $cachePath);
        break;
    case 'webp':
        if (function_exists('imagewebp')) {
            imagewebp($newImg, $cachePath, $quality);
        } else {
            imagedestroy($img);
            imagedestroy($newImg);
            header('HTTP/1.1 415 Unsupported Media Type');
            echo 'WebP no soportado en este servidor';
            exit;
        }
        break;
}

// Liberar memoria
imagedestroy($img);
imagedestroy($newImg);

// Enviar imagen
sendImage($cachePath, $format);
exit;

// Función para enviar imagen con cabeceras adecuadas y cache HTTP
function sendImage(string $path, string $format): void {
    global $http_cache_time;

    $mimeTypes = [
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png' => 'image/png',
        'gif' => 'image/gif',
        'webp' => 'image/webp',
    ];

    if (!isset($mimeTypes[$format])) {
        header('Content-Type: application/octet-stream');
    } else {
        header('Content-Type: ' . $mimeTypes[$format]);
    }

    header('Cache-Control: public, max-age=' . $http_cache_time);
    header('Expires: ' . gmdate('D, d M Y H:i:s', time() + $http_cache_time) . ' GMT');
    header('Content-Length: ' . filesize($path));
    readfile($path);
}
