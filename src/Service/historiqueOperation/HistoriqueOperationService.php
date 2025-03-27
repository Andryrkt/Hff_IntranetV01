<?php

namespace App\Service\historiqueOperation;

use App\Controller\Controller;
use App\Entity\admin\utilisateur\User;
use App\Service\SessionManagerService;
use App\Entity\admin\historisation\documentOperation\TypeDocument;
use App\Entity\admin\historisation\documentOperation\TypeOperation;
use App\Entity\admin\historisation\documentOperation\HistoriqueOperationDocument;

class HistoriqueOperationService implements HistoriqueOperationInterface
{
    private $em;
    private $userRepository;
    private $typeOperationRepository;
    private $typeDocumentRepository;
    protected $sessionService;
    private $typeDocumentId;

    /** 
     * Constructeur pour l'historique des opérations de document par type
     * 
     * @param int $typeDocumentId ID du type de document
     *  - 1 : DIT - Demande d'intervention
     *  - 2 : OR - Ordre de réparation
     *  - 3 : FAC - Facture
     *  - 4 : RI - Rapport d'intervention
     *  - 5 : TIK - Ticketing (Demande de support informatique)
     *  - 6 : DA - Demande d'approvisionnement
     *  - 7 : DOM - Ordre de mission
     *  - 8 : BADM - Mouvement matériel
     *  - 9 : CAS - Casier
     *  - 10 : CDE - Commande
     *  - 11 : DEV - Devis
     *  - 12 : BC - Bon de commande
     *  - 13 : AC - Accusé de réception
     *  - 16 : MUT - Demande de mutation
     */
    public function __construct(int $typeDocumentId)
    {
        $this->em                      = Controller::getEntity();
        $this->userRepository          = $this->em->getRepository(User::class);
        $this->typeOperationRepository = $this->em->getRepository(TypeOperation::class);
        $this->typeDocumentRepository  = $this->em->getRepository(TypeDocument::class);
        $this->sessionService = new SessionManagerService();
        $this->typeDocumentId = $typeDocumentId;
    }

    /** 
     * Méthode pour enregistrer l'historique de l'opération
     * 
     * @param string $numeroDocument numéro du document, mettre '-' s'il n'y en a pas
     * @param int $typeOperationId ID de l'opération effectué, avec les valeurs possibles:
     *  - 1 : SOUMISSION
     *  - 2 : VALIDATION
     *  - 3 : MODIFICATION
     *  - 4 : SUPPRESSION
     *  - 5 : CREATION
     *  - 6 : CLOTURE
     * @param bool $succes statut de l'opération, valeur possible:
     *  - true : Succès de l'opération
     *  - false : Echec de l'opération (avec erreur)
     * @param string $libelleOperation libellé de l'opération
     */
    public function enregistrer(string $numeroDocument, int $typeOperationId, bool $statutOperation, ?string $libelleOperation = null): void
    {
        $historique    = new HistoriqueOperationDocument();
        $utilisateurId = $this->sessionService->get('user_id');
        $historique
            ->setNumeroDocument($numeroDocument)
            ->setUtilisateur($this->userRepository->find($utilisateurId)->getNomUtilisateur())
            ->setIdTypeOperation($this->typeOperationRepository->find($typeOperationId))
            ->setIdTypeDocument($this->typeDocumentRepository->find($this->typeDocumentId))
            ->setStatutOperation($statutOperation ? 'Succès' : 'Echec')
            ->setLibelleOperation($libelleOperation)
        ;

        // Sauvegarder dans la base de données
        $this->em->persist($historique);
        $this->em->flush();
    }

    /**
     * Methode qui permet d'enregistrer le message et le type dans une session
     *
     * @param string $message
     * @param boolean $success
     * @return void
     */
    protected function enregistrerDansSession(string $message,  bool $success = false) {
        $this->sessionService->set('notification', [
            'type'    => $success ? 'success' : 'danger',
            'message' => $message,
        ]);
    }

    /**
     * Methode qui permet d'enregistrer les information de l'historique dans la table historique_des_operations
     *
     * @param string $message
     * @param string $numeroDocument
     * @param integer $typeOperationId
     * @param boolean $success
     * @return void
     */
    protected function sendNotificationCore(string $message, string $numeroDocument, int $typeOperationId, bool $success = false)
    {
        $this->enregistrer($numeroDocument, $typeOperationId, $success, $message);
    }

