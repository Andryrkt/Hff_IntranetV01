<?php

namespace App\Api\tik;

use App\Controller\Controller;
use App\Entity\admin\tik\TkiCategorie;
use App\Entity\admin\tik\TkiSousCategorie;
use Symfony\Component\Routing\Annotation\Route;

class CategorieApi extends Controller
{
    /**
     * @Route("/api/sous-categorie-fetch/{id}", name="sous_categorie_fetch")
     *
     * @return void
     */
    public function sousCategorie($id)
    {
        $categorie = $this->getEntityManager()->getRepository(TkiCategorie::class)->find($id);

        $sousCategorie = [];
        foreach ($categorie->getSousCategories() as $value) {
            $sousCategorie[] = [
                'value' => $value->getId(),
                'text' => $value->getDescription()
            ];
        }

        header("Content-type:application/json");

        echo json_encode($sousCategorie);
    }

    /**
     * @Route("/api/autres-categorie-fetch/{id}", name="autre_categorie_fetch")
     *
     * @return void
     */
    public function autresCategorie($id)
    {
        $sousCategorie = $this->getEntityManager()->getRepository(TkiSousCategorie::class)->find($id);

        $autreCategorie = [];
        foreach ($sousCategorie->getAutresCategories() as $value) {
            $autreCategorie[] = [
                'value' => $value->getId(),
                'text' => $value->getDescription()
            ];
        }

        header("Content-type:application/json");

        echo json_encode($autreCategorie);
    }
}
