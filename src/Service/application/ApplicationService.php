<?php

namespace App\Service\application;

use App\Entity\admin\Application;

class ApplicationService
{
    private $em;

    public function __construct($em)
    {
        $this->em = $em;
    }

    /**
     * Met à jour la dernière ID utilisée pour une application donnée.
     *
     * @param string $codeApp Le code de l'application à mettre à jour.
     * @param string $numero  La nouvelle valeur du champ `derniereId`.
     */
    public function mettreAJourDerniereIdApplication(string $codeApp, string $numero): void
    {
        $application = $this->em->getRepository(Application::class)->findOneBy(['codeApp' => $codeApp]);

        if ($application === null) {
            throw new \RuntimeException("Aucune application trouvée pour le code : $codeApp");
        }

        $application->setDerniereId($numero);
        $this->em->persist($application);
    }
}
