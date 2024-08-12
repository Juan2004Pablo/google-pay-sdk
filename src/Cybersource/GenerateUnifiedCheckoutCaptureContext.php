<?php

namespace Placetopay\ClicktopayClient\Cybersource;

use CyberSource\Api\UnifiedCheckoutCaptureContextApi;
use CyberSource\ApiClient;
use CyberSource\ApiException;
use CyberSource\Model\GenerateUnifiedCheckoutCaptureContextRequest;
use CyberSource\Model\Upv1capturecontextsCaptureMandate;
use CyberSource\Model\Upv1capturecontextsOrderInformation;
use CyberSource\Model\Upv1capturecontextsOrderInformationAmountDetails;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

require_once __DIR__.DIRECTORY_SEPARATOR.'../../vendor/autoload.php';

function GenerateUnifiedCheckoutCaptureContext(): void
{
    $targetOrigins = ['https://poc-lemon-five.vercel.app'];
    $allowedCardNetworks = ['VISA', 'MASTERCARD', 'AMEX'];
    $allowedPaymentTypes = ['PANENTRY', 'SRC'];
    $orderInformationAmountDetailsArr = [
        'totalAmount' => '20000',
        'currency' => 'COP',
    ];
    $orderInformationAmountDetails = new Upv1capturecontextsOrderInformationAmountDetails($orderInformationAmountDetailsArr);

    $orderInformationArr = [
        'amountDetails' => $orderInformationAmountDetails,
    ];
    $orderInformation = new Upv1capturecontextsOrderInformation($orderInformationArr);

    $shipToCountries = ['US', 'GB'];
    /*$captureMandateArr = [
        "billingType" => "FULL",
        "requestEmail" => true,
        "requestPhone" => true,
        "requestShipping" => true,
        "shipToCountries" => $shipToCountries,
        "showAcceptedNetworkIcons" => true
    ];*/
    $captureMandateArr = [
        'billingType' => 'FULL',
        'requestEmail' => false,
        'requestPhone' => false,
        'requestShipping' => false,
        'shipToCountries' => $shipToCountries,
        'showAcceptedNetworkIcons' => true,
    ];
    $captureMandate = new Upv1capturecontextsCaptureMandate($captureMandateArr);

    $requestObjArr = [
        'targetOrigins' => $targetOrigins,
        'clientVersion' => '0.22',
        'allowedCardNetworks' => $allowedCardNetworks,
        'allowedPaymentTypes' => $allowedPaymentTypes,
        'country' => 'CO',
        'locale' => 'es_CO',
        'captureMandate' => $captureMandate,
        'orderInformation' => $orderInformation,
    ];
    $requestObj = new GenerateUnifiedCheckoutCaptureContextRequest($requestObjArr);

    $commonElement = new ExternalConfiguration();
    $config = $commonElement->ConnectionHost();
    $merchantConfig = $commonElement->merchantConfigObject();

    $apiClient = new ApiClient($config, $merchantConfig);
    $apiInstance = new UnifiedCheckoutCaptureContextApi($apiClient);

    try {
        $apiResponse = $apiInstance->generateUnifiedCheckoutCaptureContext($requestObj);

        $publicKey = file_get_contents('./clave_publica_getnetcl_sandbox.pem');

        $decoded = JWT::decode($apiResponse[0], new Key($publicKey, 'RS256'));
        print_r($decoded);

        // LIBRARY
        print_r('library: '.$decoded->ctx[0]->data->clientLibrary);
        print_r('jwt: '.$apiResponse[0]);
    } catch (ApiException $e) {
        print_r($e->getResponseBody());
        print_r($e->getMessage());
    }
}

if (! defined('DO_NOT_RUN_SAMPLES')) {
    echo "\nGenerateUnifiedCheckoutCaptureContext Sample Code is Running...".PHP_EOL;
    GenerateUnifiedCheckoutCaptureContext();
}
