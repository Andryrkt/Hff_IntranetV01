<?php

namespace App\Api\dom;

use App\Entity\admin\dom\Rmq;
use App\Controller\Controller;
use App\Entity\admin\dom\Catg;
use App\Entity\admin\dom\Site;
use App\Entity\admin\Personnel;
use App\Entity\admin\dom\Indemnite;
use App\Entity\admin\utilisateur\User;
use App\Controller\Traits\FormatageTrait;
use App\Entity\admin\dom\SousTypeDocument;
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
        try {
            $userId = $this->getSessionService()->get('user_id');
            $user = $this->getEntityManager()->getRepository(User::class)->find($userId);

            if (!$user) {
                throw new \Exception('Utilisateur non trouvé');
            }

            $sousTypedocument = $this->getEntityManager()->getRepository(SousTypeDocument::class)->find($id);

            if (!$sousTypedocument) {
                throw new \Exception('Sous-type de document non trouvé');
            }

            if ($user->getAgenceServiceIrium()->getAgenceIps() === '50') {
                $rmq = $this->getEntityManager()->getRepository(Rmq::class)->findOneBy(['description' => '50']);
            } else {
                $rmq = $this->getEntityManager()->getRepository(Rmq::class)->findOneBy(['description' => 'STD']);
            }

            if (!$rmq) {
                throw new \Exception('Rmq non trouvé');
            }

            $criteria = [
                'sousTypeDoc' => $sousTypedocument,
                'rmq' => $rmq
            ];

            $catg = $this->getEntityManager()->getRepository(Indemnite::class)->findDistinctByCriteria($criteria);

            $categories = [];
            foreach ($catg as $value) {
                $category = $this->getEntityManager()->getRepository(Catg::class)->find($value['id']);
                if ($category) {
                    $categories[] = [
                        'id' => $category->getId(),
                        'description' => $category->getDescription()
                    ];
                }
            }

            header("Content-type:application/json");
            echo json_encode($categories);
        } catch (\Exception $e) {
            header("Content-type:application/json");
            http_response_code(500);
            echo json_encode(['error' => 'Erreur lors de la récupération des catégories', 'message' => $e->getMessage()]);
        }
    }



    /**
     * @Route("/form1Data-fetch", name="fetch_form1Data", methods={"GET"})
     *permet d'envoyer les donnner du form1
     * @return void
     */
    public function form1DataFetch()
    {
        try {
            $form1Data = $this->getSessionService()->get('form1Data', []);
            header("Content-type:application/json");
            echo json_encode($form1Data);
        } catch (\Exception $e) {
            header("Content-type:application/json");
            http_response_code(500);
            echo json_encode(['error' => 'Erreur lors de la récupération des données', 'message' => $e->getMessage()]);
        }
    }



    /**
     * @Route("/site-idemnite-fetch/{siteId}/{docId}/{catgId}/{rmqId}", name="fetch_siteIdemnite", methods={"GET"})
     *
     * @return void
     */
    public function siteIndemniteFetch(int $siteId, int $docId, int $catgId, int $rmqId)
    {
        try {
            $site = $this->getEntityManager()->getRepository(Site::class)->find($siteId);
            $sousTypedocument = $this->getEntityManager()->getRepository(SousTypeDocument::class)->find($docId);
            $catg = $this->getEntityManager()->getRepository(Catg::class)->find($catgId);
            $rmq = $this->getEntityManager()->getRepository(Rmq::class)->find($rmqId);

            if (!$site || !$sousTypedocument || !$catg || !$rmq) {
                throw new \Exception('Une ou plusieurs entités non trouvées');
            }

            $criteria = [
                'sousTypeDoc' => $sousTypedocument,
                'rmq' => $rmq,
                'categorie' => $catg,
                'site' => $site
            ];

            $indemnite = $this->getEntityManager()->getRepository(Indemnite::class)->findOneBy($criteria);

            if (!$indemnite) {
                throw new \Exception('Indemnité non trouvée');
            }

            $montant = $indemnite->getMontant();
            $montant = $this->formatNumber($montant);

            header("Content-type:application/json");
            echo json_encode(['montant' => $montant]);
        } catch (\Exception $e) {
            header("Content-type:application/json");
            http_response_code(500);
            echo json_encode(['error' => 'Erreur lors de la récupération du montant', 'message' => $e->getMessage()]);
        }
    }

    /**
     * @Route("/personnel-fetch/{matricule}", name="fetch_personnel", methods={"GET"})
     *
     * @param [type] $matricule
     * @return void
     */
    public function personnelFetch($matricule)
    {
        try {
            $personne = $this->getEntityManager()->getRepository(Personnel::class)->findOneBy(['Matricule' => $matricule]);

            if (!$personne) {
                throw new \Exception('Personnel non trouvé pour le matricule: ' . $matricule);
            }

            // $numTel = $this->getEntityManager()->getRepository(Dom::class)->findLastNumtel($matricule);
            $tab = [
                'compteBancaire' => $personne->getNumeroCompteBancaire(),
                // 'telephone' => $numTel
            ];

            header("Content-type:application/json");
            echo json_encode($tab);
        } catch (\Exception $e) {
            header("Content-type:application/json");
            http_response_code(500);
            echo json_encode(['error' => 'Erreur lors de la récupération du personnel', 'message' => $e->getMessage()]);
        }
    }
}
