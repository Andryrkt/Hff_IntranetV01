Utilisation avec votre format spécifique

```php

// Dans votre contrôleur
$numDevis = "DEV2023-001";
$numeroVersion = "v2";
$suffix = "final";

$fichiers = $fichierService->enregistrementFichier($form, [
'repertoire' => $this->cheminDeBase . '/' . $numDa,
'generer_nom_callback' => [FichierService::class, 'genererNomVerificationPrix'],
'variables' => [
'numDevis' => $numDevis,
'numeroVersion' => $numeroVersion,
'suffix' => $suffix
]
]);
```

Alternative avec une fonction anonyme

```php

$fichiers = $fichierService->enregistrementFichier($form, [
'repertoire' => $this->cheminDeBase . '/' . $numDa,
'generer_nom_callback' => function(UploadedFile $file, int $index, string $extension, array $variables) {
$numDevis = $variables['numDevis'] ?? 'inconnu';
$numeroVersion = $variables['numeroVersion'] ?? 'v1';
$suffix = $variables['suffix'] ?? $index;

        return 'verificationprix_' . $numDevis . '-' . $numeroVersion . '#' . $suffix . '.' . $extension;
    },
    'variables' => [
        'numDevis' => $numDevis,
        'numeroVersion' => $numeroVersion,
        'suffix' => $suffix
    ]

]);
```

Alternative avec format string

```php

$fichiers = $fichierService->enregistrementFichier($form, [
'repertoire' => $this->cheminDeBase . '/' . $numDa,
'format*nom' => 'verificationprix*{numDevis}-{numeroVersion}#{suffix}.{extension}',
'variables' => [
'numDevis' => $numDevis,
'numeroVersion' => $numeroVersion,
'suffix' => $suffix
]
]);
```
