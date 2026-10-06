# structure de fichier

sql (ce sont les requets de creation de table et modification de table)

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
      $user = $this->getUser();

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
    $genererPdfDit->copyInterneToDOCUWARE($pdfDemandeInterventions->getNumeroDemandeIntervention(),str_replace("-", "", $pdfDemandeInterventions->getAgenceServiceEmetteur()));

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

src
|API (c'est controller mais transforme les donners en JSON pour qu'on peut l'utiliser en JS)
|Controller (c'est le point d'entrer de l'application | tous les noms de fichiers sont terminer par "Controller.php" )

        |admin (tout ce qui n'est pas utiliser par l'utilisateur)
        |badm
        |dit
            DitController.php
                - revoie vers l'affichage du template

                ```php
                self::$twig->display('dit/new.html.twig', [
                    'form' => $form->createView()
                ]);
                ```
                - pour l'affichage du formulaire, il faut :

                ```php
                $form = self::$validator->createBuilder(demandeInterventionType::class, $demandeIntervention)->getForm();
                ```
                - control d'accées

                ```php
                    //verification si user connecter
                    $this->verifierSessionUtilisateur();

                    //recuperation de l'utilisateur connecter
                    $user = $this->getUser();

                    /** Autorisation accées */
                    $this->autorisationAcces($user);
                    /** FIN AUtorisation acées */
                ```
                - pour l'initialisation (ex: formulaire)

                ```php
                    //il faut instancier l'entiter (creation de nouveau objet)
                    $demandeIntervention = new DemandeIntervention();

                    //stocker les valeurs initial dans le nouveau objet
                    $this->initialisationForm($demandeIntervention, self::$em);
                ```

                - lorsqu'on soumettre le formulaire, on recupère les données par :

                ```php
                    $form->handleRequest($request);

                    if($form->isSubmitted() && $form->isValid())
                    {
                        $form->getData();
                    }
                ```
                - pour envoye les données dans la base de donnée

                ```php
                    self::$em->persist($insertDemandeInterventions);
                    self::$em->flush();
                ```

                - pour la creation pdf

                ```php
                $genererPdfDit = new GenererPdfDit();
                    $genererPdfDit->genererPdfDit($pdfDemandeInterventions, $historiqueMateriel);
                ```
                - notification et revoyer vers une autre page

                ```php
                $this->sessionService->set('notification',['type' => 'success', 'message' => 'Votre demande a été enregistrée']);
                    $this->redirectToRoute("dit_index");
                ```
            DitlisteController.php
                - recupération des données (soit on utilise le repository soit le model)
                    + recupération par répository (methode par defaut : find($id), findAll(), findOneBy([$critere]), findBy($critere))

                        ```php
                        $paginationData = self::$em->getRepository(DemandeIntervention::class)->findPaginatedAndFiltered($page, $limit, $ditSearch, $option);
                        ```
                    + recupération par model

                        ```php
                            //instancier le model
                            $ditModel = new DitModel()
                            //utiliser le methode convenable
                            $recupTout = $ditModel->recuperationNumSerieNumParc($matricule)
                        ```

                - evoyer les données recupérer pour affichage

                    ```php
                        self::$twig->display('dit/list.html.twig', [
                            'data' => $paginationData['data'],
                            'currentPage' => $paginationData['currentPage'],
                            'totalPages' =>$paginationData['lastPage'],
                            'resultat' => $paginationData['totalItems'],
                            'statusCounts' => $paginationData['statusCounts'],
                        ]);
                    ```

                - formulaire de recherche
        |dom
        |dw
        |magasin
        |planning
        |tik
        |Traits
    |Entity (tous les noms de fichiers sont terminer par ".php" | c'est le mappage avec le base de donnée)
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
            demandeInterventiontype.php
                methode obligatoire :

                ```php
                public function buildForm(FormBuilderInterface $builder, array $options)
                {}

                    public function configureOptions(OptionsResolver $resolver)
                    {
                        $resolver->setDefaults([
                            'data_class' => DemandeIntervention::class,
                        ]);
                    }
                ```
        |dom
        |dw
        |magasin
        |planning
        |tik
        |Traits

Views
|css
|js
|templates
|admin
|badm
|dit
|shared
new.html.twig

            ```php
            {{ form_start(form, { 'attr': { 'id': 'myForm' } }) }}
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

                    //boutton
    				<a onclick="return confirm('Veuillez vérifier attentivement avant d\'envoyer.')">
    					<button type="submit" class="btn bouton" id="formDit">
    						<i class="fas fa-save"></i>
    						Enregistrer
    					</button>
    				</a>
    			{{ form_end(form) }}
            ```
            duplication.html.twig
            list.html.twig
            validation.html.twig (detail)
            DitRiSoumisAValidation.html.twig
            DitFactureSoumisAValidation.html.twig
            DitCdeSoumisAValidation.html.twig
        |dom
        |dw
        |magasin
        |main
        |partials
        |planning
        |tik
        baseTemplate.html.twig
        macroForm.html.twig
        signin.html.twig

# pour nouvelle projet, voici les étapes à suivre

    - creation d'entité et ses relation avec d'autre entité
    - creation de repository
    - creation de form
    - creation de controller
    - creation template
