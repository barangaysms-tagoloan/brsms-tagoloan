<?php

function sendOtpSms($toPhone, $otp) {
    $apiKey   = $_ENV['TEXTBEE_API_KEY'];
    $deviceId = $_ENV['TEXTBEE_DEVICE_ID'];
    $baseUrl  = $_ENV['TEXTBEE_BASE_URL'];

    $url = "$baseUrl/gateway/devices/$deviceId/send-sms";

    $data = [
        "recipients" => [$toPhone],
        "message" => "Your OTP code for BRSMS login is: {$otp}. This code will expire in 10 minutes."
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "x-api-key: $apiKey",
        "Content-Type: application/json"
    ]);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    if ($curlError) {
        return ["success" => false, "message" => "cURL Error: $curlError"];
    }

    $responseData = json_decode($response, true);

    if ($httpCode === 200 && isset($responseData['data']['status'])) {
        $status = strtoupper($responseData['data']['status']);
        if (in_array($status, ['PENDING', 'SENT', 'DELIVERED'])) {
            return ["success" => true, "message" => "SMS status: $status"];
        } else {
            return ["success" => false, "message" => "SMS status: $status"];
        }
    }
    error_log("TextBee raw response: " . $response);

    $errorMsg = isset($responseData['message']) ? $responseData['message'] : "Unknown API error";
    return ["success" => false, "message" => "TextBee Error: $errorMsg"];
}

?>