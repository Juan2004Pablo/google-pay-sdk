<?php

namespace Placetopay\ClicktopayClient\Contracts;

interface ResponseContract
{
    public function getHeader(): array;

    public function getBody(): array;

    public function getCode(): int;
}