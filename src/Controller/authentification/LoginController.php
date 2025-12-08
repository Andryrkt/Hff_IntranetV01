<?php

namespace App\Controller\authentification;

use App\Controller\Controller;
use Exception;
use App\Entity\admin\utilisateur\User;
use App\Model\LdapModel;
use App\Repository\admin\utilisateur\UserRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class LoginController extends Controller
{
    private ?LdapModel $ldapModel = null;
    private UserRepository $userRepository;

    public function __construct()
    {
        parent::__construct();
        $this->ldapModel = new LdapModel();
        $this->userRepository = $this->getEntityManager()->getRepository(User::class);
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
                $userId = $user ? $user->getId() : '-';

                if (!$user) {
                    throw new \Exception('Utilisateur non trouvé avec le nom d\'utilisateur : ' . $username);
                }

                if (!$this->ldapModel->userConnect($username, $password)) {
                    $this->logUserVisit('security_signin');
                    $error_msg = "Vérifier les informations de connexion, veuillez saisir le nom d'utilisateur et le mot de passe de votre session Windows";
                } else {
                    $userInfo = [
                        "id"       => $userId,
                        "name"     => $username,
                        "mail"     => $user->getMail(),
                        "role"     => $user->getRoleIdsAssoc(),
                        "password" => $password,
                    ];

                    $this->getSessionService()->set('user_info', $userInfo);

                    $this->getSessionService()->set('user_id', $userId);
                    $this->getSessionService()->set('user', $username);
                    $this->getSessionService()->set('password', $password);

                    $filename = $_ENV['BASE_PATH_LONG'] . "\src\Controller\authentification.csv";
                    $newData = [$userId, $username, $password];
                    $this->synchronizeCSV($filename, $newData);

                    if (preg_match('/Hffintranet_pre_prod/i', $_SERVER['REQUEST_URI']) && !in_array(1, $user->getRoleIds())) $this->redirectTo('/Hffintranet/login');
                    else $this->redirectToRoute('profil_acceuil');
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
}
