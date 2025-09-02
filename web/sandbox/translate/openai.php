<?php
include "./envvars.php";

header("Content-Type: application/json");

$api_key_openai = "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";

$input = json_decode(file_get_contents("php://input"), true);
$term = $input["search_term"] ?? "";
$lang = substr($input["user_lang"] ?? "not-detected", 0, 2); // normalizar idioma

$ch = curl_init("https://api.openai.com/v1/chat/completions");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Content-Type: application/json",
    "Authorization: Bearer $api_key_openai"
]);

$data = [
    "model" => "gpt-4o-mini",
    "messages" => [
        [
            "role" => "system",
            "content" => "Eres un experto en música y cultura. 
                          El idioma detectado del navegador es: $lang. 
                          IMPORTANTE: todas tus respuestas deben estar escritas en ese idioma ($lang), 
                            sin importar en qué idioma esté el título de la canción. 
                            Si $lang no corresponde a un idioma válido, responde en español."
        ],
        [
            "role" => "user",
            "content" => "Dame un resumen breve y relevante de la canción: $term"
        ]
    ],
    "max_tokens" => 250
];

curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
$response = curl_exec($ch);
curl_close($ch);

echo $response;
