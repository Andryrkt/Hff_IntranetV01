<?php


namespace App\Controller\admin\historisation;

use App\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\admin\historisation\pageConsultation\UserLogger;

/**
 * Historique des consultations de pages (table Log_utilisateur) :
 * onglet « Tableau de bord » (statistiques de la période) et onglet « Consultations » (recherche).
 */
class PageConsultationController extends Controller
{
    private const LIMITES_POSSIBLES = [25, 50, 100, 200, 500];

    /**
     * @Route("/admin/consultation-page", name="consultation_page_index")
     */
    public function index(Request $request)
    {
        $vue = $request->query->get('vue') === 'tableau-de-bord' ? 'tableau-de-bord' : 'consultations';
        // période par défaut : 30 jours pour les statistiques, 7 jours pour la liste
        $dateDebut = $this->creerDate($request->query->get('date_debut')) ?? new \DateTime($vue === 'tableau-de-bord' ? '-29 days' : '-6 days');
        $dateFin   = $this->creerDate($request->query->get('date_fin')) ?? new \DateTime();

        /** @var \App\Repository\admin\historisation\pageConsultation\UserLoggerRepository $repository */
        $repository = $this->getEntityManager()->getRepository(UserLogger::class);
        $parametres = ['vue' => $vue, 'dateDebut' => $dateDebut, 'dateFin' => $dateFin];

        if ($vue === 'tableau-de-bord') {
            return $this->render('admin/historisation/consultation-page/tableau-de-bord.html.twig', $parametres + [
                'stats'     => $repository->tableauDeBord($dateDebut, $dateFin),
                'connectes' => $repository->connectes(),
            ]);
        }

        $filtres = [
            'recherche'   => trim((string) $request->query->get('recherche')),
            'utilisateur' => trim((string) $request->query->get('utilisateur')),
            'page'        => trim((string) $request->query->get('page')),
        ];
        // valeur imposée : la limite est injectée dans le TOP de la requête
        $limite = in_array($request->query->getInt('limite'), self::LIMITES_POSSIBLES, true) ? $request->query->getInt('limite') : 50;

        return $this->render('admin/historisation/consultation-page/index.html.twig', $parametres + [
            'filtres'          => $filtres,
            'limite'           => $limite,
            'limitesPossibles' => self::LIMITES_POSSIBLES,
            'resultat'         => $repository->rechercher($filtres, $dateDebut, $dateFin, $limite),
            'pages'            => $repository->nomsPages(),
        ]);
    }

    private function creerDate(?string $valeur): ?\DateTime
    {
        if (!$valeur) {
            return null;
        }
        $date = \DateTime::createFromFormat('!Y-m-d', $valeur);

        return $date ?: null;
    }

    /**
     * @Route("/admin/consultation-page/dashboard", name="consultation_page_dashboard")
     */
    public function dashboard()
    {
        return $this->redirectToRoute('consultation_page_index', ['vue' => 'tableau-de-bord']);
    }

    /**
     * @Route("/admin/consultation-page/detail", name="consultation_page_detail")
     */
    public function detail()
    {
        return $this->render('admin/historisation/consultation-page/detail.html.twig');
    }
}
