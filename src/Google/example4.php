<?php

function convertEcPublicKeyToPem($publicKeyBase64)
{
    // Decodifica la cadena base64
    $ecPublicKey = base64_decode($publicKeyBase64);

    // OID para id-ecPublicKey (1.2.840.10045.2.1)
    $algo_oid = "\x06\x07\x2A\x86\x48\xCE\x3D\x02\x01";

    // OID para prime256v1 (1.2.840.10045.3.1.7)
    $param_oid = "\x06\x08\x2A\x86\x48\xCE\x3D\x03\x01\x07";

    // Construye la secuencia del algoritmo
    $algo_sequence = "\x30" . chr(strlen($algo_oid . $param_oid)) . $algo_oid . $param_oid;

    // Construye el BIT STRING de la clave pública
    $bit_string = "\x03" . chr(strlen($ecPublicKey) + 1) . "\x00" . $ecPublicKey;

    // Construye la estructura SubjectPublicKeyInfo
    $public_key_info = "\x30" . chr(strlen($algo_sequence . $bit_string)) . $algo_sequence . $bit_string;

    // Codifica en base64 y agrega las cabeceras PEM
    $pem = "-----BEGIN PUBLIC KEY-----\n" . chunk_split(base64_encode($public_key_info), 64, "\n") . "-----END PUBLIC KEY-----\n";

    return $pem;
}

$publicKeyBase64 = 'BCyzOOq+rnR6jwsln9qUDhFUW43CHloNKphC4UCxY28PaP39h1SbO4JqGJEYHYdpoVSJmtx04T8GEaGAN6qlULQ=';

// Convierte la clave pública a formato PEM
$publicKeyPEM = convertEcPublicKeyToPem($publicKeyBase64);

// Carga la clave pública
$publicKey = openssl_pkey_get_public($publicKeyPEM);
if (!$publicKey) {
    throw new Exception("Error al cargar la clave pública efímera.");
}

// Continúa con tu lógica...
echo "Clave pública cargada correctamente.\n";
