<?php

namespace App\Controller\magasin\devis;

use App\Controller\Controller;
use App\Entity\admin\Application;
use App\Entity\magasin\devis\DevisMagasin;
use App\Controller\Traits\AutorisationTrait;
use App\Form\magasin\devis\DevisMagasinType;
use Symfony\Component\Routing\Annotation\Route;
use App\Model\magasin\devis\ListeDevisMagasinModel;

/**
 * @Route("/magasin/dematerialisation")
 */
class DevisMagasinController extends Controller
{
    private const TYPE_SOUMISSION_VERIFICATION_PRIX = 'VP';
    private const STATUT_SOUMISSION_A_VALIDATION = 'Soumis à validation';

    use AutorisationTrait;

    private ListeDevisMagasinModel $listeDevisMagasinModel;

    public function __construct()
    {
        parent::__construct();
        $this->listeDevisMagasinModel = new ListeDevisMagasinModel();
    }

    /**
     * @Route("/soumission-devis-magasin/{numeroDevis}", name="devis_magasion_soumission")
     */
    public function soumission(string $numeroDevis = '')
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        /** Autorisation accées */
        $this->autorisationAcces($this->getUser(), Application::ID_DVM);

        //instancier le devis magasin
        $devisMagasin = new DevisMagasin();
        $devisMagasin->setNumeroDevis($numeroDevis);

        //création du formulaire
        $form = self::$validator->createBuilder(DevisMagasinType::class, $devisMagasin)->getForm();

        //traitement du formualire
        $this->traitementFormualire($form, $devisMagasin);

        //affichage du formulaire
        self::$twig->display('magasin/devis/soumission.html.twig', [
            'form' => $form->createView()
        ]);
    }

    private function traitementFormualire($form, DevisMagasin $devisMagasin)
    {
        $form->handleRequest(self::$request);
        if ($form->isSubmitted() && $form->isValid()) {

            $suffixConstructeur = $this->listeDevisMagasinModel->constructeurPieceMagasin($devisMagasin->getNumeroDevis());
            //recupération des informations utile dans IPS
            $devisIps = $this->listeDevisMagasinModel->getInfoDev($devisMagasin->getNumeroDevis());

            if (!empty($devisIps)) {
                // recupération de numero version max
                $numeroVersion = self::$em->getRepository(DevisMagasin::class)->getNumeroVersionMax($devisMagasin->getNumeroDevis());

                //TODO : ajout des informations de IPS et des informations manuel comment nombre de lignes, cat, nonCatdans le devis magasin
                    $devisMagasin
                        ->setMontantDevis($devisIps[0]['montant_total'])
                        ->setDevise($devisIps[0]['devise'])
                        ->setSommeNumeroLignes($devisIps[0]['somme_numero_lignes'])
                        ->setUtilisateur($this->getUser()->getNomUtilisateur())
                        ->setNumeroVersion($this->autoIncrement($numeroVersion))
                        ->setStatutDw(self::STATUT_SOUMISSION_A_VALIDATION)
                        ->setTypeSoumission(self::TYPE_SOUMISSION_VERIFICATION_PRIX)
                        ->setCat($suffixConstructeur === 'C' || $suffixConstructeur === 'CP' ? true : false)
                        ->setNonCat($suffixConstructeur === 'P' || $suffixConstructeur === 'CP' ? true : false)
                    ;
                    self::$em->persist($devisMagasin);
                
            }

            //enregistrement du devis magasin
            self::$em->flush();

            //redirection vers la liste des devis magasin
            $this->redirectToRoute('devis_magasin_liste');
        }
    }

    private function autoIncrement(?int $num)
    {
        if ($num === null || $num === 0) {
            $num = 0;
        }
        return (int) $num + 1;
    }
}
