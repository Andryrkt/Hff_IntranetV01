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

use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Contracts\Cache\ItemInterface;

class CdeFrnPresenter
{
    use MarkupIconTrait;
    use StatutBcTrait;

    private UrlGeneratorInterface $router;
    private FilesystemAdapter $cache;

    public function __construct(UrlGeneratorInterface $router)
    {
        $this->router = $router;
        $this->cache = new FilesystemAdapter('da_presenter', 600); // 10 minutes
        $this->initStatutBcTrait();
    }

    /** 
     * Fonction pour préparer les données à afficher dans Twig 
     *  @param DaAfficher[] $data données avant préparation
     **/
    public function present(array $data): array
    {
        $datasPrepared = [];
        
        // Cache des icônes et routes
        $daIcons = [
            DemandeAppro::TYPE_DA_AVEC_DIT         => $this->getIconDaAvecDIT(),
            DemandeAppro::TYPE_DA_DIRECT           => $this->getIconDaDirect(),
            DemandeAppro::TYPE_DA_REAPPRO_MENSUEL  => $this->getIconDaReapproMensuel(),
            DemandeAppro::TYPE_DA_REAPPRO_PONCTUEL => $this->getIconDaReapproPonctuel(),
        ];

        $routeDetailNames = [
            DemandeAppro::TYPE_DA_DIRECT           => 'da_detail_direct',
            DemandeAppro::TYPE_DA_AVEC_DIT         => 'da_detail_avec_dit',
            DemandeAppro::TYPE_DA_REAPPRO_MENSUEL  => 'da_detail_reappro',
            DemandeAppro::TYPE_DA_REAPPRO_PONCTUEL => 'da_detail_reappro',
        ];

        $safeIconBan = new Markup('<i class="fas fa-ban text-muted"></i>', 'UTF-8');

        foreach ($data as $item) {
            if (!$item instanceof DaAfficher) continue;

            // Utilisation du cache pour chaque ligne
            // La clé de cache dépend de l'ID de la ligne et de son statut (pour invalider si besoin)
            $cacheKey = 'da_row_' . $item->getId() . '_' . md5($item->getStatutDal() . $item->getStatutCde());

            $datasPrepared[] = $this->cache->get($cacheKey, function (ItemInterface $cacheItem) use ($item, $daIcons, $routeDetailNames, $safeIconBan) {
                $daTypeId = $item->getDaTypeId();
                $envoyeFrn = $item->getStatutCde() === DaSoumissionBc::STATUT_BC_ENVOYE_AU_FOURNISSEUR;
                
                $urlDetail = isset($routeDetailNames[$daTypeId]) 
                    ? $this->router->generate($routeDetailNames[$daTypeId], ['id' => $item->getDemandeAppro()->getId()])
                    : '';

                $dateFinStr = 'N/A';
                if ($item->getDateFinSouhaite() && $item->getDateFinSouhaite()->format('Y-m-d') !== '1900-01-01') {
                    $dateFinStr = $item->getDateFinSouhaite()->format('d/m/Y');
                }

                $dateLivPrevStr = 'N/A';
                if ($item->getDateLivraisonPrevue() && $item->getDateLivraisonPrevue()->format('Y-m-d') !== '1900-01-01') {
                    $dateLivPrevStr = $item->getDateLivraisonPrevue()->format('d/m/Y');
                }

                return [
                    'id'                   => $item->getId(),
                    'objet'                => $item->getObjetDal(),
                    'urlDetail'            => $urlDetail,
                    'numDaParent'          => $item->getNumeroDemandeApproMere(),
                    'numeroDemandeAppro'   => $item->getNumeroDemandeAppro(),
                    'datype'               => $daIcons[$daTypeId] ?? $safeIconBan,
                    'numeroDemandeDit'     => $item->getNumeroDemandeDit() ?? $safeIconBan,
                    'niveauUrgence'        => $item->getNiveauUrgence(),
                    'numeroOr'             => ($daTypeId == DemandeAppro::TYPE_DA_DIRECT) ? $safeIconBan : $item->getNumeroOr(),
                    'datePlannigOr'        => ($daTypeId == DemandeAppro::TYPE_DA_AVEC_DIT) ? ($item->getDatePlannigOr() ? $item->getDatePlannigOr()->format('d/m/Y') : '') : $safeIconBan,
                    'numeroFournisseur'    => $item->getNumeroFournisseur(),
                    'nomFournisseur'       => $item->getNomFournisseur(),
                    'numeroCde'            => $item->getNumeroCde() ?: '-',
                    'tdNumCdeAttributes'   => $this->prepareTdNumCdeAttributes($item),
                    'styleStatutDA'        => $this->styleStatutDA[$item->getStatutDal()] ?? '',
                    'styleStatutBC'        => $this->styleStatutBC[$item->getStatutCde()] ?? '',
                    'statutDal'            => $item->getStatutDal(),
                    'statutCde'            => $item->getStatutCde(),
                    'envoyeFrn'            => $envoyeFrn,
                    'styleClickableCell'   => $envoyeFrn ? 'clickable-td' : '',
                    'tdCheckboxAttributes' => [
                        'class' => 'modern-checkbox', 'type' => 'checkbox', 'value' => $item->getId(),
                        'data-numero-demande-appro' => $item->getNumeroDemandeAppro(),
                        'data-numero-ligne' => $item->getNumeroLigne(),
                    ],
                    'aDtLivPrevAttributes' => [
                        'href' => '#', "data-bs-toggle" => "modal", "data-bs-target" => "#dateLivraison",
                        "data-numero-cde" => $item->getNumeroCde(),
                        "data-date-actuelle" => $item->getDateLivraisonPrevue() ? $item->getDateLivraisonPrevue()->format('Y-m-d') : '',
                    ],
                    'dateFinSouhaite'      => $dateFinStr,
                    'artConstp'            => $item->getArtConstp(),
                    'artRefp'              => $item->getArtRefp(),
                    'artDesi'              => $item->getArtDesi(),
                    'qteDem'               => $item->getQteDem() ?: '-',
                    'qteEnAttent'          => $item->getQteEnAttent() ?: '-',
                    'qteDispo'             => $item->getQteDispo() ?: '-',
                    'qteLivrer'            => $item->getQteLivrer() ?: '-',
                    'dateLivraisonPrevue'  => $dateLivPrevStr,
                    'joursDispo'           => $item->getJoursDispo(),
                    'styleJoursDispo'      => ($item->getJoursDispo() < 0) ? 'text-danger' : '',
                    'demandeur'            => $item->getDemandeur(),
                ];
            });
        }

        return $datasPrepared;
    }

    private function prepareTdNumCdeAttributes(DaAfficher $item): array
    {
        if (empty($item->getNumeroCde())) {
            return ['class' => 'text-center'];
        }

        return [
            'class'             => 'text-center commande-cellule commande',
            'data-commande-id'  => $item->getNumeroCde(),
            'data-num-da'       => $item->getNumeroDemandeAppro(),
            'data-num-or'       => $item->getNumeroOr(),
            'data-statut-bc'    => $item->getStatutCde(),
            'data-position-cde' => $item->getPositionBc(),
            'data-type-da'      => $item->getDaTypeId()
        ];
    }

    public function getIcons(): array
    {
        return $this->getAllIcons();
    }
}
