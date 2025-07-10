<?php
// Parámetros básicos
$width = max(1, min(intval($_GET['width'] ?? 300), 4000));
$height = max(1, min(intval($_GET['height'] ?? 150), 4000));
$bg_color = $_GET['bg'] ?? 'cccccc';
$text_color = $_GET['color'] ?? '000000';
$text = urldecode($_GET['text'] ?? "{$width}x{$height}");
$alpha = floatval($_GET['alpha'] ?? 1.0);
$format = strtolower($_GET['format'] ?? 'png');
$align = strtolower($_GET['align'] ?? 'center');
$font_name = strtolower($_GET['font'] ?? 'arial');

// Hex → RGB
function hex2rgb($hex) {
    $hex = str_replace('#', '', $hex);
    if (strlen($hex) == 3) $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
    return [
        hexdec(substr($hex, 0, 2)),
        hexdec(substr($hex, 2, 2)),
        hexdec(substr($hex, 4, 2))
    ];
}

// SVG soportado por HTML
if ($format === 'svg') {
    $bg = hex2rgb($bg_color);
    $fg = hex2rgb($text_color);
    header('Content-Type: image/svg+xml');
    echo <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="{$width}" height="{$height}">
  <rect width="100%" height="100%" fill="rgb({$bg[0]},{$bg[1]},{$bg[2]})" fill-opacity="{$alpha}"/>
  <text x="50%" y="50%" text-anchor="middle" dominant-baseline="middle"
        fill="rgb({$fg[0]},{$fg[1]},{$fg[2]})" font-size="20" font-family="{$font_name}">
    {$text}
  </text>
</svg>
SVG;
    exit;
}

// Crear imagen GD
$image = imagecreatetruecolor($width, $height);
imagesavealpha($image, true);
$transparency = 127 - intval($alpha * 127);
$bg_rgb = hex2rgb($bg_color);
$bg = imagecolorallocatealpha($image, $bg_rgb[0], $bg_rgb[1], $bg_rgb[2], $transparency);
imagefill($image, 0, 0, $bg);

$text_rgb = hex2rgb($text_color);
$color = imagecolorallocate($image, $text_rgb[0], $text_rgb[1], $text_rgb[2]);

// Selección de fuente "simulada"
$font_map = [
    'arial'     => 2,
    'verdana'   => 2,
    'trebuchet' => 2,
    'times'     => 1,
    'georgia'   => 1,
    'courier'   => 4,
    'impact'    => 4,
];
$font = $font_map[$font_name] ?? 2;
$font_width = imagefontwidth($font);
$font_height = imagefontheight($font);
$text_width = $font_width * strlen($text);
$text_height = $font_height;

switch ($align) {
    case 'left': $x = 10; break;
    case 'right': $x = $width - $text_width - 10; break;
    default: $x = ($width - $text_width) / 2;
}
$y = ($height - $text_height) / 2;

imagestring($image, $font, $x, $y, $text, $color);

// Exportar imagen
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
