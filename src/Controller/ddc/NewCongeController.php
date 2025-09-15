<?php

namespace App\Controller\ddc;

use App\Controller\Controller;
use App\Entity\admin\Application;
use App\Controller\Traits\AutorisationTrait;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/rh/demande-de-conge")
 */
class NewCongeController extends Controller
{
    use AutorisationTrait;

    /**
     * @Route("/nouveau-conge", name="new_conge")
     */
    public function nouveauConge()
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        /** Autorisation accès */
        $this->autorisationAcces($this->getUser(), Application::ID_DDC);
        /** FIN AUtorisation accès */

        // ⚠️ URL distante (celle que tu veux masquer)
        $url = "https://hffc.docuware.cloud/docuware/formsweb/demande-de-conges-new?orgID=5adf2517-2f77-4e19-8b42-9c3da43af7be";

        // Récupération du contenu distant (sans HttpClient)
        $context = stream_context_create([
            "http" => [
                "header" => "User-Agent: Symfony-Proxy\r\n"
            ]
        ]);

        $content = @file_get_contents($url, false, $context);

        if ($content === false) {
            $content = "<p>Impossible de charger le contenu distant.</p>";
        }

        // Tu passes ça dans Twig (par ex. pour l'afficher dans un <div>)
        return $this->render('ddc/conge_new.html.twig', [
            'content' => $content,
        ]);
    }
}
