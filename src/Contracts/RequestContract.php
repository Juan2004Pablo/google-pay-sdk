<?php

namespace Placetopay\ClicktopayClient\Contracts;

use Psr\Http\Message\RequestInterface;

interface RequestContract
{
    public function getRequest(string $method, array $auth): RequestInterface;

    public function getProcess(): string;
}