# structure de fichier

## sql (ce sont les requets de creation de table et modification de table)

## Public (les images)

Public/ └── images/

## src

### Controller

c'est le point d'entrer de l'application | tous les noms de fichiers sont terminer par "Controller.php"

src/Controller/
├── admin/
├── badm/
├── dit/
├── dom/
├── dw/
├── magasin/
├── planning/
├── tik/
└── Traits/

#### admin

    tout ce qui n'est pas utiliser par l'utilisateur

userController.php

- Nom d'utilisateur
- numero Matricule
- email
- role
- application
- sociétes
- code sage
- nom personnel => matricule
- agence autoriser
- service autoriser

#### badm

#### dit

    DitController.php (classe qui herite de la classe Controller)

- chaque methode du controller doivent avoir une route

  ```php
  /**
   * @Route("/dit/new", name="dit_new")
   */
  ```

- verification et controle d'accés

  ```php
  //verification si user connecter
      $this->verifierSessionUtilisateur();

      //recuperation de l'utilisateur connecter
      $userId = $this->sessionService->get('user_id');
      $user = self::$em->getRepository(User::class)->find($userId);

      /** Autorisation accées */
      $this->autorisationAcces($user);
      /** FIN AUtorisation acées */
  ```

- instancier l'entité

  ```php
  $demandeIntervention = new DemandeIntervention();
  ```

- initialisation de l'entité

  ```php
  //INITIALISATION DU FORMULAIRE
      $this->initialisationForm($demandeIntervention, self::$em);
  ```

- affichage formulaire

  ```php
  //AFFICHE LE FORMULAIRE
          $form = self::$validator->createBuilder(demandeInterventionType::class, $demandeIntervention)->getForm();
  //AFFICHE LE FORMULAIRE
          $form = self::$validator->createBuilder('App\Form\dit\demandeInterventionType')->getForm();
  ```

- renvoie la template

```php
 self::$twig->display('dit/new.html.twig', [
    'form' => $form->createView()
]);
```

- lorsqu'on soumi la formulaire

```php
    $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
           $dits =  $form->getData();
        }
```

- envoie les donnée dans la base de donnée

```php
    self::$em->persist($insertDemandeInterventions);
    self::$em->flush();
```

-modification de la base de donnée

```php

//recuperation de la ligne à modifier
$application = self::$em->getRepository(Application::class)->findOneBy(['codeApp' => 'DIT']);
//nouveau valeur
$application->setDerniereId($dits->getNumeroDemandeIntervention());
            // Persister l'entité Application (modifie la colonne derniere_id dans le table applications)
            self::$em->persist($application);
            self::$em->flush();
```

-creation pdf

```PHP
    $pdfDemandeInterventions = $this->pdfDemandeIntervention($dits, $demandeIntervention);
    //récupération des historique de materiel (informix)
    $historiqueMateriel = $this->historiqueInterventionMateriel($dits);
    //genere le PDF
    $genererPdfDit = new GenererPdfDit();
    $genererPdfDit->genererPdfDit($pdfDemandeInterventions, $historiqueMateriel);

    //envoie des pièce jointe dans une dossier et la fusionner
    $this->envoiePieceJoint($form, $dits, $this->fusionPdf);

    //ENVOYER le PDF DANS DOXCUWARE
    $genererPdfDit->copyInterneToDOXCUWARE($pdfDemandeInterventions->getNumeroDemandeIntervention(),str_replace("-", "", $pdfDemandeInterventions->getAgenceServiceEmetteur()));

```

- notification et rediretion

```php
 $this->sessionService->set('notification',['type' => 'success', 'message' => 'Votre demande a été enregistrée']);
            $this->redirectToRoute("dit_index");
```

#### dom

#### dw

#### magasin

#### planning

#### tik

#### Traits

### Entity (tous les noms de fichiers sont terminer par ".php")

        |admin
        |badm
        |dit
        |dom
        |dw
        |magasin
        |planning
        |tik
        |Traits

### Form (tous les noms de fichiers sont terminer par "Type.php")

        |admin
        |badm
        |dit
        |dom
        |dw
        |magasin
        |planning
        |tik
        |Traits

### Model (tous les noms de fichiers sont terminer par "Model.php")

### Repository (tous les noms de fichiers sont terminer par "Repository.php")

### Service (tous les noms de fichiers sont terminer par "Service.php")

#### genererPdf

    genererPdfDit.php
    DOIT HERITER LA CLASSE class GeneratePdf

## Views

### css

### js

### templates

#### dit

    new.html.twig

