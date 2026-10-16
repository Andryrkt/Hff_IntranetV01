<?php

namespace App\Service\historiqueOperation\suivi;

/**
 * Suivi étape par étape d'une opération (OR, RI, facture, devis, BC négoce, commande fournisseur...).
 *
 * Chaque étape (fichiers joints, page de garde, conversion, fusion, DocuWare, base de données...) est
 * enregistrée dans la table historique_operation_etape avec son statut, sa durée et, en cas d'erreur,
 * le message et la trace. Toutes les étapes d'une même opération partagent le même id_suivi.
 *
 * Utilisation dans un contrôleur :
 *   $suivi = SuiviOperationService::demarrer(SuiviOperationService::TYPE_OR, $numOr);
 *   $suivi->etape('Conversion', function () use (...) { return $this->ConvertirLesPdf(...); });
 *
 * Une étape est enregistrée "EN_COURS" dès son début : si le script s'arrête brutalement (erreur fatale,
 * dépassement de temps, exit), la ligne indique l'étape où le traitement s'est arrêté.
 * L'écriture passe par la connexion DBAL (comme le faisait le journal des copies DocuWare) pour ne pas dépendre de
 * l'unité de travail Doctrine de l'appelant. Une erreur de suivi ne doit jamais bloquer l'opération.
 */
class SuiviOperationService
{
    // Atelier
    public const TYPE_DIT                 = 'DIT';
    public const TYPE_DIT_DUPLICATION     = 'DIT_DUPLICATION';
    public const TYPE_DEVIS_ATELIER       = 'DEVIS_ATELIER';
    public const TYPE_OR                  = 'OR';
    public const TYPE_RI                  = 'RI';
    public const TYPE_FACTURE             = 'FACTURE';
    public const TYPE_AC_BC               = 'AC_BC';
    // RH et matériel
    public const TYPE_DOM                 = 'DOM';
    public const TYPE_DOM_TROP_PERCU      = 'DOM_TROP_PERCU';
    public const TYPE_MUTATION            = 'MUTATION';
    public const TYPE_BADM                = 'BADM';
    public const TYPE_BADM_DUPLICATION    = 'BADM_DUPLICATION';
    public const TYPE_CASIER              = 'CASIER';
    // Appro et paiement
    public const TYPE_DA_BC               = 'DA_BC';
    public const TYPE_DA_FAC_BL           = 'DA_FAC_BL';
    public const TYPE_DA_BL_REAPPRO       = 'DA_BL_REAPPRO';
    public const TYPE_DDP                 = 'DDP';
    public const TYPE_DDP_MODIFICATION    = 'DDP_MODIFICATION';
    public const TYPE_DDP_DA              = 'DDP_DA';
    public const TYPE_DDP_REGULARISATION  = 'DDP_REGULARISATION';
    public const TYPE_CDE_FOURNISSEUR     = 'CDE_FOURNISSEUR';
    // Magasin, POL, ticketing
    public const TYPE_DEVIS_NEG_VERIFICATION_PRIX = 'DEVIS_NEG_VP';
    public const TYPE_DEVIS_NEG_VALIDATION        = 'DEVIS_NEG_VALIDATION';
    public const TYPE_DEVIS_NEG_ENVOI_CLIENT      = 'DEVIS_NEG_ENVOI_CLIENT';
    public const TYPE_BC_NEGOCE                   = 'BC_NEGOCE';
    public const TYPE_DEVIS_POL_VERIFICATION_PRIX = 'DEVIS_POL_VP';
    public const TYPE_DEVIS_POL_VALIDATION        = 'DEVIS_POL_VALIDATION';
    public const TYPE_DEVIS_POL_ENVOI_CLIENT      = 'DEVIS_POL_ENVOI_CLIENT';
    public const TYPE_BC_POL                      = 'BC_POL';
    public const TYPE_BL                          = 'BL';
    public const TYPE_DDD                         = 'DDD';
    public const TYPE_TIK                         = 'TIK';
    // copie vers DocuWare faite hors d'une opération suivie
    public const TYPE_COPIE_DOCUWARE              = 'COPIE_DOCUWARE';

