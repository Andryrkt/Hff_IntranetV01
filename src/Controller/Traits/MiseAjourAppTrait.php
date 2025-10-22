<?php

namespace App\Controller\Traits;

use App\Entity\admin\Application;

trait MiseAjourAppTrait
{
    /**
     * Met à jour la dernière ID utilisée pour une application donnée.
     *
     * @param string $codeApp Le code de l'application à mettre à jour.
     * @param string $numero  La nouvelle valeur du champ `derniereId`.
     */
    public function mettreAJourDerniereIdApplication(string $codeApp, string $numero): void
    {
        $application = $this->getEntityManager->getRepository(Application::class)->findOneBy(['codeApp' => $codeApp]);

        if ($application === null) {
            throw new \RuntimeException("Aucune application trouvée pour le code : $codeApp");
        }

        $application->setDerniereId($numero);
        $this->getEntityManager->persist($application);
    }
}
