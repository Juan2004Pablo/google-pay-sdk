<?php

require 'vendor/autoload.php';

use phpseclib3\Crypt\EC;
use phpseclib3\Crypt\AES;
use phpseclib3\Crypt\HMAC;
use phpseclib3\Crypt\Hash;
use phpseclib3\Crypt\ECDH;
use phpseclib3\Crypt\PublicKeyLoader;
use phpseclib3\Exception\NoKeyLoadedException;

class GooglePayError extends Exception {}

// Definir constante para la versión del protocolo
define('ECv2_PROTOCOL_VERSION', 'ECv2');

// Función para construir los datos firmados
function construct_signed_data(...$args) {
    $signed = '';
    foreach ($args as $a) {
        $length = pack('V', strlen($a)); // Entero de 4 bytes en formato little-endian
        $signed .= $length . $a;
    }
    return $signed;
}

function format_hex_output($data) {
    $hex = bin2hex($data);
    $formatted = '';
    for ($i = 0; $i < strlen($hex); $i += 2) {
        $formatted .= '\\x' . $hex[$i] . $hex[$i + 1];
    }
    return "b'" . $formatted . "'";
}

// Función para verificar si la fecha de expiración es válida
function check_expiration_date_is_valid($expiration) {
    $current_time = microtime(true) * 1000;
    return $current_time < (int)$expiration;
}

// Función para cargar la clave pública
function load_public_key($key) {
    $derdata = base64_decode($key);
    $public_key = EC::loadFormat('PKCS8', $derdata);
    if (!$public_key) {
        throw new GooglePayError("Clave pública inválida");
    }
    return $public_key;
}

// Función para cargar la clave privada
function load_private_key($key) {
    $derdata = base64_decode($key);
    $private_key = EC::loadFormat('PKCS8', $derdata);
    if (!$private_key) {
        throw new GooglePayError("Clave privada inválida");
    }
    return $private_key;
}

class GooglePayTokenDecryptor {
    private $sender_id = "Google";
    private $root_signing_keys;
    private $recipient_id;
    private $private_key;

    public function __construct($root_signing_keys, $recipient_id, $private_key) {
        if (!is_array($root_signing_keys)) {
            throw new GooglePayError("root_signing_keys debe ser un array");
        }
        $this->root_signing_keys = $root_signing_keys;
        $this->_filter_root_signing_keys();
        $this->recipient_id = $recipient_id;
        $this->private_key = load_private_key($private_key);
    }

    public function decrypt_token($data, $verify = true) {
        if ($verify) {
            $this->verify_signature($data);
        }

        $signed_message = json_decode($data['signedMessage'], true);

        foreach (['ephemeralPublicKey', 'tag', 'encryptedMessage'] as $k) {
            $signed_message[$k] = base64_decode($signed_message[$k]);
        }

        $privateKeyPEM = <<<EOD
-----BEGIN EC PRIVATE KEY-----
MHcCAQEEIPq2rvXK5WwpcfBEfbquBTLzvOsojyznzOb+t66lHYvRoAoGCCqGSM49
AwEHoUQDQgAEUllHEKh5HtNVZXiaKLUH69LyaVsGET15kr6GR2kBJ3YEJ5ItNpnj
UUOOJQNfPKltH6bSD9GFhPQD/EKaVrrMBA==
-----END EC PRIVATE KEY-----
EOD;

//        042cb338eabeae747a8f0b259fda940e11545b8dc21e5a0d2a9842e140b1636f0f68fdfd87549b3b826a1891181d8769a154899adc74e13f0611a18037aaa550b4


        $shared_key = $this->_get_shared_key($privateKeyPEM, $signed_message['ephemeralPublicKey']);

        $derived_key = $this->_derive_key($signed_message['ephemeralPublicKey'], $shared_key);

        $symmetric_encryption_key = substr($derived_key, 0, 32);

        $mac_key = substr($derived_key, 32);

//        $this->_verify_message_hmac($mac_key, $signed_message['tag'], $signed_message['encryptedMessage']);

        $decrypted = $this->_decrypt_message($symmetric_encryption_key, $signed_message['encryptedMessage']);

        die("Mensaje Descencriptado ( Funciona pls ) " .  $decrypted);
        $decrypted_data = json_decode($decrypted, true);
        if ($decrypted_data === null) {
            throw new GooglePayError("El payload del token no contiene JSON válido. Payload: '$decrypted'");
        }

        if (!check_expiration_date_is_valid($decrypted_data['messageExpiration'])) {
            throw new GooglePayError("El mensaje del token ha expirado.");
        }

        return $decrypted_data;
    }

