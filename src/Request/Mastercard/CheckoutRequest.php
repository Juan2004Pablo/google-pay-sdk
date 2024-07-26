<?php

namespace Placetopay\ClicktopayClient\Request\Mastercard;

use GuzzleHttp\Psr7\Request;
use Psr\Http\Message\RequestInterface;
use Placetopay\ClicktopayClient\Constants\Utils;
use Placetopay\ClicktopayClient\Contracts\RequestContract;

class CheckoutRequest implements RequestContract
{
    public function __construct(
        private array $data
    ) {
    }

    public function getRequest(string $method, array $auth): RequestInterface
    {
        return new Request(
            $method,
            $auth['url'],
            ['Content-Type' => 'application/json', 'x-openapi-clientid' => 'ZKRNamOA1q9jKu5jFOjMsuW_WCFqmJ-dl1OT0Quw5e917ba8',
                'x-src-cx-flow-id' => "34f4a04b.cb2770ae-438a-4372-a048-3caaeb748971.1689082013"],
            json_encode($this->data)
        );
    }

    public function getProcess(): string
    {
        return Utils::PROCESS_MASTERCARD;
    }
}