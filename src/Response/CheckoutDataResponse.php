<?php

namespace Placetopay\ClicktopayClient\Response;

class CheckoutDataResponse extends DataResponse
{
    public function getBody(): array
    {
        return $this->body;
    }
}