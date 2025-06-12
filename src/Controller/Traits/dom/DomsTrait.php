<?php

namespace App\Controller\Traits\dom;

use DateTime;
use Exception;
use App\Entity\dom\Dom;
use App\Entity\admin\Agence;
use App\Entity\admin\dom\Rmq;
use App\Entity\admin\Service;
use App\Entity\admin\dom\Catg;
use App\Entity\admin\dom\Site;
use App\Entity\admin\Personnel;
use App\Entity\admin\Application;
use App\Entity\admin\dom\Indemnite;
use App\Entity\admin\StatutDemande;
use App\Repository\dom\DomRepository;
use App\Entity\admin\utilisateur\User;
use App\Entity\admin\AgenceServiceIrium;
use App\Entity\admin\dom\SousTypeDocument;
use App\Service\genererPdf\GeneratePdfDom;

trait DomsTrait
{
    public function initialisationSecondForm($form1Data, $em, $dom)
    {

        $agenceServiceEmetteur =  $this->agenceServiceIpsObjet();
        $dom->setMatricule($form1Data['matricule']);
        $dom->setSalarier($form1Data['salarier']);
        $dom->setSousTypeDocument($form1Data['sousTypeDocument']);
        $dom->setCategorie($form1Data['categorie']);
        $dom->setDateDemande(new \DateTime());
        if ($form1Data['salarier'] === "TEMPORAIRE") {
            $dom->setNom($form1Data['nom']);
            $dom->setPrenom($form1Data['prenom']);
            $dom->setCin($form1Data['cin']);

            $agenceEmetteur = $agenceServiceEmetteur['agenceIps']->getCodeAgence() . ' ' . $agenceServiceEmetteur['agenceIps']->getLibelleAgence();
            $serviceEmetteur = $agenceServiceEmetteur['serviceIps']->getCodeService() . ' ' . $agenceServiceEmetteur['serviceIps']->getLibelleService();
            $codeAgenceEmetteur = $agenceServiceEmetteur['agenceIps']->getCodeAgence();
            $codeServiceEmetteur = $agenceServiceEmetteur['serviceIps']->getCodeService();
        } else {

            $personnel = $em->getRepository(Personnel::class)->findOneBy(['Matricule' => $form1Data['matricule']]);
            $agenceServiceIrium = $em->getRepository(AgenceServiceIrium::class)->findOneBy(['service_sage_paie' => $personnel->getCodeAgenceServiceSage()]);

            $dom->setNom($personnel->getNom());
            $dom->setPrenom($personnel->getPrenoms());
            $agenceEmetteur = $agenceServiceIrium->getAgenceips() . ' ' . strtoupper($agenceServiceIrium->getNomagencei100());
            $serviceEmetteur = $agenceServiceIrium->getServiceips() . ' ' . $agenceServiceIrium->getLibelleserviceips();
            $codeAgenceEmetteur = $agenceServiceIrium->getAgenceips();
            $codeServiceEmetteur =  $agenceServiceIrium->getServiceips();
        }
        /** INITIALISATION AGENCE ET SERVICE Emetteur et Debiteur */
        $dom->setAgenceEmetteur($agenceEmetteur);
        $dom->setServiceEmetteur($serviceEmetteur);
        $idAgence = $em->getRepository(Agence::class)->findOneBy(['codeAgence' => $codeAgenceEmetteur])->getId();
        $dom->setAgence($em->getRepository(Agence::class)->find($idAgence));
        $dom->setService($em->getRepository(Service::class)->findOneBy(['codeService' => $codeServiceEmetteur]));

        //initialisation site

        $criteria = $this->criteria($form1Data, $em);
        $indemites = $em->getRepository(Indemnite::class)->findBy($criteria);
        $sites = [];
        foreach ($indemites as $key => $value) {
            $sites[] = $value->getSite()->getId();
        }
        if (in_array(8, $sites)) {
            $dom->setSite($em->getRepository(Site::class)->find(8));
        } else {
            $dom->setSite($em->getRepository(Site::class)->find(1));
        }

        $dom->setRmq($criteria['rmq']);

        $numTel = $em->getRepository(Dom::class)->findLastNumtel($form1Data['matricule']);
        $dom->setNumeroTel($numTel);
    }

    private function criteria($form1Data, $em)
    {
        $sousTypedocument = $form1Data['sousTypeDocument'];
        $catg = $form1Data['categorie'];

        $agenceServiceEmetteur =  $this->agenceServiceIpsObjet();

        if ($agenceServiceEmetteur['agenceIps']->getCodeAgence() == '50') {
            $rmq = $em->getRepository(Rmq::class)->findOneBy(['description' => '50']);
        } else {
            $rmq = $em->getRepository(Rmq::class)->findOneBy(['description' => 'STD']);
        }
        return  [
            'sousTypeDoc' => $sousTypedocument,
            'rmq' => $rmq,
            'categorie' => $catg
        ];
    }



