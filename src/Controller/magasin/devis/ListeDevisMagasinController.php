<?php

namespace App\Controller\magasin\devis;

use App\Controller\Controller;
use App\Entity\admin\Application;
use App\Entity\magasin\devis\DevisMagasin;
use App\Controller\Traits\AutorisationTrait;
use Symfony\Component\Routing\Annotation\Route;
use App\Form\magasin\devis\DevisMagasinSearchType;
use App\Model\magasin\devis\ListeDevisMagasinModel;
use App\Factory\magasin\devis\ListeDevisMagasinFactory;
use App\Repository\magasin\devis\DevisMagasinRepository;
use App\Controller\BaseController;

/**
 * @Route("/magasin/dematerialisation")
 */
class ListeDevisMagasinController extends BaseController
{
    use AutorisationTrait;

    private ListeDevisMagasinModel $listeDevisMagasinModel;
    private DevisMagasinRepository $devisMagasinRepository;

    public function __construct()
    {
        parent::__construct();
        $this->listeDevisMagasinModel = new ListeDevisMagasinModel();
        $this->devisMagasinRepository = $this->getEntityManager()->getRepository(DevisMagasin::class);
    }

    /**
     * @Route("/liste-devis-magasin", name="devis_magasin_liste")
     */
    public function listeDevisMagasin()
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        /** Autorisation accées */
        $this->autorisationAcces($this->getUser(), Application::ID_DVM);

        //formulaire de recherhce
        $form = $this->getFormFactory()->createBuilder(DevisMagasinSearchType::class)->getForm();

        // recupération des données
        $listeDevisFactory = $this->recuperationDonner();

        // affichage de la liste des devis magasin
        return $this->render('magasin/devis/listeDevisMagasin.html.twig', [
            'listeDevis' => $listeDevisFactory,
            'form' => $form->createView()
        ]);
    }

    public function recuperationDonner(): array
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
