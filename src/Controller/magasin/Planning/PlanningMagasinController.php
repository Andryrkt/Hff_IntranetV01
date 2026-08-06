<?php

namespace App\Controller\magasin\planning;

use App\Controller\Controller;
use App\Controller\Traits\PlanningTraits;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Model\magasin\planning\PlanningMagasinModel;
use App\Form\magasin\Planning\PlanningMagasinSearchType;
use App\Dto\Magasin\Planning\PlanningMagasinSearchDto;

/**
 * @Route("/magasin/planning-commande-fournisseur")
 */
class PlanningMagasinController extends Controller
{
    use PlanningTraits;

    private PlanningMagasinModel $planningMagasinModel;

    public function __construct()
    {
        parent::__construct();
        $this->planningMagasinModel = new PlanningMagasinModel();
    }

    /**
     * @Route("", name = "interface_planning_cde_frn_magasin")
     */
    public function headPlanning(Request $request)
    {
        $form = $this->getFormFactory()->createNamedBuilder(
            'planning_magasin_frn_search',
            PlanningMagasinSearchType::class,
            new PlanningMagasinSearchDto(),
            [
                'method' => 'GET',
                'em'     => $this->getEntityManager(),
            ]
        )->getForm();

        $form->handleRequest($request);
        $dto = $form->getData() ?? new PlanningMagasinSearchDto();

        $condition = $request->query->get('condition', 'tous');

        $data = $this->planningMagasinModel->getPlanningMagasin();
        $data = $this->filtrerDonnees($data, $dto);
        $data = $this->filtrerParStatut($data, $condition);

        $uniqueMonths = $this->genererMoisAffiches($dto->months ?? 3);
        $preparedData = $this->preparerDonnees($data);

        return $this->render('magasin/planning/planning.html.twig', [
            'form'         => $form->createView(),
            'uniqueMonths' => $uniqueMonths,
            'preparedData' => $preparedData,
            'condition'    => $condition,
            'currentQuery' => $request->query->all(),
        ]);
    }

    /**
     * Filtre selon la légende cliquée (TOUT AFFICHER / statut). "back_order" ne peut pas
     * encore être détecté par PlanningMagasinModel::getPlanningMagasin() (pas de flag
     * back order/error dans les données) : il ne renverra donc aucune commande pour l'instant.
     */
    private function filtrerParStatut(array $data, string $condition): array
    {
        $statutParCondition = [
            'partiel_facture'     => 'Partiellement facturé',
            'partiel_dispo'       => 'Partiellement dispo',
            'complet_non_facture' => 'Complet non facturé',
            'complet_facture'     => 'Complet facturé',
        ];

        if (!isset($statutParCondition[$condition]) && $condition !== 'back_order') {
            return $data;
        }

        $statutAttendu = $statutParCondition[$condition] ?? null;

        return array_values(array_filter($data, function ($item) use ($statutAttendu) {
            return $statutAttendu !== null && $this->normaliserStatut($item['statut']) === $statutAttendu;
        }));
    }

