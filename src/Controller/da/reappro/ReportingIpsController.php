<?php

namespace App\Controller\da\reappro;

use DateTime;
use App\Controller\Controller;
use App\Entity\admin\Application;
use App\Service\GlobalVariablesService;
use App\Service\TableauEnStringService;
use Symfony\Component\Form\FormInterface;
use App\Model\da\reappro\ReportingIpsModel;
use App\Controller\Traits\AutorisationTrait;
use Symfony\Component\HttpFoundation\Request;
use App\Form\da\reappro\ReportingIpsSearchType;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/demande-appro")
 */
class ReportingIpsController extends Controller
{
    use AutorisationTrait;

    /**
     * @Route("/reporting-ips", name = "da_reporting_ips")
     */
    public function index(Request $request)
    {
        // verification de la session utilisateur
        $this->verifierSessionUtilisateur();

        /** Autorisation accées */
        $this->autorisationAcces($this->getUser(), Application::ID_DAP);
        /** FIN AUtorisation acées */

        $form = $this->getFormFactory()->createBuilder(ReportingIpsSearchType::class, null, [
            'method' => 'GET',
            'em' => $this->getEntityManager()
        ])->getForm();

        if ($form === null) {
            throw new \RuntimeException('La création du formulaire a échoué : l\'objet Form est null.');
        }

        if (!$form instanceof FormInterface) {
            throw new \RuntimeException('L\'objet formulaire créé n\'est pas une instance de FormInterface.');
        }

        // traitement du formulaire
        $criterias = $this->traitementFormulaire($form, $request);

        /** recuperation des données @var array $reportingIps @var int $qteTotale @var float $montantTotal  */
        ['reportingIps' => $reportingIps, 'qteTotale' => $qteTotale, 'montantTotal' => $montantTotal] = $this->getData($criterias);

        return $this->render('da/reappro/reporting_ips/index.html.twig', [
            'reporting_ips' => $reportingIps,
            'qte_totale' => $qteTotale,
            'montant_total' => $montantTotal,
            'form' => $form->createView(),
        ]);
    }

    private function traitementFormulaire(FormInterface $form, Request $request): array
    {
        $form->handleRequest($request);
        $aujourdhui = new DateTime();
        $criterias = [
            'constructeur' => GlobalVariablesService::get('reappro'),
            'debiteur' => [
                'agence' => null,
                'service' => null
            ],
            'date_debut' => null, // date du premier mois précédent
            'date_fin' => null, // date du jour
        ];

        if ($form->isSubmitted() && $form->isValid()) {
            // Données des champs mappés
            $criterias = $form->getData();

            // Transformation du tableau des constructeurs en chaîne formatée
            if (isset($criterias['constructeur']) && is_array($criterias['constructeur'])) {
                $criterias['constructeur'] = TableauEnStringService::orEnString($criterias['constructeur']);
            } else {
                $criterias['constructeur'] = GlobalVariablesService::get('reappro');
            }

            // Récupération des données du champ composite 'date' non mappé
            $dateData = $form->get('date')->getData();
            $criterias['date_debut'] = $dateData['debut'] != null ? $dateData['debut']->format('Y-m-d') : null;
            $criterias['date_fin'] = $dateData['fin'] != null ? $dateData['fin']->format('Y-m-d') : $aujourdhui->format('Y-m-d');
        }

        return $criterias;
    }

    private function calculQteEtMontantTotals(array $reportingIps): array
    {
        $result = [
            'qte_totale' => 0,
            'montant_total' => 0
        ];
        foreach ($reportingIps as $item) {
            $result['qte_totale'] += $item['qte_demande'];
            $result['montant_total'] += $item['montant'];
        }
        return $result;
    }

    private function getData(array $criterias): array
    {
        $reportingIpsModel = new ReportingIpsModel();
        $reportingIps = $reportingIpsModel->getReportingData($criterias);

        ['qte_totale' => $qteTotale, 'montant_total' => $montantTotal] = $this->calculQteEtMontantTotals($reportingIps);

        return [
            'reportingIps' => $reportingIps,
            'qteTotale' => $qteTotale,
            'montantTotal' => $montantTotal
        ];
    }
}
