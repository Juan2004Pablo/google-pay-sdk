<?php

namespace Placetopay\GooglePaySdk\Helpers;

use Exception;
use phpseclib3\Crypt\EC;
use Placetopay\GooglePaySdk\Exceptions\GooglePayError;

class KeyManager
{
    /**
     * @throws GooglePayError
     */
    public static function loadKeyInPKCS8(string $keyContent): EC
    {
        self::validateKeyContentNotEmpty($keyContent);
        $keyInBinary = self::decodeBase64($keyContent);

        return self::loadKey($keyInBinary);
    }

    /**
     * @throws GooglePayError
     */
    private static function validateKeyContentNotEmpty(string $keyContent): void
    {
        if (empty($keyContent)) {
            throw new GooglePayError('Key content is empty');
        }
    }

    /**
     * @throws GooglePayError
     */
    private static function decodeBase64(string $keyContent): string
    {
        $keyInBinary = base64_decode($keyContent, true);
        if ($keyInBinary === false) {
            throw new GooglePayError('Key content is not valid base64');
        }

        return $keyInBinary;
    }

    /**
     * @throws GooglePayError
     */
    private static function loadKey(string $keyInBinary): EC
    {
        try {
            $key = EC::loadFormat('PKCS8', $keyInBinary);
        } catch (Exception $e) {
            throw new GooglePayError('Failed to load public key: '.$e->getMessage(), $e->getCode(), $e);
        }

        if (! $key) {
            throw new GooglePayError('Invalid public key');
        }

        return $key;
    }
}
