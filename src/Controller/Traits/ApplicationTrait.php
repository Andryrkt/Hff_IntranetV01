<?php

namespace App\Controller\Traits;

use App\Entity\admin\Application;

trait ApplicationTrait
{
    use EntityManagerAwareTrait;

    /**
     * Met à jour la dernière ID utilisée pour une application donnée.
     *
     * @param string $codeApp Le code de l'application à mettre à jour.
     * @param int    $numero  La nouvelle valeur du champ `derniereId`.
     */
    private function mettreAJourDerniereIdApplication(string $codeApp, int $numero): void
    {
        $em = $this->getEntityManager();
        $application = $em->getRepository(Application::class)->findOneBy(['codeApp' => $codeApp]);

        if ($application === null) {
            throw new \RuntimeException("Aucune application trouvée pour le code : $codeApp");
        }

        $application->setDerniereId($numero);
        $em->persist($application);
    }
}
