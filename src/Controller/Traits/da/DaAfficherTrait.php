<?php

namespace App\Controller\Traits\da;

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
     * @return void
     */
    public function ajouterDansTableAffichageParNumDa(string $numDa): void
    {
        $em = $this->getEntityManager();

        /** @var DemandeAppro $demandeAppro la DA correspondant au numero DA $numDa */
        $demandeAppro = $this->demandeApproRepository->findOneBy(['numeroDemandeAppro' => $numDa]);

        $oldDaAffichers = $this->daAfficherRepository->getLastDaAfficher($numDa);
        $numeroVersionMaxDaAfficher = 0;

        if (!empty($oldDaAffichers) && isset($oldDaAffichers[0])) {
            $numeroVersionMaxDaAfficher = $oldDaAffichers[0]->getNumeroVersion();
        }

        $numeroVersionMax = $this->demandeApproLRepository->getNumeroVersionMax($numDa);
        $newDaAffichers = $this->getLignesRectifieesDA($numDa, $numeroVersionMax); // Récupère les lignes rectifiées de la DA (nouveaux Da afficher)

        $deletedLineNumbers = $this->getDeletedLineNumbers($oldDaAffichers, $newDaAffichers);
        $this->daAfficherRepository->markAsDeletedByNumeroLigne($numDa, $deletedLineNumbers, $this->getUserName());

        foreach ($newDaAffichers as $newDaAfficher) {
            $daAfficher = new DaAfficher();
            if ($demandeAppro->getDit()) {
                $daAfficher->setDit($demandeAppro->getDit());
            }
            $daAfficher->enregistrerDa($demandeAppro);
            $daAfficher->setNumeroVersion(VersionService::autoIncrement($numeroVersionMaxDaAfficher));
            if ($newDaAfficher instanceof DemandeApproL) {
                $daAfficher->enregistrerDal($newDaAfficher); // enregistrement pour DAL
            } else if ($newDaAfficher instanceof DemandeApproLR) {
                $daAfficher->enregistrerDalr($newDaAfficher); // enregistrement pour DALR
            }

            $em->persist($daAfficher);
        }
        $em->flush();
    }
}
