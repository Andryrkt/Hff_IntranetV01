<?php

namespace App\Controller\badm;

use App\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;

class BadmDupliController extends Controller
{
    private function rendreSeultableau(array $tabs): array
    {
        $tab = [];
        foreach ($tabs as $values) {
            foreach ($values as $value) {
                $tab[] = $value;
            }
        }
        return $tab;
    }

    /**
     * @Route("/dupliBADM/{numBadm}/{id}", name="BadmDupli_dupliBadm")
     */
    public function dupliBadm($numBadm, $id)
    {
        $this->SessionStart();
        $infoUserCours = $this->profilModel->getINfoAllUserCours($_SESSION['user']);
        $fichier = "../Hffintranet/Views/assets/AccessUserProfil_Param.txt";
        $text = file_get_contents($fichier);
        $boolean = strpos($text, $_SESSION['user']);
        // $NumBDM = $_GET['NumBDM'];
        // $id = $_GET['Id'];

        $badmDetailSqlServer = $this->badmDetail->DetailBadmModelAll($numBadm, $id);
        $badmDetailInformix = $this->badmDetail->findAll($badmDetailSqlServer[0]['ID_Materiel']);
        $agenceServiceEmetteur = $this->badmDetail->recupeAgenceServiceInformix($badmDetailSqlServer[0]['Agence_Service_Emetteur']);
        $agenceServiceDestinataire = $this->badmDetail->recupeAgenceServiceInformix($badmDetailSqlServer[0]['Agence_Service_Destinataire']);
        $agence = $this->badmDetail->recupAgence();
        $agenceDestinataire = $this->rendreSeultableau($agence);


        self::$twig->display(
            'badm/formCompleBadm.html.twig',
            [
                'codeMouvement' => $badmDetailSqlServer[0]['Description'],
                'infoUserCours' => $infoUserCours,
                'boolean' => $boolean,
                'Server' => $badmDetailSqlServer,
                'Informix' => $badmDetailInformix,
                'Emetteur' => $agenceServiceEmetteur,
                'Destinataire' => $agenceServiceDestinataire,
                'agenceDestinataire' => $agenceDestinataire,
                'numBdm' => $numBadm,
                'duplication' => true
            ]
        );
    }
}
