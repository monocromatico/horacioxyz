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
// Lang normalization
$term = $input["search_term"] ?? "";
$user_lang = substr($input["user_lang"] ?? "en", 0, 2); 

$ch = curl_init("https://api.openai.com/v1/chat/completions");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Content-Type: application/json",
    "Authorization: Bearer $api_key_openai"
]);

// AI Prompt
$data = [
	"model" => "gpt-4o-mini",
	"response_format" => [ "type" => "json_object" ], // Force JSON
	"messages" => [
		[
			"role" => "system",
			"content" => "You are a music and culture expert. 
				Always respond strictly in JSON with this structure:
				{
					\"relevant_data\": \"...\",
					\"artist\": \"...\",
					\"production\": \"...\",
					\"popularity\": \"...\",
					\"trivia\": \"...\",
					\"recommendations\": [\"...\", \"...\"]
				}
				
				Details:
				- relevant_data: cultural meaning, historical context, significance.
				- artist: short biography and artist's situation when the song was released.
				- production: composers, producers, label, and notable technical details.
				- popularity: charts, awards, streaming/view counts.
				- trivia: fun facts, anecdotes, covers, appearances in movies/series.
				- recommendations: array of related songs or artists.
				
				All values must be written in the user language ($user_lang)."
		],
		[
			"role" => "user",
			"content" => "Give me the complete information for the song: '$term'."
		]
	],
	"max_tokens" => 1000
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
