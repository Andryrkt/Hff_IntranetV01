<?php

namespace App\Mapper\Da;

use App\Constants\da\RouteConstant;
use App\Controller\Traits\da\MarkupIconTrait;
use App\Dto\Da\DaAfficherDto;
use App\Entity\da\DaAfficher;
use App\Entity\da\DemandeAppro;
use App\Entity\da\DaSoumissionBc;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Markup;

class DaAfficherMapper
{
    use MarkupIconTrait;

    private UrlGeneratorInterface $router;

    public function __construct(UrlGeneratorInterface $router)
    {
        $this->router = $router;
    }

    public function map(DaAfficher $data, array $options = []): DaAfficherDto
    {
        $estAdmin   = $options['estAdmin'] ?? false;
        $estAppro   = $options['estAppro'] ?? false;
        $estAtelier = $options['estAtelier'] ?? false;

        $dto = new DaAfficherDto();
        $dto->id = $data->getId();
        $dto->objet = $data->getObjetDal();
        $dto->numDaParent = $data->getNumeroDemandeApproMere();
        $dto->numeroDemandeAppro = $data->getNumeroDemandeAppro();
        $dto->datype = $data->getDatypeId();
        
        // Icônes
        $dto->daTypeIcon = $this->getTypeDaIcon($dto->datype);
        
        $dto->niveauUrgence = $data->getNiveauUrgence();
        $dto->numeroFournisseur = $data->getNumeroFournisseur();
        $dto->nomFournisseur = $data->getNomFournisseur();
        $dto->envoyeFrn = $data->getStatutCde() === DaSoumissionBc::STATUT_BC_ENVOYE_AU_FOURNISSEUR;
        $dto->dateFinSouhaite = $data->getDateFinSouhaite() ? $data->getDateFinSouhaite()->format('d/m/Y') : null;
        $dto->artConstp = $data->getArtConstp();
        $dto->artRefp = $data->getArtRefp();
        $dto->artDesi = $data->getArtDesi();
        $dto->qteDem = $data->getQteDem() ?: '-';
        $dto->qteEnAttent = $data->getQteEnAttent() ?: '-';
        $dto->qteDispo = $data->getQteDispo() ?: '-';
        $dto->qteLivrer = $data->getQteLivrer() ?: '-';
        $dto->dateLivraisonPrevue = $data->getDateLivraisonPrevue() ? $data->getDateLivraisonPrevue()->format('d/m/Y') : 'N/A';
        $dto->joursDispo = $data->getJoursDispo();
        $dto->styleJoursDispo = ($dto->joursDispo < 0) ? 'text-danger' : '';
        $dto->demandeur = $data->getDemandeur();
        $dto->dateDemande = $data->getDateDemande()->format('d/m/Y');
        $dto->estDalr = $data->getEstDalr();
        $dto->verouille = $data->getVerouille();
        
        $safeIconSuccess = new Markup('<i class="fas fa-check text-success"></i>', 'UTF-8');
        $safeIconXmark   = new Markup('<i class="fas fa-xmark text-danger"></i>', 'UTF-8');
        $dto->estFicheTechnique = $data->getEstFicheTechnique() ? $safeIconSuccess : $safeIconXmark;

        // OR
        $dto->numeroOr = $data->getNumeroOr();
        $dto->datePlannigOr = $data->getDatePlannigOr() ? $data->getDatePlannigOr()->format('d/m/Y') : null;
        $dto->statutOr = $data->getStatutOr();
        if ($dto->datype == DemandeAppro::TYPE_DA_AVEC_DIT && !empty($dto->statutOr)) {
            $dto->statutOr = "OR - " . $dto->statutOr;
        }

        // Cde
        $dto->numeroCde = $data->getNumeroCde();
        $dto->positionBc = $data->getNumeroLigne();
        $dto->statutCde = $data->getStatutCde();
        
        // DAL
        $dto->statutDal = $data->getStatutDal();
        
        // DIT
        $dto->numeroDemandeDit = $data->getNumeroDemandeDit();

        // Calculs de droits & URLs
        $this->computeRightsAndUrls($dto, $data, $estAdmin, $estAppro, $estAtelier);

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

    private function computeRightsAndUrls(DaAfficherDto $dto, DaAfficher $item, bool $estAdmin, bool $estAppro, bool $estAtelier): void
    {
        $daTypeId = (int) $dto->datype;
        $daViaOR = $daTypeId === DemandeAppro::TYPE_DA_AVEC_DIT;
        $daDirect = $daTypeId === DemandeAppro::TYPE_DA_DIRECT;

        $dto->ajouterDA = $daViaOR && ($estAtelier || $estAdmin);
        $statutDASupprimable = [DemandeAppro::STATUT_SOUMIS_APPRO, DemandeAppro::STATUT_SOUMIS_ATE, DemandeAppro::STATUT_VALIDE];
        $dto->supprimable = ($estAppro || $estAtelier || $estAdmin) && in_array($dto->statutDal, $statutDASupprimable) && ($daViaOR || $daDirect);
        $dto->demandeDevis = ($estAppro || $estAdmin) && $dto->statutDal === DemandeAppro::STATUT_SOUMIS_APPRO && ($daViaOR || $daDirect);
        $dto->centrale = (!$daViaOR) ? $item->getDesiCentrale() : '';

        // URLs optimisées : On ne génère que ce qui est nécessaire
        $daEntity = $item->getDemandeAppro();
        $paramsDa = $daEntity ? ['id' => $daEntity->getId()] : null;
        
        // URL Detail
        if ($paramsDa) {
            $detailRoutes = [
                DemandeAppro::TYPE_DA_AVEC_DIT         => 'da_detail_avec_dit',
                DemandeAppro::TYPE_DA_DIRECT           => 'da_detail_direct',
                DemandeAppro::TYPE_DA_REAPPRO_MENSUEL  => 'da_detail_reappro',
                DemandeAppro::TYPE_DA_REAPPRO_PONCTUEL => 'da_detail_reappro',
            ];
            $dto->urlDetail = isset($detailRoutes[$daTypeId]) ? $this->router->generate($detailRoutes[$daTypeId], $paramsDa) : '#';
        } else {
            $dto->urlDetail = '#';
        }

        // URL Creation (seulement si nécessaire)
        if ($dto->ajouterDA) {
            $ditEntity = $item->getDit();
            $paramsDit = $ditEntity ? ['daId' => 0, 'ditId' => $ditEntity->getId()] : null;
            $creationRoutes = [
                DemandeAppro::TYPE_DA_AVEC_DIT        => 'da_new_avec_dit',
                DemandeAppro::TYPE_DA_REAPPRO_MENSUEL => 'da_new_reappro_mensuel',
                DemandeAppro::TYPE_DA_PARENT          => 'da_new_achat'
            ];
            $dto->urlCreation = ($paramsDit && isset($creationRoutes[$daTypeId])) ? $this->router->generate($creationRoutes[$daTypeId], $paramsDit) : '#';
        } else {
            $dto->urlCreation = '#';
        }

        // URL Delete (seulement si nécessaire)
        if ($dto->supprimable) {
            $dto->urlDelete = $this->router->generate('da_delete_line_avec_dit', ['numDa' => $dto->numeroDemandeAppro, 'ligne' => $dto->positionBc]);
        } else {
            $dto->urlDelete = '#';
        }

        // URL Demande Devis
        $dto->urlDemandeDevis = ($dto->demandeDevis && $paramsDa) ? $this->router->generate('da_demande_devis_en_cours', $paramsDa) : '#';
    }

    private function prepareTdNumCdeAttributes(DaAfficherDto $dto): array
    {
        if (empty($dto->numeroCde)) {
            return ['class' => 'text-center'];
        }

        return [
            'class'             => 'text-center commande-cellule commande',
            'data-commande-id'  => $dto->numeroCde,
            'data-num-da'       => $dto->numeroDemandeAppro,
            'data-num-or'       => $dto->numeroOr,
            'data-statut-bc'    => $dto->statutCde,
            'data-position-cde' => $dto->positionBc,
            'data-type-da'      => $dto->datype,
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
            'href'              => $dto->urlDetail,
            'class'             => 'designation-btn',
            'data-numero-ligne' => $dto->positionBc,
            'data-numero-da'    => $dto->numeroDemandeAppro,
            'target'            => '_blank'
        ];
    }

    private function getTypeDaIcon($typeId): string
    {
        $daIcons = [
            DemandeAppro::TYPE_DA_AVEC_DIT         => $this->getIconDaAvecDIT(),
            DemandeAppro::TYPE_DA_DIRECT           => $this->getIconDaDirect(),
            DemandeAppro::TYPE_DA_REAPPRO_MENSUEL  => $this->getIconDaReapproMensuel(),
            DemandeAppro::TYPE_DA_REAPPRO_PONCTUEL => $this->getIconDaReapproPonctuel(),
        ];

        return $daIcons[$typeId] ?? (string) new Markup('<i class="fas fa-ban text-muted"></i>', 'UTF-8');
    }

    public function getIcons(): array
    {
        return $this->getAllIcons();
    }
}
