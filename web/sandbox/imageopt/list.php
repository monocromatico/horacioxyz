<?php
// list.php
include __DIR__ . '/settings.php';
include __DIR__ . '/functions.php';

$project = $_GET['project'] ?? '';
$project = preg_replace('/[^a-zA-Z0-9_\-]/', '', $project);

if ($project === '') {
    die('Falta el parámetro project en la URL');
}

$uploadDir = "$base_dir/$project/uploads";
$baseUrl = rtrim($base_url, '/');

$images = list_images_in_dir($uploadDir);

if (isset($_GET['download']) && $_GET['download'] === 'zip') {
    $zipPath = create_zip_for_project($base_dir, $project, $thumbnail_presets);
    if ($zipPath) {
        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="'.$project.'_thumbnails.zip"');
        header('Content-Length: ' . filesize($zipPath));
        readfile($zipPath);
        unlink($zipPath);
        exit;
    } else {
        echo "Error creando archivo ZIP.";
    }
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8" />
<title>Galería de proyecto <?= htmlspecialchars($project) ?></title>
<style>
  body { font-family: sans-serif; max-width: 900px; margin: auto; padding: 1em; }
  table { width: 100%; border-collapse: collapse; }
  th, td { border: 1px solid #ccc; padding: 0.5em; text-align: center; vertical-align: middle; }
  img { max-width: 100px; max-height: 80px; }
  .original-img { max-width: 150px; }
  .download-btn { margin: 1em 0; padding: 0.5em 1em; font-size: 1rem; }
</style>
</head>
<body>
  <h1>Galería de Proyecto: <?= htmlspecialchars($project) ?></h1>
  <a href="list.php?project=<?= urlencode($project) ?>&download=zip">
    <button class="download-btn">Descargar todas las miniaturas (ZIP)</button>
  </a>

  <?php include __DIR__ . '/form.php'; ?>
  <h2>Subir más imágenes a este proyecto</h2>

  <table>
    <thead>
      <tr>
        <th>Imagen original</th>
        <th>Miniaturas generadas</th>
      </tr>
    </thead>
    <tbody>
        
    <?php foreach ($images as $img): ?>
        <pre>
      <?php 
      echo $base_dir . $project . $img ;
      ?>
    </pre>
     <pre>
      <?php 
      print_r($thumbnail_presets);
        $thumbs = get_thumbnails_for_image($base_dir, $project, $filename);
      ?>
      </pre>
      <tr>
        <td>
          <img class="original-img" src="<?= htmlspecialchars("$baseUrl/$project/uploads/$img") ?>" alt="<?= htmlspecialchars($img) ?>">
          <br><?= htmlspecialchars($img) ?>
        </td>
        <td>
          <?php if (count($thumbs) === 0): ?>
            Sin miniaturas generadas
          <?php else: ?>
            <table>
              <thead>
                <tr><th>Tamaño</th><th>Vista previa</th><th>URL</th></tr>
              </thead>
              <tbody>
                <?php foreach ($thumbs as $size => $path): ?>
                  <?php
                    $thumbUrl = "$baseUrl/$project/assets/$size/$img";
                  ?>
                  <tr>
                    <td><?= htmlspecialchars($size) ?></td>
                    <td><img src="<?= htmlspecialchars($thumbUrl) ?>" alt="Miniatura <?= htmlspecialchars($size) ?>"></td>
                    <td><code><?= htmlspecialchars($thumbUrl) ?></code></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          <?php endif; ?>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</body>
</html>
