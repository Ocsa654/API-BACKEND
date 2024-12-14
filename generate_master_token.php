<?php

$url = 'http://127.0.0.1:8000/api/generate-master-token';
$secretKey = 'ClaveSecretaMuySegura'; // Debe coincidir con MASTER_SECRET_KEY en .env

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['secret_key' => $secretKey]));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json'
]);

$response = curl_exec($ch);

if (curl_errno($ch)) {
    echo 'Error en cURL: ' . curl_error($ch);
} else {
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    if ($httpCode === 200) {
        $result = json_decode($response, true);
        if (isset($result['master_token'])) {
            echo "Tu token maestro es: " . $result['master_token'];
        } else {
            echo "Error: No se pudo obtener el token maestro de la respuesta.";
        }
    } else {
        echo "Error HTTP: " . $httpCode . "\n";
        echo "Respuesta: " . $response;
    }
}

curl_close($ch);