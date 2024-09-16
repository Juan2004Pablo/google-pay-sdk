<?php
/**
 * Deriva una clave simétrica utilizando la función HKDF con SHA-256.
 *
 * @param string $ephemeralPublicKey La clave pública efímera en formato binario.
 * @param string $sharedKey La clave compartida en formato binario.
 * @return string La clave derivada en formato binario.
 */
function derive_key($ephemeralPublicKey, $sharedKey) {
    // Concatenar la clave pública efímera y la clave compartida
    $ikm = $ephemeralPublicKey . $sharedKey;

    // Definir la sal como 32 bytes de ceros
    $salt = str_repeat("\x00", 32);

    // Información adicional
    $info = "Google";

    // Longitud de la clave derivada en bytes (64 bytes = 512 bits)
    $length = 64;

    // Derivar la clave utilizando HKDF con SHA-256
    $derivedKey = hash_hkdf('sha256', $ikm, $length, $info, $salt);

    return $derivedKey;
}

// Valores de ejemplo proporcionados
$ephemeralPublicKeyHex = '042cb338eabeae747a8f0b259fda940e11545b8dc21e5a0d2a9842e140b1636f0f68fdfd87549b3b826a1891181d8769a154899adc74e13f0611a18037aaa550b4';
$sharedKeyHex = '2b8fb5a3b09cebbaa0caa227c1ce353d1522c9054744416990556a05a23a62db';

// Función para verificar y corregir la longitud de la cadena hex
function ensure_even_length($hex) {
    if (strlen($hex) % 2 !== 0) {
        // Agregar un cero al inicio si la longitud es impar
        $hex = '0' . $hex;
    }
    return $hex;
}

// Corregir las cadenas hexadecimales
$ephemeralPublicKeyHex = ensure_even_length($ephemeralPublicKeyHex);
$sharedKeyHex = ensure_even_length($sharedKeyHex);

// Convertir las cadenas hexadecimales a binario
$ephemeralPublicKey = hex2bin($ephemeralPublicKeyHex);
$sharedKey = hex2bin($sharedKeyHex);

// Verificar que la conversión fue exitosa
if ($ephemeralPublicKey === false) {
    die("Error al convertir ephemeralPublicKeyHex a binario.\n");
}
if ($sharedKey === false) {
    die("Error al convertir sharedKeyHex a binario.\n");
}

// Derivar la clave
$derivedKey = derive_key($ephemeralPublicKey, $sharedKey);

// Mostrar la clave derivada en formato hexadecimal
echo "Derived Key: " . bin2hex($derivedKey) . PHP_EOL;

// Opcional: Verificar que la clave derivada coincide con la esperada
$expectedDerivedKeyHex = '8ba5ec96baa47273acd1d7640634a276e3baaea86cd38eba17bf1b971a6ca0428b1da72a48c809c2cd75804c697993b7b9f21e74359614a83f443771c82e7fb7';
if (strtolower(bin2hex($derivedKey)) === strtolower($expectedDerivedKeyHex)) {
    echo "La clave derivada coincide con la esperada." . PHP_EOL;
} else {
    echo "La clave derivada NO coincide con la esperada." . PHP_EOL;
}

$symmetric_encryption_key = substr($derivedKey, 0, 32);

echo "Symmetric Encryption Key: " . bin2hex($symmetric_encryption_key) . PHP_EOL;

echo "Longitud de symmetric_encryption_key: " . strlen($symmetric_encryption_key) . " bytes" . PHP_EOL;
?>