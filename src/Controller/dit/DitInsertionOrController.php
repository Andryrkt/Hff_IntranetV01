<?php

namespace App\Controller\dit;

use App\Controller\Controller;
use App\Form\dit\DitInsertionOrType;
use Symfony\Component\Routing\Annotation\Route;

class DitInsertionOrController extends Controller
{
    /**
     * @Route("/insertion-or", name="dit_insertion_or")
     *
     * @return void
     */
    public function insertionOr()
    {
        $form = self::$validator->createBuilder(DitInsertionOrType::class)->getForm();

        self::$twig->display('dit/DitInsertionOr.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
