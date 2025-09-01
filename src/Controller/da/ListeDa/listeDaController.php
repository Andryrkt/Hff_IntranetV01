<?php

namespace App\Controller\da\ListeDa;

use App\Form\da\DaSearchType;
use App\Controller\Controller;
use App\Entity\admin\Application;
use App\Controller\Traits\da\DaListeTrait;
use App\Controller\Traits\da\StatutBcTrait;
use App\Controller\Traits\AutorisationTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/demande-appro")
 */
class listeDaController extends Controller
{
    use DaListeTrait;
    use StatutBcTrait;
    use AutorisationTrait;

    public function __construct()
    {
        parent::__construct();
        $this->setEntityManager(self::$em);
        $this->initDaListeTrait(self::$generator);
    }

    /**
     * @Route("/da-list", name="list_da")
     */
    public function index(Request $request)
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        /** Autorisation accès */
        $this->autorisationAcces($this->getUser(), Application::ID_DAP);
        /** FIN AUtorisation accès */

        //formulaire de recherche
        $form = self::$validator->createBuilder(DaSearchType::class, null, ['method' => 'GET'])->getForm();
        $form->handleRequest($request);

        $criteria = [];
        if ($form->isSubmitted() && $form->isValid()) {
            $criteria = $form->getData();
        }
        $this->sessionService->set('criteria_for_excel', $criteria);

        // Donnée à envoyer à la vue
        $data = $this->getData($criteria);
        $dataPrepared = $this->prepareDataForDisplay($data);
        self::$twig->display('da/list-da.html.twig', [
            'data' => $dataPrepared,
            'form' => $form->createView(),
        ]);
    }
}
