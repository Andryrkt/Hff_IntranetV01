<?php

namespace App\Service\magasin\cdeFrn;

use App\Service\genererPdf\GeneratePdf;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\cde\CdefnrSoumisAValidation;
use App\Service\fichier\TraitementDeFichier;
use App\Mapper\Magasin\CdeFrn\CdeFrnSoumissionMapper;
use App\Dto\Magasin\cdeFrn\CdeFrnSoumisAValidationDTO;
use App\Repository\cde\CdefnrSoumisAValidationRepository;
use App\Model\magasin\cdeFrn\soumission\CdeSoumissionModel;
use App\Service\genererPdf\magasin\cdeFrn\GeneratePdfCdeMagasin;
use App\Service\historiqueOperation\HistoriqueOperationCDEFNRService;
use App\Factory\magasin\cdeFrn\soumission\CdeFrnSoumisAValidationFactory;

final class CdeFrnSoumissionService
{
    private EntityManagerInterface $em;
    private CdeFrnSoumissionStore $store;
    private CdeFrnSoumissionMapper $mapper;
    private CdeFrnSoumisAValidationFactory $factory;
    private CdeSoumissionModel $cdeSoumissionModel;
    private HistoriqueOperationCDEFNRService $historiqueOperation;
    private CdefnrSoumisAValidationRepository $repo;

    public function __construct(EntityManagerInterface $em, CdeFrnSoumissionStore $store, CdeFrnSoumissionMapper $mapper, CdeFrnSoumisAValidationFactory $factory)
    {
        $this->em                  = $em;
        $this->store               = $store;
        $this->mapper              = $mapper;
        $this->factory             = $factory;
        $this->cdeSoumissionModel  = new CdeSoumissionModel();
        $this->historiqueOperation = new HistoriqueOperationCDEFNRService($em);
        $this->repo                = $em->getRepository(CdefnrSoumisAValidation::class);
    }

    /**
     * Générer un PDF pour un numéro de commande donné et retourne un DTO contenant les informations de la commande.
     *
     * @param string $numCde      Le numéro de commande.
     * @param string $userMail    L'email de l'utilisateur.
     * @param string $codeSociete Le code de la société.
     *
     * @return CdeFrnSoumisAValidationDTO|null Le DTO contenant les informations de la commande.
     */
    public function generatePdfForSubmission(string $numCde, string $userMail, string $codeSociete): ?CdeFrnSoumisAValidationDTO
    {
        // 1. Récupération des données du document
        $commandeSoumissionDto = $this->cdeSoumissionModel->findInfoCommande($numCde, $userMail, $codeSociete);

        if ($commandeSoumissionDto === null) return null;

        // 2. Création du DTO pour la soumission à validation
        $cdeFrnSoumisAValidationDTO = $this->factory->hydrate($commandeSoumissionDto);

        // 3. Génération du PDF
        (new GeneratePdfCdeMagasin($commandeSoumissionDto))->generate($cdeFrnSoumisAValidationDTO->urlPDFLong);

        // 4. Sauvegarder token
        $cdeFrnSoumisAValidationDTO->token = $this->store->save($userMail, $cdeFrnSoumisAValidationDTO);

        return $cdeFrnSoumisAValidationDTO;
    }

    /**
     * @param \Symfony\Component\HttpFoundation\File\UploadedFile[] $piecesJointes PDF à fusionner après le PDF généré
     */
    public function soumettre(string $userMail, ?string $numCdeSaisi, ?string $token, array $piecesJointes = [])
    {
        $numCdeSaisi = trim((string) $numCdeSaisi);
        try {
            // 1. Obtenir données stockés en cache (données du DTO envoyé depuis l'API)
            $dto = $token ? $this->store->get($userMail, $token) : null;

            if ($dto === null) throw new \Exception('La génération du PDF a expiré ou est introuvable. Veuillez régénérer le PDF.');

            if ($dto->numCde !== $numCdeSaisi) throw new \Exception('Le numéro de commande a changé depuis la génération. Veuillez régénérer le PDF.');

            if (!file_exists($dto->urlPDFLong)) throw new \Exception("Le fichier PDF n’a pas été trouvé. Veuillez régénérer le PDF.");

            // Vérifier si une soumission existe déjà
            $lastVersion = $this->repo->findNumeroVersionMax($numCdeSaisi);
            if ($lastVersion && $lastVersion === $dto->numVersion) throw new \Exception("Ce document a déjà été soumis. Veuillez régénérer le PDF si vous voulez quand même le soumettre.");

            // Fusionner les pièces jointes après le PDF généré
            $pdfADeposerDW = $this->fusionnerPiecesJointes($dto->urlPDFLong, $piecesJointes);

            // 2. Copier le fichier PDF dans DocuWare (dépôt de fichier dans DocuWare)
            if (GeneratePdf::copyToDWCdeFnrSoumis($pdfADeposerDW, $dto->numCde)) {
                $dto->pdfDeposerDw = true;
                $dto->dateDepotDw  = new \DateTime("now", new \DateTimeZone("Indian/Antananarivo"));
            }

            // 3. Sauvegarde des données dans la base de données
            $cdeFrnSoumisAValidation = $this->mapper->toEntityCdeFrnSoumission($dto);
            $cdeFrnSoumisAValidationLignes = $this->mapper->toEntityCdeFrnLignesSoumission($dto);

            foreach ($cdeFrnSoumisAValidationLignes as $cdeFrnSoumisAValidationLigne) {
                $this->em->persist($cdeFrnSoumisAValidationLigne);
            }

            $this->em->persist($cdeFrnSoumisAValidation);
            $this->em->flush();

            // 4. Suppression des données stockées en cache
            $this->store->discard($userMail, $token);

            // 5. Enregistrement de l'opération + Notification
            $this->historiqueOperation->sendNotificationSoumission('Votre demande a été enregistrée', $numCdeSaisi, 'profil_acceuil', true);
        } catch (\Throwable $th) {
            // 6. Enregistrement de l'opération en cas d'erreur
            $this->historiqueOperation->sendNotificationSoumission('Echec lors de la soumission : ' . $th->getMessage(), $numCdeSaisi, 'generer_commande_fournisseur');
        }
    }

    /**
     * Enregistre les pièces jointes (noms uniques) à côté du PDF généré puis crée le PDF fusionné.
     *
     * @return string chemin du PDF à déposer (l'original si aucune pièce jointe)
     */
    private function fusionnerPiecesJointes(string $pdfGenere, array $piecesJointes): string
    {
        if (!$piecesJointes) return $pdfGenere;

        $dossier    = dirname($pdfGenere);
        $fichiers   = [$pdfGenere];
        $traitement = new TraitementDeFichier();

        foreach ($piecesJointes as $piece) {
            $nom = pathinfo($piece->getClientOriginalName(), PATHINFO_FILENAME) . '_' . uniqid() . '.pdf';
            $traitement->upload($piece, $dossier, $nom);
            $fichiers[] = "$dossier/$nom";
        }

        $pdfFusionne = $dossier . '/' . pathinfo($pdfGenere, PATHINFO_FILENAME) . '_fusionne.pdf';
        try {
            $traitement->fusionFichers($fichiers, $pdfFusionne);
        } catch (\Throwable $th) {
            throw new \Exception("Impossible de fusionner les pièces jointes : " . $th->getMessage());
        }

        return $pdfFusionne;
    }
}