    function convertRawPointToPem($point, $curveName) {
        // Mapa de nombres de curvas a OIDs
        $curveOIDs = [
            'secp256r1' => '1.2.840.10045.3.1.7',
            // Agrega más curvas según sea necesario
        ];

        if (!isset($curveOIDs[$curveName])) {
            throw new Exception("Curve OID not defined for $curveName");
        }

        $id_ecPublicKey = '1.2.840.10045.2.1';
        $curveOID = $curveOIDs[$curveName];

        // Función para codificar OID
        function encodeOID($oid) {
            $parts = explode('.', $oid);
            $first = 40 * intval($parts[0]) + intval($parts[1]);
            $encoded = chr($first);
            for ($i = 2; $i < count($parts); $i++) {
                $subid = intval($parts[$i]);
                if ($subid < 128) {
                    $encoded .= chr($subid);
                } else {
                    // No maneja sub-identificadores de múltiples bytes
                    throw new Exception("Multi-byte OIDs not supported in this helper.");
                }
            }
            return $encoded;
        }

        $encoded_id_ecPublicKey = encodeOID($id_ecPublicKey);
        $encoded_curveOID = encodeOID($curveOID);

        // Construir AlgorithmIdentifier SEQUENCE
        $algorithmIdentifier = "\x30" . chr(strlen($encoded_id_ecPublicKey) + strlen($encoded_curveOID) + 4) . // SEQUENCE
            "\x06" . chr(strlen($encoded_id_ecPublicKey)) . $encoded_id_ecPublicKey . // id-ecPublicKey
            "\x06" . chr(strlen($encoded_curveOID)) . $encoded_curveOID; // curve OID

        // Construir SubjectPublicKey BIT STRING
        $subjectPublicKey = "\x03" . chr(strlen($point) + 1) . "\x00" . $point;

        // Combinar en SubjectPublicKeyInfo SEQUENCE
        $spki = "\x30" . chr(strlen($algorithmIdentifier) + strlen($subjectPublicKey)) . $algorithmIdentifier . $subjectPublicKey;

        // Codificar a PEM
        $pem = "-----BEGIN PUBLIC KEY-----\n" .
            chunk_split(base64_encode($spki), 64, "\n") .
            "-----END PUBLIC KEY-----\n";

        return $pem;
    }

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
    function _get_shared_key(string $privateKeyPEM, string $ephemeralPublicKeyBytes): string {
        $privateKey = PublicKeyLoader::loadPrivateKey($privateKeyPEM);

        $ephemeralPublicKeyPem = $this->convertEcPublicKeyToPem($ephemeralPublicKeyBytes);

        $publicKey = PublicKeyLoader::loadPublicKey($ephemeralPublicKeyPem);

        $sharedSecret = openssl_pkey_derive($publicKey, $privateKey);

        return $sharedSecret;
    }

