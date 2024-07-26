<?php

namespace Placetopay\ClicktopayClient\Requests\Mastercard;

use GuzzleHttp\Psr7\Request;
use Placetopay\ClicktopayClient\Constants\Utils;
use Placetopay\ClicktopayClient\Contracts\RequestContract;
use Psr\Http\Message\RequestInterface;

class ConfirmationRequest implements RequestContract
{
    public function __construct(
        private readonly array $data
    ) {
    }

    public function getRequest(string $method, array $auth): RequestInterface
    {
        return new Request(
            $method,
            $auth['url'],
            ['Content-Type' => 'application/json', 'x-openapi-clientid' => 'a2833c3d-f6d6-487d-8c56-77eaeffc5546'],
            json_encode($this->data)
        );
    }

    public function getProcess(): string
    {
        return Utils::PROCESS_MASTERCARD;
    }
}