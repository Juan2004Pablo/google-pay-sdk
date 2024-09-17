<?php

namespace Placetopay\GooglePaySdk\Helpers;

use DateTimeImmutable;
use Exception;
use phpseclib3\Crypt\EC\PublicKey;
use Placetopay\GooglePaySdk\Constants\ProtocolSettings;
use Placetopay\GooglePaySdk\Exceptions\GooglePayError;

class SignatureVerifier
{
    private string $recipientId;

    public function __construct(string $recipientId)
    {
        $this->recipientId = $recipientId;
    }

    /**
     * @throws GooglePayError
     */
    public function verify(array $data): void
    {
        if ($data['protocolVersion'] !== ProtocolSettings::ECV2_PROTOCOL_VERSION) {
            throw new GooglePayError('Protocol version not supported');
        }

        $signedKey = $this->validateIntermediateSigningKey($data);
        $this->verifyMessageSignature($signedKey, $data);
    }

    /**
     * @throws GooglePayError
     */
    private function validateIntermediateSigningKey(array $data): array
    {
        $signedKey = json_decode($data['intermediateSigningKey']['signedKey'], true);

        if (! isset($signedKey['keyExpiration'])) {
            throw new GooglePayError('Invalid signing key format');
        }

        if (! $this->validateKeyExpiration($signedKey['keyExpiration'])) {
            throw new GooglePayError('The intermediate signing key has expired');
        }

        return $signedKey;
    }

    private function validateKeyExpiration(string $expiration): bool
    {
        $currentTime = (new DateTimeImmutable())->getTimestamp() * 1000;

        return $currentTime < (int) $expiration;
    }

    /**
     * @throws GooglePayError
     */
    private function verifyMessageSignature(array $signedKey, array $data): void
    {
        $publicKey = KeyManager::loadKeyInPKCS8($signedKey['keyValue']);
        $signature = base64_decode($data['signature'], true);

        if ($signature === false) {
            throw new GooglePayError('Invalid signature format');
        }

        $signedData = $this->constructSignedData([
            ProtocolSettings::SENDER_ID,
            $this->recipientId,
            $data['protocolVersion'],
            $data['signedMessage'],
        ]);

        try {
            /** @var PublicKey $key */
            $key = $publicKey->withHash('sha256');
            $key->verify($signedData, $signature);

            /* if (!$key->verify($signedData, $signature)) {
                throw new GooglePayError('Signature verification failed');
            } */
        } catch (Exception $e) {
            throw new GooglePayError('The message signature could not be verified: ', $e->getMessage());
        }
    }

    private function constructSignedData(array $data): string
    {
        $signed = '';
        foreach ($data as $a) {
            $length = pack('V', strlen($a));
            $signed .= $length.$a;
        }

        return $signed;
    }
}
