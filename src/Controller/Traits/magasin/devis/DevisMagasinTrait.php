<?php

namespace App\Controller\Traits\magasin\devis;

use App\Service\autres\VersionService;
use Symfony\Component\Form\FormInterface;
use App\Entity\magasin\devis\DevisMagasin;
use Symfony\Component\HttpFoundation\File\UploadedFile;

trait DevisMagasinTrait
{
    /**
     * Récupère les informations du devis dans IPS
     * 
     * @param string $numeroDevis Le numéro de devis
     * @return array Les informations du devis
     */
    public function getInfoDevisIps(string $numeroDevis): array
    {
        $devisIps = $this->listeDevisMagasinModel->getInfoDev($numeroDevis);

        if (empty($devisIps)) {
            //message d'erreur
            $message = "Aucune information trouvé dans IPS pour le devis numero : " . $numeroDevis;
            $this->historiqueOperationDeviMagasinService->sendNotificationSoumission($message, $numeroDevis, 'devis_magasin_liste', false);
        }

        return reset($devisIps);
    }

    /**
     * Récupère les nouveaux nombres de lignes et le nouveau montant total du devis
     * 
     * @param array $firstDevisIps Les informations du devis
     * @return array [$newSumOfLines, $newSumOfMontant]
     */
    public function newSumOfLinesAndAmount(array $firstDevisIps): array
    {
        $newSumOfLines = (int)$firstDevisIps['somme_numero_lignes'];
        $newSumOfMontant = (float)$firstDevisIps['montant_total'];
        return [$newSumOfLines, $newSumOfMontant];
    }


    private function ajoutInfoIpsDansDevisMagasin(DevisMagasin $devisMagasin, array $firstDevisIps, string $numeroVersion, string $nomFichier, string $typeSoumission): void
    {
        $suffixConstructeur = $this->listeDevisMagasinModel->constructeurPieceMagasin($devisMagasin->getNumeroDevis());

        // est validation pm
        $cosntructeur = false;
        if ($devisMagasin->constructeur === 'TOUS NEST PAS CAT') {
            $cosntructeur = true;
        } elseif ($devisMagasin->constructeur === 'TOUT CAT' && $devisMagasin->getEstValidationPm() == true) {
            $cosntructeur = true;
        }

        // tache validateur
        $tacheValidateur = null;
        if ($typeSoumission == 'VP') {

            if ($devisMagasin->getEstValidationPm() == false) {
                $tacheValidateur = 'AUTOVALIDATION';
            } else {
                $tacheValidateur = $devisMagasin->getTacheValidateur();
            }
        }

        $devisMagasin
            ->setNumeroDevis($devisMagasin->getNumeroDevis())
            ->setMontantDevis($firstDevisIps['montant_total'])
            ->setDevise($firstDevisIps['devise'])
            ->setSommeNumeroLignes($firstDevisIps['somme_numero_lignes'])
            ->setUtilisateur($this->getUser()->getNomUtilisateur())
            ->setNumeroVersion(VersionService::autoIncrement($numeroVersion))
            ->setStatutDw($typeSoumission == 'VP' ? DevisMagasin::STATUT_PRIX_A_CONFIRMER : DevisMagasin::STATUT_A_VALIDER_CHEF_AGENCE)
            ->setTypeSoumission($typeSoumission)
            ->setCat($suffixConstructeur === 'C' || $suffixConstructeur === 'CP' ? true : false)
            ->setNonCat($suffixConstructeur === 'P' || $suffixConstructeur === 'CP' ? true : false)
            ->setNomFichier((string)$nomFichier)
            ->setTacheValidateur($tacheValidateur)
            ->setEstValidationPm($cosntructeur)
        ;
    }

    private function enregistrementFichier(FormInterface $form, string $numDevis, int $numeroVersion, string $suffix, string $mail, string $typeDevis): array
    {
        $devisPath = $this->cheminBaseUpload . $numDevis . '/';
        if (!is_dir($devisPath)) {
            mkdir($devisPath, 0777, true);
        }

        // generer le nom des fichiers
        $nomEtCheminFichiersEnregistrer = $this->uploader->getNomsEtCheminFichiers($form, [
            'repertoire' => $devisPath,
            'generer_nom_callback' => function (
                UploadedFile $file,
                int $index
            ) use ($numDevis, $numeroVersion, $suffix, $mail, $typeDevis) {
                if ($typeDevis === 'VP') {
                    return $this->nameGenerator->generateVerificationPrixName($file, $numDevis, $numeroVersion, $suffix, $mail, $index);
                } else {

                    return $this->nameGenerator->generateValidationDevisName($file, $numDevis, $numeroVersion, $suffix, $mail, $index);
                }
            }
        ]);

        dd($nomEtCheminFichiersEnregistrer);

        if (empty($nomEtCheminFichiersEnregistrer)) {

            // copier le fichier par defaut dans le bon dossier
            $remoteUrl = $_ENV['BASE_PATH_FICHIER'] . "/verif prix/RECAP BAP.pdf";
            $localPath = $devisPath . "DEVIS MAGASIN_{$numDevis}_000_000.pdf";
            copy($remoteUrl, $localPath);

            // ajouter le nom de fichier par defaut
            $nomEtCheminFichiersEnregistrer[] = $localPath;
        }


        $nomAvecCheminFichier = $this->nameGenerator->getCheminEtNomDeFichierSansIndex($nomEtCheminFichiersEnregistrer[0]);
        $nomFichier = $this->nameGenerator->getNomFichier($nomAvecCheminFichier);

        return [$nomEtCheminFichiersEnregistrer, $nomAvecCheminFichier, $nomFichier];
    }
}
