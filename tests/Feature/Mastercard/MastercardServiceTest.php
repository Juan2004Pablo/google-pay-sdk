<?php

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use PHPUnit\Framework\TestCase;
use Placetopay\ClicktopayClient\MastercardService;
use Placetopay\ClicktopayClient\Request\Mastercard\AuthRequest;
use Placetopay\ClicktopayClient\Request\Mastercard\CheckoutRequest;

class MastercardServiceTest extends TestCase
{
    /**
     * @test
     * @throws GuzzleException
     */
    public function itMustReturnCheckoutEndpointResult(): void
    {
        $authData = new AuthRequest(
            'https://sandbox.api.mastercard.com/srci/api/checkout',
            'clicktopayMastercard123',
            'juan.pabon@evertecinc.com',
            'ZKRNamOA1q9jKu5jFOjMsuW_WCFqmJ-dl1OT0Quw5e917ba8!3b2978dbbbf94484a9376110aa4112c30000000000000000',
            './clicktopayMastercard123.p12'
        );

        $client = new Client();

        $service = new MastercardService($authData, $client);

        $result = $service->checkout(new CheckoutRequest([
            "dpaTransactionOptions" => [
                "transactionAmount" => [
                    "transactionAmount" => 100,
                    "transactionCurrencyCode" => "USD"
                ]
            ],
            "srcDpaId" => "a2833c3d-f6d6-487d-8c56-77eaeffc5546",
            "correlationId" => "ba7a2034-3c9e-4d74-b0e9-d77435fd35d7",
            "checkoutType" => "CLICK_TO_PAY",
            "checkoutReference" => [
                "type" => "MERCHANT_TRANSACTION_ID",
                "data" => [
                    "merchantTransactionId" => "0a4e0d3.34f4a04b.dbdba979d4caac43665f0e95c45c472ec59511cb"
                ]
            ]
        ]));

        dd($result);
    }
}