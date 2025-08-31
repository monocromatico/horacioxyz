<?php
// form.php
include_once __DIR__ . '/settings.php';
?>

<form action="uploader.php" method="POST" enctype="multipart/form-data">
  <fieldset>
    <legend>Subir imágenes</legend>

    <label for="project">Nombre del proyecto:</label>
    <input type="text" name="project" id="project" placeholder="Opcional (se autogenera si se deja vacío)">
    <br><br>

    <label for="images">Selecciona imágenes:</label>
    <input type="file" name="images[]" id="images" multiple required>
    <br><br>

    <label>Miniaturas predefinidas:</label><br>
    <?php foreach ($thumbnail_presets as $preset): ?>
      <label>
        <input type="checkbox" name="preset_sizes[]" value="<?= $preset['width'] . 'x' . $preset['height'] ?>">
        <?= $preset['width'] . 'x' . $preset['height'] ?>
      </label><br>
    <?php endforeach; ?>
    <br>

    <label>Miniatura personalizada:</label><br>
    <input type="number" name="custom_width" placeholder="Ancho" min="1">
    <input type="number" name="custom_height" placeholder="Alto" min="1">
    <br><br>

    <button type="submit">Subir imágenes</button>
  </fieldset>
</form>
