<?php

function sendAttendanceEmail(
    string $receiverEmail,
    string $studentName,
    string $uid,
    string $date,
    string $time
){

    $apiKey = "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";


    $data = [

        "sender" => [
            "name" => "RFID Attendance System",
            "email" => "benette.arocha1@gmail.com"
        ],


        "to" => [

            [
                "email" => $receiverEmail,
                "name" => $studentName
            ]

        ],


        "subject" => "Attendance Recorded",


        "htmlContent" => "

        <h2>Attendance Confirmation</h2>

        <p>Hello <b>$studentName</b></p>

        <p>Your attendance has been recorded.</p>

        <hr>

        <b>RFID UID:</b> $uid <br>

        <b>Date:</b> $date <br>

        <b>Time:</b> $time <br>

        <b>Status:</b> Present

        "

    ];


    $curl = curl_init();


    curl_setopt_array($curl,[

        CURLOPT_URL =>
        "https://api.brevo.com/v3/smtp/email",

        CURLOPT_RETURNTRANSFER => true,


        CURLOPT_POST => true,


        CURLOPT_HTTPHEADER => [

            "accept: application/json",

            "api-key: ".$apiKey,

            "content-type: application/json"

        ],


        CURLOPT_POSTFIELDS =>
        json_encode($data)

    ]);



    $response = curl_exec($curl);

if($response === false){
    echo "CURL ERROR: " . curl_error($curl);
}

$httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

echo "HTTP CODE: " . $httpCode;
echo "<br>";
echo "RESPONSE: " . $response;

curl_close($curl);

return $response;

}

?>