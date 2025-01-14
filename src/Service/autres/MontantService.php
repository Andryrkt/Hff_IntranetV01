<?php

namespace App\Service\autres;

class MontantService
{
    public function traiterDonnees(array $donneesAvant, array $donneesApres, array $config): array
    {
        $recapAvantApres = $this->comparerDonnees($donneesAvant, $donneesApres, $config['compareKeys']);
        $recapAvecStatuts = $this->ajouterStatuts($recapAvantApres, $config['compareKeys']);

        return [
            'avantApres' => $recapAvecStatuts,
            'totauxAvantApres' => $this->calculerTotaux($recapAvecStatuts, $config['totauxKeys']),
            'statistiques' => $this->calculerStatistiques($recapAvecStatuts),
        ];
    }

    private function comparerDonnees(array $avant, array $apres, array $compareKeys): array
    {
        $resultat = [];
        foreach ($apres as $index => $itemApres) {
            $itemAvant = $avant[$index] ?? null;
            $resultat[] = $this->genererRecapitulatif($itemAvant, $itemApres, $compareKeys);
        }
        return $resultat;
    }

    private function genererRecapitulatif(?object $avant, object $apres, array $keys): array
    {
        $recap = [];
        foreach ($keys as $key) {
            $recap[$key . 'Av'] = $avant ? $avant->{'get' . ucfirst($key)}() : 0;
            $recap[$key . 'Ap'] = $apres->{'get' . ucfirst($key)}();
        }
        return $recap;
    }

    private function ajouterStatuts(array $recapAvantApres, array $keys): array
    {
        foreach ($recapAvantApres as &$ligne) {
            $isSupp = true;
            $isNouv = true;
            $isModif = false;

            foreach ($keys as $key) {
                $valAvant = $ligne[$key . 'Av'];
                $valApres = $ligne[$key . 'Ap'];

                if ($valAvant > 0) {
                    $isNouv = false; // Ce n'est pas un nouveau
                }

                if ($valApres > 0) {
                    $isSupp = false; // Ce n'est pas supprimé
                }

                if ($valAvant !== $valApres) {
                    $isModif = true; // C'est modifié
                }
            }

            if ($isNouv) {
                $ligne['statut'] = 'Nouv';
            } elseif ($isSupp) {
                $ligne['statut'] = 'Supp';
            } elseif ($isModif) {
                $ligne['statut'] = 'Modif';
            } else {
                $ligne['statut'] = '';
            }
        }

        return $recapAvantApres;
    }

    private function calculerTotaux(array $donnees, array $keys): array
    {
        $totaux = array_fill_keys($keys, 0);
        foreach ($donnees as $ligne) {
            foreach ($keys as $key) {
                $totaux[$key] += $ligne[$key];
            }
        }
        return $totaux;
    }

    private function calculerStatistiques(array $donnees): array
    {
        $statistiques = ['nouveaux' => 0, 'supprimes' => 0, 'modifies' => 0];
        foreach ($donnees as $ligne) {
            switch ($ligne['statut']) {
                case 'Nouv':
                    $statistiques['nouveaux']++;
                    break;
                case 'Supp':
                    $statistiques['supprimes']++;
                    break;
                case 'Modif':
                    $statistiques['modifies']++;
                    break;
            }
        }
        return $statistiques;
    }
}