    /** 
     * @param int $typeOperationId ID de l'opération effectué, avec les valeurs possibles:
     *  - 1 : SOUMISSION
     *  - 2 : VALIDATION
     *  - 3 : MODIFICATION
     *  - 4 : SUPPRESSION
     *  - 5 : CREATION
     *  - 6 : CLOTURE
     */
    protected function sendNotification(string $message, string $numeroDocument, string $routeName, int $typeOperationId, bool $success = false)
    {
        $this->enregistrerDansSession($message, $success);

        $this->sendNotificationCore($message, $numeroDocument, $typeOperationId, $success);

        header("Location: " . Controller::getGenerator()->generate($routeName));
        exit();
    }

    /** 
     * Méthode pour envoyer une notification et enregistrer l'historique de la SOUMISSION dU document
     * 
     * @param string $message message pour la notification
     * @param string $numeroDocument numéro du document, mettre '-' s'il n'y en a pas
     * @param string $routeName nom de la route pour la redirection
     * @param bool $success statut de la soumission, valeurs possibles:
     *  - true : Succès de la soumission
     *  - false : Echec de la soumission (valeur par défaut)
     */
    public function sendNotificationSoumission(string $message, string $numeroDocument, string $routeName, bool $success = false)
    {
        $this->sendNotification($message, $numeroDocument, $routeName, 1, $success);
    }

    /** 
     * Méthode pour envoyer une notification et enregistrer l'historique de la VALIDATION dU document
     * 
     * @param string $message message pour la notification
     * @param string $numeroDocument numéro du document, mettre '-' s'il n'y en a pas
     * @param string $routeName nom de la route pour la redirection
     * @param bool $success statut de la validation, valeurs possibles:
     *  - true : Succès de la validation
     *  - false : Echec de la validation (valeur par défaut)
     */
    public function sendNotificationValidation(string $message, string $numeroDocument, string $routeName, bool $success = false)
    {
        $this->sendNotification($message, $numeroDocument, $routeName, 2, $success);
    }

    /** 
     * Méthode pour envoyer une notification et enregistrer l'historique de la MODIFICATION dU document
     * 
     * @param string $message message pour la notification
     * @param string $numeroDocument numéro du document, mettre '-' s'il n'y en a pas
     * @param string $routeName nom de la route pour la redirection
     * @param bool $success statut de la modification, valeurs possibles:
     *  - true : Succès de la modification
     *  - false : Echec de la modification (valeur par défaut)
     */
    public function sendNotificationModification(string $message, string $numeroDocument, string $routeName, bool $success = false)
    {
        $this->sendNotification($message, $numeroDocument, $routeName, 3, $success);
    }

    /** 
     * Méthode pour envoyer une notification et enregistrer l'historique de la SUPPRESSION dU document
     * 
     * @param string $message message pour la notification
     * @param string $numeroDocument numéro du document, mettre '-' s'il n'y en a pas
     * @param string $routeName nom de la route pour la redirection
     * @param bool $success statut de la suppression, valeurs possibles:
     *  - true : Succès de la suppression
     *  - false : Echec de la suppression (valeur par défaut)
     */
    public function sendNotificationSuppression(string $message, string $numeroDocument, string $routeName, bool $success = false)
    {
        $this->sendNotification($message, $numeroDocument, $routeName, 4, $success);
    }

    /** 
     * Méthode pour envoyer une notification et enregistrer l'historique de la CREATION dU document
     * 
     * @param string $message message pour la notification
     * @param string $numeroDocument numéro du document, mettre '-' s'il n'y en a pas
     * @param string $routeName nom de la route pour la redirection
     * @param bool $success statut de la création, valeurs possibles:
     *  - true : Succès de la création
     *  - false : Echec de la création (valeur par défaut)
     */
    public function sendNotificationCreation(string $message, string $numeroDocument, string $routeName, bool $success = false)
    {
        $this->sendNotification($message, $numeroDocument, $routeName, 5, $success);
    }

    /** 
     * Méthode pour envoyer une notification et enregistrer l'historique de la CLOTURE dU document
     * 
     * @param string $message message pour la notification
     * @param string $numeroDocument numéro du document, mettre '-' s'il n'y en a pas
     * @param string $routeName nom de la route pour la redirection
     * @param bool $success statut de la clôture, valeurs possibles:
     *  - true : Succès de la clôture
     *  - false : Echec de la clôture (valeur par défaut)
     */
    public function sendNotificationCloture(string $message, string $numeroDocument, string $routeName, bool $success = false)
    {
        $this->sendNotification($message, $numeroDocument, $routeName, 6, $success);
    }
}
