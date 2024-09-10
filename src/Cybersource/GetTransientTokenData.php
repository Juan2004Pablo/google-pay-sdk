<?php

use CyberSource\Api\TransientTokenDataApi;
use CyberSource\ApiClient;
use Placetopay\ClicktopayClient\Constants\Utils;
use Placetopay\ClicktopayClient\Cybersource\ExternalConfiguration;

require_once __DIR__.DIRECTORY_SEPARATOR.'../../vendor/autoload.php';

function getTransactionForTransientToken(): void
{
    $commonElement = new ExternalConfiguration();
    $config = $commonElement->ConnectionHost();
    $merchantConfig = $commonElement->merchantConfigObject();

    $apiClient = new ApiClient($config, $merchantConfig);
    $api_instance = new TransientTokenDataApi($apiClient);

    // string | Transient Token returned by the Unified Checkout application.
    $transientToken = Utils::TRANSIENT_TOKEN;

    try {
        $response = $api_instance->getTransactionForTransientToken($transientToken);

        dd($response);
    } catch (Exception $e) {
        echo 'Exception when calling TransientTokenDataApi->getTransactionForTransientToken: ', $e->getMessage(), PHP_EOL;
    }
}

if (! defined('DO_NOT_RUN_SAMPLES')) {
    echo "\nGetTransactionForTransientToken Sample Code is Running...".PHP_EOL;
    GetTransactionForTransientToken();
}
