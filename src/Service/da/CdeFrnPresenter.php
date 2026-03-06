<?php

namespace App\Service\da;

use App\Entity\da\DaAfficher;
use App\Entity\da\DemandeAppro;
use App\Entity\da\DaSoumissionBc;
use App\Controller\Traits\da\MarkupIconTrait;
use App\Controller\Traits\da\StatutBcTrait;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Markup;
use DateTime;

class CdeFrnPresenter
{
    use MarkupIconTrait;
    use StatutBcTrait;

    private UrlGeneratorInterface $router;

    public function __construct(UrlGeneratorInterface $router)
    {
        $this->router = $router;
        $this->initStatutBcTrait();
    }

    /** 
     * Fonction pour préparer les données à afficher dans Twig 
     *  @param DaAfficher[] $data données avant préparation
     **/
    public function present(array $data): array
    {
        $datasPrepared = [];

        $daType = [
            DemandeAppro::TYPE_DA_AVEC_DIT         => $this->getIconDaAvecDIT(),
            DemandeAppro::TYPE_DA_DIRECT           => $this->getIconDaDirect(),
            DemandeAppro::TYPE_DA_REAPPRO_MENSUEL  => $this->getIconDaReapproMensuel(),
            DemandeAppro::TYPE_DA_REAPPRO_PONCTUEL => $this->getIconDaReapproPonctuel(),
        ];

        $routeDetailName = [
            DemandeAppro::TYPE_DA_DIRECT           => 'da_detail_direct',
            DemandeAppro::TYPE_DA_AVEC_DIT         => 'da_detail_avec_dit',
            DemandeAppro::TYPE_DA_REAPPRO_MENSUEL  => 'da_detail_reappro',
            DemandeAppro::TYPE_DA_REAPPRO_PONCTUEL => 'da_detail_reappro',
        ];

        $safeIconBan = new Markup('<i class="fas fa-ban text-muted"></i>', 'UTF-8');

        foreach ($data as $item) {
            if (!$item instanceof DaAfficher) {
                continue;
            }

            // Variables à employer
            $daDirect = $item->getDaTypeId() == DemandeAppro::TYPE_DA_DIRECT;
            $daViaOR = $item->getDaTypeId() == DemandeAppro::TYPE_DA_AVEC_DIT;
            $envoyeFrn = $item->getStatutCde() === DaSoumissionBc::STATUT_BC_ENVOYE_AU_FOURNISSEUR;

            // Si numeroCde est vide ou null, on met un '-'
            $numeroCde = !empty($item->getNumeroCde()) ? $item->getNumeroCde() : '-';

            // Préparer les classes et attributs pour le <td> du numéro cde
            if (!empty($item->getNumeroCde())) {
                $tdNumCdeAttributes = [
                    'class'             => 'text-center commande-cellule commande',
                    'data-commande-id'  => $item->getNumeroCde(),
                    'data-num-da'       => $item->getNumeroDemandeAppro(),
                    'data-num-or'       => $item->getNumeroOr(),
                    'data-statut-bc'    => $item->getStatutCde(),
                    'data-position-cde' => $item->getPositionBc(),
                    'data-type-da'      => $item->getDaTypeId()
                ];
            } else {
                $tdNumCdeAttributes = [
                    'class'             => 'text-center'
                ];
            }

            // Préparer les classes et attributs pour le <td> du numéro cde
            $tdCheckboxAttributes = [
                'class'                     => 'modern-checkbox',
                'type'                      => 'checkbox',
                'value'                     => $item->getId(),
                'data-numero-demande-appro' => $item->getNumeroDemandeAppro(),
                'data-numero-ligne'         => $item->getNumeroLigne(),
            ];

            // Préparer attributs pour la balise <a> de la date de livraison prévue
            $aDtLivPrevAttributes = [
                'href'               => '#',
                "data-bs-toggle"     => "modal",
                "data-bs-target"     => "#dateLivraison",
                "data-numero-cde"    => $item->getNumeroCde(),
                "data-date-actuelle" => $item->getDateLivraisonPrevue() ? $item->getDateLivraisonPrevue()->format('Y-m-d') : '',
            ];

            // Pré-calculer les styles
            $styleStatutDA = $this->styleStatutDA[$item->getStatutDal()] ?? '';
            $styleStatutBC = $this->styleStatutBC[$item->getStatutCde()] ?? '';
            $styleClickableCell = $envoyeFrn ? 'clickable-td' : '';

            // Construction d'urls
            $urlDetail = '';
            if (!empty($routeDetailName[$item->getDaTypeId()])) {
                $urlDetail = $this->router->generate(
                    $routeDetailName[$item->getDaTypeId()],
                    ['id' => $item->getDemandeAppro()->getId()]
                );
            }

            // Tout regrouper
            $datasPrepared[] = [
                'objet'                => $item->getObjetDal(),
                'urlDetail'            => $urlDetail,
                'numDaParent'          => $item->getNumeroDemandeApproMere(),
                'numeroDemandeAppro'   => $item->getNumeroDemandeAppro(),
                'datype'               => $daType[$item->getDaTypeId()] ?? $safeIconBan,
                'numeroDemandeDit'     => $item->getNumeroDemandeDit() ?? $safeIconBan,
                'niveauUrgence'        => $item->getNiveauUrgence(),
                'numeroOr'             => $daDirect ? $safeIconBan : $item->getNumeroOr(),
                'datePlannigOr'        => $daViaOR ? ($item->getDatePlannigOr() ? $item->getDatePlannigOr()->format('d/m/Y') : '') : $safeIconBan,
                'numeroFournisseur'    => $item->getNumeroFournisseur(),
                'nomFournisseur'       => $item->getNomFournisseur(),
                'numeroCde'            => $numeroCde,
                'tdNumCdeAttributes'   => $tdNumCdeAttributes,
                'styleStatutDA'        => $styleStatutDA,
                'styleStatutBC'        => $styleStatutBC,
                'statutDal'            => $item->getStatutDal(),
                'statutCde'            => $item->getStatutCde(),
                'envoyeFrn'            => $envoyeFrn,
                'styleClickableCell'   => $styleClickableCell,
                'tdCheckboxAttributes' => $tdCheckboxAttributes,
                'aDtLivPrevAttributes' => $aDtLivPrevAttributes,
                'dateFinSouhaite'      => $item->getDateFinSouhaite() && $item->getDateFinSouhaite() != new \DateTime('1900-01-01') ? $item->getDateFinSouhaite()->format('d/m/Y') : 'N/A',
                'artConstp'            => $item->getArtConstp(),
                'artRefp'              => $item->getArtRefp(),
                'artDesi'              => $item->getArtDesi(),
                'qteDem'               => $item->getQteDem() == 0 ? '-' : $item->getQteDem(),
                'qteEnAttent'          => $item->getQteEnAttent() == 0 ? '-' : $item->getQteEnAttent(),
                'qteDispo'             => $item->getQteDispo() == 0 ? '-' : $item->getQteDispo(),
                'qteLivrer'            => $item->getQteLivrer() == 0 ? '-' : $item->getQteLivrer(),
                'dateLivraisonPrevue'  => $item->getDateLivraisonPrevue() && $item->getDateLivraisonPrevue() != new \DateTime('1900-01-01') ? $item->getDateLivraisonPrevue()->format('d/m/Y') : 'N/A',
                'joursDispo'           => $item->getJoursDispo(),
                'styleJoursDispo'      => $item->getJoursDispo() < 0 ? 'text-danger' : '',
                'demandeur'            => $item->getDemandeur(),
            ];
        }

        return $datasPrepared;
    }

    public function getIcons(): array
    {
        return $this->getAllIcons();
    }
}
