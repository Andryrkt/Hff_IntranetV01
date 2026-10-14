<?php

namespace App\Controller\admin\historisation;

use App\Controller\Controller;
use App\Entity\admin\historisation\JournalCopieDocuware;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Journal des copies de fichiers vers DocuWare (accessible uniquement aux administrateurs HFF-ADMIN)
 */
class JournalCopieDocuwareController extends Controller
{
    /**
     * @Route("/admin/journal-copie-docuware", name="journal_copie_docuware_index")
     */
    public function index(Request $request)
    {
        $this->verifierAcces();

        $recherche      = $request->query->get('recherche');
        $echecSeulement = $request->query->getBoolean('echec');
        $dateDebut      = $this->creerDate($request->query->get('date_debut'));
        $dateFin        = $this->creerDate($request->query->get('date_fin'));

        $repository = $this->getEntityManager()->getRepository(JournalCopieDocuware::class);

        return $this->render('admin/historisation/journal-copie-docuware/index.html.twig', [
            'copies'         => $repository->rechercher($recherche, $echecSeulement, $dateDebut, $dateFin),
            'nbEchecsNonVus' => $repository->countEchecsNonVus(),
            'recherche'      => $recherche,
            'echecSeulement' => $echecSeulement,
            'dateDebut'      => $dateDebut,
            'dateFin'        => $dateFin,
        ]);
    }

    /**
     * @Route("/admin/journal-copie-docuware/echecs-vus", name="journal_copie_docuware_echecs_vus")
     */
    public function marquerEchecsVus()
    {
        $this->verifierAcces();

        $this->getEntityManager()->getRepository(JournalCopieDocuware::class)->marquerEchecsVus();

        $this->redirectToRoute('journal_copie_docuware_index', ['echec' => 1]);
    }

    private function creerDate(?string $date): ?\DateTime
    {
        if (!$date) return null;
        $dateTime = \DateTime::createFromFormat('Y-m-d', $date);
        return $dateTime ?: null;
    }

    /**
     * Redirige vers l'accueil si l'utilisateur connecté n'est pas administrateur (profil HFF-ADMIN)
     */
    private function verifierAcces(): void
    {
        if (!$this->estAdmin()) {
            $this->redirectToRoute('profil_acceuil');
        }
    }
}
