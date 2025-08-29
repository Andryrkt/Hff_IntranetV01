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
     *     self::$em->flush();
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
        $numeroVersionMaxDaAfficher = $this->daAfficherRepository->getNumeroVersionMax($numDa);
        $numeroVersionMax = $this->demandeApproLRepository->getNumeroVersionMax($numDa);
        $donneesAfficher = $this->getLignesRectifieesDA($numDa, $numeroVersionMax);
        foreach ($donneesAfficher as $donneeAfficher) {
            $daAfficher = new DaAfficher();
            if ($demandeAppro->getDit()) {
                $daAfficher->setDit($demandeAppro->getDit());
            }
            $daAfficher->enregistrerDa($demandeAppro);
            $daAfficher->setNumeroVersion(VersionService::autoIncrement($numeroVersionMaxDaAfficher));
            if ($donneeAfficher instanceof DemandeApproL) {
                $daAfficher->enregistrerDal($donneeAfficher); // enregistrement pour DAL
            } else if ($donneeAfficher instanceof DemandeApproLR) {
                $daAfficher->enregistrerDalr($donneeAfficher); // enregistrement pour DALR
            }

            $em->persist($daAfficher);
        }
        $em->flush();
    }
}
