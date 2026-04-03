<?php

namespace App\Mapper\Da;

use App\Constants\da\RouteConstant;
use App\Constants\da\StatutBcConstant;
use App\Constants\da\StatutDaConstant;
use App\Controller\Traits\da\MarkupIconTrait;
use App\Dto\Da\DaAfficherDto;
use App\Entity\da\DaAfficher;
use App\Entity\da\DemandeAppro;
use App\Service\da\PermissionDaService;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Markup;

class DaAfficherMapper
{
    use MarkupIconTrait;

    private UrlGeneratorInterface $router;
    private PermissionDaService $permissionDaService;

    public function __construct(UrlGeneratorInterface $router)
    {
        $this->router = $router;
        $this->permissionDaService = new PermissionDaService();
    }

    public function map(DaAfficher $data, array $options = []): DaAfficherDto
    {
        $estAdmin   = $options['estAdmin'] ?? false;
        $estAppro   = $options['estAppro'] ?? false;
        $estCreateur = $options['estCreateur'] ?? false;
        $estAtelier = $options['estAtelier'] ?? false;
        $codeAgenceUser = $options['codeAgenceUser'] ?? null;
        $codeServiceUser = $options['codeServiceUser'] ?? null;

        $dto = new DaAfficherDto();
        $dto->id = $data->getId();
        $dto->objet = $data->getObjetDal();
        $dto->numeroLigne = $data->getNumeroLigne();
        $dto->numDaParent = $data->getNumeroDemandeApproMere();
        $dto->numeroDemandeAppro = $data->getNumeroDemandeAppro();
        $dto->dateFinSouhaite = $data->getDateFinSouhaite() ? $data->getDateFinSouhaite()->format('d/m/Y') : 'N/A';
        $dto->artConstp = $data->getArtConstp();
        $dto->artRefp = $data->getArtRefp();
        $dto->artDesi = $data->getArtDesi();
        $dto->dateLivraisonPrevue = $data->getDateLivraisonPrevue() ? $data->getDateLivraisonPrevue()->format('d/m/Y') : 'N/A';
        $dto->estDalr = $data->getEstDalr();
        $dto->estAppro = $estAppro;

        // type de DA
        $dto->datype = $data->getDatypeId();
        $dto->daViaOR = $dto->datype === DemandeAppro::TYPE_DA_AVEC_DIT;
        $dto->daDirect = $dto->datype === DemandeAppro::TYPE_DA_DIRECT;
        $dto->daReappro = $dto->datype === DemandeAppro::TYPE_DA_REAPPRO_MENSUEL;
        $dto->daParent = $dto->datype === DemandeAppro::TYPE_DA_PARENT;

        // Icônes
        $dto->daTypeIcon = $this->getTypeDaIcon($dto->datype);
        $dto->allIcons = $this->getAllIcons();
        $safeIconSuccess = new Markup('<i class="fas fa-check text-success"></i>', 'UTF-8');
        $safeIconXmark   = new Markup('<i class="fas fa-xmark text-danger"></i>', 'UTF-8');
        $safeIconBan     = new Markup('<i class="fas fa-ban text-muted"></i>', 'UTF-8');
        $dto->estFicheTechnique = $data->getEstFicheTechnique() ? $safeIconSuccess : $safeIconXmark;

        // Jours dispo
        $dto->joursDispo = $data->getJoursDispo();
        $dto->styleJoursDispo = ($dto->joursDispo < 0) ? 'text-danger' : '';

        // demandeur
        $dto->demandeur = $data->getDemandeur();
        $dto->dateDemande = $data->getDateDemande() ? $data->getDateDemande()->format('d/m/Y') : null;

        // Consultateur
        $dto->codeAgenceUser = $codeAgenceUser;
        $dto->codeServiceUser = $codeServiceUser;

        // QTE
        $dto->qteDem = $data->getQteDem() ?: '-';
        $dto->qteEnAttent = $data->getQteEnAttent() ?: '-';
        $dto->qteDispo = $data->getQteDispo() ?: '-';
        $dto->qteLivrer = $data->getQteLivrer() ?: '-';

        // Fournisseur
        $dto->numeroFournisseur = $data->getNumeroFournisseur();
        $dto->nomFournisseur = $data->getNomFournisseur();
        $dto->envoyeFrn = $data->getStatutCde() === StatutBcConstant::STATUT_BC_ENVOYE_AU_FOURNISSEUR;

        // OR
        $dto->numeroOr = $dto->daDirect || $dto->daParent ? null : $data->getNumeroOr();
        $dto->datePlannigOr = $dto->daViaOR ? ($data->getDatePlannigOr() ? $data->getDatePlannigOr()->format('d/m/Y') : null) : $safeIconBan;
        $dto->statutOr = $data->getStatutOr();
        if ($dto->datype == DemandeAppro::TYPE_DA_AVEC_DIT && !empty($dto->statutOr)) {
            $dto->statutOr = "OR - " . $dto->statutOr;
        }

        // Cde
        $dto->numeroCde = $data->getNumeroCde();
        $dto->positionBc = $data->getNumeroLigne();
        $dto->statutCde = !$estAppro && in_array($data->getStatutCde(), StatutBcConstant::STATUT_BC_EN_COURS) ? StatutBcConstant::BC_EN_COURS : $data->getStatutCde();

        // DAL
        $dto->statutDal = !$estAppro && in_array($data->getStatutDal(), StatutDaConstant::STATUT_TRAITEMENT_APPRO) ? StatutDaConstant::TRAITEMENT_APPRO : $data->getStatutDal();
        $dto->verouille = $dto->datype === DemandeAppro::TYPE_DA_REAPPRO_PONCTUEL ? true : $this->permissionDaService->estDaVerrouillee(
            $dto->statutDal,
            $dto->statutOr,
            $estAdmin,
            $estAppro,
            $estAtelier,
            $estCreateur
        );

        // DIT
        $dto->numeroDemandeDit = $dto->daViaOR ? $data->getNumeroDemandeDit() : $safeIconBan;
        $dto->niveauUrgence = $dto->daReappro ? $safeIconBan : $data->getNiveauUrgence();

        // Calculs de droits & URLs (Actions & URLs)
        $this->computeRightsAndUrls($dto, $data, $safeIconBan, $estAdmin, $estAppro, $estAtelier);

        // HTML Attributes
        $dto->tdNumCdeAttributes = $this->prepareTdNumCdeAttributes($dto);
        $dto->styleClickableCell = $dto->envoyeFrn ? 'clickable-td' : '';
        $dto->tdCheckboxAttributes = $this->getCheckboxAttributes($dto);
        $dto->aDtLivPrevAttributes = $this->getADtLivPrevAttributes($dto);
        $dto->aArtDesiAttributes = $this->getAArtDesiAttributes($dto);

        return $dto;
    }

