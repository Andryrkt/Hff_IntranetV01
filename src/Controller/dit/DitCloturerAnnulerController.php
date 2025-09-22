<?php

namespace App\Controller\dit;

use App\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\docuware\CopyDocuwareService;
use App\Repository\admin\StatutDemandeRepository;
use App\Repository\dit\DitRepository;
use App\Entity\admin\StatutDemande;

/**
 * @Route("/atelier/demande-intervention")
 */
class DitCloturerAnnulerController extends Controller
{
    protected CopyDocuwareService $copyDocuwareService;

    public function __construct(CopyDocuwareService $copyDocuwareService)
    {
        parent::__construct();
        $this->copyDocuwareService = $copyDocuwareService;
    }

    /**
     * @Route("/cloturer-annuler/{id}", name="cloturer_annuler_dit_liste")
     */
    public function clotureStatut($id)
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        $ditRepository = $this->getEntityManager()->getRepository(\App\Entity\dit\DemandeIntervention::class);
        $dit = $ditRepository->find($id); // recupération de l'information du DIT à annuler

        $this->modificationTableDit($dit);

        $fileNameUplode = 'fichier_cloturer_annuler_' . $dit->getNumeroDemandeIntervention() . '.csv';
        $filePathUplode = $_ENV['BASE_PATH_FICHIER'] . '/dit/csv/' . $fileNameUplode;
        $fileNameDw = 'fichier_cloturer_annuler' . '.csv';
        // $filePathDw = $_ENV['BASE_PATH_FICHIER'] . '/dit/csv/' . $fileNameDw;
        $headers = ['numéro DIT', 'statut'];
        $numDits = $ditRepository->getNumDitAAnnuler();

        $data = [];
        foreach ($numDits as  $numDit) {
            $data[] = [
                $numDit,
                'Clôturé annulé'
            ];
        }

        if (file_exists($filePathUplode)) {
            unlink($filePathUplode);
        }

        $this->ajouterDansCsv($filePathUplode, $data, $headers);

        $this->copyDocuwareService->copyCsvToDw($fileNameDw, $filePathUplode);

        $message = "La DIT a été clôturé avec succès.";
        $this->notification($message);
        $this->redirectToRoute("dit_index");
    }

    private function modificationTableDit($dit)
    {
        $statutCloturerAnnuler = $this->getEntityManager()->getRepository(StatutDemande::class)->find(52);
        $dit
            ->setIdStatutDemande($statutCloturerAnnuler)
            ->setAAnnuler(true)
            ->setDateAnnulation(new \DateTime())
        ;
        $this->getEntityManager()->persist($dit);
        $this->getEntityManager()->flush();
    }

    private function ajouterDansCsv($filePath, $data, $headers = null)
    {
        $fichierExiste = file_exists($filePath);
        $handle = fopen($filePath, 'a');

        // Si le fichier est nouveau, ajoute un BOM UTF-8
        if (!$fichierExiste) {
            fwrite($handle, "\xEF\xBB\xBF"); // Ajout du BOM
        }

        // Fonction pour écrire une ligne sans guillemets
        $ecrireLigne = function ($ligne) use ($handle) {
            $ligneUtf8 = array_map(function ($field) {
                if (is_array($field)) {
                    // Tu peux choisir un séparateur ou une structure ici
                    $field = implode(';', $field);
                }
                return mb_convert_encoding($field, 'UTF-8');
            }, $ligne);
            fwrite($handle, implode(';', $ligneUtf8) . PHP_EOL); // tu peux changer ';' par ',' si nécessaire
        };
        // Écrit les en-têtes si le fichier est nouveau
        if (!$fichierExiste && $headers !== null) {
            $ecrireLigne($headers);
        }

        // Écrit les données sans guillemets
        foreach ($data as $ligne) {
            $ecrireLigne($ligne);
        }

        fclose($handle);
    }

    private function notification($message)
    {
        $this->getSessionService()->set('notification', ['type' => 'success', 'message' => $message]);
        $this->redirectToRoute("dit_index");
    }
}
