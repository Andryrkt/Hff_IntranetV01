<?php

namespace App\Controller;

use App\Service\navigation\MenuService;
use App\Service\SessionManagerService;
use App\Entity\admin\utilisateur\User;
use App\Entity\admin\Application;

/**
 * Contrôleur de diagnostic pour les vignettes
 */
class DebugController extends Controller
{
    /**
     * @Route("/debug/vignettes", name="debug_vignettes")
     */
    public function debugVignettes()
    {
        $this->verifierSessionUtilisateur();
        
        $debugInfo = [];
        
        // 1. Vérifier la session
        $sessionManager = $this->getSessionService();
        $debugInfo['session'] = [
            'session_id' => session_id(),
            'user_id' => $sessionManager->get('user_id'),
            'session_active' => session_status() === PHP_SESSION_ACTIVE
        ];
        
        // 2. Vérifier l'utilisateur connecté
        if ($sessionManager->has('user_id')) {
            $userId = $sessionManager->get('user_id');
            $user = $this->getEntityManager()->getRepository(User::class)->find($userId);
            
            if ($user) {
                $debugInfo['user'] = [
                    'id' => $user->getId(),
                    'nom' => $user->getNomUtilisateur(),
                    'applications' => $user->getApplicationsIds(),
                    'roles' => $user->getRoleIds()
                ];
            } else {
                $debugInfo['user'] = ['error' => 'Utilisateur non trouvé en base'];
            }
        } else {
            $debugInfo['user'] = ['error' => 'Aucun utilisateur connecté'];
        }
        
        // 3. Tester le MenuService
        try {
            $menuService = new MenuService($this->getEntityManager());
            $menuStructure = $menuService->getMenuStructure();
            
            $debugInfo['menu'] = [
                'count' => count($menuStructure),
                'vignettes' => array_map(function($v) {
                    return [
                        'id' => $v['id'],
                        'title' => $v['title'],
                        'icon' => $v['icon']
                    ];
                }, $menuStructure)
            ];
            
        } catch (\Exception $e) {
            $debugInfo['menu'] = ['error' => $e->getMessage()];
        }
        
        // 4. Vérifier les constantes d'application
        $debugInfo['applications'] = [
            'ID_DOM' => Application::ID_DOM,
            'ID_BADM' => Application::ID_BADM,
            'ID_CAS' => Application::ID_CAS,
            'ID_DIT' => Application::ID_DIT,
            'ID_MAG' => Application::ID_MAG,
            'ID_REP' => Application::ID_REP,
            'ID_TIK' => Application::ID_TIK
        ];
        
        return $this->render('debug/vignettes.html.twig', [
            'debugInfo' => $debugInfo
        ]);
    }
}
