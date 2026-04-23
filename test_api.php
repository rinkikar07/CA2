<?php
$data = [
    'model' => 'meta/llama-3.1-70b-instruct',
    'messages' => [['role'=>'user','content'=>'Hello']],
    'max_tokens' => 10
];
$ch = curl_init('https://integrate.api.nvidia.com/v1/chat/completions');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode($data),
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
        'Authorization: Bearer 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'
    ],
    CURLOPT_SSL_VERIFYPEER => false
]);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
echo "HTTP CODE: " . $httpCode . "\n";
echo "RESPONSE: " . $response . "\n";
if (curl_errno($ch)) echo "ERROR: " . curl_error($ch) . "\n";
curl_close($ch);
