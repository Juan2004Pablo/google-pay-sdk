<?php

require 'vendor/autoload.php';

use phpseclib3\Crypt\EC;
use phpseclib3\Crypt\PublicKeyLoader;
use phpseclib3\Crypt\EC\Curves\prime256v1;
use phpseclib3\Crypt\AES;
use phpseclib3\Math\BigInteger;

// Cargar la clave privada desde el archivo PEM
$privateKeyPem = file_get_contents(__DIR__ . '/key.pem');
$privateKey = PublicKeyLoader::loadPrivateKey($privateKeyPem);

// Payload a descifrar
$payload = '{"signature":"MEQCIAlgo8hpFULyONbAT3i18fsw422MNm3kReDE2J4XAwweAiBjA6f8YoU2EYmlfaZZJ3NmyYlV+B42CkAzXcJcM6+KQw==","intermediateSigningKey":{"signedKey":"{\"keyValue\":\"MFkwEwYHKoZIzj0CAQYIKoZIzj0DAQcDQgAElB8L86PfkSJcSKxRmvEywUSHHiSunjNTYpdzX5uIkvLwfi4QCdDoL5NGOqSU8wXqTkayDxkVCtyc4a/bi3r3ag==\",\"keyExpiration\":\"1726912651597\"}","signatures":["MEYCIQCr1UGarl2O6iyK3PPudEIPuNOCrsl+kh61EEQxdHYY3QIhALyGXEGj0jkyhTrkhNHnBU3UMKwzRhFK4+rsEmap6uT5"]},"protocolVersion":"ECv2","signedMessage":"{\"encryptedMessage\":\"fa6uzE2zk62uXJ6nVfir+Enw9GGTKTOLByTqSvvmTJ+Mot6aqeU2AtqWp1xfrf/wg543VNkzipbz4ErxOhqF660mTUPOxUaahGGj2UubWg4KazXV0uIWuBUXOJG9q1THb+cE9UsDGria8cKIAJEjbuJ1zFRzFlvuN+LoSjLKPuHz03q1zHpkqwQLjl3/eEKjbXkQDxbWhUE874c+SAqH/HX6IXRakTQxUCIIFxAMwY2JiTBVCK4/11yof/2H1BlETzRDw0f0J3f8vRi0XOe0bAeXf3THngWBjEKof17bE2nylDZZ3IbICtlNNp7rqB5IUbtRQGJFNUW9Ov70jmYqenMXN1zedbepn33/D0DiLt7PyQMhIAMvcddAuwiO+UUQE9Nj6G+zItKsuva5p5QauVHro+EDnMeeuxNt5xtbgeiQEKArhvR6g2QdY8Q2rNnu4FOCbZThNeK7s7oHZEXgRzY+XWm1BwKSSL4Z407swf++CmwA1gzX0FwA0poTuCWQtpDlBSfyxzL98VI/A203khkXx4EQKKoxnLoM/AF2WTCoVvmA\",\"ephemeralPublicKey\":\"BCyzOOq+rnR6jwsln9qUDhFUW43CHloNKphC4UCxY28PaP39h1SbO4JqGJEYHYdpoVSJmtx04T8GEaGAN6qlULQ=\",\"tag\":\"aA2Kqic98E7AUjIEOOjdOr25aNPQiEBrzZsJRduLuEU=\"}"}';

// Decodificar el payload JSON
$data = json_decode($payload, true);
$signedMessage = json_decode($data['signedMessage'], true);

// Base64-decodificar los componentes
$ephemeralPublicKeyBytes = base64_decode($signedMessage['ephemeralPublicKey']);

$encryptedMessageBytes = base64_decode($signedMessage['encryptedMessage']);
$tagBytes = base64_decode($signedMessage['tag']);

// Verificar que la clave pública efímera comienza con el marcador de formato sin comprimir (0x04)
if (substr($ephemeralPublicKeyBytes, 0, 1) !== "\x04") {
    throw new Exception('El formato de la clave pública efímera no es válido');
}

// Extraer las coordenadas X e Y de la clave pública efímera
$x = substr($ephemeralPublicKeyBytes, 1, 32);
$y = substr($ephemeralPublicKeyBytes, 33, 32);

$xBigInt = new BigInteger(bin2hex($x), 16);
$yBigInt = new BigInteger(bin2hex($y), 16);

// Crear la curva
$curve = new prime256v1();

//--------

$private_key = 'MIGHAgEAMBMGByqGSM49AgEGCCqGSM49AwEHBG0wawIBAQQg+rau9crlbClx8ER9uq4FMvO86yiPLOfM5v63rqUdi9GhRANCAARSWUcQqHke01VleJootQfr0vJpWwYRPXmSvoZHaQEndgQnki02meNRQ44lA188qW0fptIP0YWE9AP8QppWuswE';
$decoded_key = base64_decode($private_key);
$escaped_bytes = '';

for ($i = 0; $i < strlen($decoded_key); $i++) {

    $byte = ord($decoded_key[$i]);
    if ($byte >= 32 && $byte <= 126 && $decoded_key[$i] !== '\\' && $decoded_key[$i] !== '"') {
        $escaped_bytes .= $decoded_key[$i]; // Caracteres imprimibles
    } else {
        $escaped_bytes .= sprintf("\\x%02x", $byte); // Escapado hexadecimal
    }
}


$ephemeralPublicKeyBytes = 'b"' . $escaped_bytes . '"';


//--------

//die($ephemeralPublicKeyBytes);

// Crear la clave pública efímera a partir de las coordenadas
//$ephemeralPublicKey = EC::loadPublicKey([
//    'curve' => $curve,
//    'Q' => [
//        'x' => $xBigInt,
//        'y' => $yBigInt
//    ]
//]);

// Derivar la clave compartida utilizando ECDH
$sharedSecret = $privateKey->deriveSharedSecret($ephemeralPublicKey);
$sharedSecretBytes = $sharedSecret->toBytes();

// Concatenar la clave pública efímera y la clave compartida
$ikm = $ephemeralPublicKeyBytes . $sharedSecretBytes;

// Realizar HKDF con SHA-256 para derivar las claves
$keyMaterial = hash_hkdf('sha256', $ikm, 64, 'Google', null);

// Dividir el material de clave en symmetricEncryptionKey y macKey
$symmetricEncryptionKey = substr($keyMaterial, 0, 32);
$macKey = substr($keyMaterial, 32, 32);

// Verificar el tag utilizando HMAC-SHA256
$expectedTag = hash_hmac('sha256', $encryptedMessageBytes, $macKey, true);

if (!hash_equals($expectedTag, $tagBytes)) {
    throw new Exception('El tag no es un MAC válido para el mensaje cifrado');
}

// Descifrar el mensaje cifrado utilizando AES-256-CTR con IV cero
$iv = str_repeat("\x00", 16);
$aes = new AES('ctr');
$aes->setKey($symmetricEncryptionKey);
$aes->setIV($iv);
$plaintext = $aes->decrypt($encryptedMessageBytes);

if ($plaintext === false) {
    throw new Exception('Fallo al descifrar el mensaje');
}

// Decodificar el JSON del mensaje descifrado
$decryptedData = json_decode($plaintext, true);

if ($decryptedData === null) {
    throw new Exception('El mensaje descifrado no es un JSON válido');
}

// Mostrar los datos descifrados
print_r($decryptedData);

?>