    public function mapList(array $data, array $options = []): array
    {
        $datasPrepared = [];
        foreach ($data as $item) {
            $datasPrepared[] = $this->map($item, $options);
        }
        return $datasPrepared;
    }

    private function computeRightsAndUrls(DaAfficherDto $dto, DaAfficher $item, Markup $safeIconBan,  bool $estAdmin, bool $estAppro, bool $estAtelier): void
    {
        $dto->ajouterDA = $dto->daViaOR && ($estAtelier || $estAdmin);
        $statutDASupprimable = [StatutDaConstant::STATUT_SOUMIS_APPRO, StatutDaConstant::STATUT_SOUMIS_ATE, StatutDaConstant::STATUT_VALIDE];
        $dto->supprimable = ($estAppro || $estAtelier || $estAdmin) && in_array($dto->statutDal, $statutDASupprimable) && ($dto->daViaOR || $dto->daDirect);
        $dto->demandeDevis = ($estAppro || $estAdmin) && $dto->statutDal === StatutDaConstant::STATUT_SOUMIS_APPRO && ($dto->daViaOR || $dto->daDirect);
        $dto->centrale = (!$dto->daViaOR) ? $item->getDesiCentrale() : $safeIconBan;
        $dto->statutValide = $item->getStatutDal() === StatutDaConstant::STATUT_VALIDE;

        $parametres = [
            'daId'           => $item->getDemandeAppro() ? ['id' => $item->getDemandeAppro()->getId()] : [],
            'daParentId'     => $item->getDemandeApproParent() ? ['id' => $item->getDemandeApproParent()->getId()] : [],
            'daId-0-ditId'   => $item->getDit() ? ['daId' => 0, 'ditId' => $item->getDit()->getId()] : [],
            'daId-ditId'     => $item->getDemandeAppro() && $item->getDit() ? ['daId' => $item->getDemandeAppro()->getId(), 'ditId' => $item->getDit()->getId()] : [],
            'numDa-numLigne' => ['numDa' => $item->getNumeroDemandeAppro(), 'ligne' => $item->getNumeroLigne()],
        ];

        // URLs optimisées : On ne génère que ce qui est nécessaire
        $daEntity = $item->getDemandeAppro();
        $paramsDa = $daEntity ? ['id' => $daEntity->getId()] : null;

        // URL Detail
        if ($paramsDa) {
            $dto->urlDetail = isset(RouteConstant::DETAIL[$dto->datype]) ? $this->router->generate(RouteConstant::DETAIL[$dto->datype], $paramsDa) : '#';
        } else {
            $dto->urlDetail = '#';
        }

        // URL Creation (seulement si nécessaire)
        if ($dto->ajouterDA) {
            $ditEntity = $item->getDit();
            $paramsDit = $ditEntity ? ['daId' => 0, 'ditId' => $ditEntity->getId()] : null;
            $dto->urlCreation = ($paramsDit && isset(RouteConstant::CREATION[$dto->datype])) ? $this->router->generate(RouteConstant::CREATION[$dto->datype], $paramsDit) : '#';
        } else {
            $dto->urlCreation = '#';
        }

        // URL Delete (seulement si nécessaire)
        if ($dto->supprimable) {
            $dto->urlDelete = isset(RouteConstant::DELETE[$dto->datype]) ? $this->router->generate(RouteConstant::DELETE[$dto->datype], ['numDa' => $dto->numeroDemandeAppro, 'ligne' => $dto->positionBc]) : '#';
        } else {
            $dto->urlDelete = '#';
        }

        if ($dto->statutDal === StatutDaConstant::STATUT_EN_COURS_CREATION && isset(RouteConstant::CREATION[$dto->datype])) {
            $params = ($dto->datype == DemandeAppro::TYPE_DA_AVEC_DIT) ? $parametres['daId-ditId']
                : (($dto->datype == DemandeAppro::TYPE_DA_PARENT) ? $parametres['daParentId'] : $parametres['daId']);
            $dto->urlProposition = $this->router->generate(RouteConstant::CREATION[$dto->datype], $params);
        } else {
            $params = ($dto->datype == DemandeAppro::TYPE_DA_PARENT) ? $parametres['daParentId'] : $parametres['daId'];
            $dto->urlProposition = isset(RouteConstant::PROPOSITION[$dto->datype]) ? $this->router->generate(RouteConstant::PROPOSITION[$dto->datype], $params) : '#';
        }

        // URL Demande Devis
        $dto->urlDemandeDevis = ($item->getDemandeAppro())  ? $this->router->generate('da_demande_devis_en_cours', $paramsDa) : '#';
    }

