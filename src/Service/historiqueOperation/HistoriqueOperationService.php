<?php

namespace App\Service\historiqueOperation;

use App\Controller\Controller;
use App\Entity\admin\utilisateur\User;
use App\Service\SessionManagerService;
use App\Entity\admin\dit\DitTypeDocument;
use App\Entity\admin\dit\DitTypeOperation;
use App\Entity\dit\DitHistoriqueOperationDocument;

class HistoriqueOperationService implements HistoriqueOperationInterface
{
    private $em;
    private $userRepository;
    private $typeOperationRepository;
    private $typeDocumentRepository;
    private $sessionService;

    public function __construct()
    {
        $this->em                      = Controller::getEntity();
        $this->userRepository          = $this->em->getRepository(User::class);
        $this->typeOperationRepository = $this->em->getRepository(DitTypeOperation::class);
        $this->typeDocumentRepository  = $this->em->getRepository(DitTypeDocument::class);
        $this->sessionService = new SessionManagerService();
    }

    public function enregistrer(string $numeroDocument, int $typeOperationId, int $typeDocumentId, string $statutOperation, ?string $libelleOperation = null): void
    {
        $historique    = new DitHistoriqueOperationDocument();
        $utilisateurId = $this->sessionService->get('user_id');
        $historique
            ->setNumeroDocument($numeroDocument)
            ->setUtilisateur($this->userRepository->find($utilisateurId)->getNomUtilisateur())
            ->setIdTypeOperation($this->typeOperationRepository->find($typeOperationId))
            ->setIdTypeDocument($this->typeDocumentRepository->find($typeDocumentId))
            ->setStatutOperation($statutOperation)
            ->setLibelleOperation($libelleOperation)
        ;

        // Sauvegarder dans la base de données
        $this->em->persist($historique);
        $this->em->flush();
    }

    /** 
     * Méthode qui enregistre l'historique de l'opération fait sur le type de document DIT: DEMANDE D'INTERVENTION
     * 
     * @param string $numeroDocument numéro du document
     * @param int $typeOperationId id de l'opération effectué
     * @param string $statutOperation statut de l'opération 
     * @param string $libelleOperation libellé de l'opération
     */
    public function enregistrerDIT(string $numeroDocument, int $typeOperationId, string $statutOperation, ?string $libelleOperation = null): void
    {
        $this->enregistrer($numeroDocument, $typeOperationId, 1, $statutOperation, $libelleOperation);
    }

    /** 
     * Méthode qui enregistre l'historique de l'opération fait sur le type de document OR: ORDRE DE REPARATION
     * 
     * @param string $numeroDocument numéro du document
     * @param int $typeOperationId id de l'opération effectué
     * @param string $statutOperation statut de l'opération 
     * @param string $libelleOperation libellé de l'opération
     */
    public function enregistrerOR(string $numeroDocument, int $typeOperationId, string $statutOperation, ?string $libelleOperation = null): void
    {
        $this->enregistrer($numeroDocument, $typeOperationId, 2, $statutOperation, $libelleOperation);
    }

    /** 
     * Méthode qui enregistre l'historique de l'opération fait sur le type de document FAC: FACTURE
     * 
     * @param string $numeroDocument numéro du document
     * @param int $typeOperationId id de l'opération effectué
     * @param string $statutOperation statut de l'opération 
     * @param string $libelleOperation libellé de l'opération
     */
    public function enregistrerFAC(string $numeroDocument, int $typeOperationId, string $statutOperation, ?string $libelleOperation = null): void
    {
        $this->enregistrer($numeroDocument, $typeOperationId, 3, $statutOperation, $libelleOperation);
    }

    /** 
     * Méthode qui enregistre l'historique de l'opération fait sur le type de document RI: RAPPORT INTERVENTION
     * 
     * @param string $numeroDocument numéro du document
     * @param int $typeOperationId id de l'opération effectué
     * @param string $statutOperation statut de l'opération 
     * @param string $libelleOperation libellé de l'opération
     */
    public function enregistrerRI(string $numeroDocument, int $typeOperationId, string $statutOperation, ?string $libelleOperation = null): void
    {
        $this->enregistrer($numeroDocument, $typeOperationId, 4, $statutOperation, $libelleOperation);
    }

    /** 
     * Méthode qui enregistre l'historique de l'opération fait sur le type de document TIK: DEMANDE DE SUPPORT INFORMATIQUE
     * 
     * @param string $numeroDocument numéro du document
     * @param int $typeOperationId id de l'opération effectué
     * @param string $statutOperation statut de l'opération 
     * @param string $libelleOperation libellé de l'opération
     */
    public function enregistrerTIK(string $numeroDocument, int $typeOperationId, string $statutOperation, ?string $libelleOperation = null): void
    {
        $this->enregistrer($numeroDocument, $typeOperationId, 5, $statutOperation, $libelleOperation);
    }

    /** 
     * Méthode qui enregistre l'historique de l'opération fait sur le type de document DA: DEMANDE APPROVISIONNEMENT
     * 
     * @param string $numeroDocument numéro du document
     * @param int $typeOperationId id de l'opération effectué
     * @param string $statutOperation statut de l'opération 
     * @param string $libelleOperation libellé de l'opération
     */
    public function enregistrerDA(string $numeroDocument, int $typeOperationId, string $statutOperation, ?string $libelleOperation = null): void
    {
        $this->enregistrer($numeroDocument, $typeOperationId, 6, $statutOperation, $libelleOperation);
    }

    /** 
     * Méthode qui enregistre l'historique de l'opération fait sur le type de document DOM: DEMANDE ORDRE DE MISSION
     * 
     * @param string $numeroDocument numéro du document
     * @param int $typeOperationId id de l'opération effectué
     * @param string $statutOperation statut de l'opération 
     * @param string $libelleOperation libellé de l'opération
     */
    public function enregistrerDOM(string $numeroDocument, int $typeOperationId, string $statutOperation, ?string $libelleOperation = null): void
    {
        $this->enregistrer($numeroDocument, $typeOperationId, 7, $statutOperation, $libelleOperation);
    }

    /** 
     * Méthode qui enregistre l'historique de l'opération fait sur le type de document BADM: MOUVEMENT MATERIEL BADM
     * 
     * @param string $numeroDocument numéro du document
     * @param int $typeOperationId id de l'opération effectué
     * @param string $statutOperation statut de l'opération 
     * @param string $libelleOperation libellé de l'opération
     */
    public function enregistrerBADM(string $numeroDocument, int $typeOperationId, string $statutOperation, ?string $libelleOperation = null): void
    {
        $this->enregistrer($numeroDocument, $typeOperationId, 8, $statutOperation, $libelleOperation);
    }

    /** 
     * Méthode qui enregistre l'historique de l'opération fait sur le type de document CAS: CASIER
     * 
     * @param string $numeroDocument numéro du document
     * @param int $typeOperationId id de l'opération effectué
     * @param string $statutOperation statut de l'opération 
     * @param string $libelleOperation libellé de l'opération
     */
    public function enregistrerCAS(string $numeroDocument, int $typeOperationId, string $statutOperation, ?string $libelleOperation = null): void
    {
        $this->enregistrer($numeroDocument, $typeOperationId, 9, $statutOperation, $libelleOperation);
    }
}
