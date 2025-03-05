<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;
use App\Entity\admin\utilisateur\User;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Doctrine\ORM\EntityManagerInterface;

class AppExtension extends AbstractExtension implements GlobalsInterface
{
    private SessionInterface $session;
    private RequestStack $requestStack;
    private EntityManagerInterface $em;

    public function __construct(SessionInterface $session, RequestStack $requestStack, EntityManagerInterface $em)
    {
        $this->session = $session;
        $this->requestStack = $requestStack;
        $this->em = $em;
    }

    public function getGlobals(): array
    {
        $user = null;
        $notification = $this->session->get('notification');
        $this->session->remove('notification');

        if ($this->session->get('user_id') !== null) {
            $user = $this->em->getRepository(User::class)->find($this->session->get('user_id'));
        }

        return [
            'user' => $user,
            'base_path' => $_ENV['BASE_PATH_COURT'],
            'notification' => $notification,
        ];
    }
}



