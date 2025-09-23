# Exemple concret : Contrôleur avec Voter

## DomFirstController avec Voter

```php
<?php

namespace App\Controller\dom;

use App\Controller\Controller;
use App\Form\dom\DomForm1Type;
use App\Entity\admin\Application;
use App\Entity\admin\utilisateur\User;
use App\Entity\admin\dom\SousTypeDocument;
use App\Service\security\SecurityService;
use App\Security\Voter\ApplicationVoter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/rh/ordre-de-mission")
 */
class DomFirstController extends Controller
{
    private $securityService;

    public function __construct(SecurityService $securityService)
    {
        parent::__construct();
        $this->securityService = $securityService;
    }

    /**
     * @Route("/dom-first-form", name="dom_first_form")
     * @IsGranted("ACCESS", subject="DOM")
     */
    public function firstForm(Request $request)
    {
        // L'autorisation est vérifiée automatiquement par l'annotation @IsGranted
        
        //récupération de l'utilisateur connecté
        $user = $this->getUser();

        // Récupération de l'agence et du service de l'utilisateur connecté
        $agenceServiceIps = $this->agenceServiceIpsString();

        //INITIALISATION 
        $dom = new Dom();
        $dom = $this->initialisationDom($dom, $agenceServiceIps, $user);

        //CREATION DU FORMULAIRE
        $form = $this->getFormFactory()->createBuilder(DomForm1Type::class, $dom)->getForm();
        //TRAITEMENT DU FORMULAIRE
        $this->traitemementForm($form, $request);

        //HISTORISATION DE LA PAGE
        $this->logUserVisit('dom_first_form');
        
        //RENDU DE LA VUE
        return $this->render('doms/firstForm.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/dom-create", name="dom_create")
     * @IsGranted("CREATE", subject="DOM")
     */
    public function create(Request $request)
    {
        // Seuls les utilisateurs avec permission CREATE peuvent accéder
        // Votre logique de création ici...
    }

    // ... autres méthodes
}
```

## DomSecondController avec Voter

```php
<?php

namespace App\Controller\dom;

use App\Entity\dom\Dom;
use App\Controller\Controller;
use App\Form\dom\DomForm2Type;
use App\Entity\admin\Application;
use App\Controller\Traits\dom\DomsTrait;
use App\Controller\Traits\FormatageTrait;
use App\Controller\Traits\AutorisationTrait;
use App\Service\historiqueOperation\HistoriqueOperationDOMService;
use App\Model\dom\DomModel;
use App\Service\FusionPdf;
use App\Service\security\SecurityService;
use App\Security\Voter\ApplicationVoter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/rh/ordre-de-mission")
 */
class DomSecondController extends Controller
{
    use FormatageTrait;
    use DomsTrait;
    use AutorisationTrait;

    private $historiqueOperation;
    private $DomModel;
    private $fusionPdf;
    private $securityService;

    public function __construct(
        DomModel $domModel, 
        FusionPdf $fusionPdf, 
        SecurityService $securityService
    ) {
        parent::__construct();
        $this->historiqueOperation = new HistoriqueOperationDOMService(
            $this->getEntityManager(), 
            $this->getSessionService()
        );
        $this->DomModel = $domModel;
        $this->fusionPdf = $fusionPdf;
        $this->securityService = $securityService;
    }

    /**
     * @Route("/dom-second-form", name="dom_second_form")
     * @IsGranted("ACCESS", subject="DOM")
     */
    public function secondForm(Request $request)
    {
        // L'autorisation est vérifiée automatiquement par l'annotation @IsGranted
        
        //recuperation de l'utilisateur connecter
        $user = $this->getUser();

        $dom = new Dom();
        //recupération des données qui vient du formulaire 1
        $form1Data = $this->getSessionService()->get('form1Data', []);
        $codeSousTypeDoc = $form1Data['sousTypeDocument']->getCodeSousType();

        /** INITIALISATION des données  */
        $this->initialisationSecondForm($form1Data, $this->getEntityManager(), $dom);
        $criteria = $this->criteria($form1Data, $this->getEntityManager());

        $is_temporaire = $form1Data['salarier'];

        $form = $this->getFormFactory()->createBuilder(DomForm2Type::class, $dom)->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Vérification supplémentaire pour la création
            if (!$this->securityService->canCreate('DOM')) {
                throw new AccessDeniedException('Permission de création refusée');
            }

            $domForm = $form->getData();
            $this->enregistrementValeurdansDom($dom, $domForm, $form, $form1Data, $this->getEntityManager(), $user);

            // ... reste de la logique
        }

        $this->logUserVisit('dom_second_form');

        return $this->render('doms/secondForm.html.twig', [
            'form'          => $form->createView(),
            'is_temporaire' => $is_temporaire,
            'criteria'      => $criteria,
            'codeSousTypeDoc'   => $codeSousTypeDoc
        ]);
    }

    /**
     * @Route("/dom-edit/{id}", name="dom_edit")
     * @IsGranted("EDIT", subject="DOM")
     */
    public function edit($id, Request $request)
    {
        // Seuls les utilisateurs avec permission EDIT peuvent accéder
        // Votre logique de modification ici...
    }

    /**
     * @Route("/dom-delete/{id}", name="dom_delete")
     * @IsGranted("DELETE", subject="DOM")
     */
    public function delete($id)
    {
        // Seuls les utilisateurs avec permission DELETE peuvent accéder
        // Votre logique de suppression ici...
    }
}
```

## Template Twig avec vérifications d'autorisation

```twig
{# templates/doms/firstForm.html.twig #}

{% extends 'base.html.twig' %}

{% block content %}
    <h1>Formulaire DOM</h1>

    {# Vérification d'autorisation dans le template #}
    {% if is_granted('CREATE', 'DOM') %}
        <div class="alert alert-info">
            Vous avez la permission de créer des DOM.
        </div>
    {% endif %}

    {{ form_start(form) }}
        {{ form_widget(form) }}
        
        {# Boutons conditionnels selon les permissions #}
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">
                {% if is_granted('CREATE', 'DOM') %}
                    Créer le DOM
                {% else %}
                    Continuer
                {% endif %}
            </button>
            
            {% if is_granted('EDIT', 'DOM') %}
                <button type="button" class="btn btn-secondary">Modifier</button>
            {% endif %}
        </div>
    {{ form_end(form) }}

    {# Liens de navigation conditionnels #}
    <nav class="mt-3">
        {% if is_granted('ACCESS', 'TIK') %}
            <a href="{{ path('tik_index') }}" class="btn btn-outline-primary">Accéder au TIK</a>
        {% endif %}
        
        {% if is_granted('ACCESS', 'MAGASIN') %}
            <a href="{{ path('magasin_index') }}" class="btn btn-outline-secondary">Accéder au Magasin</a>
        {% endif %}
    </nav>
{% endblock %}
```

## Avantages de cette approche

### ✅ **Code plus propre**
- Les annotations `@IsGranted` sont claires et expressives
- Séparation nette entre sécurité et logique métier

### ✅ **Performance optimisée**
- Cache automatique des décisions d'autorisation
- Évite les requêtes répétitives

### ✅ **Flexibilité maximale**
- Support de différents niveaux d'autorisation (ACCESS, CREATE, EDIT, DELETE)
- Logique centralisée dans le Voter

### ✅ **Intégration native**
- Compatible avec tous les composants Symfony
- Support des templates Twig avec `is_granted()`

### ✅ **Maintenabilité**
- Un seul endroit pour modifier la logique d'autorisation
- Tests unitaires simplifiés
