<?php
include "./envvars.php";

/*
$api_key_youtube = $_ENV["YOUTUBE_API_KEY"] ?? "";
$query = $_GET["q"] ?? "";

$url = "https://www.googleapis.com/youtube/v3/search?part=snippet&type=video&maxResults=1&q=" . urlencode($query) . "&key=" . $api_key_youtube;

header("Content-Type: application/json");
echo file_get_contents($url);

*/
$api_key = $_ENV["YOUTUBE_API_KEY"] ?? "";
$query = $_GET["q"] ?? "";

$url = "https://www.googleapis.com/youtube/v3/search"
     . "?part=snippet&type=video&maxResults=1&q=" . urlencode($query)
     . "&key=" . $api_key;

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);

if (curl_errno($ch)) {
    http_response_code(500);
    echo json_encode(["error" => "cURL error: " . curl_error($ch)]);
    curl_close($ch);
    exit;
}

$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code !== 200) {
    http_response_code($http_code);
    echo $response; // YouTube devuelve JSON con el error
    exit;
}

$data = json_decode($response, true);

header("Content-Type: application/json");
if (isset($data["items"][0]["id"]["videoId"])) {
    echo json_encode([
        "video_id" => $data["items"][0]["id"]["videoId"],
        "title"    => $data["items"][0]["snippet"]["title"] ?? ""
    ]);
} else {
    echo json_encode(["error" => "No video found"]);
}