    /**
     * Filtre les commandes selon les critères saisis dans le formulaire de recherche.
     */
    private function filtrerDonnees(array $data, PlanningMagasinSearchDto $dto): array
    {
        $fournisseur = trim((string) $dto->fournisseur);
        $numeroCommande = trim((string) $dto->numeroCommande);
        $agence = $dto->agenceService['agence'] ?? null;
        $service = $dto->agenceService['service'] ?? null;
        $dateDebut = $dto->dateCommande['debut'] ?? null;
        $dateFin = $dto->dateCommande['fin'] ?? null;

        if ($fournisseur === '' && $numeroCommande === '' && !$agence && !$service && !$dateDebut && !$dateFin) {
            return $data;
        }

        return array_values(array_filter($data, function ($item) use ($fournisseur, $numeroCommande, $agence, $service, $dateDebut, $dateFin) {
            // Un seul champ pour chercher par nom OU par code fournisseur.
            if ($fournisseur !== '') {
                $matchNom = stripos(trim($item['nom_fournisseur']), $fournisseur) !== false;
                $matchCode = stripos(trim((string) $item['numero_fournisseur']), $fournisseur) !== false;

                if (!$matchNom && !$matchCode) {
                    return false;
                }
            }

            if ($numeroCommande !== '' && stripos(trim((string) $item['numero_commande']), $numeroCommande) === false) {
                return false;
            }

            if ($agence && trim((string) $item['code_agence']) !== $agence->getCodeAgence()) {
                return false;
            }

            if ($service && trim((string) $item['code_service']) !== $service->getCodeService()) {
                return false;
            }

            if ($dateDebut || $dateFin) {
                $timestamp = strtotime($item['date_commande']);
                if ($timestamp === false) {
                    return false;
                }

                $dateCommande = date('Y-m-d', $timestamp);

                if ($dateDebut && $dateCommande < $dateDebut->format('Y-m-d')) {
                    return false;
                }

                if ($dateFin && $dateCommande > $dateFin->format('Y-m-d')) {
                    return false;
                }
            }

            return true;
        }));
    }

    /**
     * Génère la fenêtre de mois affichée dans l'entête du tableau : toujours 12 mois,
     * alignés selon la période choisie dans le formulaire (form.months).
     */
    private function genererMoisAffiches(int $selectedOption): array
    {
        $moisLabels = ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Jun', 'Jul', 'Aoû', 'Sep', 'Oct', 'Nov', 'Déc'];
        $currentMonth = (int) date('n') - 1;
        $currentYear = (int) date('Y');
        $currentKey = sprintf('%04d-%02d', $currentYear, $currentMonth + 1);

        $selectedMonths = $this->getSelectedMonths($moisLabels, $currentMonth, $currentYear, $selectedOption);

        return array_map(function ($mois) use ($currentKey) {
            $mois['current'] = $mois['key'] === $currentKey;
            return $mois;
        }, $selectedMonths);
    }

    /**
     * Regroupe les commandes par fournisseur / agence-service et les répartit par mois.
     */
    private function preparerDonnees(array $data): array
    {
        $grouped = [];

        foreach ($data as $item) {
            $cle = $item['numero_fournisseur'] . '|' . $item['agence_service'];

            if (!isset($grouped[$cle])) {
                $grouped[$cle] = [
                    'fournisseur'   => trim($item['nom_fournisseur']),
                    'agenceService' => trim($item['agence_service']),
                    'codeFourn'     => $item['numero_fournisseur'],
                    'commandes'     => [],
                ];
            }

            $timestamp = strtotime($item['date_commande']);
            if ($timestamp === false) {
                continue;
            }

            $moisCle = date('Y-m', $timestamp);

            $grouped[$cle]['commandes'][$moisCle][] = [
                'numero' => $item['numero_commande'],
                'statut' => $this->normaliserStatut($item['statut']),
            ];
        }

        return array_values($grouped);
    }

    /**
     * Corrige le mojibake produit par DatabaseInformix::convertToUtf8() : cette méthode
     * teste 'ISO-8859-1' avant 'UTF-8' pour deviner l'encodage, et comme le Latin-1 accepte
     * n'importe quel octet, une chaîne déjà en UTF-8 (ex: "facturé") est ré-encodée comme si
     * elle était en Latin-1, produisant "facturÃ©". On annule ce ré-encodage ici.
     */
    private function normaliserStatut(string $statut): string
    {
        $statut = trim($statut);

        if (strpos($statut, 'Ã') === false && strpos($statut, 'Â') === false) {
            return $statut;
        }

        $repare = @mb_convert_encoding($statut, 'ISO-8859-1', 'UTF-8');
        if ($repare !== false && mb_check_encoding($repare, 'UTF-8') && $repare !== $statut) {
            return $repare;
        }

        return $statut;
    }
}
