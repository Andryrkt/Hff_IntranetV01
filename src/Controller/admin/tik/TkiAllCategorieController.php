<?php


namespace App\Controller\admin\tik;

use App\Controller\Controller;
use App\Entity\admin\tik\TkiCategorie;
use App\Entity\admin\tik\TkiSousCategorie;
use App\Entity\admin\tik\TkiAutresCategorie;
use Symfony\Component\Routing\Annotation\Route;

class TkiAllCategorieController extends Controller
{
    /**
     * @Route("/admin/tki-tous-categorie-liste", name="tki_all_categorie_index")
     */
    public function index()
    {
        $dataCategorie      = $this->getEntityManager()->getRepository(TkiCategorie::class)->findBy([], ['id' => 'DESC']);
        $dataSousCategorie  = $this->getEntityManager()->getRepository(TkiSousCategorie::class)->findBy([], ['id' => 'DESC']);
        $dataAutreCategorie = $this->getEntityManager()->getRepository(TkiAutresCategorie::class)->findBy([], ['id' => 'DESC']);

        return $this->render(
            'admin/tik/tousCategorie/List.html.twig',
            [
                'dataCategorie'      => $dataCategorie,
                'dataSousCategorie'  => $dataSousCategorie,
                'dataAutreCategorie' => $dataAutreCategorie
            ]
        );
    }
}
