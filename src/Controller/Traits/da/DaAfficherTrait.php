<?php

namespace App\Controller\Traits\da;

use DateTime;
use DateTimeZone;
use App\Entity\da\DaAfficher;
use App\Entity\da\DemandeAppro;
use App\Entity\da\DemandeApproL;
use App\Entity\da\DemandeApproLR;
use App\Service\autres\VersionService;

trait DaAfficherTrait
{
    use DaTrait;

    /**
     * Ajoute les données d'une Demande d'Achat dans la table `DaAfficher`, 
     * par le numéro de la Demande d'Achat.
     *
     * ⚠️ IMPORTANT : Avant d'appeler cette fonction, il est impératif d'exécuter :
     *     $this->getEntityManager()->flush();
     * Sans cela, les données risquent de ne pas être cohérentes ou correctement persistées.
     *
     * @param string $numDa  le numéro de la Demande d'Achat à traiter
     * @param bool $validationDA  indique si l'ajout est effectué dans le cadre d'une validation de la DA
     * @param string $statutOr le statut depuis DW (statut OR pour une DA avec DIT)
     * 
     * @return void
     */
    public function ajouterDansTableAffichageParNumDa(string $numDa, bool $validationDA = false, string $statutOr = ''): void
    {
        $em = $this->getEntityManager();

        /** @var DemandeAppro $demandeAppro la DA correspondant au numero DA $numDa */
        $demandeAppro = $this->demandeApproRepository->findOneBy(['numeroDemandeAppro' => $numDa]);

        /** @var iterable<DaAfficher> $oldDaAffichers collection d'objets d'anciens DaAfficher */
        $oldDaAffichers = $this->daAfficherRepository->getLastDaAfficher($numDa);
        $oldDaAffichersByNumero = [];
        foreach ($oldDaAffichers as $old) {
            $oldDaAffichersByNumero[$old->getNumeroLigne()] = $old;
        }

        $numeroVersionMaxDaAfficher = !empty($oldDaAffichers) ? $oldDaAffichers[0]->getNumeroVersion() : 0;
        $numeroVersionMaxDAL = $this->demandeApproLRepository->getNumeroVersionMax($numDa);

        /** @var iterable<DaAfficher> $newDaAffichers collection d'objets des nouveaux DaAfficher */
        $newDaAffichers = $this->getLignesRectifieesDA($numDa, (int) $numeroVersionMaxDAL); // Récupère les lignes rectifiées de la DA (nouveaux Da afficher)

        $deletedLineNumbers = $this->getDeletedLineNumbers($oldDaAffichers, $newDaAffichers);
        $this->daAfficherRepository->markAsDeletedByNumeroLigne($numDa, $deletedLineNumbers, $this->getUserName(), $numeroVersionMaxDaAfficher);

        $dateValidation = new DateTime('now', new DateTimeZone('Indian/Antananarivo'));

        foreach ($newDaAffichers as $newDaAfficher) {
            $daAfficher = new DaAfficher();
            if (isset($oldDaAffichersByNumero[$newDaAfficher->getNumeroLigne()])) {
                $ancien = $oldDaAffichersByNumero[$newDaAfficher->getNumeroLigne()];
                $daAfficher->copyFromOld($ancien);
            }
            if ($demandeAppro->getDit()) $daAfficher->setDit($demandeAppro->getDit());

            $daAfficher->enregistrerDa($demandeAppro);
            $daAfficher->setNumeroVersion(VersionService::autoIncrement($numeroVersionMaxDaAfficher));

            if ($newDaAfficher instanceof DemandeApproL) $daAfficher->enregistrerDal($newDaAfficher); // enregistrement pour DAL
            else if ($newDaAfficher instanceof DemandeApproLR) $daAfficher->enregistrerDalr($newDaAfficher); // enregistrement pour DALR

            if ($validationDA) $daAfficher->setDateValidation($dateValidation);
            if ($statutOr) $daAfficher->setStatutOr($statutOr);

            $em->persist($daAfficher);
        }
        $em->flush();
    }
}
