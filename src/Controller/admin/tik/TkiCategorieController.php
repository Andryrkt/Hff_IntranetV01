<?php
namespace App\Controller\admin\tik;

use App\Controller\Controller;
use App\Entity\admin\tik\TkiCategorie;
use Symfony\Component\Routing\Annotation\Route;


class TkiCategorieController extends Controller
{
    /**
     * @Route("/tki-categorie-new", name="tki_categorie_new")
     */
    public function new()
    {
        $form = self::$validator->createBuilder(TkiCategorie::class)->getForm();
        self::$twig->display('admin/tki/new.html.twig', [
        'form' => $form->createView()
    ]);
    }
}
?>