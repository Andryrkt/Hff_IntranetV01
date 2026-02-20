<?php

namespace App\Controller\da\reappro;

use DateTime;
use App\Controller\Controller;
use App\Entity\admin\Application;
use App\Service\GlobalVariablesService;
use App\Service\TableauEnStringService;
use Symfony\Component\Form\FormInterface;
use App\Service\Utils\RollingMonthsService;
use Symfony\Component\HttpFoundation\Request;
use App\Form\da\reappro\ReportingIpsSearchType;
use Symfony\Component\Routing\Annotation\Route;
use App\Controller\Traits\da\reappro\ReportingIpsTrait;

/**
 * @Route("/demande-appro")
 */
class ReportingIpsController extends Controller
{
    use ReportingIpsTrait;

    private RollingMonthsService $rollingMonthsService;

    public function __construct()
    {
        parent::__construct();

        $this->rollingMonthsService = new RollingMonthsService();
    }
    /**
     * @Route("/reporting-ips", name = "da_reporting_ips")
     */
    public function index(Request $request)
    {
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

        /** recuperation des données @var array $results @var array $totals  */
        ['results' => $results, 'totals' => $totals] = $this->getData($criterias);

        return $this->render('da/reappro/reporting_ips/index.html.twig', [
            'results' => $results,
            'totals' => $totals,
            'form' => $form->createView(),
        ]);
    }

    private function traitementFormulaire(FormInterface $form, Request $request): array
    {
        $form->handleRequest($request);

        $aujourdhui = new DateTime();

        $criterias = [
            'periodType' => 'PREVIOUS_12_MONTHS',
            'constructeur' => GlobalVariablesService::get('reappro'),
            'agences' => null,
            'services' => null,
            'agenceDebiteur' => "'01','02','20','30','40','50','60','80','90','91','92'",
            'serviceDebiteur' => null,
            'numFacture' => null,
            'description' => null,
            'date_debut' => $aujourdhui->modify('first day of january this year')->format('Y-m-d'), // date du premier janvier de l'année en cours
            'date_fin' => null, // date du jour
        ];

        if ($form->isSubmitted() && $form->isValid()) {
            // Données des champs mappés
            $criterias = $form->getData();

            if ($criterias['agences']) {
                $agences = [];
                foreach ($criterias['agences'] as $agence) {
                    $agences[] = $agence->getCodeAgence();
                }
                $agencesString = TableauEnStringService::orEnString($agences);
                $criterias['agenceDebiteur'] = $agencesString;
            }

            if ($criterias['services']) {
                $services = [];
                foreach ($criterias['services'] as $service) {
                    $services[] = $service->getCodeService();
                }
                $serviceString = TableauEnStringService::orEnString($services);
                $criterias['serviceDebiteur'] = $serviceString;
            }


            // Transformation du tableau des constructeurs en chaîne formatée
            if (isset($criterias['constructeur']) && is_array($criterias['constructeur'])) {
                $criterias['constructeur'] = TableauEnStringService::orEnString($criterias['constructeur']);
            } else {
                $criterias['constructeur'] = GlobalVariablesService::get('reappro');
            }

            // Récupération des données du champ composite 'date' non mappé
            $dateData = $form->get('date')->getData();

            $criterias['date_debut'] = $dateData['debut'] != null ? $dateData['debut']->format('Y-m-d') : $aujourdhui->modify('first day of january this year')->format('Y-m-d');
            $criterias['date_fin'] = $dateData['fin'] != null ? $dateData['fin']->format('Y-m-d') : $aujourdhui->format('Y-m-d');

            $criterias['periodType'] = 'PREVIOUS_12_MONTHS';
        }
        $this->getSessionService()->set('criterias_reporting_ips', $criterias);
        return $criterias;
    }
}
