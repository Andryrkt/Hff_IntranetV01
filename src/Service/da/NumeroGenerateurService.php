<?php

namespace App\Service\da;

use App\Entity\admin\Application;
use App\Service\autres\AutoIncDecService;
use Doctrine\ORM\EntityManagerInterface;
use Exception;

class NumeroGenerateurService
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * Génère un numéro automatique pour une application donnée (BAP, DDP, etc.)
     * 
     * @param string $codeApp Le code de l'application (ex: 'BAP', 'DDP')
     * @return string
     * @throws Exception
     */
    public function genererNumero(string $codeApp): string
    {
        $application = $this->em->getRepository(Application::class)->findOneBy(['codeApp' => $codeApp]);
        
        if (!$application) {
            throw new Exception("L'application '$codeApp' n'a pas été trouvée dans la configuration.");
        }

        // Génération du numéro
        $numero = AutoIncDecService::autoGenerateNumero($codeApp, $application->getDerniereId(), true);

        // Mise à jour de la dernière ID de l'application
        AutoIncDecService::mettreAJourDerniereIdApplication($application, $this->em, $numero);

        return $numero;
    }

    public function genererNumeroBap(): string
    {
        return $this->genererNumero('BAP');
    }

    public function genererNumeroDdp(): string
    {
        return $this->genererNumero('DDP');
    }
}
