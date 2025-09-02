<?php

namespace App\Controller;

use Exception;
use App\Entity\admin\utilisateur\User;
use App\Model\LdapModel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
/**
 * Contrôleur d'authentification refactorisé pour utiliser l'injection de dépendances
 */
class Authentification extends Controller
{
    private ?LdapModel $ldapModel = null;

    public function __construct()
    {
        parent::__construct();
    }

    private function getLdapModel(): LdapModel
    {
        if ($this->ldapModel === null) {
            $this->ldapModel = new LdapModel();
        }
        return $this->ldapModel;
    }

    /**
     * @Route("/login", name="security_signin", methods={"GET", "POST"})
     */
    public function affichageSingnin(Request $request)
    {
        $error_msg = null;

        if ($request->isMethod('POST')) {
            $Username = $request->request->get('Username', '');
            $Password = $request->request->get('Pswd', '');

            try {
                $user = $this->getEntityManager()->getRepository(User::class)->findOneBy(['nom_utilisateur' => $Username]);
                $userId = ($user) ? $user->getId() : '-';

                // Utiliser le service de session injecté
                $this->getSessionService()->set('user_id', $userId);

                if (!$user) {
                    throw new \Exception('Utilisateur non trouvé avec le nom d\'utilisateur : ' . $Username);
                }

                if (!$this->getLdapModel()->userConnect($Username, $Password)) {
                    $this->logUserVisit('security_signin');
                    $error_msg = "Vérifier les informations de connexion, veuillez saisir le nom d'utilisateur et le mot de passe de votre session Windows";
                } else {
                    $this->getSessionService()->set('user', $Username);
                    $this->getSessionService()->set('password', $Password);

                    $filename = $_ENV['BASE_PATH_LONG'] . "\src\Controller\authentification.csv";
                    $newData = [$userId, $Username, $Password];
                    $this->updateOrInsertCSV($filename, $newData);

                    if (preg_match('/Hffintranet_pre_prod/i', $_SERVER['REQUEST_URI'])) {
                        // Donner accès qu'à certains utilisateurs
                        if (in_array(1, $user->getRoleIds())) {
                            $this->redirectToRoute('profil_acceuil');
                        } else {
                            $this->redirectTo('/Hffintranet/login');
                        }
                    }

                    $this->redirectToRoute('profil_acceuil');
                }
            } catch (Exception $e) {
                $this->logUserVisit('security_signin');
                $error_msg = $e->getMessage();
            }
        }

        return $this->render('signin.html.twig', [
            'error_msg' => $error_msg,
        ]);
    }

    private function updateOrInsertCSV(string $filename, array $newData)
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
