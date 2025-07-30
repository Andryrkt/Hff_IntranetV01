<?php

namespace App\Controller\Traits;

use App\Entity\admin\Agence;
use App\Entity\admin\Application;
use App\Entity\admin\dom\SousTypeDocument;
use App\Entity\admin\Service;
use App\Entity\admin\StatutDemande;
use App\Entity\admin\utilisateur\User;
use App\Entity\mutation\Mutation;
use DateTime;
use Symfony\Component\HttpFoundation\File\UploadedFile;

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

        if ($mutation->getPieceJoint01() !== null) {
            $mutation->setPieceJoint01($mutation->getNumeroMutation() . '_01.pdf');
        }
        if ($mutation->getPieceJoint02() !== null) {
            $mutation->setPieceJoint02($mutation->getNumeroMutation() . '_02.pdf');
        }

        $application = $em->getRepository(Application::class)->findOneBy(['codeApp' => 'MUT']);
        $application->setDerniereId($mutation->getNumeroMutation());

        $em->persist($application);
        $em->persist($mutation);
        $em->flush();

        return $mutation;
    }

    private function donneePourPdf($form, User $user): array
    {
        /** 
         * @var Mutation $mutation entité correspondant aux données du formulaire
         */
        $mutation = $form->getData();
        $devis = $mutation->getDevis();

        $formatDate = fn(DateTime $date) => $date->format('d/m/Y');
        $withDevis = fn($value) => $value !== null ? $value . ' ' . $devis : '';

        $rental = $mutation->getAgenceDebiteur()->getCodeAgence() === '50';

        $tab = [
            'MailUser'              => $user->getMail(),
            'dateS'                 => $formatDate($mutation->getDateDemande()),
            "NumMut"                => $mutation->getNumeroMutation(),
            "Nom"                   => $mutation->getNom(),
            "Prenoms"               => $mutation->getPrenom(),
            "matr"                  => $mutation->getMatricule(),
            "CategoriePers"         => $mutation->getCategorie() === null ? '' : $mutation->getCategorie()->getDescription(),
            "agenceOrigine"         => $this->formatAgence($mutation->getAgenceEmetteur()),
            "serviceOrigine"        => $this->formatService($mutation->getServiceEmetteur()),
            "dateAffectation"       => $mutation->getDateDebut()->format('d/m/Y'),
            "lieuAffectation"       => $mutation->getLieuMutation(),
            "motif"                 => $mutation->getMotifMutation(),
            "agenceDestination"     => $this->formatAgence($mutation->getAgenceDebiteur()),
            "serviceDestination"    => $this->formatService($mutation->getServiceDebiteur()),
            "client"                => $mutation->getClient(),
            "avanceSurIndemnite"    => $form->get('avanceSurIndemnite')->getData(),
            "NbJ"                   => '',
            "indemnite"             => '',
            "supplement"            => '',
            "totalIndemnite"        => '',
            "motifdep01"            => $mutation->getMotifAutresDepense1() ?? '',
            "montdep01"             => $withDevis($mutation->getAutresDepense1()),
            "motifdep02"            => $mutation->getMotifAutresDepense2() ?? '',
            "montdep02"             => $withDevis($mutation->getAutresDepense2()),
            "totaldep"              => $withDevis($mutation->getTotalAutresDepenses()),
            "totalGeneral"          => $withDevis($mutation->getTotalGeneralPayer() ?? 0),
            "libModPaie"            => $form->get('modePaiementLabel')->getData(),
            "valModPaie"            => $form->get('modePaiementValue')->getData(),
            "mode"                  => $form->get('modePaiementLabel')->getData() === 'MOBILE MONEY' ? 'TEL' : 'CPT',
            "message"               => $rental ? 'Les avances sur indemnité seront retirées du salaire à la prochaine paie.' : 'Les frais d\'installation sont à la charge de l\'entreprise.',
            "codeAg_serv"           => $mutation->getAgenceEmetteur()->getCodeAgence() . $mutation->getServiceEmetteur()->getCodeService()
        ];
        if ($tab['avanceSurIndemnite'] === 'OUI') {
            $tab['NbJ'] = $mutation->getNombreJourAvance() ?? '';
            $tab['indemnite'] = $withDevis($mutation->getIndemniteForfaitaire()) . ' / jour';
            $tab['totalIndemnite'] = $withDevis($mutation->getTotalIndemniteForfaitaire());

            if ($form->get('supplementJournaliere')->getData() !== null) {
                $tab['supplement'] = $form->get('supplementJournaliere')->getData() . ' ' . $devis . ' / jour';
            }
        }
        return $tab;
    }


    private function formatAgence(Agence $agence): string
    {
        return $agence->getCodeAgence() . ' - ' . $agence->getLibelleAgence();
    }

    private function formatService(Service $service): string
    {
        return $service->getCodeService() . ' - ' . $service->getLibelleService();
    }

    private function envoyerPieceJointes($form, $fusionPdf)
    {
        $pdfFiles = [];

        $mutation = $form->getData();

        // Ajouter le fichier PDF principal en tête du tableau
        $mainPdf = sprintf(
            '%s/Upload/mut/%s_%s%s.pdf',
            rtrim($_SERVER['DOCUMENT_ROOT'], '/'),
            $mutation->getNumeroMutation(),
            $mutation->getAgenceEmetteur()->getCodeAgence(),
            $mutation->getServiceEmetteur()->getCodeService()
        );

        // Vérifier que le fichier principal existe avant de l'ajouter
        if (!file_exists($mainPdf)) {
            throw new \RuntimeException('Le fichier PDF principal n\'existe pas.');
        }

        array_unshift($pdfFiles, $mainPdf);

        // Récupérer tous les champs de fichiers du formulaire
        foreach ($form->all() as $fieldName => $field) {
            if (preg_match('/^pieceJoint\d+$/', $fieldName)) {
                /** @var UploadedFile|null $file */
                $file = $field->getData();
                if ($file !== null) {
                    $pdfPath = $this->uploadFile($file, $mutation, $fieldName);
                    if ($pdfPath !== null) {
                        $pdfFiles[] = $pdfPath;
                    }
                }
            }
        }

        // Nom du fichier PDF fusionné
        $mergedPdfFile = $mainPdf;

        // Appeler la fonction pour fusionner les fichiers PDF
        if (!empty($pdfFiles)) {
            $fusionPdf->mergePdfs($pdfFiles, $mergedPdfFile);
        }
    }

    /**
     * Upload un fichier et retourne le chemin du fichier enregistré si c'est un PDF, sinon null.
     *
     * @param UploadedFile $file
     * @param Mutation $mutation
     * @param string $fieldName
     *
     * @return string|null
     *
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     */
    private function uploadFile($file, $mutation, $fieldName)
    {
        // Récupérer l'extension et le type MIME
        $extension = strtolower($file->getClientOriginalExtension());
        $mimeType = strtolower($file->getMimeType());

        // Pour déboguer, enregistrer les valeurs
        error_log("Uploading file for field $fieldName: Extension = $extension, MIME type = $mimeType");

        // Validation des extensions et types MIME - uniquement PDF
        $allowedExtensions = ['pdf'];
        $allowedMimeTypes = ['application/pdf'];

        if (
            !$file->isValid() ||
            !in_array($extension, $allowedExtensions, true) ||
            !in_array($mimeType, $allowedMimeTypes, true)
        ) {
            throw new \InvalidArgumentException("Type de fichier non autorisé pour le champ $fieldName. Extension: $extension, MIME type: $mimeType");
        }

        // Générer un nom de fichier sécurisé et unique
        $fileName = sprintf(
            '%s_0%d.%s',
            $mutation->getNumeroMutation(),
            substr($fieldName, -1),
            $file->guessExtension()
        );

        // Définir le répertoire de destination
        $destination = $_ENV['BASE_PATH_FICHIER'] . '/mut/fichier/';

        // Assurer que le répertoire existe
        if (!is_dir($destination) && !mkdir($destination, 0755, true) && !is_dir($destination)) {
            throw new \RuntimeException(sprintf('Le répertoire "%s" n\'a pas pu être créé.', $destination));
        }

        try {
            $file->move($destination, $fileName);
        } catch (\Exception $e) {
            throw new \RuntimeException('Erreur lors de l\'upload du fichier : ' . $e->getMessage());
        }

        // Retourner le chemin complet du fichier si c'est un PDF, sinon null
        if ($extension === 'pdf') {
            return $destination . $fileName;
        }

        return null;
    }
}
