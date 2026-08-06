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
            ['method' => 'GET']
        )->getForm();

        $form->handleRequest($request);
        $dto = $form->getData() ?? new PlanningMagasinSearchDto();

        $data = $this->planningMagasinModel->getPlanningMagasin();
        $data = $this->filtrerDonnees($data, $dto);

        $uniqueMonths = $this->genererMoisAffiches($dto->months ?? 3);
        $preparedData = $this->preparerDonnees($data);

        return $this->render('magasin/planning/planning.html.twig', [
            'form'         => $form->createView(),
            'uniqueMonths' => $uniqueMonths,
            'preparedData' => $preparedData,
        ]);
    }

    /**
     * Filtre les commandes selon les critères saisis dans le formulaire de recherche.
     */
    private function filtrerDonnees(array $data, PlanningMagasinSearchDto $dto): array
    {
        $nomFournisseur = trim((string) $dto->nomFournisseur);
        $codeFournisseur = trim((string) $dto->codeFournisseur);
        $numeroCommande = trim((string) $dto->numeroCommande);

        if ($nomFournisseur === '' && $codeFournisseur === '' && $numeroCommande === '') {
            return $data;
        }

        return array_values(array_filter($data, function ($item) use ($nomFournisseur, $codeFournisseur, $numeroCommande) {
            // nomFournisseur / codeFournisseur viennent d'une liste déroulante (choix exact),
            // contrairement à numeroCommande qui reste une recherche texte libre.
            if ($nomFournisseur !== '' && strcasecmp(trim($item['nom_fournisseur']), $nomFournisseur) !== 0) {
                return false;
            }

            if ($codeFournisseur !== '' && trim((string) $item['numero_fournisseur']) !== $codeFournisseur) {
                return false;
            }

            if ($numeroCommande !== '' && stripos(trim((string) $item['numero_commande']), $numeroCommande) === false) {
                return false;
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
