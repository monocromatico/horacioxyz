<?php
// --- Parámetros ---
$width = max(1, min(intval($_GET['width'] ?? 300), 4000));
$height = max(1, min(intval($_GET['height'] ?? 150), 4000));
$bg_color = $_GET['bg'] ?? 'cccccc';
$text_color = $_GET['color'] ?? '000000';
$text = urldecode($_GET['text'] ?? "{$width}x{$height}");
$alpha = floatval($_GET['alpha'] ?? 1.0);
$format = strtolower($_GET['format'] ?? 'png');
$align = strtolower($_GET['align'] ?? 'center');
$font_name = strtolower(basename($_GET['font'] ?? 'arial')); // evitar path traversal
$font_size = intval($_GET['size'] ?? min($width, $height) / 8);

// Generar un hash único para este conjunto de parámetros
$etag = md5($width . $height . $bg_color . $text_color . $text . $alpha . $format . $font_name . $font_size . $align);

// Fecha de "última modificación" simulada (se puede fijar al deploy o hacer más dinámica)
$last_modified = gmdate('D, d M Y H:i:s', time() - 3600) . ' GMT'; // hace 1 hora

// Cabeceras de caché
header("Cache-Control: public, max-age=86400"); // 1 día
header("ETag: \"$etag\"");
header("Last-Modified: $last_modified");
header("Expires: " . gmdate('D, d M Y H:i:s', time() + 86400) . ' GMT');

// Verificar si cliente ya tiene una versión válida
if (
    isset($_SERVER['HTTP_IF_NONE_MATCH']) &&
    trim($_SERVER['HTTP_IF_NONE_MATCH']) === "\"$etag\""
) {
    header("HTTP/1.1 304 Not Modified");
    exit;
}

if (
    isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) &&
    $_SERVER['HTTP_IF_MODIFIED_SINCE'] === $last_modified
) {
    header("HTTP/1.1 304 Not Modified");
    exit;
}


// --- Colores ---
function hex2rgb($hex) {
    $hex = str_replace('#', '', $hex);
    if (strlen($hex) == 3) $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
    return [hexdec(substr($hex, 0, 2)), hexdec(substr($hex, 2, 2)), hexdec(substr($hex, 4, 2))];
}

// --- Ruta de fuente TTF ---
$font_dir = __DIR__ . '/fonts/';
$font_path = "{$font_dir}{$font_name}.ttf";
$font_exists = file_exists($font_path);

// --- Salida SVG si se solicita ---
if ($format === 'svg') {
    $bg = hex2rgb($bg_color);
    $fg = hex2rgb($text_color);
    header('Content-Type: image/svg+xml');
    echo <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="{$width}" height="{$height}">
  <rect width="100%" height="100%" fill="rgb({$bg[0]},{$bg[1]},{$bg[2]})" fill-opacity="{$alpha}"/>
  <text x="50%" y="50%" text-anchor="middle" dominant-baseline="middle"
        fill="rgb({$fg[0]},{$fg[1]},{$fg[2]})" font-size="{$font_size}" font-family="{$font_name}">
    {$text}
  </text>
</svg>
SVG;
    exit;
}

// --- Imagen GD ---
$image = imagecreatetruecolor($width, $height);
imagesavealpha($image, true);
$transparency = 127 - intval($alpha * 127);
$bg = hex2rgb($bg_color);
$bg_color_alloc = imagecolorallocatealpha($image, $bg[0], $bg[1], $bg[2], $transparency);
imagefill($image, 0, 0, $bg_color_alloc);

$fg = hex2rgb($text_color);
$text_color_alloc = imagecolorallocate($image, $fg[0], $fg[1], $fg[2]);

if ($font_exists) {
    // Calcular tamaño y posición del texto con TTF
    $bbox = imagettfbbox($font_size, 0, $font_path, $text);
    $text_width = abs($bbox[2] - $bbox[0]);
    $text_height = abs($bbox[7] - $bbox[1]);

    // Alineación horizontal
    switch ($align) {
        case 'left':  $x = 10; break;
        case 'right': $x = $width - $text_width - 10; break;
        default:      $x = ($width - $text_width) / 2;
    }

    $y = ($height + $text_height) / 2;

    // Dibujar texto
    imagettftext($image, $font_size, 0, $x, $y, $text_color_alloc, $font_path, $text);
} else {
    // Fallback: texto mínimo usando imagestring si no hay TTF
    imagestring($image, 2, 10, 10, "Missing font: {$font_name}.ttf", $text_color_alloc);
}

// --- Salida final ---
switch ($format) {
    case 'jpg':
    case 'jpeg':
        header('Content-Type: image/jpeg');
        imagejpeg($image, null, 90);
        break;
    case 'gif':
        header('Content-Type: image/gif');
        imagegif($image);
        break;
    default:
        header('Content-Type: image/png');
        imagepng($image);
}

imagedestroy($image);
