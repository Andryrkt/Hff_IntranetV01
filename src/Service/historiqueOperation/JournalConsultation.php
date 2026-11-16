<?php

namespace App\Service\historiqueOperation;

use Doctrine\DBAL\Connection;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Historique des consultations (table Log_utilisateur) pour toutes les pages HTML :
 * appelé par index.php après l'envoi de la réponse, pour les pages qui n'appellent pas
 * elles-mêmes Controller::logUserVisit() (celles-là gardent leurs paramètres choisis).
 */
final class JournalConsultation
{
    private static bool $dejaEnregistree = false;

    /** Appelé par Controller::logUserVisit() : la page a déjà enregistré sa consultation */
    public static function marquerEnregistree(): void
    {
        self::$dejaEnregistree = true;
    }

    public static function doitEnregistrer(Request $request, Response $response, ?array $userInfo): bool
    {
        $route = (string) $request->attributes->get('_route');

        return !self::$dejaEnregistree
            && !empty($userInfo['id'])
            && $route !== ''
            && $request->isMethod('GET')
            && !$request->isXmlHttpRequest()
            && $response->getStatusCode() === 200
            && !preg_match('/^(api_|export_)/', $route)
            && str_contains((string) ($response->headers->get('Content-Type') ?? 'text/html'), 'text/html');
    }

    /**
     * Ne bloque jamais la page : toute erreur est seulement journalisée.
     */
    public static function enregistrer(Connection $connexion, Request $request, Response $response, ?array $userInfo): void
    {
        try {
            if (!self::doitEnregistrer($request, $response, $userInfo)) {
                return;
            }
            $route = (string) $request->attributes->get('_route');
            $page  = $connexion->fetchAssociative('SELECT TOP 1 id, nom FROM Hff_pages WHERE nom_route = ?', [$route]) ?: null;
            $nom   = $connexion->fetchOne('SELECT nom_utilisateur FROM users WHERE id = ?', [(int) $userInfo['id']]);

            $connexion->insert('Log_utilisateur', [
                'utilisateur'             => $nom ?: ($userInfo['username'] ?? '-'),
                'nom_page'                => $page['nom'] ?? $route,
                'params'                  => self::parametres($request),
                // même fuseau que UserLogger (logUserVisit) : le PHP du serveur est en UTC
                'date_heure_consultation' => (new \DateTime('now', new \DateTimeZone('Indian/Antananarivo')))->format('Y-m-d H:i:s'),
                'id_utilisateur'          => (int) $userInfo['id'],
                'id_page'                 => $page['id'] ?? null,
                'machine_utilisateur'     => self::machine($request),
            ]);
        } catch (\Throwable $e) {
            error_log('JournalConsultation : ' . $e->getMessage());
        }
    }

    /** Paramètres de route et de recherche non vides, au format JSON comme logUserVisit() */
    public static function parametres(Request $request): ?string
    {
        $parametres = [];
        foreach ($request->attributes->all() as $cle => $valeur) {
            if ($cle[0] !== '_' && is_scalar($valeur) && $valeur !== '') {
                $parametres[$cle] = $valeur;
            }
        }
        foreach ($request->query->all() as $cle => $valeur) {
            if ($valeur !== '' && $valeur !== null && $valeur !== []) {
                $parametres[$cle] = $valeur;
            }
        }
        if (!$parametres) {
            return null;
        }
        $json = json_encode($parametres, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        // shortcut: paramètres tronqués au-delà de 2000 caractères (gros filtres), à revoir si on veut les rejouer
        return mb_strlen($json) > 2000 ? json_encode(['tronque' => mb_substr($json, 0, 1900)], JSON_UNESCAPED_UNICODE) : $json;
    }

    private static function machine(Request $request): string
    {
        $ip = (string) $request->getClientIp();

        return $ip !== '' ? (gethostbyaddr($ip) ?: $ip) : '-';
    }
}
