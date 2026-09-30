<?php

namespace App\Service\magasin\cdeFrn;

use App\Dto\Magasin\cdeFrn\CdeFrnSoumisAValidationDTO;
use Psr\Cache\CacheItemPoolInterface;

final class CdeFrnGenerationStore
{
    private const TTL = 7200;
    private CacheItemPoolInterface $cache;

    public function __construct(CacheItemPoolInterface $cache)
    {
        $this->cache = $cache;
    }

    public function save(string $userMail, CdeFrnSoumisAValidationDTO $dto): string
    {
        // Une seule génération active par utilisateur : on invalide la précédente
        $active = $this->cache->getItem($this->activeKey($userMail));
        if ($active->isHit()) {
            $this->cache->deleteItem($this->itemKey($userMail, $active->get()));
        }

        $token = bin2hex(random_bytes(16));

        $item = $this->cache->getItem($this->itemKey($userMail, $token));
        $item->set($dto)->expiresAfter(self::TTL);
        $this->cache->save($item);

        $active->set($token)->expiresAfter(self::TTL);
        $this->cache->save($active);

        return $token;
    }

    public function get(string $userMail, string $token): ?CdeFrnSoumisAValidationDTO
    {
        $item = $this->cache->getItem($this->itemKey($userMail, $token));

        return $item->isHit() ? $item->get() : null;
    }

    public function discard(string $userMail, string $token): void
    {
        $this->cache->deleteItem($this->itemKey($userMail, $token));
        $this->cache->deleteItem($this->activeKey($userMail));
    }

    // Les clés PSR-6 n'acceptent pas @ : / etc. → on hache l'identifiant utilisateur
    private function itemKey(string $userMail, string $token): string
    {
        return 'cde_frn_gen_' . hash('sha256', $userMail) . '_' . $token;
    }

    private function activeKey(string $userMail): string
    {
        return 'cde_frn_active_' . hash('sha256', $userMail);
    }
}