    public const LIBELLES_TYPE = [
        self::TYPE_DIT                         => 'DIT',
        self::TYPE_DIT_DUPLICATION             => 'DIT (duplication)',
        self::TYPE_DEVIS_ATELIER               => 'Devis atelier',
        self::TYPE_OR                          => 'OR',
        self::TYPE_RI                          => 'RI',
        self::TYPE_FACTURE                     => 'Facture',
        self::TYPE_AC_BC                       => 'BC / accusé de réception',
        self::TYPE_DOM                         => 'Ordre de mission',
        self::TYPE_DOM_TROP_PERCU              => 'Ordre de mission (trop perçu)',
        self::TYPE_MUTATION                    => 'Mutation',
        self::TYPE_BADM                        => 'Mouvement matériel',
        self::TYPE_BADM_DUPLICATION            => 'Mouvement matériel (duplication)',
        self::TYPE_CASIER                      => 'Casier',
        self::TYPE_DA_BC                       => 'DA - bon de commande',
        self::TYPE_DA_FAC_BL                   => 'DA - facture / BL',
        self::TYPE_DA_BL_REAPPRO               => 'DA - BL réappro',
        self::TYPE_DDP                         => 'Demande de paiement',
        self::TYPE_DDP_MODIFICATION            => 'Demande de paiement (modification)',
        self::TYPE_DDP_DA                      => 'Demande de paiement DA',
        self::TYPE_DDP_REGULARISATION          => 'Demande de régularisation',
        self::TYPE_CDE_FOURNISSEUR             => 'Commande fournisseur',
        self::TYPE_DEVIS_NEG_VERIFICATION_PRIX => 'Devis négoce - vérification prix',
        self::TYPE_DEVIS_NEG_VALIDATION        => 'Devis négoce - validation',
        self::TYPE_DEVIS_NEG_ENVOI_CLIENT      => 'Devis négoce - envoi client',
        self::TYPE_BC_NEGOCE                   => 'BC négoce',
        self::TYPE_DEVIS_POL_VERIFICATION_PRIX => 'Devis POL - vérification prix',
        self::TYPE_DEVIS_POL_VALIDATION        => 'Devis POL - validation',
        self::TYPE_DEVIS_POL_ENVOI_CLIENT      => 'Devis POL - envoi client',
        self::TYPE_BC_POL                      => 'BC POL',
        self::TYPE_BL                          => 'BL',
        self::TYPE_DDD                         => 'Diagnostic pneu',
        self::TYPE_TIK                         => 'Ticket informatique',
        self::TYPE_COPIE_DOCUWARE              => 'Copie DocuWare (hors opération suivie)',
    ];

    public const STATUT_EN_COURS   = 'EN_COURS';
    public const STATUT_SUCCES     = 'SUCCES';
    public const STATUT_ECHEC      = 'ECHEC';
    public const STATUT_INTERROMPU = 'INTERROMPU';
    // l'opération a réussi mais un contrôle signale une anomalie à vérifier
    public const STATUT_ALERTE     = 'ALERTE';

    private const TABLE = 'historique_operation_etape';
    private const ERREURS_FATALES = [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR, E_RECOVERABLE_ERROR];

    private static ?self $courant = null;

    private string $idSuivi;
    private string $typeOperation;
    private ?string $numeroDocument;
    private int $ordre = 0;

    /**
     * Pile des étapes ouvertes (une étape peut en contenir d'autres)
     * @var array<int, array{id: ?int, libelle: string, debut: float, problemes: string[]}>
     */
    private array $etapesOuvertes = [];

    private function __construct(string $typeOperation, ?string $numeroDocument)
    {
        $this->idSuivi        = self::genererId();
        $this->typeOperation = $typeOperation;
        $this->numeroDocument = $numeroDocument;

        register_shutdown_function([$this, 'surArretScript']);
    }

