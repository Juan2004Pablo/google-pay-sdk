<?php

namespace Placetopay\GooglePaySdk;

use Placetopay\GooglePaySdk\Exceptions\GooglePayError;
use Placetopay\GooglePaySdk\Helpers\SignatureVerifier;
use Placetopay\GooglePaySdk\Helpers\TokenDecrypt;

class Gateway
{
    private SignatureVerifier $signatureVerifier;
    private TokenDecrypt $tokenDecrypt;
    private string $privateKeyPath;

    public function __construct(string $privateKeyPath, string $recipientId)
    {
        $this->signatureVerifier = new SignatureVerifier($recipientId);
        $this->tokenDecrypt = new TokenDecrypt();
        $this->privateKeyPath = $privateKeyPath;
    }

    /**
     * @throws GooglePayError
     */
    public function checkout(array $encryptedToken): array
    {
        $this->signatureVerifier->verify($encryptedToken);

        return $this->tokenDecrypt->decrypt($encryptedToken, $this->privateKeyPath);
    }
}