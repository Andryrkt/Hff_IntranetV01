<?php

namespace App\Controller\admin\tik;

use App\Controller\Controller;
use App\Entity\admin\tik\TkiAutresCategorie;
use App\Entity\admin\tik\TkiCategorie;
use App\Entity\admin\tik\TkiSousCategorie;
use Symfony\Component\Routing\Annotation\Route;


class TkiAllCategorieController extends Controller
{
    /**
     * @Route("/admin/tki-tous-categorie-liste", name="tki_all_categorie_index")
     */
    public function index()
    {
        $dataCategorie      = self::$em->getRepository(TkiCategorie::class)->findBy([], ['id' => 'DESC']);
        $dataSousCategorie  = self::$em->getRepository(TkiSousCategorie::class)->findBy([], ['id' => 'DESC']);
        $dataAutreCategorie = self::$em->getRepository(TkiAutresCategorie::class)->findBy([], ['id' => 'DESC']);

        self::$twig->display(
            'admin/tik/tousCategorie/List.html.twig',
            [
                'dataCategorie'      => $dataCategorie,
                'dataSousCategorie'  => $dataSousCategorie,
                'dataAutreCategorie' => $dataAutreCategorie
            ]
        );
    }
}
