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
    $orderInformationAmountDetailsArr = [
        //Total general del pedido. (String). Puede incluir un punto decimal (.)
        'totalAmount' => '20000',

        // Moneda utilizada en la transacción.
        'currency' => 'COP',
    ];
    $orderInformationAmountDetails = new Upv1capturecontextsOrderInformationAmountDetails($orderInformationAmountDetailsArr);

    $orderInformationArr = [
        'amountDetails' => $orderInformationAmountDetails,
    ];
    $orderInformation = new Upv1capturecontextsOrderInformation($orderInformationArr);

    $captureMandateArr = [
        // Define el tipo de información de dirección de facturación capturada a través de la experiencia de usuario Visa.
        // FULL, PARTIAL
        'billingType' => 'FULL',

        // Define si solicitar el email en la experiencia de pago Visa (bool)
        'requestEmail' => false,

        // Define si solicitar el teléfono en la experiencia de pago Visa (bool)
        'requestPhone' => false,

        // Define si solicitar información de envio en la experiencia de pago Visa (bool)
        'requestShipping' => false,

        // Lista de países a los que se pueden realizar envíos. Utilice los códigos de país estándar ISO_3166_2.
        'shipToCountries' => ['US', 'GB'],

        // Define si solicitar el email en la experiencia de pago Visa (bool)
        'showAcceptedNetworkIcons' => true,
    ];
    $captureMandate = new Upv1capturecontextsCaptureMandate($captureMandateArr);

    $requestObjArr = [
        // URL donde se instanciará la experiencia de pago y debe ser https. (String)
        'targetOrigins' => ['https://poc-lemon-five.vercel.app'],

        // Versión de la libreria js a utilizar (String: 4)
        'clientVersion' => '0.22',

        // Definir las tarjetas de pago que desea aceptar. (American Express, Diners Club, Discover, JCB, Mastercard, Visa)
        'allowedCardNetworks' => ['VISA', 'MASTERCARD', 'AMEX'],

        // Tipo de pago (Enum) Possible values: CLICKTOPAY, GOOGLEPAY, PANENTRY
        'allowedPaymentTypes' => ['CLICKTOPAY'],

        // País en el que se hará la transacción. Utilizar el código del país en ISO_3166_2 (String: 2).
        'country' => 'CO',

        // Configuración regional en la que se hará la transacción (String: 5).
        // Este campo controla aspectos de la aplicación, como el idioma en el que se mostrará.
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

        dd($decoded, 'library: '.$decoded->ctx[0]->data->clientLibrary, $apiResponse[0]);
    } catch (ApiException $e) {
        dd($e->getResponseBody(), $e->getMessage());
    }
}

if (! defined('DO_NOT_RUN_SAMPLES')) {
    echo "\nGenerateUnifiedCheckoutCaptureContext Sample Code is Running...".PHP_EOL;
    GenerateUnifiedCheckoutCaptureContext();
}
