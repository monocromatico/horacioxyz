<?php
// functions.php

function generate_thumbnail($project_path, $filename, $preset) {
  $width = (int) $preset['width'];
  $height = (int) $preset['height'];

  $source_path = "$project_path/uploads/$filename";
  if (!file_exists($source_path)) return;

  $destination_dir = "$project_path/assets/{$width}x{$height}";
  if (!is_dir($destination_dir)) {
    mkdir($destination_dir, 0775, true);
  }

  $dest_path = "$destination_dir/$filename";

  list($orig_width, $orig_height, $type) = getimagesize($source_path);
  if (!$orig_width || !$orig_height) return;

  switch ($type) {
    case IMAGETYPE_JPEG:
      $src_image = imagecreatefromjpeg($source_path);
      break;
    case IMAGETYPE_PNG:
      $src_image = imagecreatefrompng($source_path);
      break;
    case IMAGETYPE_GIF:
      $src_image = imagecreatefromgif($source_path);
      break;
    default:
      return; // tipo no soportado
  }

  if (!$src_image) return;

  $thumb = imagecreatetruecolor($width, $height);
  imagecopyresampled($thumb, $src_image, 0, 0, 0, 0, $width, $height, $orig_width, $orig_height);

  switch ($type) {
    case IMAGETYPE_JPEG:
      imagejpeg($thumb, $dest_path, 85);
      break;
    case IMAGETYPE_PNG:
      imagepng($thumb, $dest_path);
      break;
    case IMAGETYPE_GIF:
      imagegif($thumb, $dest_path);
      break;
  }

  imagedestroy($src_image);
  imagedestroy($thumb);
}

function get_all_images($project_path) {
  $upload_dir = "$project_path/uploads/";
  if (!is_dir($upload_dir)) return [];

  $files = array_filter(scandir($upload_dir), function($file) use ($upload_dir) {
    return is_file($upload_dir . $file) && preg_match('/\.(jpe?g|png|gif)$/i', $file);
  });

  return array_values($files);
}

/**
 * Lista las imágenes válidas en un directorio dado.
 * 
 * @param string $dir Ruta absoluta al directorio
 * @return array Lista de nombres de archivos (sin ruta)
 */
function list_images_in_dir(string $dir): array {
    if (!is_dir($dir)) {
        return [];
    }
    $valid_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $files = scandir($dir);
    $images = [];
    foreach ($files as $file) {
        if ($file === '.' || $file === '..') {
            continue;
        }
        $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        if (in_array($ext, $valid_extensions, true) && is_file($dir . DIRECTORY_SEPARATOR . $file)) {
            $images[] = $file;
        }
    }
    return $images;
}

function get_thumbnails_for_image($base_dir, $project, $filename, $thumbnail_presets = []) {
  if (!is_array($thumbnail_presets)) {
    $thumbnail_presets = [];
  }

  $result = [];

  // Presets definidos
  foreach ($thumbnail_presets as $preset) {
    $path = "$base_dir/$project/assets/{$preset['width']}x{$preset['height']}/$filename";
    if (file_exists($path)) {
      $result["{$preset['width']}x{$preset['height']}"] = $path;
    }
  }

  // Miniaturas personalizadas
  $custom_dir = "$base_dir/$project/assets/";
  if (is_dir($custom_dir)) {
    foreach (scandir($custom_dir) as $dir) {
      if (preg_match('/^\d+x\d+$/', $dir)) {
        $path = "$custom_dir/$dir/$filename";
        if (file_exists($path)) {
          $result[$dir] = $path;
        }
      }
    }
  }

  return $result;
}

function zip_thumbnails($project_path, $filename) {
  $assets_dir = "$project_path/assets";
  if (!is_dir($assets_dir)) return false;

  $zip_path = "$project_path/thumbs_$filename.zip";
  $zip = new ZipArchive();

  if ($zip->open($zip_path, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
    return false;
  }

  foreach (scandir($assets_dir) as $dir) {
    if (preg_match('/^\d+x\d+$/', $dir)) {
      $file_path = "$assets_dir/$dir/$filename";
      if (file_exists($file_path)) {
        $zip->addFile($file_path, "$dir/$filename");
      }
    }
  }

  $zip->close();
  return basename($zip_path);
}
