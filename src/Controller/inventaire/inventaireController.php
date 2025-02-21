<?php

namespace App\Controller\inventaire;

use App\Controller\Controller;
use App\Controller\Traits\Transformation;
use App\Model\inventaire\InventaireModel;
use App\Entity\inventaire\InventaireSearch;
use App\Form\inventaire\InventaireSearchType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class InventaireController extends Controller
{
    use Transformation;
    private InventaireModel $inventaireModel;
    private InventaireSearch $inventaireSearch;

    public function __construct()
    {
        parent::__construct();
        $this->inventaireModel = new InventaireModel();
        $this->inventaireSearch = new InventaireSearch();
    }

    /**
     * @Route("/inventaire", name = "liste_inventaire")
     * 
     * @return void
     */
    public function listeInventaire(Request $request)
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        $form = self::$validator->createBuilder(
            InventaireSearchType::class,
            $this->inventaireSearch,
            [
                'method' => 'GET'
            ]
        )->getForm();

        $form->handleRequest($request);
        //initialisation criteria
        $criteria = $this->inventaireSearch;

        if ($form->isSubmitted() && $form->isValid()) {
            // dd($form->getdata());
            $criteria =  $form->getdata();
        }

        $data  = [];
        if ($request->query->get('action') !== 'oui') {
            $data = $this->inventaireModel->listeInventaire($criteria);
            // dd($data);
        } 
        self::$twig->display('inventaire/inventaire.html.twig', [
            'form' => $form->createView(),
             'data' => $data
        ]);
    }
}
