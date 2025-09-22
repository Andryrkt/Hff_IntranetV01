<?php

namespace App\Controller\magasin\devis;

use App\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use App\Model\magasin\devis\ListeDevisMagasinModel;
use App\Factory\magasin\devis\ListeDevisMagasinFactory;
use App\Repository\magasin\devis\DevisMagasinRepository;
use App\Entity\magasin\devis\DevisMagasin;

class DevisMagasinExportExcelController extends Controller
{
    private ListeDevisMagasinModel $listeDevisMagasinModel;
    protected DevisMagasinRepository $devisMagasinRepository;
    public function __construct()
    {
        parent::__construct();
        $this->listeDevisMagasinModel = $this->getService(ListeDevisMagasinModel::class);
        $this->devisMagasinRepository = $this->getEntityManager()->getRepository(DevisMagasin::class);
    }

    /**
     * @Route("/devis-magasin-export-excel-list-devis-magasin", name="devis_magasin_export_excel_list_devis_magasin")
     *
     * @return void
     */
    public function exportExcel()
    {
        $this->verifierSessionUtilisateur();

        $criteria = $this->getSessionService()->get('criteria_for_excel_liste_devis_magasin');

        $listeDevisFactory = $this->recuperationDonner($criteria);

        $data = [];
        // En-tête du tableau d'excel
        $data[] = [
            "Statut DW",
            "Numéro devis",
            "Date de création",
            "Succursale + service émetteur",
            "Code client + libellé client",
            "Référence client",
            "Montant",
            "Opérateur",
            "Date d'envoi devis au client",
            "Statut IPS"
        ];

        $data = $this->convertirObjetEnTableau($listeDevisFactory, $data);

        $this->getExcelService()->createSpreadsheet($data);
    }

    /** 
     * Convertis les données d'objet en tableau
     * 
     * @param array $listeDevisFactory tableau d'objets à convertir
     * @param array $data tableau de retour
     * 
     * @return array
     */
    private function convertirObjetEnTableau(array $listeDevisFactory, array $data): array
    {
        /** @var ListeDevisMagasinFactory */
        foreach ($listeDevisFactory as $devisFactory) {
            $data[] = [
                $devisFactory->getStatutDw(),
                $devisFactory->getNumeroDevis(),
                $devisFactory->getDateCreation(),
                $devisFactory->getSuccursaleServiceEmetteur(),
                $devisFactory->getCodeClientLibelleClient(),
                $devisFactory->getReferenceCLient(),
                $devisFactory->getMontant(),
                $devisFactory->getOperateur(),
                $devisFactory->getDateDenvoiDevisAuClient(),
                $devisFactory->getStatutIps(),
            ];
        }

        return $data;
    }

    public function recuperationDonner(array $criteria = []): array
    {
        // recupération de la liste des devis magasin dans IPS
        $devisIps = $this->listeDevisMagasinModel->getDevis();

        $listeDevisFactory = [];
        foreach ($devisIps as  $devisIp) {
            //recupération des information de devis soumission à validation neg
            $numeroVersionMax = $this->devisMagasinRepository->getNumeroVersionMax($devisIp['numero_devis']);
            $devisSoumi = $this->devisMagasinRepository->findOneBy(['numeroDevis' => $devisIp['numero_devis'], 'numeroVersion' => $numeroVersionMax]);
            //ajout des informations manquantes
            $devisIp['statut_dw'] = $devisSoumi ? $devisSoumi->getStatutDw() : '';
            $devisIp['operateur'] = $devisSoumi ? $devisSoumi->getUtilisateur() : '';
            $devisIp['date_envoi_devis_au_client'] = $devisSoumi ? ($devisSoumi->getDateEnvoiDevisAuClient() ? $devisSoumi->getDateEnvoiDevisAuClient() : '') : '';


            //transformation par le factory
            $listeDevisFactory[] = (new ListeDevisMagasinFactory())->transformationEnObjet($devisIp);
        }

        return $listeDevisFactory;
    }
}