    private function prepareTdNumCdeAttributes(DaAfficherDto $dto): array
    {
        if (empty($dto->numeroCde)) {
            return ['class' => 'text-center'];
        }

        return [
            'class'             => 'text-center commande-cellule commande',
            'data-commande-id'  => $dto->numeroCde,
            'data-statut-bc'    => $dto->statutCde,
            'data-position-cde' => $dto->positionBc,
            'data-type-da'      => $dto->datype,
            'data-num-da'       => $dto->numeroDemandeAppro,
            'data-num-or'       => $dto->numeroOr,
        ];
    }

    private function getCheckboxAttributes(DaAfficherDto $dto): array
    {
        return [
            'class' => 'modern-checkbox',
            'type' => 'checkbox',
            'value' => $dto->id,
            'data-numero-demande-appro' => $dto->numeroDemandeAppro,
            'data-numero-ligne' => $dto->positionBc,
        ];
    }

    private function getADtLivPrevAttributes(DaAfficherDto $dto): array
    {
        return [
            'href' => '#',
            "data-bs-toggle" => "modal",
            "data-bs-target" => "#dateLivraison",
            "data-numero-cde" => $dto->numeroCde,
            "data-date-actuelle" => $dto->dateLivraisonPrevue ?? '',
        ];
    }

    private function getAArtDesiAttributes(DaAfficherDto $dto): array
    {
        return [
            'href'              => $dto->urlProposition,
            'class'             => 'designation-btn',
            'data-numero-ligne' => $dto->numeroLigne,
            'data-numero-da'    => $dto->numeroDemandeAppro,
            'target'            => $dto->urlProposition === '#' ? '_self' : '_blank'
        ];
    }

    private function getTypeDaIcon($typeId): string
    {
        $daIcons = [
            DemandeAppro::TYPE_DA_AVEC_DIT         => $this->getIconDaAvecDIT(),
            DemandeAppro::TYPE_DA_DIRECT           => $this->getIconDaDirect(),
            DemandeAppro::TYPE_DA_REAPPRO_MENSUEL  => $this->getIconDaReapproMensuel(),
            DemandeAppro::TYPE_DA_REAPPRO_PONCTUEL => $this->getIconDaReapproPonctuel(),
            DemandeAppro::TYPE_DA_PARENT           => ''
        ];

        return $daIcons[$typeId] ?? (string) new Markup('<i class="fas fa-ban text-muted"></i>', 'UTF-8');
    }
}
