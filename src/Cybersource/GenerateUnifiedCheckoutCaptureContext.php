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

require_once __DIR__ . DIRECTORY_SEPARATOR . '../../vendor/autoload.php';

function GenerateUnifiedCheckoutCaptureContext()
{
    $targetOrigins = ["https://the-up-demo.appspot.com"];
    $allowedCardNetworks = ["VISA", "MASTERCARD", "AMEX"];
    $allowedPaymentTypes = ["PANENTRY", "SRC"];
    $orderInformationAmountDetailsArr = [
        "totalAmount" => "21.00",
        "currency" => "USD"
    ];
    $orderInformationAmountDetails = new Upv1capturecontextsOrderInformationAmountDetails($orderInformationAmountDetailsArr);

    $orderInformationArr = [
        "amountDetails" => $orderInformationAmountDetails
    ];
    $orderInformation = new Upv1capturecontextsOrderInformation($orderInformationArr);

    $shipToCountries = ["US", "GB"];
    $captureMandateArr = [
        "billingType" => "FULL",
        "requestEmail" => true,
        "requestPhone" => true,
        "requestShipping" => true,
        "shipToCountries" => $shipToCountries,
        "showAcceptedNetworkIcons" => true
    ];
    $captureMandate = new Upv1capturecontextsCaptureMandate($captureMandateArr);

    $requestObjArr = [
        "targetOrigins" => $targetOrigins,
        "clientVersion" => "0.11",
        "allowedCardNetworks" => $allowedCardNetworks,
        "allowedPaymentTypes" => $allowedPaymentTypes,
        "country" => "US",
        "locale" => "en_US",
        "captureMandate" => $captureMandate,
        "orderInformation" => $orderInformation
    ];
    $requestObj = new GenerateUnifiedCheckoutCaptureContextRequest($requestObjArr);

    $commonElement = new ExternalConfiguration();
    $config = $commonElement->ConnectionHost();
    $merchantConfig = $commonElement->merchantConfigObject();

    $apiClient = new ApiClient($config, $merchantConfig);
    $apiInstance = new UnifiedCheckoutCaptureContextApi($apiClient);

    try {
        $apiResponse = $apiInstance->generateUnifiedCheckoutCaptureContext($requestObj);

        $secretKey = "d5f80542-fa77-4cd6-9621-6546cdf26d9b";
        $secretKey = "o1bjil39jdmPDtoRgsgzZ4H0LpnXVGagXRIJuj/0pAs=";

        $publicKey = <<<EOD
        -----BEGIN PUBLIC KEY-----
        MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAuNHQexUInV2ewInxGPJc
        nF6VKFph/X892AZdLuBUG4txsywytNQKEtitXrLBqREVjJaTZSj0x0rvPYjNn/dG
        dV7HhPsnjf+jC2mMMYAdGVvWsGLZmKKRN7kYxdWO0OcICAAcAtywRqrEikzaT9/a
        p4DrnqTVLaEHjtI0aRlbOWR+QR290gk4WweobxvtRkNGsYCAFyjFed2Vot6lJVQ5
        +uxbDIY1Zlwve0E722jFCva48CSl5Efwaj7izLkQMkoP/6Lr5S8Jq89I3XP1CFf/
        /N7wfPeiuWsZC7tcVGXePbeoI5Te8Oi1iOl5mJAxENuQn9RdtyBsNk/3RrYg/BGy
        NwIDAQAB
        -----END PUBLIC KEY-----
        EOD;

        $key = new Key(file_get_contents('./clave_publica_getnetcl_sandbox.pem'), 'RS256');
        $decoded = JWT::decode($apiResponse[0], $key);

        print_r($decoded);
        $payload = (array) $decoded;
        // print_r($payload);

        return $apiResponse;
    } catch (ApiException $e) {
        print_r($e->getResponseBody());
        print_r($e->getMessage());
    }
}

function getJwtAlgorithm($jwt) {
    // Dividir el JWT en sus tres partes
    list($headerEncoded, $payloadEncoded, $signatureEncoded) = explode('.', $jwt);

    // Decodificar el encabezado de base64url a JSON
    $headerJson = base64url_decode($headerEncoded);
    $header = json_decode($headerJson, true);

    // Obtener el algoritmo del encabezado
    return isset($header['alg']) ? $header['alg'] : null;
}

function base64url_decode($data) {
    $data = str_replace(array('-', '_'), array('+', '/'), $data);
    return base64_decode($data);
}

if (!defined('DO_NOT_RUN_SAMPLES')) {
    echo "\nGenerateUnifiedCheckoutCaptureContext Sample Code is Running..." . PHP_EOL;
    GenerateUnifiedCheckoutCaptureContext();
}