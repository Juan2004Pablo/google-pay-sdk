<?php
//
//$shared_key = '+\x8f\xb5\xa3\xb0\x9c\xeb\xba\xa0\xca\xa2\'\xc1\xce5=\x15"\xc9\x05GDAi\x90Uj\x05\xa2:b\xdb';
//
//$derived_key = '\x8b\xa5\xec\x96\xba\xa4rs\xac\xd1\xd7d\x064\xa2v\xe3\xba\xae\xa8l\xd3\x8e\xba\x17\xbf\x1b\x97\x1al\xa0B\x8b\x1d\xa7*H\xc8\t\xc2\xcdu\x80Liy\x93\xb7\xb9\xf2\x1et5\x96\x14\xa8?D7q\xc8.\x7f\xb7';
//
//$encrypted_message = b'}\xae\xae\xccM\xb3\x93\xad\xae\\\x9e\xa7U\xf8\xab\xf8I\xf0\xf4a\x93)3\x8b\x07$\xeaJ\xfb\xe6L\x9f\x8c\xa2\xde\x9a\xa9\xe56\x02\xda\x96\xa7\\_\xad\xff\xf0\x83\x9e7T\xd93\x8a\x96\xf3\xe0J\xf1:\x1a\x85\xeb\xad&MC\xce\xc5F\x9a\x84a\xa3\xd9K\x9bZ\x0e\nk5\xd5\xd2\xe2\x16\xb8\x15\x178\x91\xbd\xabT\xc7o\xe7\x04\xf5K\x03\x1a\xb8\x9a\xf1\xc2\x88\x00\x91#n\xe2u\xccTs\x16[\xee7\xe2\xe8J2\xca>\xe1\xf3\xd3z\xb5\xcczd\xab\x04\x0b\x8e]\xffxB\xa3my\x10\x0f\x16\xd6\x85A<\xef\x87>H\n\x87\xfcu\xfa!tZ\x9141P"\x08\x17\x10\x0c\xc1\x8d\x89\x890U\x08\xae?\xd7\\\xa8\x7f\xfd\x87\xd4\x19DO4C\xc3G\xf4\'w\xfc\xbd\x18\xb4\\\xe7\xb4l\x07\x97\x7ft\xc7\x9e\x05\x81\x8cB\xa8\x7f^\xdb\x13i\xf2\x946Y\xdc\x86\xc8\n\xd9M6\x9e\xeb\xa8\x1eHQ\xbbQ@bE5E\xbd:\xfe\xf4\x8ef*zs\x177\\\xdeu\xb7\xa9\x9f}\xff\x0f@\xe2.\xde\xcf\xc9\x03! \x03/q\xd7@\xbb\x08\x8e\xf9E\x10\x13\xd3c\xe8o\xb3"\xd2\xac\xba\xf6\xb9\xa7\x94\x1a\xb9Q\xeb\xa3\xe1\x03\x9c\xc7\x9e\xbb\x13m\xe7\x1b[\x81\xe8\x90\x10\xa0+\x86\xf4z\x83d\x1dc\xc46\xac\xd9\xee\xe0S\x82m\x94\xe15\xe2\xbb\xb3\xba\x07dE\xe0G6>]i\xb5\x07\x02\x92H\xbe\x19\xe3N\xec\xc1\xff\xbe\nl\x00\xd6\x0c\xd7\xd0\\\x00\xd2\x9a\x13\xb8%\x90\xb6\x90\xe5\x05\'\xf2\xc72\xfd\xf1R?\x03m7\x92\x19\x17\xc7\x81\x10(\xaa1\x9c\xba\x0c\xfc\x01vY0\xa8V\xf9\x80';
//
//$symmetric_encryption_key = b'\x8b\xa5\xec\x96\xba\xa4rs\xac\xd1\xd7d\x064\xa2v\xe3\xba\xae\xa8l\xd3\x8e\xba\x17\xbf\x1b\x97\x1al\xa0B';

$shared_key = hex2bin('2b8fb5a3b09cebbba0caa227c1ce353d1522c9054744416990556a05a23a62db');
$symmetric_encryption_key = hex2bin('8ba5ec96baa47273acd1d7640634a276e3baaea86cd38eba17bf1b971a6ca042');
$encrypted_message = hex2bin('7daeaecc4db393adae5c9ea755f8abf849f0f4619329338b0724ea4afbe64c9f8ca2de9aa9e53602da96a75c5fadfff0839e3754d9338a96f3e04af13a1a85ebad264d43cec5469a8461a3d94b9b5a0e0a6b35d5d2e216b815173891bdab54c76fe704f54b031ab89af1c2880091236ee275cc5473165bee37e2e84a32ca3ee1f3d37ab5cc7a64ab040b8e5dff7842a36d79100f16d685413cef873e480a87fc75fa21745a91343150220817100cc18d8989305508ae3fd75ca87ffd87d419444f3443c347f42777fcbd18b45ce7b46c07977f74c79e05818c42a87f5edb1369f2943659dc86c80ad94d369eeba81e4851bb514062453545bd3afef48e662a7a7317375cde75b7a99f7dff0f40e22edecfc9032120032f71d740bb088ef9451013d363e86fb322d2acbaf6b9a7941ab951eba3e1039cc79ebb136de71b5b81e89010a02b86f47a83641d63c436acd9eee053826d94e135e2bbb3ba076445e047363e5d69b507029248be19e34eec41ffbe0a6c00d60cd7d05c00d29a13b82590b690e50527f2c732fdf1523f036d37921917c7811028aa319cba0cfc01765930a856f980');

echo "Symmetric encryption key (hex): " . bin2hex($symmetric_encryption_key) . "\n";
echo "Encrypted message (hex): " . bin2hex($encrypted_message) . "\n";

die($symmetric_encryption_key);

function aes_ctr_decrypt($encrypted_message, $symmetric_encryption_key, $nonce) {
    // Inicializa el contador usando el nonce
    $iv = $nonce . str_repeat("\0", 8);  // CTR usa un nonce más un contador (típicamente 8 bytes de contador)

    // Desencripta utilizando el algoritmo AES en modo CTR
    $decrypted_message = openssl_decrypt(
        $encrypted_message,
        'aes-256-ctr', // Cambia el 256 si tu clave es más corta (por ejemplo, aes-128-ctr)
        $symmetric_encryption_key,
        OPENSSL_RAW_DATA,
        $iv
    );

    return $decrypted_message;
}

// Parámetros de ejemplo
$nonce = random_bytes(8);  // El nonce debe tener 8 bytes para CTR

// Desencriptar el mensaje
$decrypted_message = aes_ctr_decrypt($encrypted_message, $symmetric_encryption_key, $nonce);
echo $decrypted_message;