```php
{{ form_start(form) }}
				{{ form_errors(form)}}

				<div class="">
					{{ form_row(form.objetDemande)}}
					{{ form_row(form.detailDemande)}}
				</div>


				<div class="row">
					<div class="col-12 col-md-2">{{ form_row(form.typeDocument) }}</div>
					<div class="col-12 col-md-2">{{ form_row(form.categorieDemande) }}</div>
					<div class="col-12 col-md-2">{{ form_row(form.internetExterne)}}</div>
					<div class="col-12 col-md-2">{{ form_row(form.demandeDevis)}}</div>
					<div class="col-12 col-md-2">{{ form_row(form.livraisonPartiel)}}</div>
					<div class="col-12 col-md-2">{{ form_row(form.avisRecouvrement)}}</div>
				</div>


				<div class="row">
					<div class="col-12 col-md-6">


						{{ macroForm.sousTitre('Agence et Service', {class: 'sousTitre'})}}
						<div class="row">
							<div class="col-12 col-md-6 mt-2">Débiteur</div>
							<div class="col-12 col-md-6 mt-2">Emetteur</div>
						</div>

						<div class="row">
							<div class="col-12 col-md-6">
								{{ form_row(form.agence)}}
								{{ form_row(form.service)}}

							</div>
							<div class="col-12 col-md-6">
								{{ form_row(form.agenceEmetteur)}}
								{{ form_row(form.serviceEmetteur)}}
							</div>

						</div>


						{{ macroForm.sousTitre('Info Client', {class: 'sousTitre'})}}
						<div class="row">
							<div class="col-12 col-md-4">
								{{ form_row(form.nomClient)}}
							</div>
							<div class="col-12 col-md-4">
								{{ form_row(form.numeroTel)}}
							</div>
							<div class="col-12 col-md-4">
								{{ form_row(form.clientSousContrat)}}
							</div>
						</div>
						{{ macroForm.sousTitre('Information Matériel', {class: 'sousTitre'})}}
						<div class="row">
							<div class="col-12 col-md-4">
								{{ form_row(form.idMateriel)}}
							</div>
							<div class="col-12 col-md-4">
								{{ form_row(form.numSerie)}}
							</div>
							<div class="col-12 col-md-4">
								{{ form_row(form.numParc)}}
							</div>
						</div>
						<div class="row">
							<span id="erreur"></span>
						</div>


						<div>
							<ul>
								<div class="row">
									<div class="col-12 col-md-6">
										<li class="fw-bold">Constructeur :
											<div class="border border-secondary border-3 rounded px-4 bg-secondary-subtle" id="constructeur"></div>
										</li>
										<li class="fw-bold">Désignation :
											<div class="border border-secondary border-3 rounded px-4 bg-secondary-subtle" id="designation"></div>
										</li>
										<li class="fw-bold">KM :
											<div class="border border-secondary border-3 rounded px-4 bg-secondary-subtle" id="km"></div>
										</li>
									</div>
									<div class="col-12 col-md-6">
										<li class="fw-bold">Modèle :
											<div class="border border-secondary border-3 rounded px-4 bg-secondary-subtle" id="model"></div>
										</li>
										<li class="fw-bold">Casier :
											<div class="border border-secondary border-3 rounded px-4 bg-secondary-subtle" id="casier"></div>
										</li>
										<li class="fw-bold">Heures :
											<div class="border border-secondary border-3 rounded px-4 bg-secondary-subtle" id="heures"></div>
										</li>
									</div>

								</div>

							</ul>
						</div>

					</div>
					<div class="col-12 col-md-6">
						{{ macroForm.sousTitre('Intervention', {class: 'sousTitre'})}}
						<div class="row">
							<div class="col-12 col-md-6">
								{{ form_label(form.idNiveauUrgence, '<a href="#" data-bs-toggle="modal" data-bs-target="#niveauUrgence" data-id="{{item.numeroDemandeIntervention}}" id="numOr">Niveau d\'urgence</a>', { 'label_html': true }) }}
								{{ form_widget(form.idNiveauUrgence) }}
								</div>
								<div class="col-12 col-md-6">
									{{ form_row(form.datePrevueTravaux)}}
								</div>
							</div>
							{{ macroForm.sousTitre('Réparation', {class: 'sousTitre'})}}
							<div class="row">
								<div class="col-12 col-md-6">
									{{ form_row(form.typeReparation) }}

								</div>
								<div class="col-12 col-md-6">
									{{ form_row(form.reparationRealise) }}
								</div>

							</div>


							{{ macroForm.sousTitre('Pièces Jointes', {class: 'sousTitre'})}}

							{{ form_row(form.pieceJoint01)}}
							{{ form_errors(form.pieceJoint01)}}

							{{ form_row(form.pieceJoint02)}}
							{{ form_errors(form.pieceJoint02) }}

							{{ form_row(form.pieceJoint03)}}
							{{ form_errors(form.pieceJoint03) }}
						</div>

					</div>

					<a onclick="return confirm('Veuillez vérifier attentivement avant d\'envoyer.')">
						<button type="submit" class="btn bouton" id="formDit">
							<i class="fas fa-save"></i>
							Enregistrer
						</button>
					</a>
					{{ form_end(form) }}
```
