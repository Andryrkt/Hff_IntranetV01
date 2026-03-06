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

class DaListePresenter
{
    use MarkupIconTrait;
    use StatutBcTrait;

    private UrlGeneratorInterface $router;
    private FilesystemAdapter $cache;

    public function __construct(UrlGeneratorInterface $router)
    {
        $this->router = $router;
        $this->cache = new FilesystemAdapter('da_liste_presenter', 600); // 10 minutes
        $this->initStatutBcTrait();
    }

    /** 
     * Fonction pour préparer les données à afficher dans Twig 
     * @param DaAfficher[] $data données avant préparation
     * @param array $options Options (roles utilisateur, etc.)
     **/
    public function present(array $data, array $options = []): array
    {
        $datasPrepared = [];

        $daIcons = [
            DemandeAppro::TYPE_DA_AVEC_DIT         => $this->getIconDaAvecDIT(),
            DemandeAppro::TYPE_DA_DIRECT           => $this->getIconDaDirect(),
            DemandeAppro::TYPE_DA_REAPPRO_MENSUEL  => $this->getIconDaReapproMensuel(),
            DemandeAppro::TYPE_DA_REAPPRO_PONCTUEL => $this->getIconDaReapproPonctuel(),
            DemandeAppro::TYPE_DA_PARENT           => ''
        ];

        $safeIconSuccess = new Markup('<i class="fas fa-check text-success"></i>', 'UTF-8');
        $safeIconXmark   = new Markup('<i class="fas fa-xmark text-danger"></i>', 'UTF-8');
        $safeIconBan     = new Markup('<i class="fas fa-ban text-muted"></i>', 'UTF-8');

        $estAdmin   = $options['estAdmin'] ?? false;
        $estAppro   = $options['estAppro'] ?? false;
        $estAtelier = $options['estAtelier'] ?? false;

        foreach ($data as $item) {
            if (!$item instanceof DaAfficher) continue;

            // Clé de cache par ligne, incluant les rôles car les URLs en dépendent
            $cacheKey = 'da_list_row_' . $item->getId() . '_' . md5($item->getStatutDal() . $item->getStatutOr() . $item->getStatutCde() . ($estAdmin?'1':'0') . ($estAppro?'1':'0') . ($estAtelier?'1':'0'));

            $datasPrepared[] = $this->cache->get($cacheKey, function (ItemInterface $cacheItem) use ($item, $daIcons, $safeIconSuccess, $safeIconXmark, $safeIconBan, $estAdmin, $estAppro, $estAtelier) {
                
                $daTypeId = $item->getDaTypeId();
                $daViaOR = $daTypeId == DemandeAppro::TYPE_DA_AVEC_DIT;
                $daDirect = $daTypeId == DemandeAppro::TYPE_DA_DIRECT;
                $daReappro = $daTypeId == DemandeAppro::TYPE_DA_REAPPRO_MENSUEL;
                $daParent = $daTypeId == DemandeAppro::TYPE_DA_PARENT;
                $envoyeFrn = $item->getStatutCde() === DaSoumissionBc::STATUT_BC_ENVOYE_AU_FOURNISSEUR;

                // Calculs booléens
                $ajouterDA = $daViaOR && ($estAtelier || $estAdmin);
                $statutDASupprimable = [DemandeAppro::STATUT_SOUMIS_APPRO, DemandeAppro::STATUT_SOUMIS_ATE, DemandeAppro::STATUT_VALIDE];
                $supprimable = ($estAppro || $estAtelier || $estAdmin) && in_array($item->getStatutDal(), $statutDASupprimable) && ($daViaOR || $daDirect);
                $demandeDevis = ($estAppro || $estAdmin) && $item->getStatutDal() === DemandeAppro::STATUT_SOUMIS_APPRO && ($daViaOR || $daDirect);

                // URLs
                $urls = $this->buildItemUrls($item, $ajouterDA);

                // Formatage Statut OR
                $statutOR = $item->getStatutOr();
                if ($daViaOR && !empty($statutOR)) $statutOR = "OR - $statutOR";

                return [
                    'id'                   => $item->getId(),
                    'objet'                => $item->getObjetDal(),
                    'numDaParent'          => $item->getNumeroDemandeApproMere(),
                    'numeroDemandeAppro'   => $item->getNumeroDemandeAppro(),
                    'datype'               => $daIcons[$daTypeId] ?? $safeIconBan,
                    'numeroDemandeDit'     => $daViaOR ? $item->getNumeroDemandeDit() : $safeIconBan,
                    'numeroOr'             => $daDirect || $daParent ? $safeIconBan : $item->getNumeroOr(),
                    'niveauUrgence'        => $daReappro ? $safeIconBan : $item->getNiveauUrgence(),
                    'demandeur'            => $item->getDemandeur(),
                    'dateDemande'          => $item->getDateDemande() ? $item->getDateDemande()->format('d/m/Y') : '',
                    'statutDal'            => $item->getStatutDal(),
                    'statutOr'             => $statutOR,
                    'statutCde'            => $item->getStatutCde(),
                    'datePlannigOr'        => $daViaOR ? ($item->getDatePlannigOr() ? $item->getDatePlannigOr()->format('d/m/Y') : '') : $safeIconBan,
                    'nomFournisseur'       => $item->getNomFournisseur(),
                    'artConstp'            => $item->getArtConstp(),
                    'artRefp'              => $item->getArtRefp(),
                    'artDesi'              => $item->getArtDesi(),
                    'estDalr'              => $item->getEstDalr(),
                    'verouille'            => $item->getVerouille(),
                    'estFicheTechnique'    => $item->getEstFicheTechnique() ? $safeIconSuccess : $safeIconXmark,
                    'qteDem'               => $item->getQteDem() ?: '-',
                    'qteEnAttent'          => $item->getQteEnAttent() ?: '-',
                    'qteDispo'             => $item->getQteDispo() ?: '-',
                    'qteLivrer'            => $item->getQteLivrer() ?: '-',
                    'dateFinSouhaite'      => $item->getDateFinSouhaite() ? $item->getDateFinSouhaite()->format('d/m/Y') : 'N/A',
                    'dateLivraisonPrevue'  => $item->getDateLivraisonPrevue() ? $item->getDateLivraisonPrevue()->format('d/m/Y') : 'N/A',
                    'joursDispo'           => $item->getJoursDispo() ?? '',
                    'styleJoursDispo'      => ($item->getJoursDispo() && $item->getJoursDispo() < 0) ? 'text-danger' : '',
                    'styleStatutDA'        => $this->styleStatutDA[$item->getStatutDal()] ?? '',
                    'styleStatutOR'        => $this->styleStatutOR[$item->getStatutOr()] ?? '',
                    'styleStatutBC'        => $this->styleStatutBC[$item->getStatutCde()] ?? '',
                    'urlCreation'          => $urls['creation'],
                    'urlDetail'            => $urls['detail'],
                    'urlDelete'            => $urls['delete'],
                    'urlDemandeDevis'      => $urls['demandeDevis'],
                    'ajouterDA'            => $ajouterDA,
                    'supprimable'          => $supprimable,
                    'demandeDevis'         => $demandeDevis,
                    'statutValide'         => $item->getStatutDal() === DemandeAppro::STATUT_VALIDE,
                    'centrale'             => !$daViaOR ? $item->getDesiCentrale() : $safeIconBan,
                    'envoyeFrn'            => $envoyeFrn,
                    'aArtDesiAttributes'   => [
                        'href'              => $urls['designation'],
                        'class'             => 'designation-btn',
                        'data-numero-ligne' => $item->getNumeroLigne(),
                        'data-numero-da'    => $item->getNumeroDemandeAppro(),
                        'target'            => $urls['designation'] === "#" ? "_self" : "_blank"
                    ],
                    'aDtLivPrevAttributes' => [
                        'href'               => '#', "data-bs-toggle" => "modal", "data-bs-target" => "#dateLivraison",
                        "data-numero-cde"    => $item->getNumeroCde(),
                        "data-date-actuelle" => $item->getDateLivraisonPrevue() ? $item->getDateLivraisonPrevue()->format('Y-m-d') : '',
                    ],
                ];
            });
        }

        return $datasPrepared;
    }

