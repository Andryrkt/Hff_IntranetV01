<?php

namespace App\Controller\authentification;

use Exception;
use App\Model\LdapModel;
use App\Controller\Controller;
use App\Entity\admin\utilisateur\AgenceServiceDefautSociete;
use App\Entity\admin\utilisateur\User;
use App\Entity\admin\utilisateur\Profil;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\admin\utilisateur\UserRepository;

class LoginController extends Controller
{
    private LdapModel $ldapModel;
    private UserRepository $userRepository;

    public function __construct(EntityManagerInterface $entityManager, LdapModel $ldapModel)
    {
        parent::__construct();

        $this->ldapModel      = $ldapModel;
        $this->userRepository = $entityManager->getRepository(User::class);
    }

    /**
     * @Route("/login", name="security_signin", methods={"GET", "POST"})
     */
    public function affichageSingnin(Request $request)
    {
        $error_msg = null;

        if ($request->isMethod('POST')) {
            $username = $request->request->get('username', '');
            $password = $request->request->get('password', '');

            try {
                /** @var User $user */
                $user = $this->userRepository->findOneBy(['nom_utilisateur' => $username]);

                if (!$user) throw new \Exception('Utilisateur non trouvé avec le nom d\'utilisateur : ' . $username);

                if (!$this->ldapModel->userConnect($username, $password)) {
                    $this->logUserVisit('security_signin');
                    $error_msg = "Vérifier les informations de connexion, veuillez saisir le nom d'utilisateur et le mot de passe de votre session Windows";
                } else {
                    $profils = $user->getProfils();

                    if ($profils->isEmpty()) throw new \Exception('Aucun profil trouvé pour l\'utilisateur : ' . $username . '. Veuillez contacter le support informatique.');

                    $userId = $user->getId();
                    $firstname = $user->getFirstName();
                    $lastname = $user->getLastName();
                    $userInfo = [
                        "id"        => $userId,
                        "username"  => $username,
                        "firstname" => $firstname,
                        "lastname"  => $lastname,
                        "fullname"  => "$lastname $firstname",
                        "email"     => $user->getMail()
                    ];

                    $this->getSessionService()->set('user_info', $userInfo);

                    $filename = $_ENV['BASE_PATH_LONG'] . "\src\Controller\authentification.csv";
                    $newData = [$userId, $username, $password];
                    $this->synchronizeCSV($filename, $newData);

                    if ($profils->count() > 1) $this->redirectToRoute('choix_societe');

                    /** @var Profil $profil */
                    $profil = $profils->first();
                    $codeSociete = $profil->getSociete()->getCodeSociete();

                    /** @var AgenceServiceDefautSociete $agenceServiceDefaut */
                    $agenceServiceDefaut = $user->getAgenceServiceDefautSocietes()->filter(fn(AgenceServiceDefautSociete $ags) => $ags->getCodeSociete() === $codeSociete)->first();

                    $userInfo["default_agence_code"]  = $agenceServiceDefaut->getCodeAgence();
                    $userInfo["default_service_code"] = $agenceServiceDefaut->getCodeService();
                    $userInfo["default_agence_id"]    = $agenceServiceDefaut->getAgence()->getId();
                    $userInfo["default_service_id"]   = $agenceServiceDefaut->getService()->getId();
                    $userInfo['societe_code']         = $codeSociete;
                    $userInfo['profil_id']            = $profil->getId();
                    $userInfo['profil_name']          = $profil->getDesignation();
                    $this->getSessionService()->set('user_info', $userInfo);

                    $this->redirectToRoute('profil_acceuil');
                }
            } catch (Exception $e) {
                $this->logUserVisit('security_signin');
                $error_msg = $e->getMessage();
            }
        }

        return $this->render('signIn.html.twig', [
            'error_msg' => $error_msg,
        ]);
    }

    private function synchronizeCSV(string $filename, array $newData)
    {
        $rows = [];
        $found = false;

        // Vérifier si le fichier existe avant de tenter de le lire
        if (file_exists($filename)) {
            $handle = fopen($filename, "r");

            if (!$handle) {
                die("Erreur : Impossible d'ouvrir le fichier $filename en lecture.");
            }

            while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {
                if ($data[0] == $newData[0]) { // Vérifie si l'ID existe déjà
                    if ($data[2] !== $newData[2]) { // Vérifie si l'email est différent
                        $data[2] = $newData[2]; // Met à jour l'email
                    }
                    $found = true;
                }
                $rows[] = $data; // Stocke la ligne (modifiée ou non)
            }

            fclose($handle);
        }

        // Si l'ID n'existe pas, ajoute une nouvelle ligne
        if (!$found) {
            $rows[] = $newData;
        }

        // Vérifier si le fichier est accessible en écriture
        if (!is_writable($filename) && file_exists($filename)) {
            die("Erreur : Impossible d'écrire dans le fichier $filename");
        }

        // Réécriture complète du fichier CSV
        $handle = fopen($filename, "w");

        if (!$handle) {
            die("Erreur : Impossible d'ouvrir le fichier $filename en écriture.");
        }

        foreach ($rows as $row) {
            fputcsv($handle, $row, ";");
        }

        fclose($handle);
    }

    /**
     * @Route("/logout", name="auth_deconnexion")
     */
    public function deconnexion()
    {
        // Détruire la session utilisateur
        $this->SessionDestroy();

        // Rediriger vers la page de connexion
        return $this->redirectToRoute('security_signin');
    }

    /**
     * @Route("/api/login", name="api_login", methods={"POST"})
     */
    public function apiLogin(Request $request)
    {
        $data = json_decode($request->getContent(), true);

        $username = $data['username'] ?? '';
        $password = $data['password'] ?? '';

        try {
            /** @var User $user */
            $user = $this->userRepository->findOneBy(['nom_utilisateur' => $username]);

            if (!$user) {
                throw new \Exception('Utilisateur non trouvé avec le nom d\'utilisateur : ' . $username);
            }

            if (!$this->ldapModel->userConnect($username, $password)) {
                return new \Symfony\Component\HttpFoundation\JsonResponse(['error' => 'Identifiants invalides'], 401);
            }

            $profils = $user->getProfils();

            if ($profils->isEmpty()) {
                return new \Symfony\Component\HttpFoundation\JsonResponse(['error' => 'Aucun profil trouvé'], 403);
            }

            $payload = [
                'id'                   => $user->getId(),
                'username'             => $username,
                'email'                => $user->getMail(),
            ];

            $jwtService = new \App\Service\security\JwtService();
            // Création du Token valables pour 2 heures (7200 secondes)
            $token = $jwtService->encode($payload, 7200);

            return new \Symfony\Component\HttpFoundation\JsonResponse([
                'success' => true,
                'token'   => $token,
                'user'    => $payload
            ]);
        } catch (\Exception $e) {
            return new \Symfony\Component\HttpFoundation\JsonResponse(['error' => $e->getMessage()], 400);
        }
    }
}
