<?php

use Symfony\Component\HttpFoundation\Response;

namespace App\Controller\admin\tik;

use App\Controller\Controller;
use App\Entity\admin\tik\TkiAutresCategorie;
use App\Entity\admin\tik\TkiCategorie;
use App\Entity\admin\tik\TkiSousCategorie;
use Symfony\Component\Routing\Annotation\Route;
use App\Controller\BaseController;


class TkiAllCategorieController extends BaseController
{
    /**
     * @Route("/admin/tki-tous-categorie-liste", name="tki_all_categorie_index")
     */
    public function index()
    {
        $dataCategorie      = $this->getEntityManager()->getRepository(TkiCategorie::class)->findBy([], ['id' => 'DESC']);
        $dataSousCategorie  = $this->getEntityManager()->getRepository(TkiSousCategorie::class)->findBy([], ['id' => 'DESC']);
        $dataAutreCategorie = $this->getEntityManager()->getRepository(TkiAutresCategorie::class)->findBy([], ['id' => 'DESC']);

        return new \Symfony\Component\HttpFoundation\Response($this->getTwig()->render(
            'admin/tik/tousCategorie/List.html.twig',
            [
                'dataCategorie'      => $dataCategorie,
                'dataSousCategorie'  => $dataSousCategorie,
                'dataAutreCategorie' => $dataAutreCategorie
            ]
        ));
    }
}
