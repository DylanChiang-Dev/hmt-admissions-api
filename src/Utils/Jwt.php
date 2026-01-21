<?php

namespace App\Utils;

/**
 * Simple JWT implementation using native PHP
 * No external dependencies required
 */
class Jwt
{
    /**
     * Encode payload to JWT token
     */
    public static function encode(array $payload, string $secret): string
    {
        $header = [
            'typ' => 'JWT',
            'alg' => 'HS256'
        ];

        $headerEncoded = self::base64UrlEncode(json_encode($header));
        $payloadEncoded = self::base64UrlEncode(json_encode($payload));

        $signature = hash_hmac('sha256', "$headerEncoded.$payloadEncoded", $secret, true);
        $signatureEncoded = self::base64UrlEncode($signature);

        return "$headerEncoded.$payloadEncoded.$signatureEncoded";
    }

    /**
     * Decode and verify JWT token
     */
    public static function decode(string $token, string $secret): array
    {
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            throw new \Exception('Invalid token format');
        }

        list($headerEncoded, $payloadEncoded, $signatureEncoded) = $parts;

        // Verify signature
        $expectedSignature = hash_hmac('sha256', "$headerEncoded.$payloadEncoded", $secret, true);
        $expectedSignatureEncoded = self::base64UrlEncode($expectedSignature);

        if (!hash_equals($expectedSignatureEncoded, $signatureEncoded)) {
            throw new \Exception('Invalid token signature');
        }

        $payload = json_decode(self::base64UrlDecode($payloadEncoded), true);
        if (!$payload) {
            throw new \Exception('Invalid token payload');
        }

        // Check expiration
        if (isset($payload['exp']) && $payload['exp'] < time()) {
            throw new \Exception('Token expired');
        }

        return $payload;
    }

    private static function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private static function base64UrlDecode(string $data): string
    {
        return base64_decode(strtr($data, '-_', '+/'));
    }
}