    /**
     * Upload un fichier et retourne le chemin du fichier enregistré si c'est un PDF, sinon null.
     *
     * @param UploadedFile $file
     * @param DomEntity $dom
     * @param string $fieldName
     * @param int $index
     *
     * @return string|null
     *
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     */
    private function uploadFile($file, $dom, string $fieldName, int $index): ?string
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
            $dom->getNumeroOrdreMission(),
            substr($fieldName, -1),
            $file->guessExtension()
        );

        // Définir le répertoire de destination
        $destination = $_ENV['BASE_PATH_FICHIER'] . '/dom/fichier/';

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


    /**
     * Envoie des pièces jointes et fusionne les PDF.
     *
     * @param FormInterface $form
     * @param DomEntity $dom
     * @param FusionPdfService $fusionPdf
     *
     * @return void
     *
     * @throws \RuntimeException
     */
    private function envoiePieceJoint($form, $dom, $fusionPdf): void
    {
        $pdfFiles = [];

        // Ajouter le fichier PDF principal en tête du tableau
        $mainPdf = sprintf(
            '%s/dom/%s_%s%s.pdf',
            $_ENV['BASE_PATH_FICHIER'],
            $dom->getNumeroOrdreMission(),
            $dom->getAgenceEmetteurId()->getCodeAgence(),
            $dom->getServiceEmetteurId()->getCodeService()
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
                    // Extraire l'index du champ (e.g., pieceJoint1 -> 1)
                    if (preg_match('/^pieceJoint(\d+)$/', $fieldName, $matches)) {
                        $index = (int)$matches[1];
                        $pdfPath = $this->uploadFile($file, $dom, $fieldName, $index);
                        if ($pdfPath !== null) {
                            $pdfFiles[] = $pdfPath;
                        }
                    }
                }
            }
        }

        // Nom du fichier PDF fusionné
        $mergedPdfFile = $mainPdf;

        // Appeler la fonction pour fusionner les fichiers PDF
        if (!empty($pdfFiles)) {
            $this->ConvertirLesPdf($pdfFiles);
            $fusionPdf->mergePdfs($pdfFiles, $mergedPdfFile);
        }
    }

    private function ConvertirLesPdf(array $tousLesFichersAvecChemin)
    {
        $tousLesFichiers = [];
        foreach ($tousLesFichersAvecChemin as $filePath) {
            $tousLesFichiers[] = $this->convertPdfWithGhostscript($filePath);
        }


        return $tousLesFichiers;
    }

    private function convertPdfWithGhostscript($filePath)
    {
        $gsPath = 'C:\Program Files\gs\gs10.05.0\bin\gswin64c.exe'; // Modifier selon l'OS
        $tempFile = $filePath . "_temp.pdf";

        // Vérifier si le fichier existe et est accessible
        if (!file_exists($filePath)) {
            throw new Exception("Fichier introuvable : $filePath");
        }

        if (!is_readable($filePath)) {
            throw new Exception("Le fichier PDF ne peut pas être lu : $filePath");
        }

        // Commande Ghostscript
        $command = "\"$gsPath\" -sDEVICE=pdfwrite -dCompatibilityLevel=1.4 -o \"$tempFile\" \"$filePath\"";
        // echo "Commande exécutée : $command<br>";

        exec($command, $output, $returnVar);

        if ($returnVar !== 0) {
            echo "Sortie Ghostscript : " . implode("\n", $output);
            throw new Exception("Erreur lors de la conversion du PDF avec Ghostscript");
        }

        // Remplacement du fichier
        if (!rename($tempFile, $filePath)) {
            throw new Exception("Impossible de remplacer l'ancien fichier PDF.");
        }

        return $filePath;
    }

    private function enregistrementValeurdansDom($dom, $domForm, $form, $form1Data, $em, $user)
    {
        $statutDemande = $em->getRepository(StatutDemande::class)->find(1);
        if ($domForm->getModePayement() === 'MOBILE MONEY') {
            $mode = $form->get('mode')->getData();
            if (substr($form->get('mode')->getData(), 0, 4) === '+261') {
                $numTel = str_replace('+261', '0', $form->get('mode')->getData());
                $mode = str_replace('+261', '0', $form->get('mode')->getData());
            } else {
                $numTel = $form->get('mode')->getData();
                $mode = $form->get('mode')->getData();
            }
        } else if ($domForm->getModePayement() === 'VIREMENT BANCAIRE') {
            $mode = $form->get('mode')->getData();
            $numTel = '';
        } else {
            $mode = '';
            $numTel = '';
        }
        $agenceDebiteur = $domForm->getAgence();
        $serviceDebiteur = $domForm->getService();
        $agenceEmetteur = $em->getRepository(Agence::class)->findOneBy(['codeAgence' => substr($domForm->getAgenceEmetteur(), 0, 2)]);
        $serviceEmetteur = $em->getRepository(Service::class)->findOneBy(['codeService' => substr($domForm->getServiceEmetteur(), 0, 3)]);
        $supplementJournaliere = $form->get('supplementJournaliere')->getData();

        if ($form1Data['salarier'] === "TEMPORAIRE") {

            $dom->setNom($form1Data['nom']);
            $dom->setPrenom($form1Data['prenom']);
            $dom->setCin($form1Data['cin']);
        } else {
            $personnel = $em->getRepository(Personnel::class)->findOneBy(['Matricule' => $form1Data['matricule']]);
            $dom->setNom($personnel->getNom());
            $dom->setPrenom($personnel->getPrenoms());
        }

        $sousTypeDocument = $em->getRepository(SousTypeDocument::class)->find($form1Data['sousTypeDocument']->getId());
        if (isset($form1Data['categorie'])) {
            $categoryId = $em->getRepository(Catg::class)->find($form1Data['categorie']->getId());
        } else {
            $categoryId = null;
        }

        if ($form1Data['salarier'] === 'TEMPORAIRE') {
            $cin = $form1Data["cin"];
            $matricule = "XER00 -" . $cin . " - TEMPORAIRE";
        } else {
            $matricule = $form1Data['matricule'];
        }

        if ($form1Data['sousTypeDocument']->getCodeSousType() === 'FRAIS EXCEPTIONNEL') {
            $site = $em->getRepository(Site::class)->find(1);
        } else {
            $site = $domForm->getSite();
        }

        $dom
            ->setTypeDocument($form1Data['sousTypeDocument']->getCodeDocument())
            ->setSousTypeDocument($sousTypeDocument)
            ->setCategorie($categoryId)
            ->setMatricule($matricule)
            ->setUtilisateurCreation($user->getNomUtilisateur())
            ->setNomSessionUtilisateur($user->getNomUtilisateur())
            ->setNumeroOrdreMission($this->autoINcriment('DOM'))
            ->setIdStatutDemande($statutDemande)
            ->setCodeAgenceServiceDebiteur($agenceDebiteur->getCodeagence() . $serviceDebiteur->getCodeService())
            ->setModePayement($domForm->getModePayement() . ':' . $mode)
            ->setCodeStatut($statutDemande->getCodeStatut())
            ->setNumeroTel($numTel)
            ->setLibelleCodeAgenceService($agenceEmetteur->getLibelleAgence() . '-' . $serviceEmetteur->getLibelleService())
            ->setDroitIndemnite($supplementJournaliere)
            ->setAgenceEmetteurId($agenceEmetteur)
            ->setServiceEmetteurId($serviceEmetteur)
            ->setAgenceDebiteurId($agenceDebiteur)
            ->setServiceDebiteurId($serviceDebiteur)
            ->setCategoryId($categoryId)
            ->setSiteId($site)
            ->setHeureDebut($domForm->getHeureDebut()->format('H:i'))
            ->setHeureFin($domForm->getHeureFin()->format('H:i'))
            ->setEmetteur($domForm->getAgenceEmetteur() . '-' . $domForm->getServiceEmetteur())
            ->setDebiteur($domForm->getAgence()->getLibelleAgence() . '-' . $domForm->getService()->getLibelleService())
        ;
    }

    private function donnerPourPdf($dom, $domForm, $em, $user, $tropPercu)
    {
        if (explode(':', $dom->getModePayement())[0] === 'MOBILE MONEY' || explode(':', $dom->getModePayement())[0] === 'ESPECE') {
            $mode = 'TEL ' . explode(':', $dom->getModePayement())[1];
        } else if (explode(':', $dom->getModePayement())[0] === 'VIREMENT BANCAIRE') {
            $mode = 'CPT ' . explode(':', $dom->getModePayement())[1];
        } else {
            $mode = 'TEL ' . explode(':', $dom->getModePayement())[1];
        }

        $email = $em->getRepository(User::class)->findOneBy(['nom_utilisateur' => $user->getNomUtilisateur()])->getMail();

        $agenceEmetteur = $tropPercu ? $dom->getAgenceEmetteurId()->getCodeAgence() . ' ' . $dom->getAgenceEmetteurId()->getLibelleAgence() : $dom->getAgenceEmetteur();
        $serviceEmetteur = $tropPercu ? $dom->getServiceEmetteurId()->getCodeService() . ' ' . $dom->getServiceEmetteurId()->getLibelleService() : $dom->getServiceEmetteur();

        return  [
            "MailUser"              => $email,
            "dateS"                 => $dom->getDateDemande()->format("d/m/Y"),
            "NumDom"                => $dom->getNumeroOrdreMission(),
            "typMiss"               => $dom->getSousTypeDocument()->getCodeSousType(),
            "Site"                  => $dom->getSite() === null ? '' : ($tropPercu ? $dom->getSite() : $dom->getSite()->getNomZone()),
            "Code_serv"             => $agenceEmetteur,
            "serv"                  => $serviceEmetteur,
            "Nom"                   => $dom->getNom(),
            "Prenoms"               => $dom->getPrenom(),
            "matr"                  => $dom->getMatricule(),
            "motif"                 => $dom->getMotifDeplacement(),
            "CategoriePers"         => $dom->getCategorie() === null ? '' : ($tropPercu ? $dom->getCategorie() : $dom->getCategorie()->getDescription()),
            "NbJ"                   => $dom->getNombreJour(),
            "dateD"                 => $dom->getDateDebut()->format("d/m/Y"),
            "heureD"                => $dom->getHeureDebut(),
            "dateF"                 => $dom->getDateFin()->format("d/m/Y"),
            "heureF"                => $dom->getHeureFin(),
            "lieu"                  => $dom->getLieuIntervention(),
            "Client"                => $dom->getClient(),
            "fiche"                 => $dom->getFiche(),
            "vehicule"              => $dom->getVehiculeSociete(),
            "numvehicul"            => $dom->getNumVehicule(),
            "Devis"                 => $dom->getDevis(),
            "idemn"                 => $this->formatMontant($dom->getIndemniteForfaitaire()),
            "Bonus"                 => $this->formatMontant($dom->getDroitIndemnite()),
            "Idemn_depl"            => $this->formatMontant($dom->getIdemnityDepl()),
            "totalIdemn"            => $this->formatMontant($dom->getTotalIndemniteForfaitaire()),
            "motifdep01"            => $dom->getMotifAutresDepense1(),
            "motifdep02"            => $dom->getMotifAutresDepense2(),
            "motifdep03"            => $dom->getMotifAutresDepense3(),
            "montdep01"             => $this->formatMontant($dom->getAutresDepense1()),
            "montdep02"             => $this->formatMontant($dom->getAutresDepense2()),
            "montdep03"             => $this->formatMontant($dom->getAutresDepense3()),
            "totaldep"              => $this->formatMontant($dom->getTotalAutresDepenses()),
            "AllMontant"            => $this->formatMontant($dom->getTotalGeneralPayer()),
            "libmodepaie"           => explode(':', $dom->getModePayement())[0],
            "mode"                  => $mode,
            "codeAg_serv"           => substr($agenceEmetteur, 0, 2) . substr($serviceEmetteur, 0, 3),
            "codeServiceDebitteur"  => $dom->getAgence()->getCodeAgence(),
            "serviceDebitteur"      => $dom->getService()->getCodeService()
        ];
    }

    private function enregistreDernierNumDansApplication($dom, $em)
    {
        $application = $em->getRepository(Application::class)->findOneBy(['codeApp' => 'DOM']);
        $application->setDerniereId($dom->getNumeroOrdreMission());
        // Persister l'entité Application (modifie la colonne derniere_id dans le table applications)
        $em->persist($application);
        $em->flush();
    }

    public function recupAppEnvoiDbEtPdf($dom, $domForm, $form, $em, $fusionPdf, $user, $tropPercu = false)
    {
        //RECUPERATION de la dernière NumeroDordre de mission 
        $this->enregistreDernierNumDansApplication($dom, $em);

        //ENVOIE DES DONNEES DE FORMULAIRE DANS LA BASE DE DONNEE
        $em->persist($dom);
        $em->flush();

        //GENERER un PDF
        $tabInternePdf = $this->donnerPourPdf($dom, $domForm, $em, $user, $tropPercu);
        $genererPdfDom = new GeneratePdfDom();
        $genererPdfDom->genererPDF($tabInternePdf);
        //Fusion piece joint
        $this->envoiePieceJoint($form, $dom, $fusionPdf);
        //envoie vers DW
        $genererPdfDom->copyInterneToDOCUWARE($dom->getNumeroOrdreMission(), $dom->getAgenceEmetteurId()->getCodeAgence() . '' . $dom->getServiceEmetteurId()->getCodeService());
    }

    private function verifierSiDateExistant(string $matricule,  $dateDebutInput, $dateFinInput): bool
    {
        $Dates = $this->DomModel->getInfoDOMMatrSelet($matricule);

        if (empty($Dates)) {
            return false; // Pas de périodes dans la base
        }

        // Convertir les dates d'entrée si elles sont en chaînes
        $dateDebutInputObj = $dateDebutInput instanceof DateTime ? $dateDebutInput : new DateTime($dateDebutInput);
        $dateFinInputObj = $dateFinInput instanceof DateTime ? $dateFinInput : new DateTime($dateFinInput);

        foreach ($Dates as $periode) {
            // Convertir les dates en objets DateTime pour faciliter la comparaison
            $dateDebut = new DateTime($periode['Date_Debut']); //date dans la base de donner
            $dateFin = new DateTime($periode['Date_Fin']); //date dans la base de donner
            $dateDebutInputObj = $dateDebutInput; // date entrer par l'utilisateur
            $dateFinInputObj = $dateFinInput; // date entrer par l'utilisateur

            // Vérifier si la date à vérifier est comprise entre la date de début et la date de fin
            if (($dateFinInputObj >= $dateDebut && $dateFinInputObj <= $dateFin) || ($dateDebutInputObj >= $dateDebut && $dateDebutInputObj <= $dateFin) || ($dateDebutInputObj === $dateFin)) { // Correction des noms de variables
                $trouve = true;

                return $trouve;
            }
        }

        return false; // Pas de chevauchement
    }

    /**
     * Retourne une valeur monétaire valide.
     * Si la chaîne est vide, retourne "0", sinon retourne la valeur d'origine.
     *
     * @param string|null $montant La valeur monétaire sous forme de chaîne.
     * @return string La valeur monétaire, avec "0" par défaut si vide.
     */
    private function formatMontant(?string $montant = null): string
    {
        return $montant === null ? '0' : $montant;
    }

    private function initialisationFormTropPercu($em, Dom $dom, Dom $oldDom)
    {
        $sousTypeDocument = $em->getRepository(SousTypeDocument::class)->find(11);
        $userId = $this->sessionService->get('user_id');
        $user = $em->getRepository(User::class)->find($userId);
        $statutOuvert = $em->getRepository(StatutDemande::class)->find(1);
        $dom
            ->setSousTypeDocument($sousTypeDocument)
            ->setDateDemande(new DateTime)
            ->setIdStatutDemande($statutOuvert)
            ->setCodeStatut($statutOuvert->getCodeStatut())
            ->setUtilisateurCreation($user->getNomUtilisateur())
            ->setNomSessionUtilisateur($user->getNomUtilisateur())
            ->setNumeroOrdreMission($this->autoINcriment('DOM'))
            ->setTypeDocument($oldDom->getTypeDocument())
            ->setDebiteur($oldDom->getDebiteur())
            ->setEmetteur($oldDom->getEmetteur())
            ->setCodeAgenceServiceDebiteur($oldDom->getCodeAgenceServiceDebiteur())
            ->setAgenceDebiteurId($oldDom->getAgenceDebiteurId())
            ->setServiceDebiteurId($oldDom->getServiceDebiteurId())
            ->setAgenceEmetteurId($oldDom->getAgenceEmetteurId())
            ->setServiceEmetteurId($oldDom->getServiceEmetteurId())
            ->setCategorie($oldDom->getCategorie())
            ->setSite($oldDom->getSite())
            ->setMatricule($oldDom->getMatricule())
            ->setNom($oldDom->getNom())
            ->setPrenom($oldDom->getPrenom())
            ->setMotifDeplacement($oldDom->getMotifDeplacement())
            ->setClient($oldDom->getClient())
            ->setFiche($oldDom->getFiche())
            ->setLibelleCodeAgenceService($oldDom->getAgenceEmetteurId()->getLibelleAgence() . '-' . $oldDom->getServiceEmetteurId()->getLibelleService())
            ->setIndemniteForfaitaire($oldDom->getIndemniteForfaitaire())
            ->setLieuIntervention($oldDom->getLieuIntervention())
            ->setVehiculeSociete($oldDom->getVehiculeSociete())
            ->setNumVehicule($oldDom->getNumVehicule())
            ->setDevis($oldDom->getDevis())
            ->setIdemnityDepl($oldDom->getIdemnityDepl())
            ->setDroitIndemnite($oldDom->getDroitIndemnite())
            ->setSiteId($oldDom->getSiteId())
            ->setCategoryId($oldDom->getCategoryId())
        ;
    }
}
