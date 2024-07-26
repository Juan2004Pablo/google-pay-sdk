<?php

namespace Placetopay\ClicktopayClient\Request\Mastercard;

use Placetopay\ClicktopayClient\Contracts\AuthContract;

class AuthRequest implements AuthContract
{
    public function __construct(
        private string $url,
        private string $signingKeyAlias,
        private string $signingKeyPassword,
        private string $consumerKey,
        private string $signingKeyFilePath,
    ) {
    }

    public function getCredentials(): array
    {
        return [
            'url' => $this->url,
            'keyAlias' => $this->signingKeyAlias,
            'consumerKey' => $this->consumerKey,
            'keyFilePath' => $this->signingKeyFilePath,
            'keyPassword' => $this->signingKeyPassword,
        ];
    }
}