    /**
     * Démarre le suivi d'une nouvelle opération et le rend accessible via courant()
     *
     * @param string      $typeOperation une des constantes TYPE_*
     * @param string|null $numeroDocument numéro du document s'il est déjà connu (sinon setNumeroDocument())
     */
    public static function demarrer(string $typeOperation, ?string $numeroDocument = null): self
    {
        self::$courant = new self($typeOperation, $numeroDocument);

        return self::$courant;
    }

    /**
     * Suivi de l'opération en cours dans cette requête, null si aucune opération n'est suivie
     */
    public static function courant(): ?self
    {
        return self::$courant;
    }

    /**
     * Exécute le traitement comme une étape du suivi en cours, ou simplement l'exécute si aucune opération n'est suivie.
     * Pratique dans les méthodes partagées (traits, services) appelées depuis plusieurs flux.
     *
     * @return mixed le retour du traitement
     */
    public static function suivre(string $libelle, callable $traitement)
    {
        return self::$courant !== null ? self::$courant->etape($libelle, $traitement) : $traitement();
    }

    /**
     * Enregistre le résultat d'un contrôle fait après coup (sans traitement à exécuter) :
     * SUCCES, ou ALERTE quand l'opération a réussi mais qu'une anomalie est à vérifier.
     * Ne fait rien si aucune opération n'est suivie.
     */
    public static function controle(string $libelle, bool $estAnomalie, string $message, ?string $detail = null): void
    {
        if (self::$courant !== null) {
            self::$courant->ajouterEtapeTerminee($libelle, $estAnomalie ? self::STATUT_ALERTE : self::STATUT_SUCCES, $message, $detail);
        }
    }

    /**
     * Enregistre une copie de fichier vers DocuWare comme une ligne "Copie DocuWare : <fichier>" du suivi en cours
     * (dossier, taille, tentatives, chemins et raison de l'échec). Une copie échouée fait aussi passer l'étape
     * englobante (ex. "DocuWare") en échec.
     * Une copie faite hors d'une opération suivie démarre son propre suivi de type TYPE_COPIE_DOCUWARE.
     * Appelée par GeneratePdf (copyFile / copierEtJournaliser). Ne lève jamais d'exception.
     */
    public static function copieDocuware(string $source, string $destination, bool $succes, int $nbTentatives = 1, ?string $messageErreur = null): void
    {
        try {
            $nomFichier = basename($destination);
            $suivi      = self::$courant ?? self::demarrer(self::TYPE_COPIE_DOCUWARE, $nomFichier);

            $dossier    = basename(dirname($destination));
            $taille     = is_file($source) ? number_format(filesize($source) / 1024, 0, ',', ' ') . ' Ko' : 'introuvable';
            $tentatives = $nbTentatives > 1 ? " ($nbTentatives tentatives)" : '';

            if ($succes) {
                $message = "Copié dans le dossier DocuWare « $dossier » ($taille)$tentatives";
            } else {
                $message = "Échec de la copie dans le dossier DocuWare « $dossier »$tentatives : " . ($messageErreur ?: 'raison inconnue');
                $suivi->signalerProbleme("Copie DocuWare échouée pour $nomFichier" . ($messageErreur ? " : $messageErreur" : ''));
            }

            $suivi->ajouterEtapeTerminee(
                'Copie DocuWare : ' . $nomFichier,
                $succes ? self::STATUT_SUCCES : self::STATUT_ECHEC,
                $message,
                "Fichier à copier : $source ($taille)\nDestination : $destination"
            );
        } catch (\Throwable $e) {
            error_log("[SuiviOperation] Echec de l'enregistrement de la copie DocuWare $source -> $destination : " . $e->getMessage());
        }
    }

    public function getIdSuivi(): string
    {
        return $this->idSuivi;
    }

    /**
     * Renseigne (ou corrige) le numéro du document, y compris sur les étapes déjà enregistrées
     */
    public function setNumeroDocument(string $numeroDocument): self
    {
        $this->numeroDocument = $numeroDocument;
        $this->executer(function ($connexion) use ($numeroDocument) {
            $connexion->update(self::TABLE, ['numero_document' => mb_substr($numeroDocument, 0, 50)], ['id_suivi' => $this->idSuivi]);
        });

        return $this;
    }

