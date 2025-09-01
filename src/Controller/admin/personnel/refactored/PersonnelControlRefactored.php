<?php

use Symfony\Component\HttpFoundation\Response;

namespace App\Controller\admin\personnel;
use App\Service\FusionPdf;
use App\Model\ProfilModel;
use App\Model\badm\BadmModel;
use App\Model\admin\personnel\PersonnelModel;
use App\Model\dom\DomModel;
use App\Model\da\DaModel;
use App\Model\dom\DomDetailModel;
use App\Model\dom\DomDuplicationModel;
use App\Model\dom\DomListModel;
use App\Model\dit\DitModel;
use App\Service\SessionManagerService;
use App\Service\ExcelService;


use App\Controller\Controller;


use App\Controller\Traits\Transformation;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use App\Controller\BaseController;



class PersonnelControl extends BaseController
{
    private FusionPdf $fusionPdfService;
    private ProfilModel $profilModelService;
    private BadmModel $badmModelService;
    private PersonnelModel $personnelModelService;
    private DomModel $domModelService;
    private DaModel $daModelService;
    private DomDetailModel $domDetailModelService;
    private DomDuplicationModel $domDuplicationModelService;
    private DomListModel $domListModelService;
    private DitModel $ditModelService;
    private SessionManagerService $sessionManagerService;
    private ExcelService $excelServiceService;

    public function __construct(
        FusionPdf $fusionPdfService,
        ProfilModel $profilModelService,
        BadmModel $badmModelService,
        PersonnelModel $personnelModelService,
        DomModel $domModelService,
        DaModel $daModelService,
        DomDetailModel $domDetailModelService,
        DomDuplicationModel $domDuplicationModelService,
        DomListModel $domListModelService,
        DitModel $ditModelService,
        SessionManagerService $sessionManagerService,
        ExcelService $excelServiceService
    ) {
        parent::__construct();
        $this->fusionPdfService = $fusionPdfService;
        $this->profilModelService = $profilModelService;
        $this->badmModelService = $badmModelService;
        $this->personnelModelService = $personnelModelService;
        $this->domModelService = $domModelService;
        $this->daModelService = $daModelService;
        $this->domDetailModelService = $domDetailModelService;
        $this->domDuplicationModelService = $domDuplicationModelService;
        $this->domListModelService = $domListModelService;
        $this->ditModelService = $ditModelService;
        $this->sessionManagerService = $sessionManagerService;
        $this->excelServiceService = $excelServiceService;
    }


    use Transformation;

    /**
     * @Route("/index")
     */
    public function index(Request $request)
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        $form = $this->getFormFactory()->createBuilder()
        ->add('firstName', TextType::class, array(
            'constraints' => array(
                new NotBlank(),
                new Length(array('min' => 4)),
            ),
        ))
        ->add('lastName', TextType::class, array(
            'constraints' => array(
                new NotBlank(),
                new Length(array('min' => 4)),
            ),
        ))
        ->add('gender', ChoiceType::class, array(
            'choices' => array('m' => 'Male', 'f' => 'Female'),
        ))
        ->add('newsletter', CheckboxType::class, array(
            'required' => false,
        ))
        ->add('submit', SubmitType::class, [
            'label' => 'Submit'
        ])
        ->getForm();

        $form->handleRequest($request);

         // Vérifier si le formulaire est soumis et valide
         if ($form->isSubmitted() && $form->isValid()) {
            // Traitement des données du formulaire
           dd( $form->getData());
          
        }

        $this->getTwig()->render('test.html.twig', [
            'form' => $form->createView()
        ]);
}


    public function showPersonnelForm()
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            echo 'okey';
        } else {
            $codeSage = $this->transformEnSeulTableau($this->personnelModelService->recupAgenceServiceSage());
            $codeIrium = $this->transformEnSeulTableau($this->personnelModelService->recupAgenceServiceIrium());
            $serviceIrium = $this->transformEnSeulTableau($this->personnelModelService->recupServiceIrium());


            return new \Symfony\Component\HttpFoundation\Response($this->getTwig()->render(
                'admin/personnel/addPersonnel.html.twig',
                [
                    'codeSage' => $codeSage,
                    'codeIrium' => $codeIrium,
                    'serviceIrium' => $serviceIrium
                ]
            ));
        }
    }

    public function showListePersonnel()
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        $infoPersonnel = $this->personnelModelService->recupInfoPersonnel();

        // var_dump($infoPersonnel);
        // die();



        return new \Symfony\Component\HttpFoundation\Response($this->getTwig()->render(
            'admin/personnel/listPersonnel.html.twig',
            [
                'infoPersonnel' => $infoPersonnel
            ]
        ));
    }

    public function updatePersonnel()
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();
        
        $codeSage = $this->transformEnSeulTableau($this->personnelModelService->recupAgenceServiceSage());
        $codeIrium = $this->transformEnSeulTableau($this->personnelModelService->recupAgenceServiceIrium());


        $infoPersonnelId = $this->personnelModelService->recupInfoPersonnelMatricule($_GET['matricule']);
        return new \Symfony\Component\HttpFoundation\Response($this->getTwig()->render(
            'admin/personnel/addPersonnel.html.twig',
            [
                'codeSage' => $codeSage,
                'codeIrium' => $codeIrium,
                'infoPersonnelId' => $infoPersonnelId
            ]
        ));
    }
}
