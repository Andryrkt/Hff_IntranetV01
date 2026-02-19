<?php


namespace App\Controller\admin;


use App\Entity\admin\Agence;
use App\Controller\Controller;
use App\Entity\admin\AgenceService;
use App\Form\admin\AgenceType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AgenceController extends Controller
{
    /**
     * @Route("/admin/agence/list-agence", name="agence_index")
     *
     * @return void
     */
    public function index()
    {
        $data = $this->getEntityManager()->getRepository(Agence::class)->findAll();
        $preparedData = $this->prepareForDisplay($data);

        return $this->render(
            'admin/agence/list.html.twig',
            [
                'data' => $preparedData
            ]
        );
    }

    /**
     * @Route("/admin/agence/new", name="agence_new")
     */
    public function new(Request $request)
    {
        $agence = new Agence();
        $form = $this->getFormFactory()->createBuilder(AgenceType::class, $agence)->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $selectedService = $form->get('services')->getData();

            foreach ($selectedService as $service) {
                $agenceService = new AgenceService($agence, $service);
                $agence->addAgenceService($agenceService);
                $this->getEntityManager()->persist($agenceService);
            }

            $this->getEntityManager()->persist($agence);
            $this->getEntityManager()->flush();

            $this->redirectToRoute("agence_index");
        }

        return $this->render('admin/agence/new.html.twig', [
            'form' => $form->createView()
        ]);
    }


    /**
     * @Route("/admin/agence/edit/{id}", name="agence_update")
     *
     * @param Request $request
     * @param int $id
     * @return Response
     */
    public function edit(Request $request, $id)
    {
        $em = $this->getEntityManager();

        /** @var Agence $agence */
        $agence = $em->getRepository(Agence::class)->find($id);
        $servicesLinked = $agence->getServices()->toArray();

        $form = $this->getFormFactory()->createBuilder(AgenceType::class, $agence)->getForm();

        // Pré-remplir le champ 'services'
        $form->get('services')->setData($servicesLinked);
        $form->handleRequest($request);

        // Vérifier si le formulaire est soumis et valide
        if ($form->isSubmitted() && $form->isValid()) {
            $selectedServices = $form->get('services')->getData(); // Collection de services

            // Supprimer les liens qui ne sont plus sélectionnés
            foreach ($agence->getAgenceServices() as $ap) {
                if (!in_array($ap->getService(), $selectedServices, true)) {
                    $agence->removeAgenceService($ap);
                    $em->remove($ap); // cascade remove assure suppression automatique
                }
            }

            // Ajouter les nouveaux liens
            foreach ($selectedServices as $service) {
                $exists = false;
                foreach ($agence->getAgenceServices() as $ap) {
                    if ($ap->getService() === $service) {
                        $exists = true;
                        break;
                    }
                }
                if (!$exists) {
                    $agenceService = new AgenceService($agence, $service);
                    $agence->addAgenceService($agenceService);
                    $em->persist($agenceService);
                }
            }

            $em->persist($agence);
            $em->flush();

            $this->redirectToRoute("agence_index");
        }

        return $this->render('admin/agence/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    private function prepareForDisplay(array $data)
    {
        $preparedData = [];

        /** @var Agence $agence */
        foreach ($data as $agence) {
            $baseData = [
                'urlUpdate'      => $this->getUrlGenerator()->generate(
                    'agence_update',
                    ['id' => $agence->getId()]
                ),
                'codeAgence'     => $agence->getCodeAgence(),
                'libelleAgence'  => $agence->getLibelleAgence(),
            ];

            $services = $agence->getServices();

            if ($services->isEmpty()) {
                $preparedData[] = $baseData + ['codeService' => '', 'libelleService' => ''];
                continue;
            }

            foreach ($services as $service) {
                $preparedData[] = $baseData + [
                    'codeService'    => $service->getCodeService(),
                    'libelleService' => $service->getLibelleService()
                ];
            }
        }

        return $preparedData;
    }
}
