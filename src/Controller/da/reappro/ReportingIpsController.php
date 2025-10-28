<?php

namespace App\Controller\da\reappro;

use App\Controller\Controller;
use App\Entity\admin\Application;
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

        $form = $this->getFormFactory()->createBuilder(ReportingIpsSearchType::class, null ,[
            'method' => 'GET',
        ])->getForm();

        if ($form === null) {
            throw new \RuntimeException('La création du formulaire a échoué : l\'objet Form est null.');
        }

        if (!$form instanceof \Symfony\Component\Form\FormInterface) {
            throw new \RuntimeException('L\'objet formulaire créé n\'est pas une instance de FormInterface.');
        }

        $form->handleRequest($request);

        /** recuperation des données @var array $reportingIps @var int $qteTotale @var float $montantTotal  */
        ['reportingIps' => $reportingIps, 'qteTotale' => $qteTotale, 'montantTotal' => $montantTotal] = $this->getData();

        return $this->render('da/reappro/reporting_ips/index.html.twig', [
            'reporting_ips' => $reportingIps,
            'qte_totale' => $qteTotale,
            'montant_total' => $montantTotal,
            'form' => $form->createView(),
        ]);
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

    private function getData(): array
    {
        $reportingIpsModel = new ReportingIpsModel();
        $reportingIps = $reportingIpsModel->getReportingData();

        ['qte_totale' => $qteTotale, 'montant_total' => $montantTotal] = $this->calculQteEtMontantTotals($reportingIps);

        return [
            'reportingIps' => $reportingIps,
            'qteTotale' => $qteTotale,
            'montantTotal' => $montantTotal
        ];
    }
}
