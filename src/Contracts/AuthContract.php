<?php

namespace Placetopay\ClicktopayClient\Contracts;

interface AuthContract
{
    public function getCredentials(): array;
}