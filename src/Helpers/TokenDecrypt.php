<?php

namespace Placetopay\GooglePaySdk\Helpers;

use Exception;
use phpseclib3\Crypt\Common\PrivateKey;
use phpseclib3\Crypt\Common\PublicKey;
use phpseclib3\Crypt\PublicKeyLoader;
use Placetopay\GooglePaySdk\Constants\ProtocolSettings;
use Placetopay\GooglePaySdk\Exceptions\GooglePayError;

class TokenDecrypt
{
    /**
     * @throws GooglePayError
     */
    public function decrypt(array $data, string $privateKeyPath): array
    {
        $privateKeyPem = file_get_contents($privateKeyPath);

        $signedMessage = $this->decodeSignedMessage($data['signedMessage']);
        $sharedKey = $this->getSharedKey($signedMessage['ephemeralPublicKey'], $privateKeyPem);
        $derivedKey = $this->deriveKey($signedMessage['ephemeralPublicKey'], $sharedKey);

        $symmetricKey = substr($derivedKey, 0, 32);
        $decryptedMessage = $this->decryptMessage($symmetricKey, $signedMessage['encryptedMessage']);

        return json_decode($decryptedMessage, true);
    }

    private function decodeSignedMessage(string $signedMessage): array
    {
        $decodedMessage = json_decode($signedMessage, true);

        foreach (['ephemeralPublicKey', 'tag', 'encryptedMessage'] as $key) {
            $decodedMessage[$key] = base64_decode($decodedMessage[$key]);
        }

        return $decodedMessage;
    }

    /**
     * @throws GooglePayError
     */
    private function getSharedKey(string $ephemeralPublicKey, string $privateKeyPEM): string
    {
        $privateKey = $this->loadPrivateKey($privateKeyPEM);
        $publicKey = $this->loadPublicKey($ephemeralPublicKey);

        return $this->deriveSharedKey($publicKey, $privateKey);
    }

    /**
     * @throws GooglePayError
     */
    private function loadPrivateKey(string $privateKeyPEM): PrivateKey
    {
        try {
            return PublicKeyLoader::loadPrivateKey($privateKeyPEM);
        } catch (Exception $e) {
            throw new GooglePayError('Failed to load private key: '.$e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * @throws GooglePayError
     */
    private function loadPublicKey(string $ephemeralPublicKey): PublicKey
    {
        $ephemeralPublicKeyPem = $this->convertEcPublicKeyToPem($ephemeralPublicKey);

        try {
            return PublicKeyLoader::loadPublicKey($ephemeralPublicKeyPem);
        } catch (Exception $e) {
            throw new GooglePayError('Failed to load public key: '.$e->getMessage(), $e->getCode(), $e);
        }
    }

    private function deriveSharedKey($publicKey, $privateKey): string
    {
        return openssl_pkey_derive($publicKey, $privateKey);
    }

    /**
     * @throws GooglePayError
     */
    public function convertEcPublicKeyToPem(string $publicKeyBytes): string
    {
        if (strlen($publicKeyBytes) !== 65 || $publicKeyBytes[0] !== "\x04") {
            throw new GooglePayError('The public key must be in uncompressed format and be 65 bytes long.');
        }

        $algorithmIdentifier = "\x30\x59\x30\x13\x06\x07\x2A\x86\x48\xCE\x3D\x02\x01\x06\x08\x2A\x86\x48\xCE\x3D\x03\x01\x07";
        $bitString = "\x03".chr(strlen($publicKeyBytes) + 1)."\x00".$publicKeyBytes;
        $subjectPublicKeyInfo = $algorithmIdentifier.$bitString;

        return "-----BEGIN PUBLIC KEY-----\n".
            chunk_split(base64_encode($subjectPublicKeyInfo), 64).
            "-----END PUBLIC KEY-----\n";
    }

    private function deriveKey(string $ephemeralPublicKey, string $sharedKey): string
    {
        $ikm = $ephemeralPublicKey.$sharedKey;

        $salt = str_repeat("\x00", 32);
        $info = ProtocolSettings::SENDER_ID;
        $length = 64;

        return hash_hkdf('sha256', $ikm, $length, $info, $salt);
    }

    private function decryptMessage(string $symmetric_key, string $encrypted_message): string|false
    {
        $iv = str_repeat("\0", 16);

        return openssl_decrypt($encrypted_message, 'aes-256-ctr', $symmetric_key, OPENSSL_RAW_DATA, $iv);
    }
}
