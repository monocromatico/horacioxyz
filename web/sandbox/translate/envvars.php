<?php
function loadEnv($path) {
    if (!file_exists($path)) return;

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        // Ignorar comentarios
        if (strpos(trim($line), '#') === 0) continue;

        // Separar clave=valor
        list($name, $value) = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value);

        // Guardar en $_ENV y en variables de entorno
        $_ENV[$name] = $value;
        putenv("$name=$value");
    }
}

// Ejecutar carga del .env en la raíz del proyecto
// loadEnv("/app/.env"); //lando
loadEnv(__DIR__ . "/../../../.env"); //prod
// Acceder a las variables
$api_key_openai = $_ENV["OPENAI_API_KEY"] ?? "APIKEY_OPENAI";
$api_key_youtube = $_ENV["YOUTUBE_API_KEY"] ?? "APIKEY_YOUTUBE";