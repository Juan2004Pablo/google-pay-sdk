<?php

use CyberSource\Api\TransientTokenDataApi;
use CyberSource\ApiClient;
use Placetopay\ClicktopayClient\Constants\Utils;
use Placetopay\ClicktopayClient\Cybersource\ExternalConfiguration;

require_once __DIR__.DIRECTORY_SEPARATOR.'../../vendor/autoload.php';

function GetPaymentCredentialsForTransientToken(): void
{
    $commonElement = new ExternalConfiguration();
    $config = $commonElement->ConnectionHost();
    $merchantConfig = $commonElement->merchantConfigObject();

    $apiClient = new ApiClient($config, $merchantConfig);
    $api_instance = new TransientTokenDataApi($apiClient);

    $paymentCredentialsReference = Utils::PAYMENT_CREDENTIALS_REFERENCE;

    try {
        // FALLA
        $result = $api_instance->getPaymentCredentialsForTransientToken($paymentCredentialsReference);
        dd($result);
    } catch (Exception $e) {
        echo 'Exception when calling TransientTokenDataApi->getPaymentCredentialsForTransientToken: ', $e->getMessage(), PHP_EOL;
    }
}

if (! defined('DO_NOT_RUN_SAMPLES')) {
    echo "\nGetPaymentCredentialsForTransientToken Sample Code is Running...".PHP_EOL;
    GetPaymentCredentialsForTransientToken();
}
