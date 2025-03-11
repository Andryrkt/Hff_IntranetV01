<?php

namespace App\Service\log;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use App\Entity\admin\utilisateur\User;
use App\Entity\admin\historisation\pageConsultation\PageHff;
use App\Entity\admin\historisation\pageConsultation\UserLogger;

class UserActivityLoggerService
{
    private SessionInterface $session;
    private EntityManagerInterface $em;

    public function __construct(SessionInterface $session, EntityManagerInterface $em)
    {
        $this->session = $session;
        $this->em = $em;
    }

    public function logUserVisit(string $nomRoute, ?array $params = null): void
    {
        $idUtilisateur = $this->session->get('user_id');
        if (!$idUtilisateur) {
            throw new \Exception("Utilisateur non trouvé en session.");
        }
        $utilisateur = ($idUtilisateur !== '-') ? $this->em->getRepository(User::class)->find($idUtilisateur) : null;
        $utilisateurNom = $utilisateur ? $utilisateur->getNomUtilisateur() : '-';
        $page = $this->em->getRepository(PageHff::class)->findPageByRouteName($nomRoute);
        $machine = gethostbyaddr($_SERVER['REMOTE_ADDR']) ?? $_SERVER['REMOTE_ADDR'];

        if ($page) {
            $log = new UserLogger();
            $log->setUtilisateur($utilisateurNom);
            // $log->setNomPage($page->getNom());
            $log->setParams($params ?: null);
            $log->setUser($utilisateur);
            $log->setPage($page);
            $log->setMachineUser($machine);

            $this->em->persist($log);
            $this->em->flush();
        }
    }
}
