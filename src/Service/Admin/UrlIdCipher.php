<?php

namespace App\Service\Admin;

class UrlIdCipher
{
    private const CIPHER_METHOD = 'aes-256-gcm';
    private const NONCE_LENGTH = 12;
    private const TAG_LENGTH = 16;
    private const MIN_TOKEN_LENGTH = 38;
    private string $key;

    public function __construct()
    {
        // Clé secrète 32 octets, généré une fois avec `echo base64_encode(sodium_crypto_secretbox_keygen());`
        $key = base64_decode($_ENV['APP_URL_CIPHER_KEY'], true);

        if ($key === false || strlen($key) !== 32) throw new \RuntimeException('Clé invalide : attendu 32 octets en base64.');

        $this->key = $key;
    }

    /**
     * Chiffre un ID (ou toute chaîne courte) pour l'utiliser dans une URL.
     * Retourne une chaîne URL-safe (base64url).
     */
    public function encrypt(string $value, string $tag = ""): string
    {
        $nonce = random_bytes(self::NONCE_LENGTH);

        $cipher = openssl_encrypt(
            $value,
            self::CIPHER_METHOD,
            $this->key,
            OPENSSL_RAW_DATA,
            $nonce,
            $tag,
            '',
            self::TAG_LENGTH
        );

        if ($cipher === false) throw new \RuntimeException('Échec du chiffrement.');

        // nonce + tag + cipher
        $payload = $nonce . $tag . $cipher;

        return rtrim(strtr(base64_encode($payload), '+/', '-_'), '=');
    }

    /**
     * Déchiffre une valeur produite par encrypt().
     * Retourne null si la valeur est invalide, altérée, ou forgée.
     */
    public function decrypt(string $token): ?string
    {
        $base64 = strtr($token, '-_', '+/');
        $base64 .= str_repeat('=', (4 - \strlen($base64) % 4) % 4);

        $payload = base64_decode($base64, true);
        if ($payload === false || \strlen($payload) < self::NONCE_LENGTH + self::TAG_LENGTH) {
            return null;
        }

        $nonce  = substr($payload, 0, self::NONCE_LENGTH);
        $tag    = substr($payload, self::NONCE_LENGTH, self::TAG_LENGTH);
        $cipher = substr($payload, self::NONCE_LENGTH + self::TAG_LENGTH);

        $plain = openssl_decrypt(
            $cipher,
            self::CIPHER_METHOD,
            $this->key,
            OPENSSL_RAW_DATA,
            $nonce,
            $tag
        );

        return $plain === false ? null : $plain;
    }

    /** Raccourci pratique pour un ID entier avec validation stricte. */
    public function decryptInt(string $token): ?int
    {
        $plain = $this->decrypt($token);
        if ($plain === null || !ctype_digit($plain)) {
            return null;
        }
        return (int) $plain;
    }

    /** Vérifier qu'une chaîne a été chiffré à l'aide de cette classe */
    public static function isValid(string $token): bool
    {
        if (strlen($token) < self::MIN_TOKEN_LENGTH) return false;

        static $instance = null;

        if ($instance === null) $instance = new self();

        return $instance->decrypt($token) !== null;
    }
}
