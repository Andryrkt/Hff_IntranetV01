<?php

namespace App\Api\da\CmdFrn;

use App\Constants\da\StatutBcConstant;
use App\Constants\da\StatutDaConstant;
use App\Entity\da\DemandeAppro;
use App\Mapper\Da\DaAfficherMapper;
use App\Repository\da\DaAfficherRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class MesDaATraiter
{
    /**
     * @Route("/api/da/mes-da-a-traiter", name="da_mes_da_a_traiter", methods={"POST"})
     */
    public function mesDaATraiter(Request $request, DaAfficherRepository $daAfficherRepository, UrlGeneratorInterface $urlGenerator)
    {
        try {
            // $this->verifierSessionUtilisateur();
            $data = json_decode($request->getContent(), true);
            $codeAgenceServiceUser = $data['codeAgenceServiceUser'] ?? [];
            $codeAgenceUser = $codeAgenceServiceUser[0] ?? null;
            $codeServiceUser = $codeAgenceServiceUser[1] ?? null;


            if (empty($codeAgenceUser) || empty($codeServiceUser)) {
                return new JsonResponse([
                    'success' => false,
                    'message' => 'Aucun code agence ou code service fourni.',
                ], 400);
            }

            $criteriaTab = [];
            if ($codeAgenceUser == '80' && $codeServiceUser == 'APP') {
                $criteriaTab['statutDA'] = [
                    StatutDaConstant::STATUT_SOUMIS_APPRO,
                    StatutDaConstant::STATUT_DEMANDE_DEVIS,
                    StatutDaConstant::STATUT_DEVIS_A_RELANCER,
                    StatutDaConstant::STATUT_EN_COURS_PROPOSITION
                ];
                $criteriaTab['statutBC'] = [
                    StatutBcConstant::STATUT_PAS_DANS_BC,
                    StatutBcConstant::STATUT_PAS_DANS_OR_CESSION,
                    StatutBcConstant::STATUT_A_GENERER,
                    StatutBcConstant::STATUT_CESSION_A_GENERER,
                    StatutBcConstant::STATUT_A_EDITER,
                    StatutBcConstant::STATUT_A_SOUMETTRE_A_VALIDATION,
                    StatutBcConstant::STATUT_A_ENVOYER_AU_FOURNISSEUR
                ];
            } else {
                $criteriaTab['statutDA'] = [
                    StatutDaConstant::STATUT_EN_COURS_CREATION,
                    StatutDaConstant::STATUT_AUTORISER_EMETTEUR,
                    StatutDaConstant::STATUT_SOUMIS_ATE
                ];
            }

            $page = 1;
            $limit = 250;

            $paginationData = $daAfficherRepository->findValidatedPaginatedDas($criteriaTab, $page, $limit);
            $daAfficherMapper = new DaAfficherMapper($urlGenerator);
            $dataPrepared = $daAfficherMapper->mapList($paginationData['data'], [
                'codeAgenceUser' => $codeAgenceUser,
                'codeServiceUser' => $codeServiceUser,
            ]);

            return new JsonResponse([
                'success' => true,
                'message' => "Les DA à traiter pour l'utilisateur : $codeAgenceUser - $codeServiceUser sont affichées avec succès",
                'data' => $dataPrepared,
                'pagination' => [
                    'totalItems' => $paginationData['totalItems'],
                    'currentPage' => $paginationData['currentPage'],
                    'lastPage' => $paginationData['lastPage']
                ]
            ]);
        } catch (\Throwable $e) {
            if (ob_get_length() > 0) {
                ob_clean();
            }
            return new JsonResponse([
                'success' => false,
                'message' => 'Une erreur est survenue lors de la transmission des demandes BAP.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