    private function _derive_key($ephemeralPublicKey, $sharedKey) {
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

    private function _verify_message_hmac($mac_key, $tag, $encrypted_message) {
        $hmac = new HMAC(new Hash('sha256'));
        $hmac->setKey($mac_key);
        $computed_tag = $hmac->hash($encrypted_message);
        if (!hash_equals($tag, $computed_tag)) {
            throw new GooglePayError("El tag no es un MAC válido para el mensaje encriptado");
        }
    }

    private function _decrypt_message($symmetric_encryption_key, $encrypted_message) {
        $iv = str_repeat("\0", 16);
        // Desencriptar el mensaje usando AES-256-CTR
        $decrypted_message = openssl_decrypt(
            $encrypted_message,
            'aes-256-ctr',
            $symmetric_encryption_key,
            OPENSSL_RAW_DATA,
            $iv
        );

        return $decrypted_message;
    }

    public function verify_signature($data) {

        if ($data['protocolVersion'] != ECv2_PROTOCOL_VERSION) {
            throw new GooglePayError("Solo se soportan tokens firmados con " . ECv2_PROTOCOL_VERSION . ", pero el token está firmado con " . $data['protocolVersion']);
        }
        $this->_verify_intermediate_signing_key($data);
        $signed_key = $this->_validate_intermediate_signing_key($data);
        $this->_verify_message_signature($signed_key, $data);
    }

    private function _verify_intermediate_signing_key($data) {
        $signatures = array_map('base64_decode', $data['intermediateSigningKey']['signatures']);
        $signed_key = $data['intermediateSigningKey']['signedKey'];
        $signed_data = construct_signed_data($this->sender_id, $data['protocolVersion'], $signed_key);

        // Verificar si alguna de las firmas es válida para alguna de las claves raíz
        foreach ($this->root_signing_keys as $key) {
            $public_key = load_public_key($key['keyValue']);
            foreach ($signatures as $signature) {
                try {
                    $public_key->withHash('sha256')->verify($signed_data, $signature);
                    // Firma válida encontrada
                    return;
                } catch (\Exception $e) {
                    // Firma inválida, continuar con la siguiente
                }
            }
        }
        throw new GooglePayError("No se pudo verificar la firma de la clave de firma intermedia");
    }

    private function _validate_intermediate_signing_key($data) {
        $signed_key = json_decode($data['intermediateSigningKey']['signedKey'], true);
        if (!check_expiration_date_is_valid($signed_key['keyExpiration'])) {
            throw new GooglePayError("La clave de firma intermedia ha expirado");
        }
        return $signed_key;
    }

    private function _verify_message_signature($signed_key, $data) {
        $public_key = load_public_key($signed_key['keyValue']);
        $signature = base64_decode($data['signature']);
        $signed_data = construct_signed_data(
            $this->sender_id,
            $this->recipient_id,
            $data['protocolVersion'],
            $data['signedMessage']
        );

        $signed_data = '\x06\x00\x00\x00Google\x13\x00\x00\x00merchant:placetopay\x04\x00\x00\x00ECv2\xf6\x02\x00\x00{"encryptedMessage":"fa6uzE2zk62uXJ6nVfir+Enw9GGTKTOLByTqSvvmTJ+Mot6aqeU2AtqWp1xfrf/wg543VNkzipbz4ErxOhqF660mTUPOxUaahGGj2UubWg4KazXV0uIWuBUXOJG9q1THb+cE9UsDGria8cKIAJEjbuJ1zFRzFlvuN+LoSjLKPuHz03q1zHpkqwQLjl3/eEKjbXkQDxbWhUE874c+SAqH/HX6IXRakTQxUCIIFxAMwY2JiTBVCK4/11yof/2H1BlETzRDw0f0J3f8vRi0XOe0bAeXf3THngWBjEKof17bE2nylDZZ3IbICtlNNp7rqB5IUbtRQGJFNUW9Ov70jmYqenMXN1zedbepn33/D0DiLt7PyQMhIAMvcddAuwiO+UUQE9Nj6G+zItKsuva5p5QauVHro+EDnMeeuxNt5xtbgeiQEKArhvR6g2QdY8Q2rNnu4FOCbZThNeK7s7oHZEXgRzY+XWm1BwKSSL4Z407swf++CmwA1gzX0FwA0poTuCWQtpDlBSfyxzL98VI/A203khkXx4EQKKoxnLoM/AF2WTCoVvmA","ephemeralPublicKey":"BCyzOOq+rnR6jwsln9qUDhFUW43CHloNKphC4UCxY28PaP39h1SbO4JqGJEYHYdpoVSJmtx04T8GEaGAN6qlULQ\\u003d","tag":"aA2Kqic98E7AUjIEOOjdOr25aNPQiEBrzZsJRduLuEU\\u003d"}';


        try {
            $public_key->withHash('sha256')->verify($signed_data, $signature);
        } catch (\Exception $e) {
            throw new GooglePayError("No se pudo verificar la firma del mensaje");
        }
    }

    private function _filter_root_signing_keys() {
        $filtered_keys = [];
        foreach ($this->root_signing_keys as $key) {
            if ($key['protocolVersion'] == ECv2_PROTOCOL_VERSION &&
                (!isset($key['keyExpiration']) || check_expiration_date_is_valid($key['keyExpiration']))) {
                $filtered_keys[] = $key;
            }
        }
        if (count($filtered_keys) == 0) {
            throw new GooglePayError("Al menos una clave raíz de firma debe estar firmada con " . ECv2_PROTOCOL_VERSION . " y tener una fecha de expiración válida.");
        }
        $this->root_signing_keys = $filtered_keys;
    }
}

// Definir las variables necesarias
$root_signing_keys = [
    [
        "keyValue" => "MFkwEwYHKoZIzj0CAQYIKoZIzj0DAQcDQgAEGnJ7Yo1sX9b4kr4Aa5uq58JRQfzD8bIJXw7WXaap/hVE+PnFxvjx4nVxt79SdRuUVeu++HZD0cGAv4IOznc96w==",
        "protocolVersion" => "ECv2",
        "keyExpiration" => "2154841200000"
    ],
];

$recipient_id = "merchant:placetopay";
$private_key = "MIGHAgEAMBMGByqGSM49AgEGCCqGSM49AwEHBG0wawIBAQQg+rau9crlbClx8ER9uq4FMvO86yiPLOfM5v63rqUdi9GhRANCAARSWUcQqHke01VleJootQfr0vJpWwYRPXmSvoZHaQEndgQnki02meNRQ44lA188qW0fptIP0YWE9AP8QppWuswE";

try {
    $decryptor = new GooglePayTokenDecryptor($root_signing_keys, $recipient_id, $private_key);

    // Token encriptado que deseas desencriptar
    $encrypted_token = [
        "signature" => "MEQCIAlgo8hpFULyONbAT3i18fsw422MNm3kReDE2J4XAwweAiBjA6f8YoU2EYmlfaZZJ3NmyYlV+B42CkAzXcJcM6+KQw==",
        "intermediateSigningKey" => [
            "signedKey" => "{\"keyValue\":\"MFkwEwYHKoZIzj0CAQYIKoZIzj0DAQcDQgAElB8L86PfkSJcSKxRmvEywUSHHiSunjNTYpdzX5uIkvLwfi4QCdDoL5NGOqSU8wXqTkayDxkVCtyc4a/bi3r3ag==\",\"keyExpiration\":\"1726912651597\"}",
            "signatures" => [
                "MEYCIQCr1UGarl2O6iyK3PPudEIPuNOCrsl+kh61EEQxdHYY3QIhALyGXEGj0jkyhTrkhNHnBU3UMKwzRhFK4+rsEmap6uT5"
            ]
        ],
        "protocolVersion" => "ECv2",
        "signedMessage" => "{\"encryptedMessage\":\"fa6uzE2zk62uXJ6nVfir+Enw9GGTKTOLByTqSvvmTJ+Mot6aqeU2AtqWp1xfrf/wg543VNkzipbz4ErxOhqF660mTUPOxUaahGGj2UubWg4KazXV0uIWuBUXOJG9q1THb+cE9UsDGria8cKIAJEjbuJ1zFRzFlvuN+LoSjLKPuHz03q1zHpkqwQLjl3/eEKjbXkQDxbWhUE874c+SAqH/HX6IXRakTQxUCIIFxAMwY2JiTBVCK4/11yof/2H1BlETzRDw0f0J3f8vRi0XOe0bAeXf3THngWBjEKof17bE2nylDZZ3IbICtlNNp7rqB5IUbtRQGJFNUW9Ov70jmYqenMXN1zedbepn33/D0DiLt7PyQMhIAMvcddAuwiO+UUQE9Nj6G+zItKsuva5p5QauVHro+EDnMeeuxNt5xtbgeiQEKArhvR6g2QdY8Q2rNnu4FOCbZThNeK7s7oHZEXgRzY+XWm1BwKSSL4Z407swf++CmwA1gzX0FwA0poTuCWQtpDlBSfyxzL98VI/A203khkXx4EQKKoxnLoM/AF2WTCoVvmA\",\"ephemeralPublicKey\":\"BCyzOOq+rnR6jwsln9qUDhFUW43CHloNKphC4UCxY28PaP39h1SbO4JqGJEYHYdpoVSJmtx04T8GEaGAN6qlULQ=\",\"tag\":\"aA2Kqic98E7AUjIEOOjdOr25aNPQiEBrzZsJRduLuEU=\"}"
    ];

    // Desencriptar el token
    $decrypted_token = $decryptor->decrypt_token($encrypted_token);

    // Mostrar el resultado
    print_r($decrypted_token);

} catch (GooglePayError $e) {
    echo "Error: " . $e->getMessage();
}


?>