    /**
     * Exécute une étape et enregistre son résultat.
     * Une exception est enregistrée (message, fichier, ligne, trace) puis relancée : le comportement de l'appelant ne change pas.
     *
     * @return mixed le retour du traitement
     */
    public function etape(string $libelle, callable $traitement)
    {
        $this->ouvrirEtape($libelle);

        try {
            $resultat = $traitement();
        } catch (\Throwable $e) {
            $this->fermerEtape(self::STATUT_ECHEC, $e->getMessage(), self::detailException($e));
            throw $e;
        }

        $this->fermerEtape(self::STATUT_SUCCES);

        return $resultat;
    }

    /**
     * Signale un problème non bloquant sur l'étape en cours (ex : copie DocuWare échouée sans exception).
     * L'étape sera enregistrée en ECHEC avec ce message à sa fermeture.
     */
    public function signalerProbleme(string $message): void
    {
        if (empty($this->etapesOuvertes)) {
            return;
        }
        $this->etapesOuvertes[count($this->etapesOuvertes) - 1]['problemes'][] = $message;
    }

    /**
     * Appelé lorsque l'opération est enregistrée en échec dans l'historique des opérations
     * (message de blocage, erreur gérée...). Les étapes ouvertes sont fermées en ECHEC avec ce message ;
     * si aucune étape n'est ouverte, le blocage est enregistré comme une étape de contrôle.
     */
    public function echecOperation(string $message): void
    {
        if (empty($this->etapesOuvertes)) {
            $this->ouvrirEtape('Contrôles');
        }

        while (!empty($this->etapesOuvertes)) {
            $this->fermerEtape(self::STATUT_ECHEC, $message);
        }
    }

    /**
     * Rattache les étapes de ce suivi à la ligne de historique_operation_document créée en fin d'opération
     */
    public function lierHistorique(int $idHistorique): void
    {
        $this->executer(function ($connexion) use ($idHistorique) {
            $connexion->update(self::TABLE, ['id_historique_operation' => $idHistorique], ['id_suivi' => $this->idSuivi]);
        });
    }

    /**
     * Fonction d'arrêt : ferme les étapes restées ouvertes (erreur fatale, dépassement de temps, exit pendant une étape)
     */
    public function surArretScript(): void
    {
        if (empty($this->etapesOuvertes)) {
            return;
        }

        $erreur = error_get_last();
        if ($erreur !== null && in_array($erreur['type'], self::ERREURS_FATALES, true)) {
            $message = 'Erreur fatale : ' . $erreur['message'];
            $detail  = $erreur['file'] . ':' . $erreur['line'];
            while (!empty($this->etapesOuvertes)) {
                $this->fermerEtape(self::STATUT_ECHEC, $message, $detail);
            }
            return;
        }

        while (!empty($this->etapesOuvertes)) {
            $this->fermerEtape(self::STATUT_INTERROMPU, "Le traitement s'est arrêté pendant cette étape (redirection ou arrêt du script)");
        }
    }

    /**
     * Enregistre directement une étape déjà terminée (sans traitement à exécuter)
     */
    private function ajouterEtapeTerminee(string $libelle, string $statut, ?string $message, ?string $detail): void
    {
        $this->ordre++;
        $maintenant = self::formaterDate(microtime(true));

        $this->executer(function ($connexion) use ($libelle, $statut, $message, $detail, $maintenant) {
            $connexion->insert(self::TABLE, [
                'id_suivi'        => $this->idSuivi,
                'type_operation'  => $this->typeOperation,
                'numero_document' => $this->numeroDocument !== null ? mb_substr($this->numeroDocument, 0, 50) : null,
                'ordre'           => $this->ordre,
                'etape'           => mb_substr($libelle, 0, 100),
                'statut'          => $statut,
                'message'         => $message !== null ? mb_substr($message, 0, 1000) : null,
                'detail_erreur'   => $detail !== null ? mb_substr($detail, 0, 8000) : null,
                'date_debut'      => $maintenant,
                'date_fin'        => $maintenant,
                'utilisateur'     => self::getUtilisateur(),
                'url'             => mb_substr($_SERVER['REQUEST_URI'] ?? 'cli', 0, 500),
            ]);
        });
    }

