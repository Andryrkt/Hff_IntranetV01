<?php

namespace App\Controller\dit;

use App\Controller\Controller;
use App\Form\dit\CommentaireDitOrType;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/atelier/demande-intervention")
 */
class CommentaireDitOrController extends Controller
{
    /**
     * @Route("/commentaire-dit-or-new", name="commentaire_Dit_or_new")
     *
     * @return void
     */
    public function newCommentaire()
    {
        $form = $this->getFormFactory()->createBuilder(CommentaireDitOrType::class)->getForm();

        return $this->render('dit/newCommentaireDitOr.html.twig', [
            'form' => $form->createView()
        ]);
    }
}
