<?php
include "./envvars.php";

header("Content-Type: application/json");

$api_key_openai = $_ENV["OPENAI_API_KEY"] ?? "";

// Leer input
$input = json_decode(file_get_contents("php://input"), true);
if (!$input) {
    http_response_code(400);
    echo json_encode(["error" => "No se recibieron datos válidos"]);
    exit;
}

$term = $input["search_term"] ?? "";
$user_lang = substr($input["user_lang"] ?? "en", 0, 2); // normalizar idioma

$ch = curl_init("https://api.openai.com/v1/chat/completions");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Content-Type: application/json",
    "Authorization: Bearer $api_key_openai"
]);

// Le pedimos a OpenAI que responda SOLO en JSON
$data = [
    "model" => "gpt-4o-mini",
    "messages" => [
        [
            "role" => "system",
            "content" => "Eres un experto en música y cultura. Y debes proporcinar la informacion en el idioma definido por el usuario ($user_lang).
                          Cuando recibas el nombre de una canción debes responder **únicamente en JSON y en el idioma del usuario ($user_lang)** con el formato:
                          {
                            \"datos_relevantes\": \"...\",

                          }
                          - 'datos_relevantes': breve resumen/contexto cultural en el idioma del usuario ($user_lang)."
        ],
        [
            "role" => "user",
            "content" => "Dame la información de la canción '$term'."
        ]
    ],
    "max_tokens" => 250,
    "response_format" => ["type" => "json_object"] // fuerza salida en JSON
];

curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
$response = curl_exec($ch);

if ($response === false) {
    http_response_code(500);
    echo json_encode(["error" => curl_error($ch)]);
    curl_close($ch);
    exit;
}

curl_close($ch);
echo $response;
