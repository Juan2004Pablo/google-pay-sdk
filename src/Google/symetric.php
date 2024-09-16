<?php
require 'vendor/autoload.php';

use phpseclib3\Crypt\PublicKeyLoader;
use phpseclib3\Exception\NoKeyLoadedException;

// Datos proporcionados
$ephemeral_public_key_bytes = "\x04\x2c\xb3\x38\xea\xbe\xae\x74\x7a\x8f\x0b\x25\x9f\xda\x94\x0e\x11\x54\x5b\x8d\xc2\x1e\x5a\x0d\x2a\x98\x42\xe1\x40\xb1\x63\x6f\x0f\x68\xfd\xfd\x87\x54\x9b\x3b\x82\x6a\x18\x91\x18\x1d\x87\x69\xa1\x54\x89\x9a\xdc\x74\xe1\x3f\x06\x11\xa1\x80\x37\xaa\xa5\x50\xb4";

$private_key_pem = <<<EOD
-----BEGIN EC PRIVATE KEY-----
MHcCAQEEIPq2rvXK5WwpcfBEfbquBTLzvOsojyznzOb+t66lHYvRoAoGCCqGSM49
AwEHoUQDQgAEUllHEKh5HtNVZXiaKLUH69LyaVsGET15kr6GR2kBJ3YEJ5ItNpnj
UUOOJQNfPKltH6bSD9GFhPQD/EKaVrrMBA==
-----END EC PRIVATE KEY-----
EOD;

/**
 * Convierte una clave pública EC en bytes sin procesar a formato PEM.
 *
 * @param string $publicKeyBytes La clave pública en formato de bytes sin procesar (uncompressed, 65 bytes para P-256).
 * @return string La clave pública en formato PEM.
 * @throws Exception Si la curva no es soportada o la clave no tiene el formato correcto.
 */
function convertEcPublicKeyToPem($publicKeyBytes) {
    // Verificar que la clave pública tenga el formato esperado
    if (strlen($publicKeyBytes) !== 65 || $publicKeyBytes[0] !== "\x04") {
        throw new Exception('La clave pública debe estar en formato uncompressed y tener 65 bytes.');
    }

    // ASN.1 encoding for SubjectPublicKeyInfo for prime256v1 (secp256r1)
    // La estructura es fija para la curva prime256v1
    $algorithmIdentifier = "\x30\x59\x30\x13\x06\x07\x2A\x86\x48\xCE\x3D\x02\x01\x06\x08\x2A\x86\x48\xCE\x3D\x03\x01\x07";
    $bitString = "\x03" . chr(strlen($publicKeyBytes) + 1) . "\x00" . $publicKeyBytes;
    $subjectPublicKeyInfo = $algorithmIdentifier . $bitString;

    // Codificar en base64 y formatear como PEM
    $pem = "-----BEGIN PUBLIC KEY-----\n" .
        chunk_split(base64_encode($subjectPublicKeyInfo), 64, "\n") .
        "-----END PUBLIC KEY-----\n";

    return $pem;
}

try {
    // Cargar la clave privada
    $privateKey = PublicKeyLoader::loadPrivateKey($private_key_pem);
} catch (Exception $e) {
    die("Error al cargar la clave privada: " . $e->getMessage() . PHP_EOL);
}

try {
    // Convertir la clave pública efímera a PEM
    $ephemeral_public_key_pem = convertEcPublicKeyToPem($ephemeral_public_key_bytes);
} catch (Exception $e) {
    die("Error al convertir la clave pública a PEM: " . $e->getMessage() . PHP_EOL);
}

try {
    // Cargar la clave pública efímera desde el PEM
    $publicKey = PublicKeyLoader::loadPublicKey($ephemeral_public_key_pem);
} catch (NoKeyLoadedException $e) {
    die("Error al cargar la clave pública: " . $e->getMessage() . PHP_EOL);
} catch (Exception $e) {
    die("Error al cargar la clave pública: " . $e->getMessage() . PHP_EOL);
}

// Verificar que ambas claves usan la misma curva usando getCurve()
if ($privateKey->getCurve() !== $publicKey->getCurve()) {
    die("Las claves no usan la misma curva." . PHP_EOL);
} else {
    echo "Ambas claves usan la curva: " . $privateKey->getCurve() . PHP_EOL;
}

try {
    // Calcular el shared secret usando deriveKey en lugar de createSharedSecret

    $sharedSecret = openssl_pkey_derive($publicKey, $privateKey);
    die("Shared Secret: " . bin2hex($sharedSecret) . PHP_EOL);
//    $sharedSecret = $privateKey->deriveKey($publicKey);
} catch (Exception $e) {
    die("Error al calcular el shared secret: " . $e->getMessage() . PHP_EOL);
}

// Derivar una clave compartida a partir del shared secret, por ejemplo usando SHA-256
$sharedKey = hash('sha256', $sharedSecret, true);

// Mostrar la clave compartida en formato hexadecimal
echo "Shared Key (hex): " . bin2hex($sharedKey) . PHP_EOL;
?>


<!--2b8fb5a3b09cebbaa0caa227c1ce353d1522c9054744416990556a05a23a62db-->
<!--2b8fb5a3b09cebbaa0caa227c1ce353d1522c9054744416990556a05a23a62db-->