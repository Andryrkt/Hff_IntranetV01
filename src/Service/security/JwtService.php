<?php

namespace App\Service\security;

class JwtService
{
    private string $secret;

    public function __construct()
    {
        // On récupère la clé secrète de l'environnement, sinon une clé par défaut
        $this->secret = $_ENV['JWT_SECRET'] ?? "VOTRE_CLE_SECRETE_TRES_LONGUE_ICI_123456"; 
    }

    public function encode(array $payload, int $expirationInSeconds = 3600): string
    {
        $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
        $payload['iat'] = time();
        $payload['exp'] = time() + $expirationInSeconds;

        $base64UrlHeader = $this->base64UrlEncode($header);
        $base64UrlPayload = $this->base64UrlEncode(json_encode($payload));

        $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, $this->secret, true);
        return $base64UrlHeader . "." . $base64UrlPayload . "." . $this->base64UrlEncode($signature);
    }

    public function decode(string $token): ?array
    {
        $parts = explode('.', $token);
        if (count($parts) !== 3) return null;

        list($header, $payload, $signature) = $parts;

        $expectedSignature = $this->base64UrlEncode(hash_hmac('sha256', $header . "." . $payload, $this->secret, true));

        if (!hash_equals($expectedSignature, $signature)) return null;

        $decodedPayload = json_decode($this->base64UrlDecode($payload), true);
        if (isset($decodedPayload['exp']) && $decodedPayload['exp'] < time()) return null;

        return $decodedPayload;
    }

    private function base64UrlEncode(string $data): string
    {
        return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($data));
    }

    private function base64UrlDecode(string $data): string
    {
        return base64_decode(str_replace(['-', '_'], ['+', '/'], $data . str_repeat('=', strlen($data) % 4)));
    }
}
