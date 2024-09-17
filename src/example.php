<?php

namespace Placetopay\GooglePaySdk;

use Placetopay\GooglePaySdk\Exceptions\GooglePayError;

require 'vendor/autoload.php';

try {
    $recipientId = 'merchant:placetopay';

    $encryptedToken = [
        'signature' => 'MEQCIAlgo8hpFULyONbAT3i18fsw422MNm3kReDE2J4XAwweAiBjA6f8YoU2EYmlfaZZJ3NmyYlV+B42CkAzXcJcM6+KQw==',
        'intermediateSigningKey' => [
            'signedKey' => '{"keyValue":"MFkwEwYHKoZIzj0CAQYIKoZIzj0DAQcDQgAElB8L86PfkSJcSKxRmvEywUSHHiSunjNTYpdzX5uIkvLwfi4QCdDoL5NGOqSU8wXqTkayDxkVCtyc4a/bi3r3ag==","keyExpiration":"1726912651597"}',
            'signatures' => [
                'MEYCIQCr1UGarl2O6iyK3PPudEIPuNOCrsl+kh61EEQxdHYY3QIhALyGXEGj0jkyhTrkhNHnBU3UMKwzRhFK4+rsEmap6uT5',
            ],
        ],
        'protocolVersion' => 'ECv2',
        'signedMessage' => '{"encryptedMessage":"fa6uzE2zk62uXJ6nVfir+Enw9GGTKTOLByTqSvvmTJ+Mot6aqeU2AtqWp1xfrf/wg543VNkzipbz4ErxOhqF660mTUPOxUaahGGj2UubWg4KazXV0uIWuBUXOJG9q1THb+cE9UsDGria8cKIAJEjbuJ1zFRzFlvuN+LoSjLKPuHz03q1zHpkqwQLjl3/eEKjbXkQDxbWhUE874c+SAqH/HX6IXRakTQxUCIIFxAMwY2JiTBVCK4/11yof/2H1BlETzRDw0f0J3f8vRi0XOe0bAeXf3THngWBjEKof17bE2nylDZZ3IbICtlNNp7rqB5IUbtRQGJFNUW9Ov70jmYqenMXN1zedbepn33/D0DiLt7PyQMhIAMvcddAuwiO+UUQE9Nj6G+zItKsuva5p5QauVHro+EDnMeeuxNt5xtbgeiQEKArhvR6g2QdY8Q2rNnu4FOCbZThNeK7s7oHZEXgRzY+XWm1BwKSSL4Z407swf++CmwA1gzX0FwA0poTuCWQtpDlBSfyxzL98VI/A203khkXx4EQKKoxnLoM/AF2WTCoVvmA","ephemeralPublicKey":"BCyzOOq+rnR6jwsln9qUDhFUW43CHloNKphC4UCxY28PaP39h1SbO4JqGJEYHYdpoVSJmtx04T8GEaGAN6qlULQ=","tag":"aA2Kqic98E7AUjIEOOjdOr25aNPQiEBrzZsJRduLuEU="}',
    ];

    $privateKeyPath = './private_key_google.pem';

    $gateway = new Gateway($privateKeyPath, $recipientId);
    $decryptedToken = $gateway->checkout($encryptedToken);

    dd($decryptedToken);
    return $decryptedToken;
} catch (GooglePayError $e) {
    echo 'Error: '.$e->getMessage();
}
