<?php
header("Content-Type: application/json; charset=UTF-8");

// 🔑 Reemplaza con tu API Key de Google
$API_KEY = "TU_API_KEY_YOUTUBE";

if (!isset($_GET['q']) || empty($_GET['q'])) {
    echo json_encode(["error" => "No se recibió ninguna búsqueda"]);
    exit;
}

$q = urlencode($_GET['q']);
$url = "https://www.googleapis.com/youtube/v3/search?part=snippet&type=video&maxResults=5&q=$q&key=$API_KEY";

// Llamada cURL
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$result = curl_exec($ch);
curl_close($ch);

if (!$result) {
    echo json_encode(["error" => "Error en la petición a YouTube API"]);
    exit;
}

$data = json_decode($result, true);

if (empty($data['items'])) {
    echo json_encode(["error" => "No se encontraron videos"]);
    exit;
}

$response = [];
foreach ($data['items'] as $item) {
    if ($item['id']['kind'] !== "youtube#video") continue;
    $response[] = [
        "title" => $item['snippet']['title'],
        "channel" => $item['snippet']['channelTitle'],
        "thumbnail" => $item['snippet']['thumbnails']['high']['url'],
        "videoId" => $item['id']['videoId']
    ];
}

echo json_encode($response);
