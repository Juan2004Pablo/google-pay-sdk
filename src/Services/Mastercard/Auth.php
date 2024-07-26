<?php

namespace Placetopay\ClicktopayClient\Services\Mastercard;

use Mastercard\Developer\OAuth\Utils\AuthenticationUtils;
use Mastercard\Developer\Signers\PsrHttpMessageSigner;
use OpenSSLAsymmetricKey;
use Psr\Http\Message\RequestInterface;

class Auth
{
    public function __construct(
        private array $authData
    ) {
    }

    public function singRequest(RequestInterface $request): RequestInterface
    {
        $signer = new PsrHttpMessageSigner($this->authData['consumerKey'], $this->signingKey());

        return $signer->sign($request);
    }

    private function signingKey(): OpenSSLAsymmetricKey|bool
    {
        return AuthenticationUtils::loadSigningKey(
            $this->authData['keyFilePath'],
            $this->authData['keyAlias'],
            $this->authData['keyPassword']
        );
    }
}