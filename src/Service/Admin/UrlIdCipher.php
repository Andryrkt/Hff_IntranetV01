<?php

namespace App\Service\Admin;

use App\Constants\da\RouteConstant;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class UrlIdCipher
{
    private const CIPHER_METHOD = 'aes-256-gcm';
    private const NONCE_LENGTH = 12;
    private const TAG_LENGTH = 16;
    private const MIN_TOKEN_LENGTH = 38;
    private string $key;

    private array $slugMap = [
        RouteConstant::SLUG_LISTE_DA      => [
            'path_name'  => RouteConstant::PATH_NAME_LISTE_DA,
            'title_page' => 'Liste des demandes d’achats'
        ],
        RouteConstant::SLUG_LISTE_CDE_FRN => [
            'path_name'  => RouteConstant::PATH_NAME_LISTE_CDE_FRN,
            'title_page' => 'Liste des commandes fournisseurs'
        ]
    ];

    public function __construct()
    {
        // Clé secrète 32 octets, généré une fois avec `echo base64_encode(random_bytes(32));`
        $key = base64_decode($_ENV['APP_URL_CIPHER_KEY'] ?? "", true);

        if (empty($key)) throw new \RuntimeException('Clé inexistante dans le fichier `.env` (APP_URL_CIPHER_KEY)');
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

    /** 
     * Résout le slug pour les liens des listes de la vignette APPRO 
     * 
     * @param ?string $slug 
     * @param UrlGeneratorInterface $urlGenerator 
     * 
     * @return array{"url":string,"title":string}
     **/
    public function resolveSlugDemandeAppro(?string $slug, UrlGeneratorInterface $urlGenerator): array
    {
        $config = $this->slugMap[$slug] ?? $this->slugMap[RouteConstant::SLUG_LISTE_DA];

        $url = $urlGenerator->generate($config["path_name"]);

        return ["url" => $url, "title" => $config["title_page"]];
    }
}
