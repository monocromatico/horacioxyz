<?php
// index.php
include __DIR__ . '/settings.php';
include __DIR__ . '/functions.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Subir Imágenes</title>
  <style>
    body {
      font-family: sans-serif;
      max-width: 800px;
      margin: auto;
      padding: 1rem;
    }
    h1 {
      text-align: center;
    }
    form {
      background: #f4f4f4;
      padding: 1rem;
      border-radius: 8px;
    }
  </style>
</head>
<body>
  <h1>Subir imágenes y generar miniaturas</h1>

  <?php
    // Renderizar formulario de subida
    include __DIR__ . '/form.php';
  ?>
</body>
</html>
