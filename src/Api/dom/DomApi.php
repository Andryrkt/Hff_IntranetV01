<?php

namespace App\Api\dom;

use App\Entity\dom\Dom;
use App\Entity\admin\Agence;
use App\Entity\admin\dom\Rmq;
use App\Controller\Controller;
use App\Entity\admin\dom\Catg;
use App\Entity\admin\dom\Site;
use App\Entity\admin\Personnel;
use App\Entity\admin\dom\Indemnite;
use App\Entity\admin\utilisateur\User;
use App\Controller\Traits\FormatageTrait;
use App\Entity\admin\dom\SousTypeDocument;
use App\Entity\mutation\Mutation;
use Symfony\Component\Routing\Annotation\Route;

class DomApi extends Controller
{
    use FormatageTrait;

    /**
     * @Route("/categorie-fetch/{id}", name="fetch_categorie", methods={"GET"})
     * 
     * Cette fonction permet d'envoier les donner de categorie selon la sousType de document
     *
     * @param int $id
     * @return void
     */
    public function categoriefetch(int $id)
    {
        $userId = $this->sessionService->get('user_id');
        $user = self::$em->getRepository(User::class)->find($userId);

        $sousTypedocument = self::$em->getRepository(SousTypeDocument::class)->find($id);

        if ($user->getAgenceServiceIrium()->getAgenceIps() === '50') {
            $rmq = self::$em->getRepository(Rmq::class)->findOneBy(['description' => '50']);
        } else {
            $rmq = self::$em->getRepository(Rmq::class)->findOneBy(['description' => 'STD']);
        }

        $criteria = [
            'sousTypeDoc' => $sousTypedocument,
            'rmq' => $rmq
        ];


        $catg = self::$em->getRepository(Indemnite::class)->findDistinctByCriteria($criteria);


        header("Content-type:application/json");

        echo json_encode($catg);;
    }



    /**
     * @Route("/form1Data-fetch", name="fetch_form1Data", methods={"GET"})
     *permet d'envoyer les donnner du form1
     * @return void
     */
    public function form1DataFetch()
    {
        $form1Data = $this->sessionService->get('form1Data', []);
        header("Content-type:application/json");

        echo json_encode($form1Data);
    }

    /**
     * @Route("/agence-fetch/{id}", name="fetch_agence", methods={"GET"})
     * cette fonction permet d'envoyer les donner du service debiteur selon l'agence debiteur en ajax
     * @return void
     */
    public function agence($id)
    {
        $agence = self::$em->getRepository(Agence::class)->find($id);

        $service = $agence->getServices();

        //   $services = $service->getValues();
        $services = [];
        foreach ($service as $key => $value) {
            $services[] = [
                'value' => $value->getId(),
                'text' => $value->getCodeService() . ' ' . $value->getLibelleService()
            ];
        }

        header("Content-type:application/json");

        echo json_encode($services);
    }

    /**
     * @Route("/site-idemnite-fetch/{siteId}/{docId}/{catgId}/{rmqId}", name="fetch_siteIdemnite", methods={"GET"})
     *
     * @return void
     */
    public function siteIndemniteFetch(int $siteId, int $docId, int $catgId, int $rmqId)
    {
        $site = self::$em->getRepository(Site::class)->find($siteId);
        $sousTypedocument = self::$em->getRepository(SousTypeDocument::class)->find($docId);
        $catg = self::$em->getRepository(Catg::class)->find($catgId);
        $rmq = self::$em->getRepository(Rmq::class)->find($rmqId);

        $criteria = [
            'sousTypeDoc' => $sousTypedocument,
            'rmq' => $rmq,
            'categorie' => $catg,
            'site' => $site
        ];

        $montant = self::$em->getRepository(Indemnite::class)->findOneBy($criteria)->getMontant();

        $montant = $this->formatNumber($montant);

        header("Content-type:application/json");

        echo json_encode(['montant' => $montant]);
    }

    /**
     * @Route("/personnel-fetch/{matricule}", name="fetch_personnel", methods={"GET"})
     *
     * @param [type] $matricule
     * @return void
     */
    public function personnelFetch($matricule)
    {
        $personne = self::$em->getRepository(Personnel::class)->findOneBy(['Matricule' => $matricule]);
        $numTel = self::$em->getRepository(Dom::class)->findLastNumtel($matricule);
        $tab = [
            'compteBancaire' => $personne->getNumeroCompteBancaire(),
            'telephone' => $numTel
        ];

        header("Content-type:application/json");

        echo json_encode($tab);
    }
}
