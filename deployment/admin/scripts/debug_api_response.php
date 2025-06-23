<?php
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://localhost:8000/api/auth/test');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
echo "Response: $response\n";
$data = json_decode($response, true);
echo "Parsed data:\n";
var_dump($data);
curl_close($ch);