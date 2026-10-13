<?php

namespace App\Controller\da\ListeCdeFrn;

use App\Controller\Controller;
use App\Entity\da\DaNotificationFacBl;
use App\Service\da\CdeFrn\FacBl\NotificationSoumissionFacBlService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Page des notifications de soumission facture / BL (accessible uniquement à l'utilisateur autorisé)
 *
 * @Route("/demande-appro")
 */
class DaNotificationFacBlController extends Controller
{
    /**
     * @Route("/notifications-facbl", name="da_notification_facbl")
     */
    public function index(Request $request)
    {
        $this->verifierAcces();

        $anomalieSeulement = $request->query->getBoolean('anomalie');
        $repository        = $this->getEntityManager()->getRepository(DaNotificationFacBl::class);

        return $this->render('da/notificationFacBl.html.twig', [
            'notifications'     => $repository->findDernieres($anomalieSeulement),
            'nbNonLues'         => $repository->countNonLues(),
            'anomalieSeulement' => $anomalieSeulement,
        ]);
    }

    /**
     * @Route("/notifications-facbl/{id}/lu", name="da_notification_facbl_lu", requirements={"id"="\d+"})
     */
    public function marquerLu(int $id)
    {
        $this->verifierAcces();

        $notification = $this->getEntityManager()->getRepository(DaNotificationFacBl::class)->find($id);
        if ($notification instanceof DaNotificationFacBl) {
            $notification->setEstLu(true);
            $this->getEntityManager()->flush();
        }

        $this->redirectToRoute('da_notification_facbl');
    }

    /**
     * @Route("/notifications-facbl/tout-lu", name="da_notification_facbl_tout_lu")
     */
    public function marquerToutLu()
    {
        $this->verifierAcces();

        $this->getEntityManager()->createQueryBuilder()
            ->update(DaNotificationFacBl::class, 'n')
            ->set('n.estLu', 1)
            ->where('n.estLu = 0')
            ->getQuery()
            ->execute();

        $this->redirectToRoute('da_notification_facbl');
    }

    /**
     * Redirige vers l'accueil si l'utilisateur connecté n'est pas autorisé à voir les notifications
     */
    private function verifierAcces(): void
    {
        if (!NotificationSoumissionFacBlService::estAutorise($this->getUserName())) {
            $this->redirectToRoute('profil_acceuil');
        }
    }
}