    private function buildItemUrls(DaAfficher $item, bool $ajouterDA): array
    {
        $daTypeId = $item->getDaTypeId();
        
        $parametres = [
            'daId'           => $item->getDemandeAppro() ? ['id' => $item->getDemandeAppro()->getId()] : [],
            'daParentId'     => $item->getDemandeApproParent() ? ['id' => $item->getDemandeApproParent()->getId()] : [],
            'daId-0-ditId'   => $item->getDit() ? ['daId' => 0, 'ditId' => $item->getDit()->getId()] : [],
            'daId-ditId'     => $item->getDemandeAppro() && $item->getDit() ? ['daId' => $item->getDemandeAppro()->getId(), 'ditId' => $item->getDit()->getId()] : [],
            'numDa-numLigne' => ['numDa' => $item->getNumeroDemandeAppro(), 'ligne' => $item->getNumeroLigne()],
        ];

        // URL création
        $creationRoutes = [
            DemandeAppro::TYPE_DA_AVEC_DIT        => 'da_new_avec_dit',
            DemandeAppro::TYPE_DA_REAPPRO_MENSUEL => 'da_new_reappro_mensuel',
            DemandeAppro::TYPE_DA_PARENT          => 'da_new_achat'
        ];
        $urls['creation'] = ($ajouterDA && isset($creationRoutes[$daTypeId])) ? $this->router->generate($creationRoutes[$daTypeId], $parametres['daId-0-ditId']) : '#';

        // URL détail
        $detailRoutes = [
            DemandeAppro::TYPE_DA_AVEC_DIT         => 'da_detail_avec_dit',
            DemandeAppro::TYPE_DA_DIRECT           => 'da_detail_direct',
            DemandeAppro::TYPE_DA_REAPPRO_MENSUEL  => 'da_detail_reappro',
            DemandeAppro::TYPE_DA_REAPPRO_PONCTUEL => 'da_detail_reappro',
        ];
        $urls['detail'] = isset($detailRoutes[$daTypeId]) ? $this->router->generate($detailRoutes[$daTypeId], $parametres['daId']) : '#';

        // URL désignation
        $propositionRoutes = [
            DemandeAppro::TYPE_DA_AVEC_DIT        => 'da_proposition_ref_avec_dit',
            DemandeAppro::TYPE_DA_DIRECT          => 'da_proposition_direct',
            DemandeAppro::TYPE_DA_PARENT          => 'da_affectation_achat',
            DemandeAppro::TYPE_DA_REAPPRO_MENSUEL => 'da_validate_reappro_mensuel',
        ];
        
        if ($item->getStatutDal() === DemandeAppro::STATUT_EN_COURS_CREATION && isset($creationRoutes[$daTypeId])) {
            $params = ($daTypeId == DemandeAppro::TYPE_DA_AVEC_DIT) ? $parametres['daId-ditId'] 
                    : (($daTypeId == DemandeAppro::TYPE_DA_PARENT) ? $parametres['daParentId'] : $parametres['daId']);
            $urls['designation'] = $this->router->generate($creationRoutes[$daTypeId], $params);
        } else {
            $params = ($daTypeId == DemandeAppro::TYPE_DA_PARENT) ? $parametres['daParentId'] : $parametres['daId'];
            $urls['designation'] = isset($propositionRoutes[$daTypeId]) ? $this->router->generate($propositionRoutes[$daTypeId], $params) : '#';
        }

        // URL suppression
        $deleteRoutes = [
            DemandeAppro::TYPE_DA_AVEC_DIT  => 'da_delete_line_avec_dit',
            DemandeAppro::TYPE_DA_DIRECT    => 'da_delete_line_direct',
        ];
        $urls['delete'] = isset($deleteRoutes[$daTypeId]) ? $this->router->generate($deleteRoutes[$daTypeId], $parametres['numDa-numLigne']) : '#';

        // URL demande de devis
        $urls['demandeDevis'] = ($item->getDemandeAppro()) ? $this->router->generate('da_demande_devis_en_cours', $parametres['daId']) : '#';

        return $urls;
    }

    public function getIcons(): array
    {
        return $this->getAllIcons();
    }
}
