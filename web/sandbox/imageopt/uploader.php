<?php
// uploader.php

include __DIR__ . '/settings.php';
include __DIR__ . '/functions.php';

date_default_timezone_set('America/Mexico_City'); // Ajusta tu zona horaria si es necesario

// Verifica si se subieron archivos
if (!isset($_FILES['images']) || !isset($_POST['project'])) {
  die('Faltan imágenes o nombre del proyecto.');
}

// Nombre del proyecto
$project = trim($_POST['project']);
if ($project === '') {
  $project = date('Ymd_His'); // Generar nombre por defecto
}

// Sanitizar el nombre del proyecto
$project = preg_replace('/[^a-zA-Z0-9_\-]/', '', $project);

// Ruta base desde settings
$uploadDir = "$base_dir/$project/uploads/";

if (!is_dir($uploadDir)) {
  mkdir($uploadDir, 0775, true);
}

// Procesar cada archivo
foreach ($_FILES['images']['tmp_name'] as $index => $tmpName) {
  if (is_uploaded_file($tmpName)) {
    $name = basename($_FILES['images']['name'][$index]);
    $dest = $uploadDir . $name;
    move_uploaded_file($tmpName, $dest);

    // Generar miniaturas con presets desde settings
    foreach ($thumbnail_presets as $preset) {
      generate_thumbnail("$base_dir/$project/uploads", $name, $preset);
    }

    // Si hay miniaturas personalizadas en el form
    if (!empty($_POST['custom_width']) && !empty($_POST['custom_height'])) {
      $custom_preset = [
        'width' => (int)$_POST['custom_width'],
        'height' => (int)$_POST['custom_height'],
      ];
      generate_thumbnail("$base_dir/$project/uploads", $name, $custom_preset);
    }

    // También puede venir una lista de presets seleccionados
    if (!empty($_POST['preset_sizes']) && is_array($_POST['preset_sizes'])) {
      foreach ($_POST['preset_sizes'] as $preset_str) {
        if (preg_match('/^(\d+)x(\d+)$/', $preset_str, $m)) {
          $preset = ['width' => (int)$m[1], 'height' => (int)$m[2]];
          generate_thumbnail("$base_dir/$project/uploads", $name, $preset);
        }
      }
    }
  }
}

// Redirigir a la galería del proyecto
header("Location: list.php?project=$project");
exit;
