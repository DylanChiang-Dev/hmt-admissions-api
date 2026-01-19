<?php
namespace HmtAdmissions\Api\Utils;

use Firebase\JWT\JWT as FirebaseJWT;
use Firebase\JWT\Key;

class Jwt {
    // Fixed: Secret key length increased to > 32 bytes (256 bits) for HS256
    private static $secret = 'hmt-admissions-secret-key-2025-must-be-very-long-and-secure';
    private static $algo = 'HS256';

    public static function encode(array $payload) {
        return FirebaseJWT::encode($payload, self::$secret, self::$algo);
    }

    public static function decode(string $token) {
        return FirebaseJWT::decode($token, new Key(self::$secret, self::$algo));
    }
}
