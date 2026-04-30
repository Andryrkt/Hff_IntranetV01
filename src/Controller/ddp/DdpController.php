<?php

namespace App\Controller\ddp;


use App\Controller\Controller;
use App\Dto\ddp\DdpDto;
use App\Factory\ddp\DdpFactory;
use App\Form\ddp\DdpType;
use App\Service\ddp\DdpGeneratorNameService;
use App\Service\fichier\UploderFileService;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/ddp")
 */
class DdpController extends Controller
{
    private DdpFactory $ddpFactory;

    public function __construct(DdpFactory $ddpFactory)
    {
        parent::__construct();
        $this->ddpFactory = $ddpFactory;
    }

    /**
     * @Route("/new/{typeDdp}", name="new_ddp")
     */
    public function new(int $typeDdp, Request $request)
    {
        // initialisation DTO
        $dto = $this->ddpFactory->initialisation($typeDdp);
        //Creation du formulaire
        $form = $this->getFormFactory()->createBuilder(DdpType::class, $dto)->getForm();
        // Traitement du formulaire
        $this->traitementDuFormulaire($form,  $request);
        return $this->render('ddp/magasin/new.html.twig', [
            'form' => $form->createView(),
            'type_ddp' => $typeDdp
        ]);
    }

    private function traitementDuFormulaire(FormInterface $form, Request $request)
    {
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var DdpDto $dto */
            $dto = $form->getData();
        }
    }

    private function enregistrementFichier(FormInterface $form, DdpDto $dto): array
    {
        $nameGenerator = new DdpGeneratorNameService();
        $cheminBaseUpload = $_ENV['BASE_PATH_FICHIER'] . '/ddp/';
        $uploader = new UploderFileService($cheminBaseUpload, $nameGenerator);
        $path = $cheminBaseUpload . $dto->numeroDdp . '/';
        if (!is_dir($path)) mkdir($path, 0777, true);

        [$nomEtCheminFichiersEnregistrer, $nomFichierTelecharger] = $uploader->getFichiers($form, [
            'repertoire' => $path,
            'conserver_nom_original' => false,
            'generer_nom_callback' => function ($file, $index, $extension, $variables) use ($dto) {
                $fieldName = $variables['field_name'] ?? '';
                $numDdp = $dto->numeroDdp;

                $mapping = [
                    'pieceJoint01' => 'PROFORMA',
                    'pieceJoint02' => 'RIB',
                    'pieceJoint03' => 'BC',
                    'pieceJoint04' => 'AUTRES FICHIERS',
                ];

                $baseName = $mapping[$fieldName] ?? 'Document';

                // Si c'est le champ multiple pieceJoint03 ou s'il y a plusieurs fichiers, on ajoute l'index
                if ($fieldName === 'pieceJoint03') {
                    return sprintf("%s_%s_%02d.%s", $baseName, $numDdp, $index, $extension);
                }

                return sprintf("%s_%s.%s", $baseName, $numDdp, $extension);
            }
        ]);

        $nomFichier = $nameGenerator->generateNamePrincipal($dto->numeroDdp);
        $nomAvecCheminFichier = $path . '/' . $nomFichier;

        return [$nomEtCheminFichiersEnregistrer, $nomFichierTelecharger,  $nomAvecCheminFichier, $nomFichier];
    }
}
