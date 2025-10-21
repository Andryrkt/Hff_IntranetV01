<?php

namespace App\Controller\admin;

use App\Controller\Controller;

use App\Entity\admin\Personnel;
use App\Form\admin\PersonnelType;
use App\Form\admin\PersonnelSearchType;
use App\Entity\admin\AgenceServiceIrium;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class PersonnelController extends Controller
{
    /**
     * @Route("/admin/personnel", name="personnel_index")
     *
     * @return void
     */
    public function index(Request $request)
    {

        //verification si user connecter
        $this->verifierSessionUtilisateur();

        $data = $this->getEntityManager()->getRepository(Personnel::class)->findBy([], ['id' => 'DESC']);

        $criteria = [

            'matricule' => $request->query->get('matricule', ''),

        ];

        $form = $this->getFormFactory()->createBuilder(PersonnelSearchType::class, null, ['method' => 'GET'])->getForm();

        $form->handleRequest($request);


        if ($form->isSubmitted() && $form->isValid()) {
            $criteria['matricule'] = $form->get('matricule')->getData();
        }

        $page = $request->query->getInt('page', 1);
        $limit = 10;

        $repository = $this->getEntityManager()->getRepository(Personnel::class);
        $data = $repository->findPaginatedAndFiltered($page, $limit, $criteria);
        $totalBadms = $repository->countFiltered($criteria);

        $totalPages = ceil($totalBadms / $limit);

        return $this->render('admin/Personnel/list.html.twig', [
            'form' => $form->createView(),
            'data' => $data,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'criteria' => $criteria,
            'resultat' => $totalBadms,
        ]);
    }

    /**
     * @Route("/admin/personnel/new", name="personnnel_new")
     */
    public function new(Request $request)
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        $personnel = new Personnel();

        $form = $this->getFormFactory()->createBuilder(PersonnelType::class)->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $personnelData = $form->getData();
            $agServIrium = $this->getEntityManager()->getRepository(AgenceServiceIrium::class)->findOneBy(['service_sage_paie' => $personnelData->getCodeAgenceServiceSage()]);
            $personnel->setNom($personnelData->getNom())
                ->setMatricule($personnelData->getMatricule())
                ->setCodeAgenceServiceSage($personnelData->getCodeAgenceServiceSage())
                ->setNumeroFournisseurIRIUM($personnelData->getNumeroFournisseurIRIUM())
                ->setCodeAgenceServiceIRIUM($personnelData->getCodeAgenceServiceIRIUM())
                ->setNumeroTelephone($personnelData->getNumeroTelephone())
                ->setDatecreation($personnelData->getDatecreation())
                ->setPrenoms($personnelData->getPrenoms())
                ->setAgenceServiceIriumId($agServIrium)
            ;

            $this->getEntityManager()->persist($personnel);
            $this->getEntityManager()->flush();


            $this->redirectToRoute("personnel_index");
        }

        return $this->render(
            'admin/Personnel/new.html.twig',
            [
                'form' => $form->createView()
            ]
        );
    }


    /**
     * @Route("/admin/personnel/edit/{id}", name="personnel_update")
     *
     * @return void
     */
    public function edit(Request $request, $id)
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        $user = $this->getEntityManager()->getRepository(Personnel::class)->find($id);

        $form = $this->getFormFactory()->createBuilder(PersonnelType::class, $user)->getForm();

        $form->handleRequest($request);

        // Vérifier si le formulaire est soumis et valide
        if ($form->isSubmitted() && $form->isValid()) {
            $personnelData = $form->getData();
            $agServIrium = $this->getEntityManager()->getRepository(AgenceServiceIrium::class)->findOneBy(['service_sage_paie' => $personnelData->getCodeAgenceServiceSage()]);
            $user->setAgenceServiceIriumId($agServIrium);

            $this->getEntityManager()->flush();
            $this->redirectToRoute("personnel_index");
        }

        return $this->render('admin/Personnel/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/admin/personnel/delete/{id}", name="personnel_delete")
     *
     * @return void
     */
    public function delete($id)
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        $user = $this->getEntityManager()->getRepository(Personnel::class)->find($id);

        $this->getEntityManager()->remove($user);
        $this->getEntityManager()->flush();

        $this->redirectToRoute("personnel_index");
    }

    /**
     * @Route("/admin/personnel/show/{id}", name="personnel_show")
     */
    public function show($id)
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        $user = $this->getEntityManager()->getRepository(Personnel::class)->find($id);

        return $this->render('admin/Personnel/show.html.twig', [
            'personnel' => $user
        ]);
    }
}
