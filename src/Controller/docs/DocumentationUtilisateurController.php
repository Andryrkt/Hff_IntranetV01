<?php

namespace App\Controller\docs;

use App\Controller\Controller;
use App\Entity\admin\DocumentationUtilisateur;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Documentation utilisateur des modules : lecture pour tous,
 * modification (éditeur TinyMCE) réservée aux administrateurs HFF-ADMIN.
 */
class DocumentationUtilisateurController extends Controller
{
    private const BALISES_AUTORISEES = '<h1><h2><h3><h4><p><br><strong><b><em><i><u><s><ul><ol><li><table><thead><tbody><tr><th><td><a><blockquote><hr><span><div><img><sub><sup><code><pre>';

    // mêmes intitulés que le menu principal (MenuService::construireMenuPrincipal)
    private const RUBRIQUES = ['Général', 'Documentation', 'Reporting', 'Compta', 'RH', 'Matériel', 'Atelier', 'Magasin', 'Appro', 'IT', 'POL', 'Energie', 'HSE'];

    /**
     * Sommaire des guides par rubrique (brouillons visibles des seuls administrateurs).
     * Un administrateur crée un guide en saisissant son identifiant (?nouveau=identifiant).
     *
     * @Route("/doc/utilisateur", name="doc_utilisateur_index")
     */
    public function index(Request $request)
    {
        $estAdmin = $this->estAdmin();

        $nouveau = trim((string) $request->query->get('nouveau'));
        if ($estAdmin && preg_match('/^[a-z0-9-]+$/', $nouveau)) {
            $this->redirectToRoute('doc_utilisateur_edit', ['slug' => $nouveau]);
        }

        $criteres = $estAdmin ? [] : ['publie' => true];
        $guides = $this->getEntityManager()->getRepository(DocumentationUtilisateur::class)
            ->findBy($criteres, ['rubrique' => 'ASC', 'ordre' => 'ASC', 'titre' => 'ASC']);

        $guidesParRubrique = [];
        foreach ($guides as $guide) {
            $guidesParRubrique[$guide->getRubrique() ?: 'Général'][] = $guide;
        }

        return $this->render('doc/utilisateur/index.html.twig', [
            'guidesParRubrique' => $guidesParRubrique,
            'estAdmin'          => $estAdmin,
        ]);
    }

    /**
     * @Route("/doc/utilisateur/{slug}", name="doc_utilisateur_show", requirements={"slug"="[a-z0-9-]+"})
     */
    public function show(string $slug)
    {
        $estAdmin = $this->estAdmin();
        $documentation = $this->documentation($slug);

        return $this->render('doc/utilisateur/show.html.twig', [
            'slug'          => $slug,
            // un brouillon n'est montré qu'aux administrateurs
            'documentation' => $documentation && ($documentation->isPublie() || $estAdmin) ? $documentation : null,
            'estAdmin'      => $estAdmin,
        ]);
    }

    /**
     * @Route("/doc/utilisateur/{slug}/modifier", name="doc_utilisateur_edit", requirements={"slug"="[a-z0-9-]+"})
     */
    public function edit(string $slug, Request $request)
    {
        if (!$this->estAdmin()) {
            $this->redirectToRoute('doc_utilisateur_show', ['slug' => $slug]);
        }

        $documentation = $this->documentation($slug) ?? new DocumentationUtilisateur($slug);

        $form = $this->getFormFactory()->createBuilder()
            ->add('titre', TextType::class, ['label' => 'Titre', 'data' => $documentation->getTitre()])
            ->add('rubrique', ChoiceType::class, [
                'label'   => 'Rubrique',
                'choices' => array_combine(self::RUBRIQUES, self::RUBRIQUES),
                'data'    => $documentation->getRubrique() ?? 'Général',
            ])
            ->add('ordre', IntegerType::class, ['label' => 'Ordre dans la rubrique', 'required' => false, 'data' => $documentation->getOrdre()])
            ->add('publie', CheckboxType::class, ['label' => 'Publié (visible de tous)', 'required' => false, 'data' => $documentation->isPublie()])
            ->add('contenu', TextareaType::class, ['label' => false, 'required' => false, 'data' => $documentation->getContenu()])
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $documentation
                ->setTitre($form->get('titre')->getData())
                ->setRubrique($form->get('rubrique')->getData())
                ->setOrdre($form->get('ordre')->getData())
                ->setPublie((bool) $form->get('publie')->getData())
                ->setContenu($this->nettoyerHtml((string) $form->get('contenu')->getData()))
                ->marquerModifie($this->getUserName());

            $this->getEntityManager()->persist($documentation);
            $this->getEntityManager()->flush();

            $this->redirectToRoute('doc_utilisateur_show', ['slug' => $slug]);
        }

        return $this->render('doc/utilisateur/edit.html.twig', [
            'slug'          => $slug,
            'documentation' => $documentation,
            'form'          => $form->createView(),
        ]);
    }

    private function documentation(string $slug): ?DocumentationUtilisateur
    {
        return $this->getEntityManager()->getRepository(DocumentationUtilisateur::class)->findOneBy(['slug' => $slug]);
    }

    /**
     * Le contenu est affiché tel quel (|raw) : on ne garde que les balises de mise
     * en forme et on retire scripts, attributs on* et liens javascript:.
     * shortcut: filtrage par expressions régulières, à remplacer par un vrai
     * nettoyeur HTML (ex: symfony/html-sanitizer) si des non-admins éditent un jour.
     */
    private function nettoyerHtml(string $html): string
    {
        $html = strip_tags($html, self::BALISES_AUTORISEES);
        $html = preg_replace('/\s+on[a-z]+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/i', '', $html);

        return preg_replace('/(href|src)\s*=\s*(["\']?)\s*javascript:[^"\'>\s]*\2/i', '$1="#"', $html);
    }
}