    private function ouvrirEtape(string $libelle): void
    {
        $this->ordre++;
        $debut = microtime(true);
        $id    = null;

        $this->executer(function ($connexion) use ($libelle, $debut, &$id) {
            $connexion->insert(self::TABLE, [
                'id_suivi'        => $this->idSuivi,
                'type_operation' => $this->typeOperation,
                'numero_document' => $this->numeroDocument !== null ? mb_substr($this->numeroDocument, 0, 50) : null,
                'ordre'           => $this->ordre,
                'etape'           => mb_substr($libelle, 0, 100),
                'statut'          => self::STATUT_EN_COURS,
                'date_debut'      => self::formaterDate($debut),
                'utilisateur'     => self::getUtilisateur(),
                'url'             => mb_substr($_SERVER['REQUEST_URI'] ?? 'cli', 0, 500),
            ]);
            $id = (int) $connexion->lastInsertId();
        });

        $this->etapesOuvertes[] = ['id' => $id, 'libelle' => $libelle, 'debut' => $debut, 'problemes' => []];
    }

    private function fermerEtape(string $statut, ?string $message = null, ?string $detailErreur = null): void
    {
        $etape = array_pop($this->etapesOuvertes);
        if ($etape === null) {
            return;
        }

        // un problème signalé sans exception fait passer l'étape en échec
        if (!empty($etape['problemes'])) {
            if ($statut === self::STATUT_SUCCES) {
                $statut = self::STATUT_ECHEC;
            }
            $message = implode(' | ', array_filter(array_merge($etape['problemes'], [$message])));
        }

        if ($etape['id'] === null) {
            return; // l'insertion de début d'étape a échoué
        }

        $fin = microtime(true);
        $this->executer(function ($connexion) use ($etape, $statut, $message, $detailErreur, $fin) {
            $connexion->update(self::TABLE, [
                'statut'        => $statut,
                'message'       => $message !== null ? mb_substr(strip_tags($message), 0, 1000) : null,
                'detail_erreur' => $detailErreur !== null ? mb_substr($detailErreur, 0, 8000) : null,
                'date_fin'      => self::formaterDate($fin),
                'duree_ms'      => (int) round(($fin - $etape['debut']) * 1000),
            ], ['id' => $etape['id']]);
        });
    }

    /**
     * Exécute une écriture dans la table de suivi sans jamais propager d'erreur
     */
    private function executer(callable $ecriture): void
    {
        try {
            global $container;
            if (!$container || !$container->has('doctrine.orm.default_entity_manager')) {
                return;
            }
            $ecriture($container->get('doctrine.orm.default_entity_manager')->getConnection());
        } catch (\Throwable $e) {
            error_log("[SuiviOperation] Echec de l'enregistrement du suivi {$this->idSuivi} : " . $e->getMessage());
        }
    }

    private static function detailException(\Throwable $e): string
    {
        $detail = '';
        do {
            $detail .= get_class($e) . ' : ' . $e->getMessage() . "\n"
                . $e->getFile() . ':' . $e->getLine() . "\n"
                . $e->getTraceAsString() . "\n\n";
            $e = $e->getPrevious();
        } while ($e !== null);

        return rtrim($detail);
    }

    private static function formaterDate(float $microtime): string
    {
        $date = \DateTime::createFromFormat('U.u', sprintf('%.6F', $microtime));
        $date->setTimezone(new \DateTimeZone('Indian/Antananarivo'));

        return $date->format('Y-m-d H:i:s.v');
    }

    private static function genererId(): string
    {
        $octets = random_bytes(16);
        $octets[6] = chr(ord($octets[6]) & 0x0f | 0x40);
        $octets[8] = chr(ord($octets[8]) & 0x3f | 0x80);

        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($octets), 4));
    }

    /**
     * Nom de l'utilisateur connecté, lu dans la session sans la démarrer
     */
    private static function getUtilisateur(): ?string
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            return null;
        }

        return $_SESSION['_sf2_attributes']['user_info']['username'] ?? null;
    }
}
