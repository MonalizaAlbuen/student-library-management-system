<?php
// API configuration file
// This replaces direct MySQL connection.
// PHP system will now communicate with Node.js API.

$API_URL = "http://localhost:3000";

function callAPI($method, $endpoint, $data = null) {
    global $API_URL;

    $url = $API_URL . $endpoint;
    $ch = curl_init();

    if ($method === "GET" && !empty($data)) {
        $url .= "?" . http_build_query($data);
    }

    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);

    if ($method === "POST") {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Content-Type: application/json"
        ]);
    }

    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        $error = curl_error($ch);
        curl_close($ch);

        return [
            "status" => "error",
            "message" => "API connection failed: " . $error
        ];
    }

    curl_close($ch);

    $decoded = json_decode($response, true);

    if ($decoded === null) {
        return [
            "status" => "error",
            "message" => "Invalid API response"
        ];
    }

    return $decoded;
}
?>