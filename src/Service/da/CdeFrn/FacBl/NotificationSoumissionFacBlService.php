<?php

namespace App\Service\da\CdeFrn\FacBl;

use App\Dto\Da\ListeCdeFrn\DaSoumissionFacBlDto;
use App\Entity\da\DaNotificationFacBl;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Enregistre une notification dans l'application à chaque soumission de facture / BL.
 * Les notifications sont consultables uniquement par l'utilisateur défini dans UTILISATEUR_AUTORISE.
 */
class NotificationSoumissionFacBlService
{
    public const UTILISATEUR_AUTORISE = 'lanto';

    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public static function estAutorise(?string $nomUtilisateur): bool
    {
        return strtolower(trim((string) $nomUtilisateur)) === self::UTILISATEUR_AUTORISE;
    }

    /**
     * Enregistre la notification. Une erreur est seulement journalisée pour ne pas bloquer la soumission.
     *
     * @param DaSoumissionFacBlDto $dto      dto de la soumission (après traitement)
     * @param string|null          $soumisPar nom de l'utilisateur qui a soumis
     */
    public function enregistrer(DaSoumissionFacBlDto $dto, ?string $soumisPar): void
    {
        try {
            $bilan              = $dto->bilanMajDaAfficher;
            $nbLignesMaj        = (int) ($bilan['nbLignesMaj'] ?? 0);
            $articlesNonTrouves = $bilan['articlesNonTrouves'] ?? [];

            $notification = (new DaNotificationFacBl())
                ->setNumeroDemandeAppro($dto->numeroDemandeAppro)
                ->setNumeroCde($dto->numeroCde)
                ->setNumeroLivraison(is_array($dto->numLiv) ? implode(', ', $dto->numLiv) : (string) $dto->numLiv)
                ->setNumeroOR($dto->numeroOR)
                ->setTypeSoumission($dto->typeDdp && $dto->typeDdp !== 'aucun' ? $dto->typeDdp : 'facbl')
                ->setMontant($dto->montantBlFacture)
                ->setSoumisPar($soumisPar)
                ->setNbArticlesLivres((int) ($bilan['nbArticlesLivres'] ?? 0))
                ->setNbLignesMaj($nbLignesMaj)
                ->setArticlesNonTrouves(empty($articlesNonTrouves) ? null : implode("\n", $articlesNonTrouves))
                ->setEstAnomalie($nbLignesMaj === 0 || !empty($articlesNonTrouves));

            $this->entityManager->persist($notification);
            $this->entityManager->flush();
        } catch (\Throwable $e) {
            error_log("[NotificationSoumissionFacBl] Echec de l'enregistrement de la notification : " . $e->getMessage());
        }
    }
}
