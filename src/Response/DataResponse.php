<?php

namespace Placetopay\ClicktopayClient\Response;

use Psr\Http\Message\ResponseInterface;
use Placetopay\ClicktopayClient\Contracts\ResponseContract;

class DataResponse implements ResponseContract
{
    protected array $body = [];

    public function __construct(
        private readonly ResponseInterface $data,
        protected array $auth,
    ) {
        if ($_data = json_decode($this->data->getBody(), true)) {
            $this->body = $_data;
        }
    }

    public function getBody(): array
    {
        return $this->body;
    }

    public function getHeader(): array
    {
        return $this->data->getHeaders();
    }

    public function getCode(): int
    {
        return $this->data->getStatusCode();
    }
}