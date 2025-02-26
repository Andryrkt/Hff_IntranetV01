<?php

namespace App\Controller\Traits;

use App\Entity\admin\Application;
use App\Entity\admin\dom\SousTypeDocument;
use App\Entity\admin\StatutDemande;
use App\Entity\admin\utilisateur\User;
use App\Entity\mutation\Mutation;
use App\Service\genererPdf\GeneratePdfMutation;
use DateTime;

trait MutationTrait
{
    private function initialisationMutation(Mutation $mutation, $em)
    {
        $agenceServiceIps = $this->agenceServiceIpsObjet();

        $mutation
            ->setDateDemande(new DateTime())
            ->setDevis('MGA')
            ->setAgenceEmetteur($agenceServiceIps['agenceIps'])
            ->setServiceEmetteur($agenceServiceIps['serviceIps'])
            ->setSousTypeDocument($em->getRepository(SousTypeDocument::class)->find(5)) // Sous-type document MUTATION
            ->setTypeDocument($mutation->getSousTypeDocument()->getCodeDocument())
        ;
    }

    private function enregistrementValeurDansMutation($form, $em, $user)
    {
        /** 
         * @var Mutation $mutation entité correspondant aux données du formulaire
         */
        $mutation = $form->getData();

        if ($form->get('modePaiementLabel')->getData() === "MOBILE MONEY") {
            $mutation->setNumeroTel($form->get('modePaiementValue')->getData());
        }

        $statutDemande = $em->getRepository(StatutDemande::class)->find(66); // A VALIDER SERVICE EMETTEUR (OUVERT)

        $mutation
            ->setNumeroMutation($this->autoINcriment('MUT'))
            ->setLibelleCodeAgenceService($mutation->getAgenceEmetteur()->getLibelleAgence() . '-' . $mutation->getServiceEmetteur()->getLibelleService())
            ->setModePaiement($form->get('modePaiementLabel')->getData() . ':' . $form->get('modePaiementValue')->getData())
            ->setStatutDemande($statutDemande)
            ->setCodeStatut($statutDemande->getCodeStatut())
            ->setUtilisateurCreation($user->getNomUtilisateur())
        ;

        $application = $em->getRepository(Application::class)->findOneBy(['codeApp' => 'MUT']);
        $application->setDerniereId($mutation->getNumeroMutation());

        $em->persist($application);
        $em->persist($mutation);
        $em->flush();
    }

    private function genererEtEnvoyerPdf($form, $user)
    {
        $generatePdf = new GeneratePdfMutation;
        $generatePdf->genererPDF($this->donneePourPdf($form, $user));
    }

    private function donneePourPdf($form, User $user): array
    {
        /** 
         * @var Mutation $mutation entité correspondant aux données du formulaire
         */
        $mutation = $form->getData();
        $tab = [
            'MailUser'              => $user->getMail(),
            'dateS'                 => $mutation->getDateDemande()->format('d/m/Y'),
            "NumMut"                => $mutation->getNumeroMutation(),
            "Nom"                   => $mutation->getNom(),
            "Prenoms"               => $mutation->getPrenom(),
            "matr"                  => $mutation->getMatricule(),
            "CategoriePers"         => $mutation->getCategorie() === null ? '' : $mutation->getCategorie()->getDescription(),
            "agenceOrigine"         => $mutation->getAgenceEmetteur()->getLibelleAgence(),
            "serviceOrigine"        => $mutation->getServiceEmetteur()->getLibelleService(),
            "dateAffectation"       => $mutation->getDateDebut()->format('d/m/Y'),
            "lieuAffectation"       => $mutation->getLieuMutation(),
            "motif"                 => $mutation->getMotifMutation(),
            "agenceDestination"     => $mutation->getAgenceDebiteur()->getLibelleAgence(),
            "serviceDestination"    => $mutation->getServiceDebiteur()->getLibelleService(),
            "client"                => $mutation->getClient(),
            "avanceSurIndemnite"    => $form->get('avanceSurIndemnite')->getData(),
            "NbJ"                   => $mutation->getNombreJourAvance(),
            "indemnite"             => '',
            "supplement"            => '',
            "totalIndemnite"        => '',
            "motifdep01"            => '',
            "montdep01"             => '',
            "motifdep02"            => '',
            "montdep02"             => '',
            "totaldep"              => '',
            "totalGeneral"          => '',
            "libModPaie"            => '',
            "valModPaie"            => '',
            "mode"                  => 'TEL',
            "codeAg_serv"           => $mutation->getAgenceEmetteur()->getCodeAgence() . $mutation->getServiceEmetteur()->getCodeService()
        ];
        if ($tab['avanceSurIndemnite'] === 'OUI') {
            $devis = $mutation->getDevis();
            $tab['indemnite'] = $mutation->getIndemniteForfaitaire() . ' ' . $devis . ' / jour';
            if ($form->get('supplementJournaliere')->getData() !== null) {
                $tab['supplement'] = $form->get('supplementJournaliere')->getData() . ' ' . $devis . ' / jour';
            }
            $tab['totalIndemnite'] = $mutation->getTotalIndemniteForfaitaire() . ' ' . $devis;
            if ($mutation->getTotalAutresDepenses() !== null) {
                $tab['totaldep'] = $mutation->getTotalAutresDepenses() . ' ' . $devis;
            }
            if ($mutation->getMotifAutresDepense1() !== null) {
                $tab['motifdep01'] = $mutation->getMotifAutresDepense1();
            }
            if ($mutation->getMotifAutresDepense2() !== null) {
                $tab['motifdep02'] = $mutation->getMotifAutresDepense2();
            }
            $tab['totalGeneral'] = $mutation->getTotalGeneralPayer();
            $tab['libModPaie'] = $form->get('modePaiementLabel')->getData();
            $tab['valModPaie'] = $form->get('modePaiementValue')->getData();
            if ($tab['libModPaie'] !== 'MOBILE MONEY') {
                $tab['mode'] = 'CPT';
            }
        }
        return $tab;
    }
}
