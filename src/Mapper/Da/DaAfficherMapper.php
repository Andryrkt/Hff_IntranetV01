<?php

namespace App\Mapper\Da;

use App\Constants\da\RouteConstant;
use App\Controller\Traits\da\MarkupIconTrait;
use App\Dto\Da\DaAfficherDto;
use App\Entity\da\DaAfficher;
use App\Entity\da\DemandeAppro;
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

    public function map(DaAfficher $data): DaAfficherDto
    {
        $dto = new DaAfficherDto();
        $dto->id = $data->getId() ?? null;
        $dto->objet = $data->getObjetDal() ?? null;
        $dto->numDaParent = $data->getNumeroDemandeApproMere() ?? null;
        $dto->numeroDemandeAppro = $data->getNumeroDemandeAppro() ?? null;
        $dto->datype = $data->getDatypeId() ?? null;
        $dto->daTypeIcon = $this->getTypeDaIcon($dto);
        $dto->niveauUrgence = $data->getNiveauUrgence() ?? null;
        $dto->numeroFournisseur = $data->getNumeroFournisseur() ?? null;
        $dto->nomFournisseur = $data->getNomFournisseur() ?? null;
        $dto->envoyeFrn = $data->getBcEnvoyerFournisseur();
        $dto->dateFinSouhaite = $data->getDateFinSouhaite() ? $data->getDateFinSouhaite()->format('d/m/Y') : null;
        $dto->artConstp = $data->getArtConstp() ?? null;
        $dto->artRefp = $data->getArtRefp() ?? null;
        $dto->artDesi = $data->getArtDesi() ?? null;
        $dto->qteDem = $data->getQteDem() ?? null;
        $dto->qteEnAttent = $data->getQteEnAttent() ?? null;
        $dto->qteDispo = $data->getQteDispo() ?? null;
        $dto->qteLivrer = $data->getQteLivrer() ?? null;
        $dto->dateLivraisonPrevue = $data->getDateLivraisonPrevue() ? $data->getDateLivraisonPrevue()->format('d/m/Y') : null;
        $dto->joursDispo = $data->getJoursDispo() ?? null;
        $dto->demandeur = $data->getDemandeur() ?? null;

        // OR
        $dto->numeroOr = $data->getNumeroOr() ?? null;
        $dto->datePlannigOr = $data->getDatePlannigOr() ? $data->getDatePlannigOr()->format('d/m/Y') : null;
        // Cde
        $dto->numeroCde = $data->getNumeroCde() ?? null;
        $dto->positionBc = $data->getPositionBc() ?? null;
        $dto->statutCde = $data->getStatutCde() ?? null;
        // DAL
        $dto->statutDal = $data->getStatutDal() ?? null;
        //DIT
        $dto->numeroDemandeDit = $data->getNumeroDemandeDit() ?? null;

        // Html
        $dto->urlDetail = $this->generateUrlDetail($dto);
        $dto->tdNumCdeAttributes = $this->prepareTdNumCdeAttributes($dto);
        $dto->styleClickableCell = $dto->envoyeFrn ? 'clickable-td' : '';
        $dto->tdCheckboxAttributes = $this->getCheckboxAttributes($dto);
        $dto->aDtLivPrevAttributes = $this->getADtLivPrevAttributes($dto);

        return $dto;
    }

    public function mapList(array $data): array
    {
        $datasPrepared = [];
        foreach ($data as $item) {
            $datasPrepared[] = $this->map($item);
        }
        return $datasPrepared;
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

    private function generateUrlDetail(DaAfficherDto $dto): string
    {
        if (array_key_exists($dto->datype, RouteConstant::ROUTE_DETAIL_NAMES)) {
            return '#';
        }

        return $this->router->generate(RouteConstant::ROUTE_DETAIL_NAMES[$dto->datype], ['id' => $dto->id]);
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

    private function getTypeDaIcon(DaAfficherDto $dto): string
    {
        $daIcons = [
            DemandeAppro::TYPE_DA_AVEC_DIT         => $this->getIconDaAvecDIT(),
            DemandeAppro::TYPE_DA_DIRECT           => $this->getIconDaDirect(),
            DemandeAppro::TYPE_DA_REAPPRO_MENSUEL  => $this->getIconDaReapproMensuel(),
            DemandeAppro::TYPE_DA_REAPPRO_PONCTUEL => $this->getIconDaReapproPonctuel(),
        ];

        $safeIconBan = new Markup('<i class="fas fa-ban text-muted"></i>', 'UTF-8');
        return $daIcons[$dto->datype] ?? $safeIconBan;
    }
}
